<div class="locale-switch {{ $class ?? '' }}" role="group" aria-label="{{ __('Language') }}">
    @foreach(\App\Support\Locale::SUPPORTED as $code => $name)
        <a href="{{ route('locale.switch', $code) }}" hreflang="{{ $code }}" lang="{{ $code }}" title="{{ $name }}"
           class="{{ app()->getLocale() === $code ? 'is-active' : '' }}" @if(app()->getLocale() === $code) aria-current="true" @endif>{{ strtoupper($code) }}</a>
    @endforeach
</div>
