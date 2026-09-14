<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Lease;
use App\Models\User;
use App\Notifications\ContractReadyToSign;
use App\Notifications\ContractSigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ContractController extends Controller
{
    // Admin: prepare (or revise) the contract for a lease, with a preview of the generated document
    public function create(Request $request, Lease $lease)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $lease->load('property.owner', 'tenant', 'contract');
        $contract = $lease->contract ?? $this->newContract($request, $lease);
        abort_if($contract->isSigned(), 403, __('This contract has been signed and can no longer be edited.'));

        $contract->setRelation('lease', $lease);

        return view('contracts.create', [
            'lease' => $lease,
            'contract' => $contract,
            'preview' => $contract->render(),
        ]);
    }

    public function store(Request $request, Lease $lease)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'special_conditions' => ['nullable', 'string', 'max:5000'],
        ]);

        $contract = $lease->contract ?? $this->newContract($request, $lease);
        abort_if($contract->isSigned(), 403, __('This contract has been signed and can no longer be edited.'));

        $contract->special_conditions = $data['special_conditions'] ?? null;
        $contract->save();

        // A contract the tenant already has must be re-sent so they sign what they read
        if ($request->boolean('send') || $contract->isSent()) {
            return $this->send($request, $contract);
        }

        return redirect()->route('contracts.create', $lease)->with('status', __('Draft saved.'));
    }

    public function send(Request $request, Contract $contract)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($contract->isSigned(), 403, __('This contract has already been signed.'));

        $body = $contract->render();

        $contract->update([
            'body' => $body,
            'body_hash' => hash('sha256', $body),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $tenant = $contract->lease->tenant;
        $tenant->notify(new ContractReadyToSign($contract));

        return redirect()->route('contracts.show', $contract)->with('status', __('Contract sent to :name for signature.', ['name' => $tenant->name]));
    }

    public function show(Request $request, Contract $contract)
    {
        $contract->load('lease.property.owner', 'lease.tenant', 'creator');
        $this->authorizeView($request, $contract);

        return view('contracts.show', [
            'contract' => $contract,
            'document' => $contract->body ?? $contract->render(), // drafts have no snapshot yet
        ]);
    }

    public function sign(Request $request, Contract $contract)
    {
        $user = $request->user();
        abort_unless($user->isTenant() && $contract->lease->tenant_id === $user->id, 403);
        abort_unless($contract->isSent(), 400, __('This contract is not awaiting a signature.'));

        $request->validate([
            'agree' => ['accepted'],
            'signature' => ['required', 'string', 'starts_with:data:image/png;base64,', 'max:400000'],
        ], [
            'agree.accepted' => __('Please confirm you have read and agree to the contract.'),
            'signature.required' => __('Please draw your signature.'),
        ]);

        $png = base64_decode(substr($request->input('signature'), strlen('data:image/png;base64,')), true);
        if ($png === false || ! str_starts_with($png, "\x89PNG")) {
            throw ValidationException::withMessages(['signature' => __('Your signature could not be read. Please draw it again.')]);
        }

        // The document must be exactly the one that was sent
        abort_unless(hash_equals((string) $contract->body_hash, hash('sha256', (string) $contract->body)), 409, __('This contract changed after it was sent. Please contact the office.'));

        $contract->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signature_data' => $request->input('signature'),
            'signer_ip' => $request->ip(),
            'signer_user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        Notification::send(User::activeAdmins()->get(), new ContractSigned($contract));

        return redirect()->route('contracts.show', $contract)->with('status', __('Contract signed. You can come back to it anytime.'));
    }

    // Tenant shortcut from the sidebar
    public function mine(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isTenant(), 403);

        $contract = Contract::whereHas('lease', fn ($q) => $q->where('tenant_id', $user->id))
            ->where('status', '!=', 'draft')
            ->latest('sent_at')
            ->first();

        return $contract
            ? redirect()->route('contracts.show', $contract)
            : view('contracts.none');
    }

    private function newContract(Request $request, Lease $lease): Contract
    {
        return new Contract([
            'lease_id' => $lease->id,
            'reference' => Contract::generateReference($lease),
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);
    }

    private function authorizeView(Request $request, Contract $contract): void
    {
        $user = $request->user();

        if ($user->isAdmin()) return;
        // Drafts stay internal until sent
        if ($contract->isDraft()) abort(403);
        if ($user->isTenant() && $contract->lease->tenant_id === $user->id) return;
        if ($user->isOwner() && $contract->lease->property->owner_id === $user->id) return;

        abort(403);
    }
}
