<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Notifications\Notification;

class ContractReadyToSign extends Notification
{
    public function __construct(public Contract $contract) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $property = $this->contract->loadMissing('lease.property')->lease->property;

        return [
            'icon' => 'bi-pen',
            'title' => 'Your contract is ready to sign',
            'body' => "Please read and sign the lease contract for {$property->name}.",
            'url' => route('contracts.show', $this->contract),
        ];
    }
}
