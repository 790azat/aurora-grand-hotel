    /* ---------- Tape chart (Шахматка) ---------- */
    .ag-tape { --cw: 2.9rem; --lw: 11.5rem; --rh: 2.35rem;
        --line: var(--gray-200); --line-strong: var(--gray-300); --surface: #fff; --surface-2: var(--gray-50);
        --weekend: color-mix(in oklab, var(--primary-500) 6%, transparent); --today: color-mix(in oklab, var(--primary-500) 16%, transparent);
        --c-pending: #d95926; --c-confirmed: #2a78d6; --c-checked_in: #199e70; --c-checked_out: #64748b; }
    .dark .ag-tape { --line: rgb(255 255 255 / .07); --line-strong: rgb(255 255 255 / .14); --surface: var(--gray-900); --surface-2: rgb(255 255 255 / .03);
        --weekend: color-mix(in oklab, var(--primary-400) 7%, transparent); --today: color-mix(in oklab, var(--primary-400) 18%, transparent);
        --c-checked_out: #56657c; }
    @media (max-width: 640px) { .ag-tape { --cw: 2.4rem; --lw: 6.5rem; } .ag-room-floor, .ag-type-count { display: none; } .ag-legend-hint { margin-inline-start: 0; } }

    .ag-tape-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .75rem; }
    .ag-tape-nav, .ag-tape-controls { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .ag-tape-range { font-weight: 700; font-size: .95rem; margin-inline-start: .25rem; color: var(--gray-900); }
    .dark .ag-tape-range { color: #fff; }
    .ag-tape-spinner { width: 1.1rem; height: 1.1rem; color: var(--primary-500); }
    .ag-tape-field { display: flex; align-items: center; gap: .4rem; font-size: .75rem; font-weight: 600; color: var(--gray-500); }
    .ag-tape-input { font-size: .85rem; padding: .35rem .6rem; border-radius: .5rem; border: 1px solid var(--line-strong); background: var(--surface); color: var(--gray-900); color-scheme: light; }
    .dark .ag-tape-input { color: #fff; color-scheme: dark; }
    .ag-tape-input:focus { outline: 2px solid var(--primary-500); outline-offset: 1px; }

    .ag-tape-legend { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem 1rem; font-size: .75rem; color: var(--gray-600); margin-bottom: .75rem; }
    .dark .ag-tape-legend { color: var(--gray-400); }
    .ag-legend-item { display: inline-flex; align-items: center; gap: .35rem; }
    .ag-legend-hint { margin-inline-start: auto; font-style: italic; color: var(--gray-500); }
    .ag-swatch { display: inline-block; width: .9rem; height: .55rem; border-radius: 9999px; }
    .ag-swatch.is-pending { background: var(--c-pending); } .ag-swatch.is-confirmed { background: var(--c-confirmed); }
    .ag-swatch.is-checked_in { background: var(--c-checked_in); } .ag-swatch.is-checked_out { background: var(--c-checked_out); }
    .ag-swatch.is-unpaid-dot { width: .55rem; background: #fff; box-shadow: inset 0 0 0 2px #e11d48; }
    .ag-swatch.is-blocked { background: repeating-linear-gradient(45deg, var(--gray-400) 0 2px, transparent 2px 5px); border-radius: 2px; }

    .ag-tape-scroller { overflow-x: auto; border: 1px solid var(--line-strong); border-radius: .9rem; background: var(--surface); box-shadow: 0 1px 2px rgb(0 0 0 / .04); transition: opacity .15s; -webkit-overflow-scrolling: touch; }
    .ag-tape-scroller.is-loading { opacity: .6; }
    .ag-tape-grid { min-width: calc(var(--lw) + var(--cw) * var(--days)); width: max-content; }
    .ag-tape-row { display: flex; border-bottom: 1px solid var(--line); }
    .ag-tape-row:last-child { border-bottom: 0; }
    .ag-tape-label { position: sticky; left: 0; z-index: 3; flex: 0 0 var(--lw); width: var(--lw); display: flex; align-items: center; gap: .4rem; padding: 0 .75rem; background: var(--surface); border-inline-end: 1px solid var(--line-strong); font-size: .8rem; min-height: var(--rh); }
    .ag-tape-track { position: relative; display: grid; grid-template-columns: repeat(var(--days), var(--cw)); }

    .ag-tape-head { position: sticky; top: 0; z-index: 4; }
    .ag-tape-head .ag-tape-label, .ag-tape-head .ag-tape-day { background: var(--surface); }
    .ag-tape-corner { font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; color: var(--gray-500); font-weight: 700; }
    .ag-tape-day { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding: 1.15rem 0 .4rem; border-inline-end: 1px solid var(--line); line-height: 1.1; }
    .ag-tape-day.is-weekend { background: var(--weekend) !important; }
    .ag-tape-day.is-today { background: var(--today) !important; }
    .ag-tape-day.is-today .ag-tape-date { background: var(--primary-500); color: #fff; border-radius: 9999px; width: 1.6rem; height: 1.6rem; display: grid; place-items: center; }
    .ag-tape-month { position: absolute; top: .2rem; left: .3rem; font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--primary-600); white-space: nowrap; }
    .dark .ag-tape-month { color: var(--primary-400); }
    .ag-tape-day.is-month-start { border-inline-start: 2px solid color-mix(in oklab, var(--primary-500) 45%, transparent); }
    .ag-tape-dow { font-size: .62rem; text-transform: uppercase; color: var(--gray-500); font-weight: 600; }
    .ag-tape-day.is-weekend .ag-tape-dow { color: var(--primary-600); }
    .ag-tape-date { font-size: .85rem; font-weight: 700; color: var(--gray-900); margin-top: .15rem; }
    .dark .ag-tape-date { color: var(--gray-100); }

    .ag-tape-occ .ag-tape-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; color: var(--gray-500); font-weight: 700; min-height: 2rem; }
    .ag-tape-occ-cell { position: relative; display: flex; align-items: flex-end; justify-content: center; height: 2rem; border-inline-end: 1px solid var(--line); }
    .ag-tape-occ-cell.is-weekend { background: var(--weekend); } .ag-tape-occ-cell.is-today { background: var(--today); }
    .ag-occ-bar { position: absolute; bottom: 0; left: 20%; right: 20%; border-radius: 3px 3px 0 0; background: color-mix(in oklab, var(--primary-500) 35%, transparent); }
    .ag-occ-num { position: relative; font-size: .62rem; font-weight: 700; color: var(--gray-700); padding-bottom: .15rem; }
    .dark .ag-occ-num { color: var(--gray-200); }

    .ag-tape-type .ag-tape-label, .ag-tape-type .ag-tape-track { background: var(--surface-2); }
    .ag-tape-type .ag-tape-label { background: color-mix(in oklab, var(--surface) 92%, var(--gray-500)); justify-content: space-between; min-height: 1.9rem; }
    .ag-type-name { font-weight: 700; font-size: .75rem; color: var(--gray-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dark .ag-type-name { color: #fff; }
    .ag-type-count { font-size: .65rem; font-weight: 700; color: var(--gray-500); background: var(--line); padding: 0 .4rem; border-radius: 9999px; }
    .ag-tape-free { display: grid; place-items: center; font-size: .68rem; font-weight: 700; color: var(--success-600); border-inline-end: 1px solid var(--line); background: color-mix(in oklab, var(--surface) 92%, var(--gray-500)); }
    .dark .ag-tape-free { color: var(--success-400); }
    .ag-tape-free.is-weekend { background: color-mix(in oklab, var(--surface) 88%, var(--primary-500)); }
    .ag-tape-free.is-full { color: var(--danger-600); }
    .dark .ag-tape-free.is-full { color: var(--danger-400); }

    .ag-room-no { font-weight: 700; font-variant-numeric: tabular-nums; color: var(--gray-900); }
    .dark .ag-room-no { color: #fff; }
    .ag-room-floor { margin-inline-start: auto; font-size: .65rem; color: var(--gray-400); }
    .ag-room-flag { margin-inline-start: auto; font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--warning-700); background: color-mix(in oklab, var(--warning-500) 15%, transparent); padding: .1rem .35rem; border-radius: .3rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dark .ag-room-flag { color: var(--warning-300); }
    .is-unassigned .ag-room-flag { margin-inline-start: 0; color: var(--gray-600); background: var(--line); }
    .ag-hk { width: .5rem; height: .5rem; border-radius: 9999px; flex-shrink: 0; }
    .ag-hk.is-clean { background: #199e70; } .ag-hk.is-dirty { background: #e11d48; } .ag-hk.is-inspected { background: #2a78d6; }

    .ag-tape-cell { display: block; height: var(--rh); border-inline-end: 1px solid var(--line); transition: background .12s; }
    .ag-tape-cell.is-weekend { background: var(--weekend); }
    .ag-tape-cell.is-today { background: var(--today); }
    .ag-tape-cell.is-past { background-image: linear-gradient(rgb(127 127 127 / .05), rgb(127 127 127 / .05)); }
    a.ag-tape-cell:hover { background: color-mix(in oklab, var(--primary-500) 22%, transparent); box-shadow: inset 0 0 0 2px var(--primary-500); cursor: copy; }
    .ag-tape-room.is-blocked .ag-tape-track { background: repeating-linear-gradient(45deg, rgb(127 127 127 / .14) 0 2px, transparent 2px 9px); }
    .ag-tape-room:hover .ag-tape-label { background: color-mix(in oklab, var(--surface) 94%, var(--primary-500)); }

    .ag-bar { position: absolute; top: .3rem; bottom: .3rem; z-index: 2; display: flex; align-items: center; gap: .3rem; padding: 0 .5rem; border-radius: .45rem; color: #fff; font-size: .72rem; font-weight: 600; overflow: hidden; white-space: nowrap; box-shadow: 0 1px 2px rgb(0 0 0 / .18), inset 0 0 0 1px rgb(255 255 255 / .12); transition: transform .12s, box-shadow .12s, filter .12s; margin-inline-start: 1.5px; }
    .ag-bar:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgb(0 0 0 / .22); filter: brightness(1.07); z-index: 5; }
    .ag-bar.is-pending { background: var(--c-pending); background-image: repeating-linear-gradient(135deg, rgb(255 255 255 / .14) 0 6px, transparent 6px 12px); }
    .ag-bar.is-confirmed { background: var(--c-confirmed); }
    .ag-bar.is-checked_in { background: var(--c-checked_in); }
    .ag-bar.is-checked_out { background: var(--c-checked_out); opacity: .85; }
    .ag-bar.cut-left { border-start-start-radius: 0; border-end-start-radius: 0; margin-inline-start: 0; }
    .ag-bar.cut-right { border-start-end-radius: 0; border-end-end-radius: 0; }
    .ag-bar-text { overflow: hidden; text-overflow: ellipsis; }
    .ag-bar-dot { flex-shrink: 0; width: .45rem; height: .45rem; border-radius: 9999px; background: #fff; box-shadow: 0 0 0 2px #e11d48; }

    .ag-tip { position: fixed; z-index: 60; width: 260px; pointer-events: none; padding: .75rem .85rem; border-radius: .75rem; background: #0f1a2e; color: #e8edf6; font-size: .78rem; box-shadow: 0 12px 32px rgb(0 0 0 / .35); border: 1px solid rgb(255 255 255 / .08); }
    .ag-tip-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .35rem; }
    .ag-tip-ref { font-family: ui-monospace, monospace; font-weight: 700; color: #e3c98f; }
    .ag-tip-status { font-size: .65rem; font-weight: 700; padding: .1rem .45rem; border-radius: 9999px; background: rgb(255 255 255 / .12); }
    .ag-tip-status.is-pending { background: var(--c-pending); } .ag-tip-status.is-confirmed { background: #2a78d6; }
    .ag-tip-status.is-checked_in { background: #199e70; } .ag-tip-status.is-checked_out { background: #64748b; }
    .ag-tip-guest { font-weight: 700; font-size: .9rem; color: #fff; margin-bottom: .4rem; }
    .ag-tip-row { display: flex; justify-content: space-between; gap: .5rem; padding: .12rem 0; color: #a9b4c7; }
    .ag-tip-row b { color: #fff; font-weight: 600; }
    .ag-tip-unpaid { margin-top: .4rem; font-size: .7rem; font-weight: 700; color: #fda4af; }
    [x-cloak] { display: none !important; }
