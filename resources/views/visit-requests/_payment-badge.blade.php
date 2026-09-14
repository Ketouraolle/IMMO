@if($vr->payment_status === 'paid')
    <span class="badge-soft badge-soft--success">{{ __('Paid · :method', ['method' => $vr->paymentMethodLabel()]) }}</span>
@elseif($vr->payment_status === 'unpaid')
    <span class="badge-soft badge-soft--warning">{{ __(':amount XAF at visit', ['amount' => number_format($vr->fee_amount)]) }}</span>
@else
    <span class="badge-soft badge-soft--neutral">{{ __('No fee') }}</span>
@endif
