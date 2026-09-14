<?php

namespace App\Notifications;

use App\Models\VisitRequest;
use Illuminate\Notifications\Notification;

class VisitRequested extends Notification
{
    public function __construct(public VisitRequest $visitRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $vr = $this->visitRequest->loadMissing('property');

        $when = $vr->visit_date
            ? ' on '.$vr->visit_date->format('D d M').' at '.$vr->visitTimeLabel()
            : '';

        $payment = match ($vr->payment_status) {
            'paid' => 'Fee paid via '.$vr->paymentMethodLabel(),
            'unpaid' => 'Fee to collect at the visit',
            default => 'No visit fee',
        };

        return [
            'icon' => 'bi-calendar2-check',
            'title' => 'New visit request',
            'body' => "{$vr->name} wants to visit {$vr->property->name}{$when}. {$payment}.",
            'url' => route('visit-requests.show', $vr),
        ];
    }
}
