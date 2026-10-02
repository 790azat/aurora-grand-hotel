    /* ---------- Live chat inbox ---------- */
    .ag-chat { --line: var(--gray-200); --surface: #fff; --surface-2: var(--gray-50); --ink: var(--gray-900); --muted: var(--gray-500);
        --bubble: var(--gray-100); --bubble-bot: color-mix(in oklab, var(--info-500) 8%, #fff); --bubble-staff: var(--primary-500);
        display: grid; grid-template-columns: 21rem minmax(0, 1fr); height: calc(100dvh - 13rem); min-height: 32rem;
        border: 1px solid var(--line); border-radius: 1rem; overflow: hidden; background: var(--surface); box-shadow: 0 1px 2px rgb(0 0 0 / .04); }
    .dark .ag-chat { --line: rgb(255 255 255 / .08); --surface: var(--gray-900); --surface-2: rgb(255 255 255 / .03); --ink: #fff; --muted: var(--gray-400);
        --bubble: rgb(255 255 255 / .07); --bubble-bot: color-mix(in oklab, var(--info-400) 12%, var(--gray-900)); --bubble-staff: var(--primary-600); }
    .ag-chat.has-selection { grid-template-columns: 21rem minmax(0, 1fr) 17rem; }
    @media (max-width: 1279px) { .ag-chat.has-selection { grid-template-columns: 19rem minmax(0, 1fr); } .ag-chat-info { display: none; } }
    @media (max-width: 767px) {
        .ag-chat, .ag-chat.has-selection { grid-template-columns: minmax(0, 1fr); height: calc(100dvh - 11rem); }
        .ag-chat.has-selection .ag-chat-list { display: none; }
        .ag-chat:not(.has-selection) .ag-chat-thread { display: none; }
        .ag-chat-btn-label { display: none; }
    }

    /* List */
    .ag-chat-list { display: flex; flex-direction: column; min-height: 0; border-inline-end: 1px solid var(--line); background: var(--surface-2); }
    .ag-chat-list-head { padding: .75rem; border-bottom: 1px solid var(--line); display: grid; gap: .6rem; }
    .ag-chat-search { position: relative; display: block; }
    .ag-chat-search-icon { position: absolute; inset-inline-start: .65rem; top: 50%; translate: 0 -50%; width: 1rem; height: 1rem; color: var(--muted); }
    .ag-chat-search input { width: 100%; font-size: .85rem; padding: .5rem .75rem .5rem 2.1rem; border-radius: .6rem; border: 1px solid var(--line); background: var(--surface); color: var(--ink); }
    .ag-chat-search input:focus { outline: 2px solid var(--primary-500); outline-offset: -1px; }
    .ag-chat-filters { display: flex; gap: .25rem; padding: 3px; border-radius: .6rem; background: var(--gray-100); }
    .dark .ag-chat-filters { background: rgb(255 255 255 / .05); }
    .ag-chat-filter { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .3rem; font-size: .72rem; font-weight: 600; padding: .3rem .25rem; border-radius: .45rem; color: var(--muted); white-space: nowrap; transition: all .15s; }
    .ag-chat-filter:hover { color: var(--ink); }
    .ag-chat-filter.is-active { background: var(--surface); color: var(--ink); box-shadow: 0 1px 2px rgb(0 0 0 / .08); }
    .dark .ag-chat-filter.is-active { background: rgb(255 255 255 / .1); }
    .ag-chat-filter-count { font-size: .65rem; min-width: 1.1rem; padding: 0 .3rem; border-radius: 9999px; background: var(--gray-200); color: var(--gray-700); line-height: 1.1rem; }
    .dark .ag-chat-filter-count { background: rgb(255 255 255 / .1); color: var(--gray-200); }
    .ag-chat-filter-count.is-danger { background: var(--danger-500); color: #fff; }

    .ag-chat-items { flex: 1; overflow-y: auto; padding: .4rem; }
    .ag-chat-item { width: 100%; display: flex; gap: .65rem; align-items: flex-start; text-align: start; padding: .65rem; border-radius: .7rem; transition: background .12s; }
    .ag-chat-item:hover { background: color-mix(in oklab, var(--gray-500) 8%, transparent); }
    .ag-chat-item.is-active { background: color-mix(in oklab, var(--primary-500) 12%, transparent); box-shadow: inset 3px 0 0 var(--primary-500); }
    .ag-chat-avatar { flex-shrink: 0; display: grid; place-items: center; width: 2.4rem; height: 2.4rem; border-radius: 9999px; font-weight: 700; font-size: .95rem;
        background: linear-gradient(135deg, #1e2a44, #3d2f19); color: #e3c98f; }
    .ag-chat-avatar.is-lg { width: 2.6rem; height: 2.6rem; }
    .ag-chat-item-body { flex: 1; min-width: 0; display: grid; gap: .15rem; }
    .ag-chat-item-top, .ag-chat-item-bottom { display: flex; align-items: center; gap: .4rem; min-width: 0; }
    .ag-chat-item-name { font-size: .875rem; font-weight: 600; color: var(--ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ag-chat-locale { flex-shrink: 0; font-size: .62rem; font-weight: 700; letter-spacing: .04em; color: var(--muted); padding: .05rem .35rem; border-radius: .3rem; background: var(--gray-100); }
    .dark .ag-chat-locale { background: rgb(255 255 255 / .07); }
    .ag-chat-time { margin-inline-start: auto; flex-shrink: 0; font-size: .7rem; color: var(--muted); }
    .ag-chat-preview { flex: 1; min-width: 0; font-size: .8rem; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ag-chat-item.is-unread .ag-chat-preview { color: var(--ink); font-weight: 600; }
    .ag-chat-item.is-unread .ag-chat-time { color: var(--primary-600); font-weight: 600; }
    .dark .ag-chat-item.is-unread .ag-chat-time { color: var(--primary-400); }
    .ag-chat-unread { flex-shrink: 0; min-width: 1.25rem; padding: 0 .35rem; border-radius: 9999px; background: var(--danger-500); color: #fff; font-size: .7rem; font-weight: 700; line-height: 1.25rem; text-align: center; }
    .ag-chat-closed-icon { width: .85rem; height: .85rem; color: var(--muted); flex-shrink: 0; }
    .ag-chat-empty-list { display: grid; justify-items: center; gap: .5rem; padding: 3rem 1rem; font-size: .85rem; color: var(--muted); text-align: center; }
    .ag-chat-empty-icon { width: 2rem; height: 2rem; opacity: .5; }

    /* Thread */
    .ag-chat-thread { display: flex; flex-direction: column; min-width: 0; min-height: 0; }
    .ag-chat-thread-head { display: flex; align-items: center; gap: .75rem; padding: .7rem 1rem; border-bottom: 1px solid var(--line); }
    .ag-chat-back { display: none; padding: .35rem; border-radius: .5rem; color: var(--muted); }
    .ag-chat-back:hover { background: color-mix(in oklab, var(--gray-500) 10%, transparent); }
    .ag-chat-back-icon { width: 1.2rem; height: 1.2rem; }
    @media (max-width: 767px) { .ag-chat-back { display: block; } }
    .ag-chat-thread-title { flex: 1; min-width: 0; }
    .ag-chat-thread-name { display: flex; align-items: center; gap: .5rem; font-weight: 700; color: var(--ink); }
    .ag-chat-thread-meta { font-size: .78rem; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .ag-chat-messages { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: .75rem;
        background: radial-gradient(circle at 1px 1px, color-mix(in oklab, var(--gray-400) 18%, transparent) 1px, transparent 0) 0 0 / 22px 22px; }
    .ag-chat-day { text-align: center; position: relative; margin: .25rem 0; }
    .ag-chat-day span { font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); background: var(--surface); padding: .2rem .7rem; border-radius: 9999px; border: 1px solid var(--line); }
    .ag-chat-msg { display: flex; flex-direction: column; align-items: flex-start; max-width: min(75%, 34rem); }
    .ag-chat-msg.is-staff { align-self: flex-end; align-items: flex-end; }
    .ag-chat-msg-label { display: flex; align-items: center; gap: .3rem; font-size: .7rem; font-weight: 600; color: var(--muted); margin: 0 .35rem .25rem; }
    .ag-chat-msg-label span { font-weight: 400; }
    .ag-chat-label-icon { width: .8rem; height: .8rem; color: var(--info-500); display: inline-block; vertical-align: -1px; }
    .ag-chat-new-dot { width: .45rem; height: .45rem; border-radius: 9999px; background: var(--danger-500); }
    .ag-chat-bubble { font-size: .875rem; line-height: 1.5; padding: .6rem .85rem; border-radius: 1rem; overflow-wrap: anywhere; color: var(--ink); background: var(--bubble); border: 1px solid transparent; }
    .ag-chat-msg.is-guest .ag-chat-bubble { border-bottom-left-radius: .3rem; background: var(--surface); border-color: var(--line); box-shadow: 0 1px 2px rgb(0 0 0 / .05); }
    .ag-chat-msg.is-bot .ag-chat-bubble { border-bottom-left-radius: .3rem; background: var(--bubble-bot); border-color: color-mix(in oklab, var(--info-500) 20%, transparent); }
    .ag-chat-msg.is-staff .ag-chat-bubble { border-bottom-right-radius: .3rem; background: var(--bubble-staff); color: #fff; }
    .ag-chat-bubble a { text-decoration: underline; text-underline-offset: 2px; font-weight: 600; color: var(--primary-700); }
    .dark .ag-chat-bubble a { color: var(--primary-300); }
    .ag-chat-msg.is-staff .ag-chat-bubble a { color: #fff; }

    .ag-chat-composer { display: flex; align-items: flex-end; gap: .6rem; padding: .75rem 1rem; border-top: 1px solid var(--line); background: var(--surface); }
    .ag-chat-composer textarea { flex: 1; resize: none; font-size: .875rem; line-height: 1.45; padding: .55rem .8rem; border-radius: .7rem; border: 1px solid var(--line); background: var(--surface-2); color: var(--ink); max-height: 10rem; }
    .ag-chat-composer textarea:focus { outline: 2px solid var(--primary-500); outline-offset: -1px; }
    .ag-chat-error { font-size: .78rem; color: var(--danger-600); padding: 0 1rem .6rem; }

    .ag-chat-placeholder { margin: auto; text-align: center; max-width: 18rem; color: var(--muted); font-size: .85rem; }
    .ag-chat-placeholder h3 { font-weight: 700; color: var(--ink); font-size: 1rem; margin-bottom: .25rem; }
    .ag-chat-placeholder-icon { display: grid; place-items: center; width: 3.5rem; height: 3.5rem; margin: 0 auto 1rem; border-radius: 9999px; color: var(--primary-600); background: color-mix(in oklab, var(--primary-500) 12%, transparent); }
    .ag-chat-placeholder-icon svg { width: 1.6rem; height: 1.6rem; }

    /* Info */
    .ag-chat-info { border-inline-start: 1px solid var(--line); padding: 1rem; overflow-y: auto; background: var(--surface-2); font-size: .8rem; }
    .ag-chat-info h4 { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin: .25rem 0 .6rem; }
    .ag-chat-info h4:not(:first-child) { margin-top: 1.4rem; }
    .ag-chat-info dl { display: grid; gap: .45rem; }
    .ag-chat-info dl div { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .1rem .5rem; }
    .ag-chat-info dl dd { margin-inline-start: auto; }
    .ag-chat-info dt { color: var(--muted); }
    .ag-chat-info dd { font-weight: 600; color: var(--ink); text-align: end; overflow-wrap: anywhere; }
    .ag-chat-info-btn { margin-top: .8rem; width: 100%; }
    .ag-chat-booking { display: grid; gap: .1rem; padding: .55rem .65rem; border-radius: .6rem; border: 1px solid var(--line); background: var(--surface); margin-bottom: .4rem; transition: border-color .15s; }
    .ag-chat-booking:hover { border-color: var(--primary-500); }
    .ag-chat-booking-ref { display: flex; justify-content: space-between; align-items: center; gap: .4rem; font-weight: 700; color: var(--ink); font-size: .78rem; }
    .ag-chat-booking-ref i { font-style: normal; font-size: .62rem; font-weight: 700; padding: .05rem .4rem; border-radius: 9999px; background: var(--gray-100); color: var(--gray-600); }
    .dark .ag-chat-booking-ref i { background: rgb(255 255 255 / .08); color: var(--gray-300); }
    .ag-chat-booking-ref i.is-confirmed { background: color-mix(in oklab, var(--info-500) 15%, transparent); color: var(--info-600); }
    .ag-chat-booking-ref i.is-checked_in { background: color-mix(in oklab, var(--success-500) 15%, transparent); color: var(--success-600); }
    .ag-chat-booking-ref i.is-pending { background: color-mix(in oklab, var(--warning-500) 18%, transparent); color: var(--warning-700); }
    .ag-chat-booking-ref i.is-cancelled { background: color-mix(in oklab, var(--danger-500) 15%, transparent); color: var(--danger-600); }
    .ag-chat-booking-meta { color: var(--muted); font-size: .74rem; }
    .ag-chat-info-muted { color: var(--muted); }
    .ag-chat-info-hint { margin-top: 1.4rem; padding: .65rem .75rem; border-radius: .6rem; font-size: .74rem; line-height: 1.45; color: var(--muted); background: color-mix(in oklab, var(--info-500) 7%, transparent); }
