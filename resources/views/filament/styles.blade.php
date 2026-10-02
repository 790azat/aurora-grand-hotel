<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Noto+Sans+Armenian:wght@400;500;600;700&family=Noto+Serif+Armenian:wght@600;700&display=swap">
<style>
    /* Manrope has no Armenian glyphs; fall back to Noto for them. */
    :root { --font-family: 'Manrope', 'Noto Sans Armenian', ui-sans-serif, system-ui, sans-serif; }

    /* ---------- Brand ---------- */
    .ag-brand { display: flex; align-items: center; gap: .65rem; height: 100%; }
    .ag-brand-mark { width: 2.25rem; height: 2.25rem; flex-shrink: 0; filter: drop-shadow(0 2px 6px rgb(15 26 46 / .25)); }
    .dark .ag-brand-mark rect { stroke: rgb(227 201 143 / .45); stroke-width: 1; }
    .ag-brand-text { display: flex; flex-direction: column; line-height: 1; }
    .ag-brand-name { font-family: 'Cormorant Garamond', 'Noto Serif Armenian', Georgia, serif; font-weight: 700; font-size: 1.35rem; letter-spacing: .01em; color: #0f1a2e; }
    .dark .ag-brand-name { color: #f3ead7; }
    .ag-brand-sub { font-size: .6rem; text-transform: uppercase; letter-spacing: .22em; color: var(--primary-600); margin-top: .2rem; font-weight: 600; }
    .dark .ag-brand-sub { color: var(--primary-400); }

    /* ---------- Language switch ---------- */
    .ag-lang { display: inline-flex; border-radius: 9999px; padding: 2px; background: var(--gray-100); margin-inline-end: .5rem; }
    .dark .ag-lang { background: rgb(255 255 255 / .06); }
    .ag-lang-item { font-size: .7rem; font-weight: 700; letter-spacing: .06em; padding: .25rem .6rem; border-radius: 9999px; color: var(--gray-500); transition: all .15s; }
    .ag-lang-item:hover { color: var(--gray-900); }
    .dark .ag-lang-item:hover { color: #fff; }
    .ag-lang-item.is-active { background: var(--primary-500); color: #fff; box-shadow: 0 1px 3px rgb(0 0 0 / .15); }

    /* ---------- Login demo hint ---------- */
    .ag-demo { border: 1px solid color-mix(in oklab, var(--primary-500) 35%, transparent); background: color-mix(in oklab, var(--primary-500) 7%, transparent); border-radius: .9rem; padding: .9rem 1rem; font-size: .85rem; }
    .ag-demo-head { display: flex; align-items: center; gap: .45rem; font-weight: 700; color: var(--primary-700); }
    .dark .ag-demo-head { color: var(--primary-300); }
    .ag-demo-icon { width: 1.1rem; height: 1.1rem; }
    .ag-demo-lang { margin-inline-start: auto; display: flex; gap: .35rem; font-size: .7rem; }
    .ag-demo-lang a { padding: .1rem .45rem; border-radius: 9999px; color: var(--gray-500); }
    .ag-demo-lang a.is-active { background: var(--primary-500); color: #fff; }
    .ag-demo-text { color: var(--gray-600); margin: .35rem 0 .6rem; }
    .dark .ag-demo-text { color: var(--gray-400); }
    .ag-demo-list { display: grid; gap: .3rem; }
    .ag-demo-row { width: 100%; display: flex; justify-content: space-between; align-items: center; gap: .5rem; padding: .4rem .6rem; border-radius: .55rem; background: var(--color-white, #fff); border: 1px solid var(--gray-200); text-align: start; transition: border-color .15s, transform .15s; cursor: pointer; }
    .dark .ag-demo-row { background: rgb(255 255 255 / .04); border-color: rgb(255 255 255 / .08); }
    .ag-demo-row:hover { border-color: var(--primary-500); }
    .ag-demo-role { font-weight: 600; color: var(--gray-700); }
    .dark .ag-demo-role { color: var(--gray-200); }
    .ag-demo code, .ag-demo-pass code { font-size: .78rem; color: var(--gray-500); }
    .ag-demo-pass { margin-top: .5rem; color: var(--gray-500); font-size: .8rem; }


    /* ---------- Booking quote ---------- */
    .ag-quote { font-size: .875rem; }
    .ag-quote-empty { text-align: center; padding: 1rem .5rem; color: var(--gray-500); }
    .ag-quote-empty-icon { width: 2rem; height: 2rem; margin: 0 auto .5rem; opacity: .6; }
    .ag-quote-lines > div { display: flex; justify-content: space-between; gap: 1rem; padding: .35rem 0; }
    .ag-quote-lines dt { color: var(--gray-700); }
    .dark .ag-quote-lines dt { color: var(--gray-300); }
    .ag-quote-lines dd { font-variant-numeric: tabular-nums; font-weight: 600; white-space: nowrap; }
    .ag-quote-lines .is-sub dt, .ag-quote-lines .is-sub dd { color: var(--gray-500); font-weight: 500; }
    .ag-quote-lines .is-sub dt span { opacity: .75; }
    .ag-quote-lines .is-discount { color: var(--success-600); }
    .ag-quote-lines .is-discount dt { color: inherit; }
    .ag-quote-lines .is-error dt { color: var(--danger-600); }
    .ag-quote-total { display: flex; justify-content: space-between; align-items: baseline; margin-top: .5rem; padding-top: .75rem; border-top: 1px dashed var(--gray-300); }
    .dark .ag-quote-total { border-color: rgb(255 255 255 / .12); }
    .ag-quote-total span { font-weight: 600; }
    .ag-quote-total strong { font-size: 1.5rem; color: var(--primary-600); font-variant-numeric: tabular-nums; }
    .dark .ag-quote-total strong { color: var(--primary-400); }
    .ag-quote-note { margin-top: .5rem; font-size: .75rem; color: var(--gray-500); }
    .ag-quote-warn { margin-top: .5rem; font-size: .75rem; color: var(--warning-600); }


    .ag-muted { color: var(--gray-500); font-weight: 400; font-size: .8em; }
    .ag-breakdown-paid { margin-top: .75rem; padding-top: .5rem; border-top: 1px solid var(--gray-200); }
    .dark .ag-breakdown-paid { border-color: rgb(255 255 255 / .08); }

    /* ---------- Timeline ---------- */
    .ag-timeline { position: relative; display: grid; gap: 1rem; padding-inline-start: .25rem; }
    .ag-timeline::before { content: ''; position: absolute; inset-inline-start: 1.05rem; top: .5rem; bottom: .5rem; width: 2px; background: var(--gray-200); }
    .dark .ag-timeline::before { background: rgb(255 255 255 / .08); }
    .ag-timeline-item { position: relative; display: flex; gap: .75rem; align-items: flex-start; }
    .ag-timeline-dot { position: relative; z-index: 1; display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: 9999px; background: var(--gray-100); color: var(--gray-500); box-shadow: 0 0 0 4px var(--color-white, #fff); flex-shrink: 0; }
    .dark .ag-timeline-dot { background: var(--gray-800); box-shadow: 0 0 0 4px var(--gray-900); }
    .ag-timeline-dot svg { width: 1rem; height: 1rem; }
    .ag-timeline-item.is-success .ag-timeline-dot { background: color-mix(in oklab, var(--success-500) 15%, transparent); color: var(--success-600); }
    .ag-timeline-item.is-info .ag-timeline-dot { background: color-mix(in oklab, var(--info-500) 15%, transparent); color: var(--info-600); }
    .ag-timeline-item.is-danger .ag-timeline-dot { background: color-mix(in oklab, var(--danger-500) 15%, transparent); color: var(--danger-600); }
    .ag-timeline-title { font-size: .875rem; font-weight: 600; color: var(--gray-900); }
    .dark .ag-timeline-title { color: var(--gray-100); }
    .ag-timeline-time { font-size: .75rem; color: var(--gray-500); }


    /* ---------- Image previews ---------- */
    .ag-image-strip { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: .6rem; margin-top: .25rem; }
    .ag-image-strip figure { position: relative; aspect-ratio: 4 / 3; border-radius: .6rem; overflow: hidden; background: linear-gradient(135deg, #1e2a44, #b8914a); }
    .ag-image-strip img { width: 100%; height: 100%; object-fit: cover; }
    .ag-image-strip figure.is-broken img { display: none; }
    .ag-image-strip figcaption { position: absolute; left: .4rem; bottom: .4rem; font-size: .65rem; font-weight: 700; color: #fff; background: rgb(0 0 0 / .45); padding: .1rem .4rem; border-radius: 9999px; }
    .ag-image-single { max-width: 22rem; aspect-ratio: 16 / 10; border-radius: .75rem; overflow: hidden; background: linear-gradient(135deg, #1e2a44, #b8914a); }
    .ag-image-single img { width: 100%; height: 100%; object-fit: cover; }

    @include('filament.partials.tape-chart-styles')
    @include('filament.partials.live-chat-styles')
</style>
