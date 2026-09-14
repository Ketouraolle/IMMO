<div class="card booking-card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="booking-card__title">{{ __('Book a visit') }}</h2>
            @if($fee > 0)
                <span class="badge-soft badge-soft--info">{{ __('Fee · :amount XAF', ['amount' => number_format($fee)]) }}</span>
            @else
                <span class="badge-soft badge-soft--success">{{ __('Free visit') }}</span>
            @endif
        </div>

        @if($booking)
            {{-- Confirmation --}}
            <div class="text-center mb-3">
                <div class="pay-success mt-0 mb-2"><i class="bi bi-check-lg"></i></div>
                <h3 class="booking-card__title">{{ __('Visit requested') }}</h3>
                <p class="text-muted small mb-0">{{ __('The agency will confirm by phone or email.') }}</p>
            </div>
            <dl class="summary-list mb-3">
                <div><dt>{{ __('Date') }}</dt><dd>{{ ucfirst($booking->visit_date->translatedFormat('l j F')) }}</dd></div>
                <div><dt>{{ __('Time') }}</dt><dd>{{ $booking->visitTimeLabel() }}</dd></div>
                <div><dt>{{ __('Reference') }}</dt><dd>VR-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</dd></div>
                <div>
                    <dt>{{ __('Visit fee') }}</dt>
                    <dd>
                        @if($booking->payment_status === 'paid')
                            {{ __('Paid via :method', ['method' => $booking->paymentMethodLabel()]) }}
                            <span class="d-block text-muted small fw-normal">{{ $booking->transaction_ref }}</span>
                        @elseif($booking->payment_status === 'unpaid')
                            {{ __(':amount XAF at the visit', ['amount' => number_format($booking->fee_amount)]) }}
                        @else
                            {{ __('Free') }}
                        @endif
                    </dd>
                </div>
            </dl>
            <button type="button" class="btn btn-outline-dark w-100" wire:click="startOver">{{ __('Book another time') }}</button>

        @else
            @error('form') <div class="alert alert-warning py-2 small">{{ $message }}</div> @enderror

            @if($slots->isNotEmpty())
                {{-- Published dates: pick one, then a time inside its window --}}
                <div class="step-label"><span class="step-label__num">1</span> {{ __('Pick a date') }}</div>
                <div class="chip-group mb-1">
                    @foreach($slots as $s)
                        <button type="button" wire:key="slot-{{ $s->id }}" wire:click="selectSlot({{ $s->id }})"
                                class="chip {{ $slotId === $s->id ? 'is-selected' : '' }}">
                            {{ ucfirst($s->date->translatedFormat('D j M')) }}
                            <small>{{ $s->windowLabel() }}</small>
                        </button>
                    @endforeach
                </div>
                @error('slotId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                @if($slot)
                    <div class="step-label mt-4"><span class="step-label__num">2</span> {{ __('Pick a time') }}</div>
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

                @php $ready = $slot && $time; $step = 3; @endphp
            @else
                {{-- No published dates: the visitor proposes a day and time --}}
                <div class="step-label"><span class="step-label__num">1</span> {{ __('Pick a day and time') }}</div>
                <p class="text-muted small mb-2">{{ __('Choose what suits you. The agency will confirm the visit.') }}</p>
                <div class="row g-2">
                    <div class="col-7">
                        <input type="date" wire:model.live="date" min="{{ today()->toDateString() }}" aria-label="{{ __('Visit day') }}"
                               class="form-control @error('date') is-invalid @enderror">
                    </div>
                    <div class="col-5">
                        <input type="time" wire:model.live.debounce.500ms="time" step="1800" aria-label="{{ __('Visit time') }}"
                               class="form-control @error('time') is-invalid @enderror">
                    </div>
                </div>
                @error('date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('time') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                @php $ready = $date && $time; $step = 2; @endphp
            @endif

            @if($ready)
                <div class="step-label mt-4"><span class="step-label__num">{{ $step }}</span> {{ __('Your details') }}</div>
                <div class="mb-2">
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Full name') }}" autocomplete="name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-sm-6">
                        <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email') }}" autocomplete="email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-6">
                        <input type="tel" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="{{ __('Phone') }}" autocomplete="tel">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <textarea wire:model="message" class="form-control" rows="2" placeholder="{{ __('Message (optional)') }}"></textarea>

                @if($fee > 0)
                    <div class="step-label mt-4"><span class="step-label__num">{{ $step + 1 }}</span> {{ __('Visit fee · :amount XAF', ['amount' => number_format($fee)]) }}</div>
                    <div class="choice-grid mb-3">
                        <button type="button" class="choice-card {{ $paymentOption === 'pay_now' ? 'is-selected' : '' }}" wire:click="$set('paymentOption', 'pay_now')">
                            <i class="bi bi-phone fs-5 text-muted"></i>
                            <span><span class="choice-card__title">{{ __('Pay now') }}</span><span class="choice-card__hint">{{ __('Orange Money or MoMo') }}</span></span>
                            <i class="bi bi-check-circle-fill choice-card__check"></i>
                        </button>
                        <button type="button" class="choice-card {{ $paymentOption === 'pay_at_visit' ? 'is-selected' : '' }}" wire:click="$set('paymentOption', 'pay_at_visit')">
                            <i class="bi bi-cash-coin fs-5 text-muted"></i>
                            <span><span class="choice-card__title">{{ __('Pay at the visit') }}</span><span class="choice-card__hint">{{ __('Cash or mobile money') }}</span></span>
                            <i class="bi bi-check-circle-fill choice-card__check"></i>
                        </button>
                    </div>

                    @if($paymentOption === 'pay_now')
                        <x-mobile-money-pay mode="livewire" action="book" validate="validateDetails" :amount="$fee" :label="__('Pay & book')"
                                            :method-error="$errors->first('payMethod')" :phone-error="$errors->first('payPhone')" />
                    @endif
                @endif

                @if($fee <= 0 || $paymentOption === 'pay_at_visit')
                    <button type="button" class="btn btn-accent w-100 py-2 mt-3" wire:click="book" wire:loading.attr="disabled" wire:target="book">
                        <span wire:loading.remove wire:target="book">{{ __('Request visit') }}</span>
                        <span wire:loading wire:target="book"><span class="spinner-border spinner-border-sm me-1"></span> {{ __('Sending…') }}</span>
                    </button>
                @endif
            @endif
        @endif
    </div>
</div>
