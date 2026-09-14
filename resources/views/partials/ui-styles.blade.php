<style>
    /* ---------- Theme tokens: light by default, dark via [data-bs-theme="dark"] (Bootstrap 5.3 color modes) ---------- */
    :root, [data-bs-theme="light"] {
        --ink: #14213d;
        --ink-soft: #3a4a6b;
        --accent: #0EA5E9;
        --accent-dark: #0369A1;
        --accent-btn: #0369A1;
        --accent-btn-hover: #075985;
        --bg: #f5f6fa;
        --surface: #ffffff;
        --surface-2: #eef1f8;
        --surface-hover: #f5f7fb;
        --border-soft: rgba(20, 33, 61, .08);
        --border-strong: rgba(20, 33, 61, .15);
        --outline: #14213d;
        --primary: #14213d;
        --primary-hover: #0d1830;
        --primary-contrast: #ffffff;
        --navy: #14213d;          /* brand navy: sidebar, toasts, banners */
        --navy-2: #1f3a63;
        --topbar-bg: rgba(245, 246, 250, .88);
        --unread-bg: #f0f9ff;
        --placeholder: #dfe3e8;
        --shadow-card: 0 1px 2px rgba(20,33,61,.06), 0 8px 24px -16px rgba(20,33,61,.15);
        --shadow-pop: 0 20px 44px -18px rgba(20,33,61,.35);
        --soft-success-bg: #dcfce7; --soft-success-fg: #166534;
        --soft-warning-bg: #fef3c7; --soft-warning-fg: #92400e;
        --soft-danger-bg: #fee2e2;  --soft-danger-fg: #991b1b;
        --soft-info-bg: #e0f2fe;    --soft-info-fg: #075985;
        --soft-neutral-bg: #eef1f5; --soft-neutral-fg: #475569;
    }
    [data-bs-theme="dark"] {
        --ink: #e5e9f2;
        --ink-soft: #9aa7bd;
        --accent: #38bdf8;
        --accent-dark: #7dd3fc;
        --accent-btn: #0284c7;
        --accent-btn-hover: #0369a1;
        --bg: #0b1120;
        --surface: #121a2b;
        --surface-2: #19233a;
        --surface-hover: #1b2640;
        --border-soft: rgba(148, 163, 184, .14);
        --border-strong: rgba(148, 163, 184, .28);
        --outline: rgba(148, 163, 184, .45);
        --primary: #e5e9f2;
        --primary-hover: #ffffff;
        --primary-contrast: #0b1120;
        --navy: #0e1629;
        --navy-2: #1a2a4a;
        --topbar-bg: rgba(11, 17, 32, .85);
        --unread-bg: rgba(56, 189, 248, .08);
        --placeholder: #1e293b;
        --shadow-card: 0 1px 2px rgba(0,0,0,.4), 0 8px 24px -16px rgba(0,0,0,.6);
        --shadow-pop: 0 20px 44px -18px rgba(0,0,0,.7);
        --soft-success-bg: rgba(34,197,94,.15);  --soft-success-fg: #86efac;
        --soft-warning-bg: rgba(245,158,11,.16); --soft-warning-fg: #fcd34d;
        --soft-danger-bg: rgba(239,68,68,.16);   --soft-danger-fg: #fca5a5;
        --soft-info-bg: rgba(56,189,248,.14);    --soft-info-fg: #7dd3fc;
        --soft-neutral-bg: rgba(148,163,184,.14); --soft-neutral-fg: #cbd5e1;
        --bs-body-bg: #0b1120;
        --bs-body-color: #e5e9f2;
        --bs-border-color: rgba(148, 163, 184, .22);
    }

    body {
        background: var(--bg);
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        color: var(--ink);
        transition: background-color .2s ease, color .2s ease;
    }

    h1, h2, h3, h4, h5, h6 { font-weight: 700; letter-spacing: -.01em; }

    .navbar-brand { font-weight: 800; letter-spacing: .2px; font-size: 1.2rem; }
    .min-w-0 { min-width: 0; }

    /* ---------- App shell: sidebar + topbar ---------- */
    .app-sidebar { --bs-offcanvas-width: 272px; --bs-offcanvas-bg: var(--navy); }
    .app-sidebar__inner { background: var(--navy); color: rgba(255,255,255,.72); display: flex; flex-direction: column; height: 100%; width: 100%; }
    .app-brand { display: flex; align-items: center; gap: .6rem; padding: 1.25rem 1.25rem 1rem; color: #fff; font-weight: 800; font-size: 1.1rem; text-decoration: none; }
    .app-brand:hover { color: #fff; }
    .app-brand__mark { width: 2rem; height: 2rem; border-radius: .55rem; background: #0EA5E9; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; }
    .app-nav { flex: 1; overflow-y: auto; padding: 0 .75rem .75rem; }
    .app-nav__label { font-size: .66rem; text-transform: uppercase; letter-spacing: .09em; color: rgba(255,255,255,.4); padding: 1.1rem .75rem .4rem; font-weight: 600; }
    .app-nav__link { position: relative; display: flex; align-items: center; gap: .7rem; padding: .55rem .75rem; margin-bottom: .1rem; border-radius: .55rem; color: rgba(255,255,255,.72); text-decoration: none; font-weight: 500; font-size: .9rem; transition: background .15s, color .15s; }
    .app-nav__link i { font-size: 1.02rem; width: 1.2rem; text-align: center; }
    .app-nav__link:hover { background: rgba(255,255,255,.06); color: #fff; }
    .app-nav__link.active { background: rgba(14,165,233,.14); color: #fff; }
    .app-nav__link.active::before { content: ''; position: absolute; left: -.75rem; top: .45rem; bottom: .45rem; width: 3px; border-radius: 0 3px 3px 0; background: #0EA5E9; }
    .app-nav__link.active i { color: #38bdf8; }
    .app-nav__badge { margin-left: auto; background: #0EA5E9; color: #fff; font-size: .68rem; font-weight: 700; border-radius: 999px; padding: .12rem .45rem; min-width: 1.3rem; text-align: center; }
    .app-user { display: flex; align-items: center; gap: .65rem; padding: .9rem 1rem; border-top: 1px solid rgba(255,255,255,.08); }
    .app-user__avatar { width: 2.1rem; height: 2.1rem; border-radius: 50%; background: rgba(255,255,255,.12); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .78rem; flex-shrink: 0; }
    .app-user__name { color: #fff; font-weight: 600; font-size: .86rem; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .app-user__role { font-size: .74rem; color: rgba(255,255,255,.5); }
    .app-user__logout { background: none; border: none; color: rgba(255,255,255,.6); font-size: 1.15rem; padding: .25rem .45rem; border-radius: .4rem; }
    .app-user__logout:hover { color: #fff; background: rgba(255,255,255,.08); }

    .app-topbar { position: sticky; top: 0; z-index: 1020; height: 60px; display: flex; align-items: center; gap: .6rem; padding: 0 1.5rem; background: var(--topbar-bg); backdrop-filter: blur(8px); border-bottom: 1px solid var(--border-soft); }
    .app-topbar__date { color: var(--ink-soft); font-size: .86rem; }
    .app-content { padding: 1.75rem 1.5rem 3rem; max-width: 1320px; }
    .icon-btn { position: relative; width: 2.4rem; height: 2.4rem; border-radius: .6rem; border: 1px solid var(--border-soft); background: var(--surface); color: var(--ink); display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .icon-btn:hover { background: var(--surface-2); }

    @media (min-width: 992px) {
        .app-sidebar { position: fixed; inset: 0 auto 0 0; width: 248px; z-index: 1030; }
        .app-main { margin-left: 248px; }
        .app-content { padding: 2rem 2.25rem 3rem; }
    }
    @media (max-width: 575.98px) {
        .app-content { padding: 1.25rem 1rem 2.5rem; }
        .app-topbar { padding: 0 1rem; }
    }

    /* Theme toggle + language switch */
    .theme-toggle__light { display: none; }
    [data-bs-theme="dark"] .theme-toggle__light { display: inline-block; }
    [data-bs-theme="dark"] .theme-toggle__dark { display: none; }
    .locale-switch { display: inline-flex; align-items: center; padding: .2rem; border-radius: 999px; border: 1px solid var(--border-soft); background: var(--surface); }
    .locale-switch a { font-size: .7rem; font-weight: 700; letter-spacing: .04em; padding: .28rem .55rem; border-radius: 999px; color: var(--ink-soft); text-decoration: none; line-height: 1.1; }
    .locale-switch a:hover { color: var(--ink); }
    .locale-switch a.is-active { background: var(--primary); color: var(--primary-contrast); }

    .toast-stack { position: fixed; top: 1rem; right: 1rem; z-index: 2100; display: flex; flex-direction: column; gap: .5rem; }
    .app-toast { background: var(--navy); color: #fff; border: 1px solid rgba(255,255,255,.08); border-radius: .7rem; box-shadow: 0 16px 36px -16px rgba(0,0,0,.5); }
    .app-toast .bi { color: #4ade80; }
    .app-toast .btn-close { filter: invert(1) grayscale(1) brightness(2); }

    /* Notifications */
    .notif { position: relative; }
    .notif__dot { position: absolute; top: -.35rem; right: -.35rem; min-width: 1.2rem; height: 1.2rem; padding: 0 .3rem; border-radius: 999px; background: #ef4444; color: #fff; font-size: .64rem; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg); }
    .notif__panel { position: absolute; right: 0; top: calc(100% + .5rem); width: min(360px, calc(100vw - 2rem)); background: var(--surface); border-radius: .8rem; box-shadow: var(--shadow-pop); border: 1px solid var(--border-soft); overflow: hidden; z-index: 1050; }
    .notif__head { display: flex; justify-content: space-between; align-items: center; padding: .8rem 1rem; border-bottom: 1px solid var(--border-soft); font-weight: 600; color: var(--ink); }
    .notif__list { max-height: 380px; overflow-y: auto; }
    .notif__item { display: flex; gap: .7rem; width: 100%; text-align: left; background: none; border: none; padding: .75rem 1rem; border-bottom: 1px solid var(--border-soft); }
    .notif__item:last-child { border-bottom: none; }
    .notif__item:hover { background: var(--surface-hover); }
    .notif__item.is-unread { background: var(--unread-bg); }
    .notif__icon { width: 2rem; height: 2rem; border-radius: .5rem; background: rgba(14,165,233,.12); color: var(--accent-dark); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .notif__title { font-weight: 600; font-size: .86rem; color: var(--ink); }
    .notif__body { font-size: .8rem; color: var(--ink-soft); }
    .notif__time { font-size: .72rem; color: var(--ink-soft); opacity: .8; margin-top: .15rem; }

    /* Page header + stats */
    .page-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .page-head h3 { margin: 0; }
    .page-head__sub { color: var(--ink-soft); margin: .25rem 0 0; font-size: .92rem; }
    .stat-icon { width: 2.4rem; height: 2.4rem; border-radius: .6rem; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; background: rgba(14,165,233,.12); color: var(--accent-dark); flex-shrink: 0; }
    .stat-icon--warning { background: var(--soft-warning-bg); color: var(--soft-warning-fg); }
    .stat-card .label { color: var(--ink-soft); font-size: .8rem; font-weight: 500; }
    .stat-card .hint { color: var(--ink-soft); font-size: .78rem; }
    .stat-card .value { font-size: 1.45rem; font-weight: 800; color: var(--ink); line-height: 1.25; margin: .1rem 0; }
    .back-to { font-size: .875rem; color: var(--ink-soft); text-decoration: none; }
    .back-to:hover { color: var(--ink); }

    /* Soft status badges */
    .badge-soft { display: inline-block; font-weight: 600; padding: .35em .7em; border-radius: 999px; font-size: .72rem; line-height: 1.2; }
    .badge-soft--success { background: var(--soft-success-bg); color: var(--soft-success-fg); }
    .badge-soft--warning { background: var(--soft-warning-bg); color: var(--soft-warning-fg); }
    .badge-soft--danger { background: var(--soft-danger-bg); color: var(--soft-danger-fg); }
    .badge-soft--info { background: var(--soft-info-bg); color: var(--soft-info-fg); }
    .badge-soft--neutral { background: var(--soft-neutral-bg); color: var(--soft-neutral-fg); }

    /* Filter pills */
    .pill-nav { display: flex; flex-wrap: wrap; gap: .4rem; }
    .pill-nav a { padding: .35rem .8rem; border-radius: 999px; font-size: .82rem; font-weight: 600; color: var(--ink-soft); text-decoration: none; background: var(--surface); border: 1px solid var(--border-soft); }
    .pill-nav a:hover { color: var(--ink); border-color: var(--border-strong); }
    .pill-nav a.active { background: var(--primary); border-color: var(--primary); color: var(--primary-contrast); }

    /* Chips (dates / times) and choice cards */
    .chip-group { display: flex; flex-wrap: wrap; gap: .5rem; }
    .chip { border: 1px solid var(--border-strong); background: var(--surface); color: var(--ink); border-radius: .6rem; padding: .45rem .8rem; font-weight: 600; font-size: .86rem; line-height: 1.15; transition: border-color .12s, background .12s; text-align: center; }
    .chip small { display: block; font-weight: 500; color: var(--ink-soft); font-size: .7rem; }
    .chip:hover:not(:disabled) { border-color: var(--accent); }
    .chip.is-selected { background: var(--primary); border-color: var(--primary); color: var(--primary-contrast); }
    .chip.is-selected small { color: var(--primary-contrast); opacity: .7; }
    .chip:disabled { opacity: .4; text-decoration: line-through; cursor: not-allowed; }
    .choice-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .6rem; }
    .choice-card { display: flex; align-items: center; gap: .7rem; text-align: left; border: 1.5px solid var(--border-strong); background: var(--surface); border-radius: .75rem; padding: .7rem .8rem; transition: border-color .12s, box-shadow .12s; width: 100%; color: var(--ink); }
    .choice-card:hover { border-color: rgba(14,165,233,.6); }
    .choice-card.is-selected { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
    .choice-card__title { font-weight: 600; font-size: .88rem; color: var(--ink); display: block; }
    .choice-card__hint { font-size: .75rem; color: var(--ink-soft); display: block; }
    .choice-card__check { margin-left: auto; color: var(--accent); opacity: 0; transition: opacity .12s; }
    .choice-card.is-selected .choice-card__check { opacity: 1; }
    .step-label { display: flex; align-items: center; gap: .5rem; font-weight: 600; font-size: .88rem; margin-bottom: .65rem; color: var(--ink); }
    .step-label__num { width: 1.35rem; height: 1.35rem; border-radius: 50%; background: var(--primary); color: var(--primary-contrast); font-size: .7rem; display: inline-flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }

    /* ---------- Bootstrap components on tokens ---------- */
    .card {
        background: var(--surface);
        color: var(--ink);
        border: none;
        border-radius: .75rem;
        box-shadow: var(--shadow-card);
    }
    .card-header, .card-header.bg-white { background-color: var(--surface) !important; color: var(--ink); border-bottom: 1px solid var(--border-soft); border-radius: .75rem .75rem 0 0 !important; }
    [data-bs-theme="dark"] .card { border: 1px solid var(--border-soft); }
    .card .list-group { --bs-list-group-bg: transparent; --bs-list-group-color: var(--ink); --bs-list-group-border-color: var(--border-soft); }
    .table { --bs-table-bg: transparent; --bs-table-color: var(--ink); --bs-table-border-color: var(--border-soft); }
    [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select, [data-bs-theme="dark"] .input-group-text { background-color: var(--surface-2); border-color: var(--border-strong); color: var(--ink); }
    [data-bs-theme="dark"] .form-control::placeholder { color: #64748b; }
    [data-bs-theme="dark"] .form-check-input:not(:checked) { background-color: var(--surface-2); border-color: var(--border-strong); }

    .btn { border-radius: .5rem; font-weight: 500; }
    .btn-dark { background: var(--primary); border-color: var(--primary); color: var(--primary-contrast); }
    .btn-dark:hover, .btn-dark:focus { background: var(--primary-hover); border-color: var(--primary-hover); color: var(--primary-contrast); }
    .btn-dark:disabled { background: var(--primary); border-color: var(--primary); color: var(--primary-contrast); }
    .btn-outline-dark { color: var(--ink); border-color: var(--outline); }
    .btn-outline-dark:hover { background: var(--primary); border-color: var(--primary); color: var(--primary-contrast); }
    .btn-accent { background: var(--accent-btn); border-color: var(--accent-btn); color: #fff; font-weight: 600; }
    .btn-accent:hover, .btn-accent:focus { background: var(--accent-btn-hover); border-color: var(--accent-btn-hover); color: #fff; }

    .badge-role-admin { background: var(--primary); color: var(--primary-contrast); }
    .badge-role-owner { background: #2563eb; }
    .badge-role-tenant { background: #0EA5E9; }

    table.table thead { background: var(--surface-2); }
    table.table thead th { font-weight: 600; color: var(--ink-soft); font-size: .85rem; text-transform: uppercase; letter-spacing: .03em; background: transparent; }
    table.table a { color: var(--ink); font-weight: 600; text-decoration: none; }
    table.table a:hover { color: var(--accent); text-decoration: underline; }

    /* Property gallery / cards */
    .property-thumb-wrap { position: relative; overflow: hidden; border-radius: .75rem .75rem 0 0; }
    .property-card img, .property-thumb {
        height: 190px;
        object-fit: cover;
        background: var(--placeholder);
        transition: transform .35s ease;
        display: block;
    }
    .property-card { overflow: hidden; transition: transform .2s ease, box-shadow .2s ease; }
    .property-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px -12px rgba(20,33,61,.25); }
    .property-card:hover img { transform: scale(1.06); }
    .price-tag { font-size: 1.15rem; font-weight: 800; color: var(--accent-dark); }

    /* Photo tiles (Livewire image manager) */
    .photo-tile { position: relative; border-radius: .6rem; overflow: hidden; background: var(--placeholder); aspect-ratio: 1 / 1; }
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
    .img-dropzone { border: 2px dashed var(--border-strong); border-radius: .75rem; background: var(--surface-2); transition: border-color .15s, background .15s; color: var(--ink); }
    .img-dropzone--active { border-color: var(--accent); background: rgba(14,165,233,.08); }

    /* Lightbox */
    .lightbox-backdrop { position: fixed; inset: 0; background: rgba(10,14,26,.92); z-index: 1080; display: flex; align-items: center; justify-content: center; }
    .lightbox-backdrop img { max-width: 92vw; max-height: 82vh; border-radius: .5rem; }
    .lightbox-close { position: absolute; top: 1rem; right: 1.25rem; color: #fff; font-size: 2rem; background: none; border: none; line-height: 1; cursor: pointer; }
    .lightbox-nav { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,.12); color: #fff; border: none; width: 3rem; height: 3rem; border-radius: 50%; font-size: 1.5rem; }
    .lightbox-nav:hover { background: rgba(255,255,255,.25); }
    .lightbox-nav--prev { left: 1rem; }
    .lightbox-nav--next { right: 1rem; }
    .gallery-thumb { cursor: zoom-in; transition: opacity .15s; }
    .gallery-thumb:hover { opacity: .8; }

    [x-cloak] { display: none !important; }

    /* Progressive image loading: shimmer placeholder fades out once the image paints */
    .progressive-img {
        background: linear-gradient(100deg, var(--placeholder) 8%, var(--surface-2) 18%, var(--placeholder) 33%);
        background-size: 200% 100%;
        animation: img-shimmer 1.4s ease-in-out infinite;
        opacity: 0;
        transition: opacity .45s ease;
    }
    .progressive-img.is-loaded { animation: none; background: none; opacity: 1; }
    @keyframes img-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    /* Mobile money */
    .mm-mark { width: 2.3rem; height: 2.3rem; border-radius: .6rem; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .68rem; letter-spacing: -.02em; flex-shrink: 0; font-family: 'Inter', sans-serif; }
    .mm-mark--om { background: #FF7900; color: #fff; }
    .mm-mark--momo { background: #FFCC00; color: #1a1a1a; }
    .mm-mark--lg { width: 3.4rem; height: 3.4rem; font-size: .92rem; border-radius: .9rem; }
    .pay-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(8,12,24,.6); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .pay-dialog { background: var(--surface); color: var(--ink); border-radius: 1rem; padding: 2rem 1.75rem; width: 100%; max-width: 340px; text-align: center; box-shadow: 0 30px 60px -20px rgba(0,0,0,.45); }
    .pay-dialog h5 { font-family: 'Inter', sans-serif; font-weight: 700; margin: 1rem 0 .35rem; font-size: 1.05rem; color: var(--ink); }
    .pay-dialog p { color: var(--ink-soft); font-size: .88rem; margin: 0; }
    .pay-dialog .spinner-border { width: 2.2rem; height: 2.2rem; color: var(--accent); margin-top: 1.2rem; }
    .pay-success { width: 3rem; height: 3rem; border-radius: 50%; background: var(--soft-success-bg); color: #16a34a; display: inline-flex; align-items: center; justify-content: center; font-size: 1.6rem; margin-top: 1rem; animation: pay-pop .3s ease; }
    @keyframes pay-pop { from { transform: scale(.6); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    /* Signature pad + contract document: always light, like paper */
    .signature-pad { position: relative; border: 1.5px dashed rgba(20,33,61,.25); border-radius: .75rem; background: #fff; }
    .signature-pad canvas { display: block; width: 100%; height: 180px; touch-action: none; cursor: crosshair; position: relative; z-index: 1; }
    .signature-pad__line { position: absolute; left: 1.5rem; right: 1.5rem; bottom: 2.6rem; border-bottom: 1px solid rgba(20,33,61,.15); }
    .signature-pad__hint { position: absolute; left: 0; right: 0; bottom: .9rem; text-align: center; color: #9aa3b5; font-size: .78rem; }
    .card.paper { background: #fff; color: #1f2937; }
    .contract-doc { padding: 2.5rem clamp(1.25rem, 5vw, 3.25rem); font-size: .94rem; line-height: 1.75; color: #1f2937; }
    .contract-doc h1, .contract-doc h2 { color: #14213d; }
    .contract-doc h1 { font-size: 1.3rem; text-align: center; letter-spacing: .03em; text-transform: uppercase; margin-bottom: .25rem; }
    .contract-doc h2 { font-size: .98rem; margin: 1.6rem 0 .5rem; }
    .contract-doc .contract-ref { text-align: center; color: #3a4a6b; font-size: .8rem; margin-bottom: 2rem; }
    .contract-doc dl { display: grid; grid-template-columns: minmax(120px, 190px) 1fr; gap: .3rem 1rem; margin: 0; }
    .contract-doc dt { font-weight: 500; color: #3a4a6b; }
    .contract-doc dd { margin: 0; font-weight: 600; }
    .contract-doc .text-muted { color: #6b7280 !important; }
    .signature-img { max-height: 90px; max-width: 260px; }

    @media print {
        .app-sidebar, .app-topbar, .toast-stack, .d-print-none { display: none !important; }
        .app-main { margin-left: 0 !important; }
        .app-content { padding: 0 !important; max-width: none; }
        body { background: #fff !important; color: #000 !important; }
        .card { box-shadow: none !important; background: #fff !important; color: #000 !important; border: none !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        .pay-success { animation: none; }
        .progressive-img { animation: none; transition: none; opacity: 1; background: var(--surface-2); }
        body { transition: none; }
    }
</style>
