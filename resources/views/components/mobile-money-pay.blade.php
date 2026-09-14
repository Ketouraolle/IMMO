@props([
    'amount' => 0,
    'phone' => '',
    'mode' => 'form',       // form: submits the enclosing <form>; livewire: calls $wire methods
    'action' => null,       // livewire: method called once the operator "confirms"
    'validate' => null,     // livewire: method returning true when the rest of the form is valid
    'amountField' => null,  // form: name of the input holding the amount, keeps the button label live
    'label' => null,
    'methodError' => null,  // livewire parents pass their own error messages
    'phoneError' => null,
])
@php
    $label ??= __('Pay');
    $methodError ??= isset($errors) ? $errors->first('method') : null;
    $phoneError ??= isset($errors) ? $errors->first('phone') : null;
    $operators = [
        'orange_money' => ['name' => 'Orange Money', 'mark' => 'OM', 'class' => 'mm-mark--om'],
        'mtn_momo' => ['name' => 'MTN MoMo', 'mark' => 'MoMo', 'class' => 'mm-mark--momo'],
    ];
@endphp
<div class="mm-pay" x-data="mobileMoneyPay(@js([
    'amount' => (float) $amount,
    'phone' => old('phone', $phone),
    'method' => old('method'),
    'mode' => $mode,
    'action' => $action,
    'validate' => $validate,
    'amountField' => $amountField,
    'label' => $label,
]))">
    <label class="form-label">{{ __('Pay with') }}</label>
    <div class="choice-grid" role="radiogroup" aria-label="{{ __('Mobile money operator') }}">
        @foreach($operators as $key => $op)
            <button type="button" class="choice-card" role="radio"
                    :class="{ 'is-selected': method === '{{ $key }}' }"
                    :aria-checked="method === '{{ $key }}'"
                    @click="method = '{{ $key }}'; error = ''">
                <span class="mm-mark {{ $op['class'] }}">{{ $op['mark'] }}</span>
                <span class="choice-card__title">{{ $op['name'] }}</span>
                <i class="bi bi-check-circle-fill choice-card__check"></i>
            </button>
        @endforeach
    </div>
    @if($mode === 'form')
        <input type="hidden" name="method" :value="method">
    @endif
    @if($methodError) <div class="text-danger small mt-2">{{ $methodError }}</div> @endif

    <label class="form-label mt-3" for="mm-phone-{{ $mode }}">{{ __('Mobile money number') }}</label>
    <input type="tel" id="mm-phone-{{ $mode }}" @if($mode === 'form') name="phone" @endif x-model="phone"
           class="form-control" placeholder="6XX XX XX XX" inputmode="tel" autocomplete="tel">
    @if($phoneError) <div class="text-danger small mt-2">{{ $phoneError }}</div> @endif
    <div class="text-danger small mt-2" x-show="error" x-text="error" x-cloak></div>

    <button type="button" class="btn btn-accent w-100 mt-3 py-2" @click="start()" :disabled="busy">
        <i class="bi bi-shield-lock me-1"></i> <span x-text="buttonLabel">{{ $label }}</span>
    </button>
    <div class="text-muted text-center mt-2" style="font-size:.75rem;">{{ __('Simulated payment · no real money is moved') }}</div>

    <div class="pay-overlay" x-show="stage" x-transition.opacity x-cloak>
        <div class="pay-dialog" role="alertdialog" aria-live="assertive" aria-label="{{ __('Payment status') }}">
            <span class="mm-mark mm-mark--lg" :class="method === 'mtn_momo' ? 'mm-mark--momo' : 'mm-mark--om'" x-text="method === 'mtn_momo' ? 'MoMo' : 'OM'"></span>
            <template x-if="stage === 'waiting'">
                <div>
                    <h5>{{ __('Waiting for confirmation') }}</h5>
                    <p x-text="promptText"></p>
                    <div class="spinner-border" role="status"><span class="visually-hidden">{{ __('Processing') }}</span></div>
                </div>
            </template>
            <template x-if="stage === 'success'">
                <div>
                    <div class="pay-success"><i class="bi bi-check-lg"></i></div>
                    <h5>{{ __('Payment confirmed') }}</h5>
                    <p>{{ __('Finishing up…') }}</p>
                </div>
            </template>
        </div>
    </div>
</div>
