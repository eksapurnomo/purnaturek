{{--
    PurNatureK Members Portal — scoped styles.

    The storefront theme ships as a precompiled Tailwind bundle (no Node build is available in this
    environment), so the portal brings its own small, `pk-` prefixed stylesheet instead of relying on
    utility classes that may not exist in the compiled CSS.
--}}
<style>
    :root {
        --pk-green-900: #0f4a3e;
        --pk-green-700: #17705d;
        --pk-green-600: #1d8570;
        --pk-green-50: #eef6f3;
        --pk-ink: #1d2a26;
        --pk-muted: #62726d;
        --pk-line: #dde7e3;
        --pk-bg: #f6f9f8;
    }

    /* ---------- Header ---------- */
    .pk-header { background: #fff; border-bottom: 1px solid var(--pk-line); position: sticky; top: 0; z-index: 30; }
    .pk-header__inner { max-width: 1320px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .pk-brand { display: flex; align-items: baseline; gap: 10px; text-decoration: none; min-width: 0; }
    .pk-brand img { max-height: 36px; width: auto; display: block; }
    .pk-brand__name { font-size: 22px; font-weight: 700; letter-spacing: -0.01em; color: var(--pk-green-900); }
    .pk-brand__tag { font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--pk-green-700); padding-left: 10px; border-left: 2px solid var(--pk-line); white-space: nowrap; }
    .pk-header__actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .pk-header__link, .pk-header__btn { font: inherit; font-size: 14px; font-weight: 500; color: var(--pk-ink); text-decoration: none; padding: 8px 14px; border-radius: 8px; border: 1px solid transparent; background: none; cursor: pointer; }
    .pk-header__link:hover { background: var(--pk-green-50); color: var(--pk-green-900); }
    .pk-header__btn { border-color: var(--pk-line); }
    .pk-header__btn:hover { border-color: var(--pk-green-700); color: var(--pk-green-900); }
    .pk-header__user { font-size: 13px; color: var(--pk-muted); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* ---------- Shell ---------- */
    .pk-shell { max-width: 1320px; margin: 0 auto; padding: 28px 24px 56px; display: flex; align-items: flex-start; gap: 28px; }
    .pk-shell > :not(.pk-sidebar) { flex: 1 1 0; min-width: 0; }

    /* ---------- Sidebar ---------- */
    .pk-sidebar { flex: 0 0 264px; width: 264px; background: #fff; border: 1px solid var(--pk-line); border-radius: 12px; padding: 8px 0 12px; position: sticky; top: 84px; max-height: calc(100vh - 100px); overflow-y: auto; }
    .pk-sidebar__toggle-input { position: absolute; opacity: 0; pointer-events: none; }
    .pk-sidebar__toggle { display: none; }
    .pk-nav-group { padding: 10px 0 4px; }
    .pk-nav-group + .pk-nav-group { border-top: 1px solid var(--pk-line); margin-top: 6px; }
    .pk-nav-group__title { margin: 0; padding: 4px 20px 6px; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--pk-muted); }
    .pk-nav-item { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 9px 20px; font-size: 14px; font-weight: 500; line-height: 1.35; color: var(--pk-ink); text-decoration: none; border-left: 3px solid transparent; }
    a.pk-nav-item:hover { background: var(--pk-green-50); color: var(--pk-green-900); }
    .pk-nav-item.is-active { background: var(--pk-green-50); color: var(--pk-green-900); border-left-color: var(--pk-green-700); font-weight: 600; }
    .pk-nav-item.is-soon { color: #8a9893; cursor: default; }
    .pk-nav-soon { font-size: 10px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--pk-muted); background: var(--pk-bg); border: 1px solid var(--pk-line); border-radius: 999px; padding: 1px 8px; white-space: nowrap; }

    /* ---------- Main / catalog ---------- */
    .pk-welcome { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 16px; padding: 10px 16px; background: var(--pk-green-50); border: 1px solid var(--pk-line); border-radius: 10px; font-size: 14px; color: var(--pk-ink); }
    .pk-pills { display: flex; flex-wrap: wrap; gap: 6px; }
    .pk-pill { font-size: 12px; font-weight: 600; color: var(--pk-green-900); background: #fff; border: 1px solid var(--pk-line); border-radius: 999px; padding: 2px 10px; }
    .pk-page-title { margin: 22px 0 4px; font-size: 26px; line-height: 1.2; font-weight: 700; color: var(--pk-green-900); letter-spacing: -0.01em; }
    .pk-page-intro { margin: 0 0 18px; font-size: 14px; line-height: 1.55; color: var(--pk-muted); max-width: 720px; }

    .pk-filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .pk-filters input[type="search"], .pk-filters select { font: inherit; font-size: 14px; color: var(--pk-ink); background: #fff; border: 1px solid var(--pk-line); border-radius: 8px; padding: 10px 12px; min-height: 42px; }
    .pk-filters input[type="search"] { flex: 1 1 240px; min-width: 0; }
    .pk-filters select { flex: 0 1 220px; min-width: 0; }
    .pk-filters input:focus, .pk-filters select:focus { outline: 2px solid var(--pk-green-600); outline-offset: 0; border-color: var(--pk-green-600); }
    .pk-btn { font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; text-align: center; border-radius: 8px; padding: 10px 18px; min-height: 42px; border: 1px solid var(--pk-green-700); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
    .pk-btn--primary { background: var(--pk-green-700); color: #fff; }
    .pk-btn--primary:hover { background: var(--pk-green-900); }
    .pk-btn--ghost { background: #fff; color: var(--pk-green-900); }
    .pk-btn--ghost:hover { background: var(--pk-green-50); }
    .pk-result-count { margin: -6px 0 16px; font-size: 13px; color: var(--pk-muted); }

    .pk-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
    .pk-card { display: flex; flex-direction: column; background: #fff; border: 1px solid var(--pk-line); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 2px rgba(15, 74, 62, .05); transition: box-shadow .15s ease, border-color .15s ease; }
    .pk-card:hover { border-color: #b9cfc7; box-shadow: 0 6px 18px rgba(15, 74, 62, .10); }
    .pk-card__media { display: block; aspect-ratio: 4 / 3; background: var(--pk-bg); border-bottom: 1px solid var(--pk-line); }
    .pk-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pk-card__body { display: flex; flex-direction: column; gap: 6px; padding: 14px 16px 16px; flex: 1 1 auto; }
    .pk-card__name { margin: 0; font-size: 15px; line-height: 1.35; font-weight: 600; color: var(--pk-ink); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.7em; }
    .pk-card__title-link { color: inherit; text-decoration: none; }
    .pk-card__title-link:hover { color: var(--pk-green-700); }
    .pk-card__sku { font-size: 12px; color: var(--pk-muted); letter-spacing: .02em; word-break: break-all; }
    .pk-card__pricing { display: flex; flex-direction: column; gap: 6px; margin: 4px 0; padding: 8px 10px; background: var(--pk-bg); border: 1px solid var(--pk-line); border-radius: 8px; }
    .pk-card__price { font-size: 15px; font-weight: 700; line-height: 1.3; }
    .pk-card__price--range { color: var(--pk-green-900); }
    .pk-card__price--contact { color: var(--pk-green-700); font-size: 14px; }
    .pk-card__meta { display: flex; flex-wrap: wrap; gap: 5px; }
    .pk-card__badge { display: inline-flex; align-items: center; font-size: 11px; font-weight: 600; line-height: 1.3; padding: 2px 8px; border-radius: 999px; border: 1px solid var(--pk-line); background: #fff; color: var(--pk-ink); }
    .pk-card__badge--moq { color: var(--pk-green-900); background: var(--pk-green-50); }
    .pk-card__badge--supply { color: var(--pk-muted); }
    .pk-card__summary { margin: 0; font-size: 13px; line-height: 1.5; color: var(--pk-muted); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 4.5em; }
    .pk-card__cta { margin-top: auto; padding-top: 8px; }
    .pk-card__cta .pk-btn { width: 100%; }

    .pk-pager { display: flex; align-items: center; justify-content: center; gap: 14px; margin-top: 28px; font-size: 14px; color: var(--pk-muted); }
    .pk-btn.is-disabled { opacity: .45; pointer-events: none; cursor: not-allowed; }

    .pk-empty { text-align: center; padding: 56px 24px; background: #fff; border: 1px dashed var(--pk-line); border-radius: 12px; color: var(--pk-muted); }
    .pk-empty h2 { margin: 0 0 6px; font-size: 18px; font-weight: 600; color: var(--pk-ink); }
    .pk-empty p { margin: 0 0 14px; font-size: 14px; }

    /* ---------- Product Detail Page ---------- */
    .pk-product__breadcrumb { margin-bottom: 18px; }
    .pk-product__back { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--pk-green-700); text-decoration: none; }
    .pk-product__back:hover { color: var(--pk-green-900); text-decoration: underline; }
    .pk-product__hero { display: grid; grid-template-columns: minmax(260px, 380px) minmax(0, 1fr); gap: 28px; background: #fff; border: 1px solid var(--pk-line); border-radius: 12px; padding: 24px; }
    .pk-product__image-main { aspect-ratio: 4 / 3; background: var(--pk-bg); border: 1px solid var(--pk-line); border-radius: 10px; overflow: hidden; }
    .pk-product__image-main img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pk-product__thumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .pk-product__thumb { width: 64px; height: 64px; border: 1px solid var(--pk-line); border-radius: 8px; overflow: hidden; display: block; }
    .pk-product__thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pk-product__summary { display: flex; flex-direction: column; gap: 18px; }
    .pk-product__badges { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 6px; }
    .pk-product__title { margin: 0 0 8px; font-size: 24px; line-height: 1.25; font-weight: 700; color: var(--pk-ink); }
    .pk-product__short-desc { font-size: 14px; line-height: 1.6; color: var(--pk-muted); }

    .pk-pricing-box { background: var(--pk-green-50); border: 1px solid var(--pk-line); border-radius: 10px; padding: 18px 20px; }
    .pk-pricing-box__header { padding-bottom: 14px; border-bottom: 1px solid var(--pk-line); }
    .pk-pricing-box__label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--pk-muted); margin-bottom: 4px; }
    .pk-pricing-box__value { font-size: 24px; font-weight: 800; line-height: 1.25; color: var(--pk-green-900); }
    .pk-pricing-box__value--contact { font-size: 20px; color: var(--pk-green-700); }
    .pk-pricing-box__specs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 14px 0; }
    .pk-pricing-box__spec { background: #fff; border: 1px solid var(--pk-line); border-radius: 8px; padding: 10px 12px; }
    .pk-pricing-box__spec dt { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--pk-muted); margin-bottom: 3px; }
    .pk-pricing-box__spec dd { margin: 0; font-size: 14px; font-weight: 700; color: var(--pk-ink); }
    .pk-pricing-box__disclaimer { margin: 10px 0 0; font-size: 12px; line-height: 1.5; color: var(--pk-muted); }

    .pk-product__actions-panel { border-top: 1px solid var(--pk-line); padding-top: 16px; }
    .pk-product__actions-title { margin: 0 0 10px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--pk-muted); }
    .pk-product__actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .pk-product__verification-note { margin: 10px 0 0; font-size: 12px; line-height: 1.5; color: var(--pk-muted); }

    .pk-product__section { margin-top: 22px; background: #fff; border: 1px solid var(--pk-line); border-radius: 12px; padding: 22px 24px; }
    .pk-product__section-title { margin: 0 0 12px; font-size: 17px; font-weight: 700; color: var(--pk-green-900); }
    .pk-product__prose { font-size: 14px; line-height: 1.65; color: var(--pk-ink); }
    .pk-spec-list { margin: 0; display: grid; gap: 8px; }
    .pk-spec-list__row { display: grid; grid-template-columns: 220px minmax(0, 1fr); gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--pk-line); font-size: 14px; }
    .pk-spec-list__row:last-child { border-bottom: 0; }
    .pk-spec-list__row dt { font-weight: 600; color: var(--pk-muted); }
    .pk-spec-list__row dd { margin: 0; color: var(--pk-ink); }
    .pk-spec-list__link { color: var(--pk-green-700); font-weight: 600; text-decoration: none; }
    .pk-spec-list__link:hover { text-decoration: underline; }

    /* ---------- Product Documentation (Module 2.5) ---------- */
    .pk-doc-groups { display: flex; flex-direction: column; gap: 24px; margin-top: 14px; }
    .pk-doc-group__title { margin: 0 0 12px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--pk-muted); border-bottom: 1px solid var(--pk-line); padding-bottom: 6px; }
    .pk-doc-list { display: grid; gap: 12px; }
    .pk-doc-card { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 14px 18px; background: var(--pk-bg); border: 1px solid var(--pk-line); border-radius: 10px; transition: border-color .15s ease; }
    .pk-doc-card:hover { border-color: var(--pk-green-600); }
    .pk-doc-card__main { flex: 1 1 auto; min-width: 0; }
    .pk-doc-card__header { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 4px; }
    .pk-doc-card__title { margin: 0; font-size: 15px; font-weight: 700; color: var(--pk-ink); }
    .pk-doc-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .pk-doc-badge--type { background: var(--pk-green-100); color: var(--pk-green-900); }
    .pk-doc-badge--version { background: #e2e8f0; color: #334155; font-family: monospace; }
    .pk-doc-card__meta { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12px; color: var(--pk-muted); margin-top: 4px; }
    .pk-doc-card__desc { margin: 6px 0 0; font-size: 13px; line-height: 1.5; color: var(--pk-ink); }
    .pk-doc-card__actions { display: flex; align-items: center; gap: 8px; flex: 0 0 auto; }
    .pk-doc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none; transition: background .15s ease; cursor: pointer; }
    .pk-doc-btn--preview { background: #fff; border: 1px solid var(--pk-line); color: var(--pk-green-900); }
    .pk-doc-btn--preview:hover { background: var(--pk-green-50); border-color: var(--pk-green-600); }
    .pk-doc-btn--download { background: var(--pk-green-700); border: 1px solid var(--pk-green-700); color: #fff; }
    .pk-doc-btn--download:hover { background: var(--pk-green-800); }
    .pk-doc-empty { padding: 28px 24px; text-align: center; background: var(--pk-bg); border: 1px dashed var(--pk-line); border-radius: 10px; color: var(--pk-muted); font-size: 14px; line-height: 1.6; }
    .pk-doc-empty p { margin: 0; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1180px) {
        .pk-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .pk-product__hero { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 900px) {
        .pk-shell { flex-direction: column; align-items: stretch; gap: 18px; padding: 18px 16px 40px; }
        .pk-shell > :not(.pk-sidebar) { flex: none; width: 100%; }
        .pk-sidebar { flex: none; width: 100%; position: static; max-height: none; overflow: visible; padding: 0; }
        .pk-sidebar__toggle { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; font-size: 14px; font-weight: 600; color: var(--pk-green-900); cursor: pointer; }
        .pk-sidebar__toggle::after { content: "+"; font-size: 20px; line-height: 1; }
        .pk-sidebar__toggle-input:checked ~ .pk-sidebar__toggle::after { content: "\2212"; }
        .pk-sidebar__toggle-input:focus-visible ~ .pk-sidebar__toggle { outline: 2px solid var(--pk-green-600); outline-offset: -2px; border-radius: 12px; }
        .pk-sidebar__groups { display: none; border-top: 1px solid var(--pk-line); padding-bottom: 8px; }
        .pk-sidebar__toggle-input:checked ~ .pk-sidebar__groups { display: block; }
        .pk-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pk-header__user { display: none; }
        .pk-pricing-box__specs { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 560px) {
        .pk-header__inner { padding: 12px 16px; }
        .pk-brand__tag { display: none; }
        .pk-header__link { padding: 8px 10px; }
        .pk-page-title { font-size: 22px; }
        .pk-filters select, .pk-filters .pk-btn { flex: 1 1 100%; }
        .pk-spec-list__row { grid-template-columns: minmax(0, 1fr); gap: 4px; }
        .pk-doc-card { flex-direction: column; align-items: flex-start; }
        .pk-doc-card__actions { width: 100%; justify-content: flex-start; }
    }

    @media (max-width: 460px) { .pk-grid { grid-template-columns: minmax(0, 1fr); } }
</style>
