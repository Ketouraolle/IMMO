<div>
    <div
        x-data="{ dragging: false }"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="
            dragging = false;
            $refs.fileInput.files = $event.dataTransfer.files;
            $refs.fileInput.dispatchEvent(new Event('change'));
        "
        :class="dragging ? 'img-dropzone img-dropzone--active' : 'img-dropzone'"
        class="mb-3"
        wire:key="dropzone"
    >
        <input
            type="file"
            x-ref="fileInput"
            wire:model="newImages"
            multiple
            accept="image/jpeg,image/png,image/webp"
            class="d-none"
        >
        <div class="text-center py-4 px-3" x-on:click="$refs.fileInput.click()" style="cursor:pointer;">
            <div class="mb-1" style="font-size:1.6rem;">🖼️</div>
            <div class="fw-semibold">Drop photos here or click to browse</div>
            <div class="text-muted small">JPG, PNG or WEBP · up to 4&nbsp;MB each</div>
        </div>
    </div>

    @error('newImages') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
    @error('newImages.*') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

    @if(!empty($newImages))
        <div class="row g-2 mb-3">
            @foreach($newImages as $index => $pending)
                <div class="col-6 col-md-3" wire:key="pending-{{ $index }}">
                    <div class="photo-tile">
                        <img src="{{ $pending->temporaryUrl() }}" class="photo-tile__img">
                        <button
                            type="button"
                            class="photo-tile__remove"
                            wire:click="removePending({{ $index }})"
                            title="Remove"
                        >&times;</button>
                        <span class="badge bg-info text-dark position-absolute bottom-0 start-0 m-1">New</span>
                    </div>
                </div>
            @endforeach
        </div>

        <button
            type="button"
            class="btn btn-dark btn-sm mb-4"
            wire:click="upload"
            wire:loading.attr="disabled"
            wire:target="upload"
        >
            <span wire:loading.remove wire:target="upload">Upload {{ count($newImages) }} photo{{ count($newImages) > 1 ? 's' : '' }}</span>
            <span wire:loading wire:target="upload">Uploading&hellip;</span>
        </button>
    @endif

    <div wire:loading.delay wire:target="newImages" class="text-muted small mb-3">
        <span class="spinner-border spinner-border-sm me-1"></span> Preparing preview&hellip;
    </div>

    @if($images->isNotEmpty())
        <div class="row g-2">
            @foreach($images as $img)
                <div class="col-6 col-md-3" wire:key="image-{{ $img->id }}">
                    <div class="photo-tile">
                        <img src="{{ asset('storage/' . $img->path) }}" class="photo-tile__img">
                        @if($img->is_primary)
                            <span class="badge bg-dark position-absolute top-0 start-0 m-1">Main</span>
                        @endif
                        <div class="photo-tile__actions">
                            @if(!$img->is_primary)
                                <button
                                    type="button"
                                    class="btn btn-light btn-sm"
                                    wire:click="makePrimary({{ $img->id }})"
                                    wire:loading.attr="disabled"
                                >Set main</button>
                            @endif
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                wire:click="remove({{ $img->id }})"
                                wire:confirm="Remove this photo?"
                                wire:loading.attr="disabled"
                            >Remove</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted small mb-0">No photos yet — the public listing will show a placeholder until you add some.</p>
    @endif
</div>
