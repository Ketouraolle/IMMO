<?php

namespace App\Models;

use App\Support\Locale;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    public const STATUS_LABELS = ['draft' => 'Draft', 'sent' => 'Awaiting signature', 'signed' => 'Signed'];

    protected $fillable = [
        'lease_id', 'reference', 'special_conditions', 'body', 'body_hash', 'status', 'created_by',
        'sent_at', 'signed_at', 'signature_data', 'signer_ip', 'signer_user_agent',
    ];

    protected $hidden = ['signature_data'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isSent(): bool { return $this->status === 'sent'; }
    public function isSigned(): bool { return $this->status === 'signed'; }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? ucfirst($this->status));
    }

    public static function generateReference(Lease $lease): string
    {
        return 'CTR-'.now()->format('Ym').'-'.str_pad($lease->id, 5, '0', STR_PAD_LEFT);
    }

    /** The contract is written in the tenant's language (their saved preference, else the app default). */
    public function documentLocale(): string
    {
        $this->loadMissing('lease.tenant');

        return $this->lease->tenant->locale ?? config('app.locale');
    }

    /** Render the contract document from the current lease data. */
    public function render(): string
    {
        $this->loadMissing('lease.property.owner', 'lease.tenant', 'creator');

        return Locale::using($this->documentLocale(), fn () => view('contracts.template', [
            'contract' => $this,
            'lease' => $this->lease,
        ])->render());
    }
}
