<style>
    /* Public site: quiet, content-first. One typeface, one accent, hairline borders. */
    :root {
        --pub-ink: #0f172a;
        --pub-muted: #64748b;
        --pub-line: #e2e8f0;
        --pub-soft: #f8fafc;
        --pub-accent: #2563eb;
        --pub-accent-dark: #1d4ed8;
    }

    body { background: #fff; color: var(--pub-ink); font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-weight: 700; letter-spacing: -.02em; color: var(--pub-ink); }
    a { color: var(--pub-accent-dark); }

    /* Header / footer */
    .site-header { position: sticky; top: 0; z-index: 1020; background: rgba(255,255,255,.92); backdrop-filter: blur(8px); border-bottom: 1px solid var(--pub-line); }
    .site-header__inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
    .site-brand { display: inline-flex; align-items: center; gap: .55rem; font-weight: 700; font-size: 1.05rem; color: var(--pub-ink); text-decoration: none; }
    .site-brand:hover { color: var(--pub-ink); }
    .site-brand__mark { width: 1.9rem; height: 1.9rem; border-radius: .5rem; background: var(--pub-ink); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; }
    .site-header__link { font-weight: 600; font-size: .88rem; color: var(--pub-ink); text-decoration: none; padding: .45rem .95rem; border-radius: 999px; border: 1px solid var(--pub-line); transition: background .15s; }
    .site-header__link:hover { background: var(--pub-soft); color: var(--pub-ink); }
    .site-main { padding-block: 2.5rem 4rem; min-height: calc(100vh - 64px - 72px); }
    .site-footer { border-top: 1px solid var(--pub-line); color: var(--pub-muted); font-size: .85rem; }
    .site-footer__inner { display: flex; flex-wrap: wrap; gap: .5rem 1.5rem; justify-content: space-between; align-items: center; padding-block: 1.5rem; }
    .site-footer a { color: var(--pub-muted); text-decoration: none; }
    .site-footer a:hover { color: var(--pub-ink); }

    /* Hero + search */
    .hero { max-width: 40rem; margin-bottom: 1.75rem; }
    .hero h1 { font-size: clamp(2rem, 2.2vw + 1.3rem, 2.9rem); line-height: 1.1; margin-bottom: .6rem; }
    .hero p { color: var(--pub-muted); font-size: 1.05rem; margin: 0; }

    .search-bar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) auto; align-items: center; background: #fff; border: 1px solid var(--pub-line); border-radius: 1rem; box-shadow: 0 10px 30px -22px rgba(15,23,42,.3); margin-bottom: 2.5rem; overflow: hidden; }
    .search-bar__field { padding: .6rem 1.1rem; min-width: 0; transition: background .15s; }
    .search-bar__field + .search-bar__field { border-left: 1px solid var(--pub-line); }
    .search-bar__field:focus-within { background: var(--pub-soft); }
    .search-bar__field label { display: block; font-size: .72rem; font-weight: 600; color: var(--pub-muted); margin-bottom: .05rem; }
    .search-bar__control { width: 100%; border: none; background: transparent; padding: 0; font-size: .95rem; font-weight: 500; color: var(--pub-ink); outline: none; }
    .search-bar__control::placeholder { color: #94a3b8; font-weight: 400; }
    .search-bar__reset { margin-right: .75rem; border: none; background: none; color: var(--pub-muted); font-weight: 600; font-size: .85rem; padding: .4rem .6rem; border-radius: .5rem; white-space: nowrap; }
    .search-bar__reset:hover { color: var(--pub-ink); background: var(--pub-soft); }
    @media (max-width: 767.98px) {
        .search-bar { grid-template-columns: 1fr; }
        .search-bar__field + .search-bar__field { border-left: none; border-top: 1px solid var(--pub-line); }
        .search-bar__reset { margin: .25rem 0 .6rem .5rem; justify-self: start; }
    }

    .section-head { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-bottom: 1rem; }
    .section-title { font-size: 1.15rem; margin: 0; }
    .section-count { color: var(--pub-muted); font-size: .88rem; }

    /* Home cards */
    .home-card { display: block; text-decoration: none; color: inherit; border-radius: 1rem; }
    .home-card:hover { color: inherit; }
    .home-card:focus-visible { outline: 2px solid var(--pub-accent); outline-offset: 4px; }
    .home-card__media { position: relative; aspect-ratio: 4 / 3; border-radius: .9rem; overflow: hidden; background: var(--pub-soft); }
    .home-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s ease; }
    .home-card:hover .home-card__media img { transform: scale(1.03); }
    .home-card__placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 2rem; }
    .home-card__body { padding-top: .8rem; }
    .home-card__meta { font-size: .8rem; color: var(--pub-muted); }
    .home-card__name { font-weight: 600; font-size: 1rem; margin: .1rem 0 .3rem; color: var(--pub-ink); }
    .home-card:hover .home-card__name { text-decoration: underline; text-underline-offset: 3px; }
    .home-card__price { font-weight: 700; color: var(--pub-ink); }
    .home-card__price span { font-weight: 400; color: var(--pub-muted); font-size: .85rem; }

    .home-skeleton__media, .home-skeleton__line {
        background: linear-gradient(100deg, #eef2f7 8%, #f8fafc 18%, #eef2f7 33%);
        background-size: 200% 100%; animation: pub-shimmer 1.4s ease-in-out infinite;
    }
    .home-skeleton__media { aspect-ratio: 4 / 3; border-radius: .9rem; }
    .home-skeleton__line { height: .7rem; border-radius: .3rem; margin-top: .7rem; }
    @keyframes pub-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    .empty-state { text-align: center; padding: 4rem 1rem; color: var(--pub-muted); border: 1px dashed var(--pub-line); border-radius: 1rem; }
    .empty-state i { font-size: 1.8rem; display: block; margin-bottom: .5rem; color: #cbd5e1; }

    /* Pagination */
    .pagination .page-link { border: none; color: var(--pub-muted); font-weight: 600; border-radius: .5rem !important; margin: 0 .15rem; }
    .pagination .page-item.active .page-link { background: var(--pub-ink); color: #fff; }
    .pagination .page-link:hover { background: var(--pub-soft); color: var(--pub-ink); }

    /* Cards, forms, buttons */
    .card { border: 1px solid var(--pub-line); border-radius: 1rem; box-shadow: none; }
    .form-control, .form-select { border-radius: .6rem; border-color: var(--pub-line); }
    .form-control:focus, .form-select:focus { border-color: var(--pub-accent); box-shadow: 0 0 0 .2rem rgba(37,99,235,.15); }
    .form-label { font-weight: 600; font-size: .85rem; color: var(--pub-ink); }
    .btn-dark, .btn-accent { background: var(--pub-accent); border-color: var(--pub-accent); color: #fff; font-weight: 600; border-radius: .6rem; }
    .btn-dark:hover, .btn-accent:hover, .btn-accent:focus { background: var(--pub-accent-dark); border-color: var(--pub-accent-dark); color: #fff; }
    .btn-outline-dark { border-color: var(--pub-line); color: var(--pub-ink); font-weight: 600; border-radius: .6rem; }
    .btn-outline-dark:hover { background: var(--pub-soft); border-color: #cbd5e1; color: var(--pub-ink); }
    .btn:disabled { opacity: .65; }

    /* Listing page */
    .site-main .gallery-thumb.w-100, .site-main .property-thumb { border-radius: 1rem !important; }
    .site-main .property-thumb { background: var(--pub-soft); color: #94a3b8; }
    .back-link { display: inline-flex; align-items: center; gap: .4rem; font-size: .88rem; font-weight: 600; color: var(--pub-muted); text-decoration: none; margin-bottom: 1.25rem; }
    .back-link:hover { color: var(--pub-ink); }
    .listing-title { font-size: clamp(1.5rem, 1.2vw + 1.1rem, 2rem); margin: .75rem 0 .25rem; }
    .listing-location { color: var(--pub-muted); }
    .listing-price { font-size: 1.35rem; font-weight: 700; white-space: nowrap; margin-top: .9rem; }
    .listing-price span { font-size: .85rem; font-weight: 400; color: var(--pub-muted); }
    .listing-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border: 1px solid var(--pub-line); border-radius: 1rem; margin-top: 1.5rem; }
    .listing-facts > div { padding: .9rem 1.1rem; min-width: 0; }
    .listing-facts > div + div { border-left: 1px solid var(--pub-line); }
    .listing-facts span { display: block; font-size: .75rem; color: var(--pub-muted); }
    .listing-facts strong { font-weight: 600; }
    @media (max-width: 575.98px) {
        .listing-facts { grid-template-columns: 1fr; }
        .listing-facts > div + div { border-left: none; border-top: 1px solid var(--pub-line); }
    }
    .listing-about { margin-top: 2rem; }
    .listing-about p { color: #334155; line-height: 1.7; }
    .listing-contact { margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--pub-line); color: var(--pub-muted); font-size: .9rem; }

    /* Booking card */
    .booking-card { box-shadow: 0 18px 44px -30px rgba(15,23,42,.35); }
    .booking-card__title { font-size: 1.1rem; margin: 0; }
    .summary-list { margin: 0; border: 1px solid var(--pub-line); border-radius: .8rem; }
    .summary-list > div { display: flex; justify-content: space-between; gap: 1rem; padding: .65rem .9rem; }
    .summary-list > div + div { border-top: 1px solid var(--pub-line); }
    .summary-list dt { font-weight: 500; color: var(--pub-muted); font-size: .85rem; }
    .summary-list dd { margin: 0; font-weight: 600; text-align: right; }

    /* Auth (login) */
    .auth-shell { min-height: 100vh; display: flex; align-items: center; background: var(--pub-soft); padding-block: 2rem; }
    .auth-card { max-width: 400px; margin: auto; box-shadow: 0 20px 50px -30px rgba(15,23,42,.35); }
    .auth-brand { display: flex; align-items: center; justify-content: center; gap: .55rem; font-weight: 700; font-size: 1.1rem; }

    /* ---------- Landing page details ---------- */
    body.page-home {
        background:
            radial-gradient(900px 380px at 8% -60px, rgba(37,99,235,.09), transparent 70%),
            radial-gradient(700px 320px at 96% -40px, rgba(14,165,233,.08), transparent 70%) no-repeat,
            #fff;
        background-repeat: no-repeat;
    }
    .site-header { transition: box-shadow .2s ease; }
    .site-header.is-scrolled { box-shadow: 0 6px 20px -16px rgba(15,23,42,.45); }

    .hero { max-width: 48rem; padding-top: .5rem; }
    .hero h1 { text-wrap: balance; }
    .hero p { text-wrap: pretty; max-width: 40rem; }
    .hero-pill { display: inline-flex; align-items: center; gap: .5rem; font-size: .8rem; font-weight: 600; color: var(--pub-ink); background: #fff; border: 1px solid var(--pub-line); border-radius: 999px; padding: .3rem .85rem .3rem .65rem; margin-bottom: 1rem; box-shadow: 0 4px 14px -10px rgba(15,23,42,.3); }
    .hero-pill__dot { width: .5rem; height: .5rem; border-radius: 50%; background: #22c55e; animation: pub-pulse 2.2s ease-out infinite; }
    @keyframes pub-pulse {
        0% { box-shadow: 0 0 0 0 rgba(34,197,94,.45); }
        70% { box-shadow: 0 0 0 7px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }
    .hero-accent { background: linear-gradient(90deg, var(--pub-accent), #0ea5e9); -webkit-background-clip: text; background-clip: text; color: transparent; }

    .type-chips { display: flex; gap: .5rem; overflow-x: auto; scrollbar-width: none; margin: -1.25rem 0 1.75rem; padding-bottom: .25rem; }
    .type-chips::-webkit-scrollbar { display: none; }
    .type-chip { flex-shrink: 0; display: inline-flex; align-items: center; gap: .4rem; border: 1px solid var(--pub-line); background: #fff; color: var(--pub-ink); font-weight: 600; font-size: .85rem; border-radius: 999px; padding: .42rem .95rem; transition: background .15s, border-color .15s, color .15s; }
    .type-chip span { color: var(--pub-muted); font-weight: 500; font-size: .76rem; }
    .type-chip:hover { border-color: #cbd5e1; background: var(--pub-soft); }
    .type-chip.is-active { background: var(--pub-ink); border-color: var(--pub-ink); color: #fff; }
    .type-chip.is-active span { color: rgba(255,255,255,.7); }
    .type-chip:focus-visible { outline: 2px solid var(--pub-accent); outline-offset: 2px; }

    .home-card__badge { position: absolute; top: .7rem; left: .7rem; background: rgba(255,255,255,.94); backdrop-filter: blur(4px); color: var(--pub-ink); font-size: .72rem; font-weight: 600; padding: .25rem .6rem; border-radius: 999px; box-shadow: 0 2px 8px -4px rgba(15,23,42,.3); }
    .home-card__count { position: absolute; bottom: .7rem; left: .7rem; background: rgba(15,23,42,.62); color: #fff; font-size: .72rem; font-weight: 600; padding: .2rem .55rem; border-radius: 999px; }
    .home-card__go { position: absolute; top: .7rem; right: .7rem; width: 2rem; height: 2rem; border-radius: 50%; background: #fff; color: var(--pub-ink); display: flex; align-items: center; justify-content: center; opacity: 0; transform: scale(.8); transition: opacity .2s ease, transform .2s ease; box-shadow: 0 4px 12px -6px rgba(15,23,42,.35); }
    .home-card:hover .home-card__go, .home-card:focus-visible .home-card__go { opacity: 1; transform: none; }

    .steps { margin-top: 5rem; padding-top: 3.5rem; border-top: 1px solid var(--pub-line); }
    .steps__eyebrow { font-size: .75rem; font-weight: 600; color: var(--pub-accent-dark); text-transform: uppercase; letter-spacing: .09em; }
    .step { position: relative; height: 100%; padding: 1.5rem; border: 1px solid var(--pub-line); border-radius: 1rem; background: #fff; transition: border-color .2s, box-shadow .2s, transform .2s; }
    .step:hover { border-color: #cbd5e1; box-shadow: 0 14px 30px -24px rgba(15,23,42,.4); transform: translateY(-2px); }
    .step__num { position: absolute; top: 1.25rem; right: 1.4rem; color: #cbd5e1; font-weight: 700; font-size: .85rem; }
    .step__icon { width: 2.6rem; height: 2.6rem; border-radius: .75rem; background: rgba(37,99,235,.08); color: var(--pub-accent-dark); display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1rem; }
    .step h3 { font-size: 1rem; margin-bottom: .35rem; }
    .step p { color: var(--pub-muted); font-size: .9rem; margin: 0; }

    /* Reveal on scroll (only when JS has flagged the page) */
    .js-reveal [data-reveal] { opacity: 0; transform: translateY(14px); transition: opacity .6s ease, transform .6s cubic-bezier(.2,.7,.2,1); transition-delay: var(--reveal-delay, 0ms); }
    .js-reveal [data-reveal].is-visible { opacity: 1; transform: none; }

    @media (prefers-reduced-motion: reduce) {
        .home-skeleton__media, .home-skeleton__line { animation: none; }
        .home-card__media img, .home-card__go, .step { transition: none; }
        .hero-pill__dot { animation: none; }
        .js-reveal [data-reveal] { opacity: 1; transform: none; transition: none; }
    }
</style>
