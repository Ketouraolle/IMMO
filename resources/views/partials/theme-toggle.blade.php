<button type="button" class="{{ $class ?? 'icon-btn' }} theme-toggle" onclick="toggleTheme()"
        aria-label="{{ __('Switch light/dark mode') }}" title="{{ __('Switch light/dark mode') }}">
    <i class="bi bi-moon-stars theme-toggle__dark"></i>
    <i class="bi bi-sun theme-toggle__light"></i>
</button>
