{{-- Params: icon, label, value; optional: hint, href, linkLabel, tone ('warning') --}}
<div class="card stat-card h-100">
    <div class="card-body d-flex gap-3 align-items-start">
        <span class="stat-icon {{ ($tone ?? null) === 'warning' ? 'stat-icon--warning' : '' }}"><i class="bi {{ $icon }}"></i></span>
        <div class="min-w-0">
            <div class="label">{{ $label }}</div>
            <div class="value">{{ $value }}</div>
            @isset($hint)<div class="hint">{{ $hint }}</div>@endisset
            @isset($href)
                <a href="{{ $href }}" class="small fw-semibold text-decoration-none d-inline-block mt-1">{{ $linkLabel ?? 'View' }} <i class="bi bi-arrow-right"></i></a>
            @endisset
        </div>
    </div>
</div>
