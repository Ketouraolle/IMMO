<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Notifications\Notification;

class ContractSigned extends Notification
{
    public function __construct(public Contract $contract) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $lease = $this->contract->loadMissing('lease.property', 'lease.tenant')->lease;

        return [
            'icon' => 'bi-patch-check',
            'title' => 'Contract signed',
            'body' => "{$lease->tenant->name} signed the contract for {$lease->property->name}.",
            'url' => route('contracts.show', $this->contract),
        ];
    }
}
