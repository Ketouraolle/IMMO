<style>
    :root {
        --ink: #14213d;
        --ink-soft: #3a4a6b;
        --accent: #0EA5E9;
        --accent-dark: #0369A1;
        --bg: #f5f6fa;
        --border-soft: rgba(20, 33, 61, .08);
    }

    body {
        background: var(--bg);
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        color: var(--ink);
    }

    h1, h2, h3, h4, h5, h6 { font-weight: 700; letter-spacing: -.01em; }

    /* Navbar */
    .app-navbar, .navbar.app-navbar {
        background: linear-gradient(135deg, var(--ink) 0%, #1f3a63 100%);
    }
    .navbar-brand { font-weight: 800; letter-spacing: .2px; font-size: 1.2rem; }
    .app-navbar .nav-link {
        opacity: .8;
        font-weight: 500;
        transition: opacity .15s;
    }
    .app-navbar .nav-link:hover,
    .app-navbar .nav-link.active { opacity: 1; }
    .app-navbar .nav-link.active { text-decoration: underline; text-underline-offset: 6px; text-decoration-color: var(--accent); text-decoration-thickness: 2px; }

    /* Cards */
    .card {
        border: none;
        border-radius: .75rem;
        box-shadow: 0 1px 2px rgba(20,33,61,.06), 0 8px 24px -16px rgba(20,33,61,.15);
    }
    .card-header { border-bottom: 1px solid var(--border-soft); border-radius: .75rem .75rem 0 0 !important; }

    .btn { border-radius: .5rem; font-weight: 500; }
    .btn-dark { background: var(--ink); border-color: var(--ink); }
    .btn-dark:hover { background: #0d1830; border-color: #0d1830; }
    .btn-outline-dark:hover { background: var(--ink); border-color: var(--ink); }

    .badge-role-admin { background: var(--ink); }
    .badge-role-owner { background: #2563eb; }
    .badge-role-tenant { background: var(--accent); }

    .stat-card .value { font-size: 1.8rem; font-weight: 800; color: var(--ink); }
    .stat-card { border-top: 3px solid var(--accent); }

    table.table thead { background: #eef1f8; }
    table.table thead th { font-weight: 600; color: var(--ink-soft); font-size: .85rem; text-transform: uppercase; letter-spacing: .03em; }
    table.table a { color: var(--ink); font-weight: 600; text-decoration: none; }
    table.table a:hover { color: var(--accent); text-decoration: underline; }

    /* Property gallery / cards */
    .property-thumb-wrap { position: relative; overflow: hidden; border-radius: .75rem .75rem 0 0; }
    .property-card img, .property-thumb {
        height: 190px;
        object-fit: cover;
        background: #dfe3e8;
        transition: transform .35s ease;
        display: block;
    }
    .property-card { overflow: hidden; transition: transform .2s ease, box-shadow .2s ease; }
    .property-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px -12px rgba(20,33,61,.25); }
    .property-card:hover img { transform: scale(1.06); }
    .property-type-badge {
        position: absolute; top: .6rem; right: .6rem;
        background: rgba(20,33,61,.75); color: #fff; font-weight: 600;
        backdrop-filter: blur(2px);
    }
    .price-tag { font-size: 1.15rem; font-weight: 800; color: var(--accent-dark); }

    /* Filter bar */
    .filter-card { border: 1px solid var(--border-soft); }

    /* Photo tiles (Livewire image manager) */
    .photo-tile {
        position: relative;
        border-radius: .6rem;
        overflow: hidden;
        background: #dfe3e8;
        aspect-ratio: 1 / 1;
    }
    .photo-tile__img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .photo-tile__actions {
        position: absolute; inset: auto 0 0 0;
        display: flex; gap: .3rem; padding: .35rem;
        background: linear-gradient(0deg, rgba(0,0,0,.55), transparent);
        opacity: 0; transition: opacity .15s;
    }
    .photo-tile:hover .photo-tile__actions { opacity: 1; }
    .photo-tile__remove {
        position: absolute; top: .3rem; right: .3rem;
        width: 1.6rem; height: 1.6rem; line-height: 1.6rem;
        border-radius: 50%; border: none; background: rgba(0,0,0,.6); color: #fff;
        font-size: 1rem; padding: 0;
    }

    /* Upload dropzone */
    .img-dropzone {
        border: 2px dashed rgba(20,33,61,.25);
        border-radius: .75rem;
        background: #fbfbfe;
        transition: border-color .15s, background .15s;
    }
    .img-dropzone--active { border-color: var(--accent); background: #eaf6fd; }

    /* Hero (public catalog) */
    .site-hero {
        background: radial-gradient(1200px 400px at 10% -10%, rgba(14,165,233,.25), transparent),
                    linear-gradient(135deg, var(--ink) 0%, #1f3a63 100%);
        border-radius: 1rem;
        color: #fff;
        padding: 2.75rem 2rem;
        margin-bottom: 1.75rem;
    }
    .site-hero h1 { font-size: 2rem; margin-bottom: .4rem; }
    .site-hero p { opacity: .85; margin-bottom: 0; }

    /* Lightbox */
    .lightbox-backdrop {
        position: fixed; inset: 0; background: rgba(10,14,26,.92);
        z-index: 1080; display: flex; align-items: center; justify-content: center;
    }
    .lightbox-backdrop img { max-width: 92vw; max-height: 82vh; border-radius: .5rem; }
    .lightbox-close {
        position: absolute; top: 1rem; right: 1.25rem; color: #fff; font-size: 2rem;
        background: none; border: none; line-height: 1; cursor: pointer;
    }
    .lightbox-nav {
        position: absolute; top: 50%; transform: translateY(-50%);
        background: rgba(255,255,255,.12); color: #fff; border: none;
        width: 3rem; height: 3rem; border-radius: 50%; font-size: 1.5rem;
    }
    .lightbox-nav:hover { background: rgba(255,255,255,.25); }
    .lightbox-nav--prev { left: 1rem; }
    .lightbox-nav--next { right: 1rem; }
    .gallery-thumb { cursor: zoom-in; transition: opacity .15s; }
    .gallery-thumb:hover { opacity: .8; }

    [x-cloak] { display: none !important; }

    /* Progressive image loading: shimmer placeholder fades out once the image paints */
    .progressive-img {
        background: linear-gradient(100deg, #dfe3e8 8%, #eef0f3 18%, #dfe3e8 33%);
        background-size: 200% 100%;
        animation: img-shimmer 1.4s ease-in-out infinite;
        opacity: 0;
        transition: opacity .45s ease;
    }
    .progressive-img.is-loaded {
        animation: none;
        background: none;
        opacity: 1;
    }
    @keyframes img-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        .progressive-img { animation: none; transition: none; opacity: 1; background: #eef0f3; }
    }
</style>
