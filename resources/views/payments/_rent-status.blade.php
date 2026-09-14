{{-- Tenant rent position for the active lease. Params: $lease, optional $showPayButton --}}
@php
    $periodsDue = $lease->periodsDue();
    $nextDue = $lease->nextDueDate();
    $credit = $lease->credit();
@endphp
<div class="card mb-4 rent-status {{ $periodsDue ? 'rent-status--due' : '' }}">
    <div class="card-body p-4 d-flex flex-wrap gap-3 gap-md-4 align-items-center">
        <span class="stat-icon {{ $periodsDue ? 'stat-icon--warning' : '' }}"><i class="bi {{ $periodsDue ? 'bi-exclamation-circle' : 'bi-calendar-check' }}"></i></span>
        <div class="flex-grow-1 min-w-0">
            @if($periodsDue)
                <div class="text-muted small">{{ trans_choice('Rent due for :count period|Rent due for :count periods', $periodsDue) }}</div>
                <div class="fs-4 fw-bold">{{ number_format($lease->amountDue()) }} XAF</div>
                <div class="small text-muted">{{ __('Unpaid since :date', ['date' => $nextDue->translatedFormat('j F Y')]) }} · {{ $lease->suggestedPeriodLabel() }}</div>
            @else
                <div class="text-muted small">{{ __('Next payment') }}</div>
                <div class="fs-4 fw-bold">{{ number_format($lease->suggestedPayment()) }} XAF</div>
                <div class="small text-muted">{{ __('Due :date · :period', ['date' => $nextDue->translatedFormat('j F Y'), 'period' => $lease->periodLabel($nextDue)]) }}</div>
            @endif
            @if($credit > 0)
                <div class="small text-muted">{{ __('Includes :amount XAF already paid toward this period.', ['amount' => number_format($credit)]) }}</div>
            @endif
        </div>
        @if($showPayButton ?? false)
            <a href="{{ route('payments.submit-form') }}" class="btn btn-dark"><i class="bi bi-phone me-1"></i> {{ __('Pay rent') }}</a>
        @endif
    </div>
</div>
