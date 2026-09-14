@if($vr->payment_status === 'paid')
    <span class="badge-soft badge-soft--success">Paid · {{ $vr->paymentMethodLabel() }}</span>
@elseif($vr->payment_status === 'unpaid')
    <span class="badge-soft badge-soft--warning">{{ number_format($vr->fee_amount) }} XAF at visit</span>
@else
    <span class="badge-soft badge-soft--neutral">No fee</span>
@endif
