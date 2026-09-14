@php
    $typeIcons = ['apartment' => 'bi-building', 'house' => 'bi-house', 'studio' => 'bi-door-open', 'commercial' => 'bi-shop'];
    $filtered = $city !== '' || $type !== '' || $maxPrice;
@endphp
<div>
    <div class="search-bar" role="search">
        <div class="search-bar__field">
            <label for="filter-city">{{ __('City') }}</label>
            <select id="filter-city" wire:model.live="city" class="search-bar__control">
                <option value="">{{ __('Anywhere') }}</option>
                @foreach($cities as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-bar__field">
            <label for="filter-price">{{ __('Max rent (XAF)') }}</label>
            <input id="filter-price" type="number" min="0" step="10000" wire:model.live.debounce.400ms="maxPrice" class="search-bar__control" placeholder="{{ __('Any price') }}">
        </div>
        <div class="search-bar__field">
            <label for="filter-sort">{{ __('Sort by') }}</label>
            <select id="filter-sort" wire:model.live="sort" class="search-bar__control">
                @foreach(\App\Livewire\PropertyCatalog::SORTS as $value => $label)
                    <option value="{{ $value }}">{{ __($label) }}</option>
                @endforeach
            </select>
        </div>
        @if($filtered)
            <button type="button" wire:click="resetFilters" class="search-bar__reset">{{ __('Clear') }}</button>
        @endif
    </div>

    <div class="type-chips" role="group" aria-label="{{ __('Property type') }}">
        <button type="button" wire:click="$set('type', '')" class="type-chip {{ $type === '' ? 'is-active' : '' }}" aria-pressed="{{ $type === '' ? 'true' : 'false' }}">
            <i class="bi bi-grid"></i> {{ __('All') }} <span>{{ $typeCounts->sum() }}</span>
        </button>
        @foreach(\App\Livewire\PropertyCatalog::TYPES as $t)
            @if(($typeCounts[$t] ?? 0) > 0 || $type === $t)
                <button type="button" wire:click="$set('type', '{{ $t }}')" class="type-chip {{ $type === $t ? 'is-active' : '' }}" aria-pressed="{{ $type === $t ? 'true' : 'false' }}">
                    <i class="bi {{ $typeIcons[$t] }}"></i> {{ __(ucfirst($t)) }} <span>{{ $typeCounts[$t] ?? 0 }}</span>
                </button>
            @endif
        @endforeach
    </div>

    <div class="section-head">
        <h2 class="section-title">{{ __('Available homes') }}</h2>
        <span class="section-count" wire:loading.remove wire:target="city,type,maxPrice,sort">{{ trans_choice(':count result|:count results', $properties->total()) }}</span>
    </div>

    <div wire:loading.flex wire:target="city,type,maxPrice,sort" class="row g-4">
        @for($i = 0; $i < 3; $i++)
            <div class="col-sm-6 col-lg-4" aria-hidden="true">
                <div class="home-skeleton__media"></div>
                <div class="home-skeleton__line" style="width:40%;"></div>
                <div class="home-skeleton__line" style="width:70%;"></div>
            </div>
        @endfor
    </div>

    <div wire:loading.remove wire:target="city,type,maxPrice,sort">
        @if($properties->isEmpty())
            <div class="empty-state">
                <i class="bi bi-search"></i>
                {{ __('No homes match your search.') }}
                @if($filtered)
                    <button type="button" wire:click="resetFilters" class="btn btn-link p-0 align-baseline">{{ __('Clear filters') }}</button>
                @endif
            </div>
        @else
            <div class="row g-4">
                @foreach($properties as $p)
                    <div class="col-sm-6 col-lg-4" wire:key="property-{{ $p->id }}" data-reveal style="--reveal-delay: {{ min($loop->index, 5) * 70 }}ms;">
                        <a href="{{ route('public.properties.show', $p) }}" class="home-card">
                            <div class="home-card__media">
                                @if($p->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $p->images->first()->path) }}" class="progressive-img" alt="{{ $p->name }}"
                                         loading="lazy" decoding="async" onload="this.classList.add('is-loaded')">
                                @else
                                    <div class="home-card__placeholder"><i class="bi bi-image"></i></div>
                                @endif
                                <span class="home-card__badge">
                                    {{ $p->visit_fee > 0 ? __('Visit · :amount XAF', ['amount' => number_format($p->visit_fee)]) : __('Free visit') }}
                                </span>
                                @if($p->images->count() > 1)
                                    <span class="home-card__count"><i class="bi bi-images"></i> {{ $p->images->count() }}</span>
                                @endif
                                <span class="home-card__go" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                            </div>
                            <div class="home-card__body">
                                <div class="home-card__meta">{{ $p->city ?? $p->address }} · {{ __(ucfirst($p->type)) }}</div>
                                <div class="home-card__name">{{ $p->name }}</div>
                                <div class="home-card__price">{{ number_format($p->monthly_rent) }} XAF <span>{{ __('/ month') }}</span></div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($properties->hasPages())
        <div class="mt-5 d-flex justify-content-center">{{ $properties->links() }}</div>
    @endif
</div>
