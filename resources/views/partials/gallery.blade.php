@php
    $emptyText = $emptyText ?? __('Photo coming soon');
@endphp
@if($images->isNotEmpty())
    <div
        x-data="{
            open: false,
            index: 0,
            images: {{ $images->pluck('path')->map(fn ($p) => asset('storage/' . $p))->toJson() }},
        }"
        class="mb-3"
    >
        <img
            :src="images[0]"
            src="{{ asset('storage/' . $images->first()->path) }}"
            class="w-100 rounded gallery-thumb progressive-img"
            style="height:280px;object-fit:cover;"
            alt="{{ $alt }}"
            decoding="async"
            onload="this.classList.add('is-loaded')"
            @click="open = true; index = 0"
        >

        @if($images->count() > 1)
            <div class="d-flex gap-2 mt-2 flex-wrap">
                @foreach($images as $i => $img)
                    <img
                        src="{{ asset('storage/' . $img->path) }}"
                        class="rounded gallery-thumb progressive-img"
                        style="width:70px;height:70px;object-fit:cover;"
                        alt="{{ __(':name photo :number', ['name' => $alt, 'number' => $i + 1]) }}"
                        loading="lazy"
                        decoding="async"
                        onload="this.classList.add('is-loaded')"
                        @click="open = true; index = {{ $i }}"
                    >
                @endforeach
            </div>
        @endif

        <div
            x-show="open"
            x-cloak
            class="lightbox-backdrop"
            @keydown.window.escape="open = false"
            @keydown.window.arrow-right="index = (index + 1) % images.length"
            @keydown.window.arrow-left="index = (index - 1 + images.length) % images.length"
            @click.self="open = false"
        >
            <button type="button" class="lightbox-close" @click="open = false" aria-label="{{ __('Close') }}">&times;</button>
            @if($images->count() > 1)
                <button type="button" class="lightbox-nav lightbox-nav--prev" @click="index = (index - 1 + images.length) % images.length" aria-label="{{ __('Previous photo') }}">&larr;</button>
            @endif
            <img :src="images[index]" alt="{{ $alt }}">
            @if($images->count() > 1)
                <button type="button" class="lightbox-nav lightbox-nav--next" @click="index = (index + 1) % images.length" aria-label="{{ __('Next photo') }}">&rarr;</button>
            @endif
        </div>
    </div>
@else
    <div class="property-thumb w-100 rounded d-flex align-items-center justify-content-center text-muted mb-3" style="height:280px;">
        {{ $emptyText }}
    </div>
@endif
