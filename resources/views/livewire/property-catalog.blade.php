<div>
    <div class="search-console mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label d-block mb-1">City</label>
                <select wire:model.live="city" class="form-select form-select-sm">
                    <option value="">Any city</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label d-block mb-1">Type</label>
                <select wire:model.live="type" class="form-select form-select-sm">
                    <option value="">Any type</option>
                    @foreach(['apartment','house','studio','commercial'] as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label d-block mb-1">Max rent (XAF)</label>
                <input type="number" wire:model.live.debounce.400ms="maxPrice" class="form-control form-control-sm" placeholder="e.g. 300000">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="button" wire:click="resetFilters" class="btn btn-outline-secondary btn-sm w-100">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <div wire:loading wire:target="city,type,maxPrice" class="row g-3">
        @for($i = 0; $i < 6; $i++)
            <div class="col-md-4">
                <div class="ticket-skeleton">
                    <div class="ticket-skeleton__photo"></div>
                    <div class="ticket-skeleton__body">
                        <div class="ticket-skeleton__line ticket-skeleton__line--w40"></div>
                        <div class="ticket-skeleton__line ticket-skeleton__line--w60"></div>
                        <div class="ticket-skeleton__line ticket-skeleton__line--w40" style="margin-bottom:0;"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <div wire:loading.remove wire:target="city,type,maxPrice" class="row g-3">
        @forelse($properties as $p)
            <div class="col-md-4" wire:key="property-{{ $p->id }}">
                <a href="{{ route('public.properties.show', $p) }}" class="ticket-card-link">
                    <div class="ticket-card h-100">
                        <div class="ticket-card__photo">
                            @if($p->images->isNotEmpty())
                                <img
                                    src="{{ asset('storage/' . $p->images->first()->path) }}"
                                    class="progressive-img"
                                    alt="{{ $p->name }}"
                                    loading="lazy"
                                    decoding="async"
                                    onload="this.classList.add('is-loaded')"
                                >
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted small">
                                    {{ ucfirst($p->type) }} · photo coming soon
                                </div>
                            @endif
                            <span class="ticket-card__type">{{ ucfirst($p->type) }}</span>
                        </div>
                        <div class="ticket-card__perf" aria-hidden="true"></div>
                        <div class="ticket-card__body">
                            <div class="ticket-card__route">
                                <span class="ticket-card__code">{{ Str::upper(Str::substr($p->city ?? $p->address, 0, 3)) }}</span>
                                <span class="ticket-card__dest">{{ $p->city ?? $p->address }}</span>
                            </div>
                            <div class="ticket-card__name">{{ $p->name }}</div>
                            <div class="ticket-card__foot">
                                <div class="ticket-card__price">{{ number_format($p->monthly_rent) }} <span>XAF/mo</span></div>
                                <span class="ticket-card__cta">View &rarr;</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center text-muted py-5">
                    No properties match your search right now — try widening your filters.
                </div></div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $properties->links() }}</div>
</div>
