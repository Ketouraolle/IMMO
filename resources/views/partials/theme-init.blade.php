{{-- In <head>, before styles: apply the saved theme (or the device's) before first paint to avoid a flash --}}
<script>
    (() => {
        const root = document.documentElement;
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const saved = () => { try { return localStorage.getItem('theme'); } catch (e) { return null; } };
        const apply = (theme) => root.setAttribute('data-bs-theme', theme);

        apply(saved() || (media.matches ? 'dark' : 'light'));

        window.toggleTheme = () => {
            const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            apply(next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        };

        // Follow the device while the visitor hasn't picked a theme
        media.addEventListener?.('change', (e) => { if (!saved()) apply(e.matches ? 'dark' : 'light'); });
    })();
</script>
