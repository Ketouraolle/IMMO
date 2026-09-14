<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Book a visit</span>
        @if($fee > 0)
            <span class="badge-soft badge-soft--info">Visit fee · {{ number_format($fee) }} XAF</span>
        @else
            <span class="badge-soft badge-soft--success">Free visit</span>
        @endif
    </div>
    <div class="card-body">
        @if($booking)
            {{-- Confirmation --}}
            <div class="text-center py-2">
                <div class="pay-success mt-0 mb-2"><i class="bi bi-check-lg"></i></div>
                <h5 class="mb-1">Visit requested</h5>
                <p class="text-muted small mb-3">The agency will confirm by phone or email.</p>
            </div>
            <div class="passport mb-3">
                <div class="passport__row"><span class="passport__label">Date</span><span class="passport__value">{{ $booking->visit_date->format('l d F') }}</span></div>
                <div class="passport__row"><span class="passport__label">Time</span><span class="passport__value">{{ $booking->visitTimeLabel() }}</span></div>
                <div class="passport__row"><span class="passport__label">Reference</span><span class="passport__value">VR-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span></div>
                <div class="passport__row">
                    <span class="passport__label">Visit fee</span>
                    <span class="passport__value text-end">
                        @if($booking->payment_status === 'paid')
                            Paid via {{ $booking->paymentMethodLabel() }}
                            <span class="d-block text-muted small fw-normal">{{ $booking->transaction_ref }}</span>
                        @elseif($booking->payment_status === 'unpaid')
                            {{ number_format($booking->fee_amount) }} XAF at the visit
                        @else
                            Free
                        @endif
                    </span>
                </div>
            </div>
            <button type="button" class="btn btn-outline-dark w-100" wire:click="startOver">Book another time</button>

        @elseif($slots->isEmpty())
            {{-- No availability --}}
            <div class="text-center py-3">
                <i class="bi bi-calendar-x fs-2 text-muted"></i>
                <div class="fw-semibold mt-2">No visit dates open yet</div>
                <p class="text-muted small mb-3">Call or email the agency and we'll arrange a time with you.</p>
                <div class="d-grid gap-2">
                    <a href="tel:+237600000000" class="btn btn-outline-dark btn-sm"><i class="bi bi-telephone me-1"></i> +237 6 00 00 00 00</a>
                    <a href="mailto:contact@diasporaimmo.test" class="btn btn-outline-dark btn-sm"><i class="bi bi-envelope me-1"></i> contact@diasporaimmo.test</a>
                </div>
            </div>

        @else
            @error('form') <div class="alert alert-warning py-2 small">{{ $message }}</div> @enderror

            {{-- 1. Date --}}
            <div class="step-label"><span class="step-label__num">1</span> Pick a date</div>
            <div class="chip-group mb-1">
                @foreach($slots as $s)
                    <button type="button" wire:key="slot-{{ $s->id }}" wire:click="selectSlot({{ $s->id }})"
                            class="chip {{ $slotId === $s->id ? 'is-selected' : '' }}">
                        {{ $s->date->format('D d M') }}
                        <small>{{ $s->windowLabel() }}</small>
                    </button>
                @endforeach
            </div>
            @error('slotId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

            {{-- 2. Time --}}
            @if($slot)
                <div class="step-label mt-4"><span class="step-label__num">2</span> Pick a time</div>
                <div class="chip-group mb-1">
                    @foreach($times as $t)
                        <button type="button" wire:key="time-{{ $slot->id }}-{{ $t }}" wire:click="selectTime('{{ $t }}')"
                                class="chip {{ $time === $t ? 'is-selected' : '' }}" @disabled(in_array($t, $unavailable, true))>
                            {{ $t }}
                        </button>
                    @endforeach
                </div>
                @error('time') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @endif

            {{-- 3. Details + 4. Payment --}}
            @if($slot && $time)
                <div class="step-label mt-4"><span class="step-label__num">3</span> Your details</div>
                <div class="mb-2">
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Full name" autocomplete="name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-sm-6">
                        <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" autocomplete="email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-6">
                        <input type="tel" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Phone" autocomplete="tel">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <textarea wire:model="message" class="form-control" rows="2" placeholder="Message (optional)"></textarea>

                @if($fee > 0)
                    <div class="step-label mt-4"><span class="step-label__num">4</span> Visit fee · {{ number_format($fee) }} XAF</div>
                    <div class="choice-grid mb-3">
                        <button type="button" class="choice-card {{ $paymentOption === 'pay_now' ? 'is-selected' : '' }}" wire:click="$set('paymentOption', 'pay_now')">
                            <i class="bi bi-phone fs-5 text-muted"></i>
                            <span><span class="choice-card__title">Pay now</span><span class="choice-card__hint">Orange Money or MoMo</span></span>
                            <i class="bi bi-check-circle-fill choice-card__check"></i>
                        </button>
                        <button type="button" class="choice-card {{ $paymentOption === 'pay_at_visit' ? 'is-selected' : '' }}" wire:click="$set('paymentOption', 'pay_at_visit')">
                            <i class="bi bi-cash-coin fs-5 text-muted"></i>
                            <span><span class="choice-card__title">Pay at the visit</span><span class="choice-card__hint">Cash or mobile money</span></span>
                            <i class="bi bi-check-circle-fill choice-card__check"></i>
                        </button>
                    </div>

                    @if($paymentOption === 'pay_now')
                        <x-mobile-money-pay mode="livewire" action="book" validate="validateDetails" :amount="$fee" label="Pay & book"
                                            :method-error="$errors->first('payMethod')" :phone-error="$errors->first('payPhone')" />
                    @endif
                @endif

                @if($fee <= 0 || $paymentOption === 'pay_at_visit')
                    <button type="button" class="btn btn-accent w-100 py-2 mt-3" wire:click="book" wire:loading.attr="disabled" wire:target="book">
                        <span wire:loading.remove wire:target="book">Request visit</span>
                        <span wire:loading wire:target="book"><span class="spinner-border spinner-border-sm me-1"></span> Sending…</span>
                    </button>
                @endif
            @endif
        @endif
    </div>
</div>
