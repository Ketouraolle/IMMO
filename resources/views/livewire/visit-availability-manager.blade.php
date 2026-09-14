<div>
    <form wire:submit="add" class="row g-2 align-items-end mb-3">
        <div class="col-12 col-sm-4">
            <label class="form-label small mb-1">Date</label>
            <input type="date" wire:model="date" min="{{ today()->toDateString() }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-sm-3">
            <label class="form-label small mb-1">From</label>
            <input type="time" wire:model="startTime" step="1800" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-sm-3">
            <label class="form-label small mb-1">To</label>
            <input type="time" wire:model="endTime" step="1800" class="form-control form-control-sm">
        </div>
        <div class="col-12 col-sm-2">
            <button class="btn btn-dark btn-sm w-100" wire:loading.attr="disabled" wire:target="add">
                <i class="bi bi-plus-lg"></i> Add
            </button>
        </div>
    </form>
    @error('date') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
    @error('startTime') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
    @error('endTime') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
    @error('slots') <div class="alert alert-warning py-2 small">{{ $message }}</div> @enderror

    @if($slots->isEmpty())
        <p class="text-muted small mb-0">No upcoming visit dates. Visitors will be asked to contact the agency until you add some.</p>
    @else
        <ul class="list-group list-group-flush">
            @foreach($slots as $slot)
                <li class="list-group-item px-0 d-flex align-items-center gap-3" wire:key="slot-{{ $slot->id }}">
                    <div class="text-center" style="width:3rem;">
                        <div class="small text-muted text-uppercase" style="font-size:.68rem;">{{ $slot->date->format('D') }}</div>
                        <div class="fw-bold lh-1">{{ $slot->date->format('d') }}</div>
                        <div class="small text-muted" style="font-size:.7rem;">{{ $slot->date->format('M') }}</div>
                    </div>
                    <div class="flex-grow-1 {{ $slot->is_active ? '' : 'opacity-50' }}">
                        <div class="fw-semibold small">{{ $slot->windowLabel() }}</div>
                        <div class="text-muted" style="font-size:.78rem;">
                            {{ $slot->bookings_count }} {{ Str::plural('booking', $slot->bookings_count) }}
                            @unless($slot->is_active) · <span class="text-warning-emphasis">paused</span> @endunless
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0" title="{{ $slot->is_active ? 'Open for booking' : 'Paused' }}">
                        <input class="form-check-input" type="checkbox" role="switch" @checked($slot->is_active) wire:click="toggle({{ $slot->id }})" aria-label="Open for booking">
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="remove({{ $slot->id }})" wire:confirm="Remove this visit date?" title="Remove" aria-label="Remove date">
                        <i class="bi bi-trash3"></i>
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
