<?php

namespace App\Notifications;

use App\Models\VisitRequest;
use Illuminate\Notifications\Notification;

// Stored as translation keys + parameters so each reader sees it in their own language (see NotificationBell::present)
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

        return [
            'icon' => 'bi-calendar2-check',
            'title' => 'New visit request',
            'body' => $vr->visit_date ? ':name wants to visit :property on :date at :time.' : ':name wants to visit :property.',
            'suffix' => match ($vr->payment_status) {
                'paid' => 'Fee paid via :method.',
                'unpaid' => 'Fee to collect at the visit.',
                default => 'No visit fee.',
            },
            'params' => array_filter([
                'name' => $vr->name,
                'property' => $vr->property->name,
                'date' => $vr->visit_date?->toDateString(),
                'time' => $vr->visitTimeLabel(),
                'method' => $vr->payment_method,
            ], fn ($value) => $value !== null),
            'url' => route('visit-requests.show', $vr),
        ];
    }
}
