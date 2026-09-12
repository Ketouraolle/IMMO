<style>
    :root {
        --pub-midnight: #0F1E3A;
        --pub-midnight-soft: #173259;
        --pub-canvas: #F3F7FC;
        --pub-blue: #2563EB;
        --pub-blue-dark: #1D4ED8;
        --pub-sky: #0EA5E9;
        --pub-sky-dark: #0369A1;
        --pub-sky-light: #93C5FD;
        --pub-ink: #12192B;
        --pub-ink-soft: #57647F;
        --pub-sand: #DCE6F4;
    }

    body { background: var(--pub-canvas); color: var(--pub-ink); }
    h1, h2, h3, h4, h5, h6 { font-family: 'Fraunces', ui-serif, Georgia, serif; letter-spacing: -.01em; }

    a { color: var(--pub-blue-dark); }

    /* Airmail stripe — signature motif, bookends the page */
    .airmail-stripe {
        height: 6px;
        background: repeating-linear-gradient(
            -45deg,
            var(--pub-blue) 0 14px,
            var(--pub-canvas) 14px 22px,
            var(--pub-sky) 22px 36px,
            var(--pub-canvas) 36px 44px
        );
    }

    /* Navbar */
    .pub-navbar { background: var(--pub-midnight); padding-top: .9rem; padding-bottom: .9rem; }
    .pub-navbar .navbar-brand { font-family: 'Fraunces', serif; font-weight: 700; font-size: 1.3rem; color: #fff; display: flex; align-items: center; gap: .55rem; }
    .pub-navbar .navbar-brand .mark {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.9rem; height: 1.9rem; border-radius: 50%; background: var(--pub-blue); font-size: .95rem;
    }
    .pub-navbar .btn-login {
        border-radius: 999px; border: 1px solid rgba(255,255,255,.35); color: #fff;
        font-weight: 600; font-size: .82rem; padding: .5rem 1.15rem; transition: all .15s ease;
        white-space: nowrap;
    }
    .pub-navbar .btn-login:hover { background: #fff; color: var(--pub-midnight); border-color: #fff; }

    /* Hero */
    .pub-hero { padding: 3.25rem 0 2.25rem; }
    .pub-eyebrow {
        font-family: 'IBM Plex Mono', ui-monospace, monospace;
        font-size: .72rem; font-weight: 600; letter-spacing: .14em; text-transform: uppercase;
        color: var(--pub-blue-dark);
    }
    .pub-hero h1 {
        font-size: clamp(1.9rem, 3.2vw + 1rem, 3.15rem);
        line-height: 1.08; margin: .7rem 0 1rem; color: var(--pub-ink); font-weight: 600;
    }
    .pub-hero h1 em { font-style: normal; color: var(--pub-blue); }
    .pub-hero p.lead { color: var(--pub-ink-soft); font-size: 1.05rem; max-width: 36rem; }
    .pub-stats { display: flex; gap: 2rem; flex-wrap: wrap; margin-top: 1.9rem; padding-top: 1.5rem; border-top: 1px solid var(--pub-sand); }
    .pub-stats__num { font-family: 'IBM Plex Mono', monospace; font-weight: 600; font-size: 1.45rem; color: var(--pub-midnight); display: block; line-height: 1.1; }
    .pub-stats__label { font-size: .76rem; color: var(--pub-ink-soft); }
    .pub-hero__aside {
        background: var(--pub-midnight);
        background-image: radial-gradient(520px 260px at 90% -10%, rgba(14,165,233,.35), transparent);
        border-radius: 1.1rem; color: #fff; padding: 1.5rem; height: 100%;
    }
    .pub-hero__aside .pub-eyebrow { color: var(--pub-sky-light); }
    .pub-hero__aside p { color: rgba(255,255,255,.78); font-size: .92rem; margin-bottom: .9rem; }
    .pub-hero__aside ul { list-style: none; padding: 0; margin: 0; }
    .pub-hero__aside li { display: flex; gap: .55rem; align-items: flex-start; padding: .5rem 0; border-top: 1px dashed rgba(255,255,255,.15); font-size: .88rem; color: rgba(255,255,255,.9); }
    .pub-hero__aside li:first-of-type { border-top: none; }

    /* Cards, forms, buttons — public pages only */
    .card { border: 1px solid var(--pub-sand); border-radius: 1rem; box-shadow: 0 10px 26px -20px rgba(18,26,43,.25); }
    .card-header { background: #fff; border-bottom: 1px solid var(--pub-sand); font-weight: 600; border-radius: 1rem 1rem 0 0 !important; }
    .form-control, .form-select { border-radius: .6rem; border-color: var(--pub-sand); }
    .form-control:focus, .form-select:focus { border-color: var(--pub-blue); box-shadow: 0 0 0 .2rem rgba(37,99,235,.15); }
    .form-label { font-weight: 600; font-size: .85rem; color: var(--pub-ink); }

    .btn-dark, .btn-accent { background: var(--pub-blue); border-color: var(--pub-blue); color: #fff; font-weight: 600; border-radius: .6rem; }
    .btn-dark:hover, .btn-accent:hover { background: var(--pub-blue-dark); border-color: var(--pub-blue-dark); color: #fff; }
    .btn-outline-dark { border-color: var(--pub-midnight); color: var(--pub-midnight); font-weight: 600; border-radius: .6rem; }
    .btn-outline-dark:hover { background: var(--pub-midnight); border-color: var(--pub-midnight); color: #fff; }
    .btn:disabled { opacity: .65; }

    /* Search console (property filters) */
    .search-console { background: #fff; border: 1px solid var(--pub-sand); border-radius: 1rem; padding: 1.1rem 1.2rem; box-shadow: 0 12px 30px -20px rgba(18,26,43,.25); }
    .search-console label { font-family: 'IBM Plex Mono', monospace; font-size: .66rem; text-transform: uppercase; letter-spacing: .08em; color: var(--pub-ink-soft); }
    .search-console .btn-outline-secondary { border-radius: .6rem; font-weight: 600; border-color: var(--pub-sand); color: var(--pub-ink-soft); }
    .search-console .btn-outline-secondary:hover { background: var(--pub-sand); color: var(--pub-ink); }

    /* Ticket-stub property cards */
    .ticket-card { border: 1px solid var(--pub-sand); border-radius: 1rem; transition: transform .2s ease, box-shadow .2s ease; box-shadow: 0 1px 2px rgba(18,26,43,.05); }
    .ticket-card:hover { transform: translateY(-4px); box-shadow: 0 18px 34px -18px rgba(18,26,43,.35); }
    .ticket-card__photo { position: relative; overflow: hidden; border-radius: 1rem 1rem 0 0; height: 190px; background: #dfe3e8; }
    .ticket-card__photo img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s ease; }
    .ticket-card:hover .ticket-card__photo img { transform: scale(1.06); }
    .ticket-card__type {
        position: absolute; top: .65rem; right: .65rem; background: rgba(15,30,58,.78); color: #fff;
        font-size: .66rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; padding: .3rem .55rem; border-radius: 999px;
    }
    .ticket-card__perf { position: relative; border-top: 2px dashed var(--pub-sand); }
    .ticket-card__perf::before, .ticket-card__perf::after {
        content: ''; position: absolute; top: -9px; width: 18px; height: 18px; border-radius: 50%; background: var(--pub-canvas);
    }
    .ticket-card__perf::before { left: -9px; }
    .ticket-card__perf::after { right: -9px; }
    .ticket-card__body { padding: 1rem 1.1rem 1.15rem; }
    .ticket-card__route { display: flex; align-items: center; gap: .45rem; margin-bottom: .4rem; }
    .ticket-card__code { font-family: 'IBM Plex Mono', monospace; font-weight: 600; font-size: .7rem; letter-spacing: .06em; color: #fff; background: var(--pub-sky-dark); padding: .12rem .4rem; border-radius: .3rem; }
    .ticket-card__dest { font-size: .78rem; color: var(--pub-ink-soft); }
    .ticket-card__name { font-size: 1.05rem; font-weight: 600; margin-bottom: .6rem; color: var(--pub-ink); }
    .ticket-card__foot { display: flex; align-items: center; justify-content: space-between; }
    .ticket-card__price { font-family: 'IBM Plex Mono', monospace; font-weight: 600; font-size: 1.02rem; color: var(--pub-blue-dark); }
    .ticket-card__price span { font-family: 'Inter', sans-serif; font-weight: 400; font-size: .72rem; color: var(--pub-ink-soft); }
    .ticket-card__cta { font-size: .8rem; font-weight: 600; color: var(--pub-midnight); }
    a.ticket-card-link { text-decoration: none; }
    a.ticket-card-link:hover .ticket-card__cta { color: var(--pub-blue-dark); }

    /* Skeleton loading state for the live filter grid */
    .ticket-skeleton { border: 1px solid var(--pub-sand); border-radius: 1rem; overflow: hidden; }
    .ticket-skeleton__photo, .ticket-skeleton__line {
        background: linear-gradient(100deg, #dbe6f4 8%, #eef4fb 18%, #dbe6f4 33%);
        background-size: 200% 100%; animation: pub-shimmer 1.4s ease-in-out infinite;
    }
    .ticket-skeleton__photo { height: 190px; }
    .ticket-skeleton__body { padding: 1rem 1.1rem; }
    .ticket-skeleton__line { height: .7rem; border-radius: .3rem; margin-bottom: .6rem; }
    .ticket-skeleton__line--w60 { width: 60%; }
    .ticket-skeleton__line--w40 { width: 40%; }
    @keyframes pub-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    @media (prefers-reduced-motion: reduce) {
        .ticket-skeleton__photo, .ticket-skeleton__line { animation: none; }
    }

    /* Pagination */
    .pagination .page-link { border: none; color: var(--pub-ink-soft); font-weight: 600; border-radius: .5rem !important; margin: 0 .15rem; }
    .pagination .page-item.active .page-link { background: var(--pub-midnight); color: #fff; }
    .pagination .page-link:hover { background: var(--pub-sand); color: var(--pub-ink); }

    /* Status pills */
    .status-pill { display: inline-flex; align-items: center; gap: .35rem; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; padding: .32rem .65rem; border-radius: 999px; }
    .status-pill--vacant { background: rgba(14,165,233,.12); color: var(--pub-sky-dark); }
    .status-pill--occupied { background: rgba(18,25,43,.08); color: var(--pub-ink-soft); }

    /* Property passport panel (public/show) */
    .passport { background: #fff; border: 1px solid var(--pub-sand); border-radius: 1rem; padding: .3rem 1.2rem; }
    .passport__row { display: flex; justify-content: space-between; align-items: center; padding: .7rem 0; border-bottom: 1px dashed var(--pub-sand); }
    .passport__row:last-child { border-bottom: none; }
    .passport__label { font-family: 'IBM Plex Mono', monospace; font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: var(--pub-ink-soft); }
    .passport__value { font-weight: 600; color: var(--pub-ink); }

    .back-pill {
        display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 600;
        color: var(--pub-ink-soft); text-decoration: none; margin-bottom: 1rem;
    }
    .back-pill:hover { color: var(--pub-blue-dark); }

    /* Footer */
    .pub-footer { background: var(--pub-midnight); color: rgba(255,255,255,.75); padding: 3rem 0 1.75rem; margin-top: 3.5rem; }
    .pub-footer h6 { font-family: 'Fraunces', serif; color: #fff; font-weight: 600; margin-bottom: .9rem; }
    .pub-footer a { color: rgba(255,255,255,.75); text-decoration: none; }
    .pub-footer a:hover { color: #fff; }
    .pub-footer p { font-size: .88rem; color: rgba(255,255,255,.65); }
    .pub-footer__bottom { border-top: 1px solid rgba(255,255,255,.12); margin-top: 2rem; padding-top: 1.4rem; font-size: .78rem; color: rgba(255,255,255,.5); }

    /* Auth shell (login) */
    .auth-shell {
        min-height: 100vh; display: flex; align-items: center;
        background: var(--pub-midnight);
        background-image: radial-gradient(900px 420px at 12% -10%, rgba(14,165,233,.28), transparent),
                           linear-gradient(160deg, var(--pub-midnight) 0%, var(--pub-midnight-soft) 100%);
    }
    .auth-card { max-width: 400px; margin: auto; border-radius: 1.1rem; box-shadow: 0 30px 60px -20px rgba(0,0,0,.5); border: 1px solid var(--pub-sand); }
    .auth-brand { display: flex; align-items: center; justify-content: center; gap: .5rem; font-weight: 700; }
    .auth-brand .mark { display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 50%; background: var(--pub-blue); font-size: 1rem; }

    @media (max-width: 767.98px) {
        .pub-hero { padding: 2rem 0 1.5rem; }
        .pub-hero__aside { margin-top: 1.5rem; }
        .pub-stats { gap: 1.4rem; }
    }
</style>
