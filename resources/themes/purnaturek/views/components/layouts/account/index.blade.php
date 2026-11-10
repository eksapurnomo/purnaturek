{{--
    PurNatureK Members Portal — Account Area Layout
    (shop::components.layouts.account).

    Wraps customer account pages, B2B Suite pages, and Portal Modules in the
    PurNatureK Members Portal shell with branded header, desktop sidebar, and shared slide-over mobile drawer.
--}}
<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="true"
>
    <x-slot:title>
        {{ $title ?? 'PurNatureK Members Workspace' }}
    </x-slot>

    <!-- Portal Header with Mobile Drawer Trigger -->
    <x-shop::portal.header :has-drawer="true" />

    <!-- Breadcrumbs (when provided) -->
    @hasSection('breadcrumbs')
        <div class="pk-breadcrumb-bar">
            <x-shop::layouts.account.breadcrumb />
        </div>
    @endif

    <!-- Main Workspace Shell -->
    <div class="pk-shell">
        {{ $slot }}
    </div>

    <!-- Shared Mobile / Tablet Navigation Drawer (Rendered for ALL account, B2B, and custom pages) -->
    @if (auth()->guard('customer')->check())
        <div
            id="pk-mobile-drawer-backdrop"
            class="pk-drawer-backdrop"
            hidden
            aria-hidden="true"
        ></div>

        <aside
            id="pk-mobile-drawer"
            class="pk-drawer"
            role="dialog"
            aria-modal="true"
            aria-label="Members Portal Menu"
            hidden
            v-pre
        >
            <div class="pk-drawer__top">
                <span class="pk-eyebrow pk-eyebrow--light">Portal Menu</span>
                <button
                    type="button"
                    id="pk-mobile-drawer-close"
                    class="pk-drawer__close"
                    aria-label="Close portal menu"
                >
                    &times;
                </button>
            </div>

            <div class="pk-drawer__body">
                <x-shop::layouts.account.navigation />
            </div>
        </aside>

        @pushOnce('scripts')
            <script>
                (function () {
                    function initPortalDrawer() {
                        var openBtn = document.getElementById('pk-mobile-drawer-open');
                        var closeBtn = document.getElementById('pk-mobile-drawer-close');
                        var drawer = document.getElementById('pk-mobile-drawer');
                        var backdrop = document.getElementById('pk-mobile-drawer-backdrop');
                        if (!openBtn || !drawer || !backdrop || openBtn.dataset.pkDrawerBound) return;
                        openBtn.dataset.pkDrawerBound = '1';

                        function openDrawer() {
                            drawer.hidden = false;
                            backdrop.hidden = false;
                            requestAnimationFrame(function () {
                                drawer.classList.add('is-open');
                                backdrop.classList.add('is-open');
                            });
                            document.body.classList.add('pk-drawer-locked');
                            openBtn.setAttribute('aria-expanded', 'true');
                            if (closeBtn) closeBtn.focus();
                        }

                        function closeDrawer(restoreFocus) {
                            drawer.classList.remove('is-open');
                            backdrop.classList.remove('is-open');
                            document.body.classList.remove('pk-drawer-locked');
                            openBtn.setAttribute('aria-expanded', 'false');
                            setTimeout(function () {
                                if (!drawer.classList.contains('is-open')) {
                                    drawer.hidden = true;
                                    backdrop.hidden = true;
                                }
                            }, 220);
                            if (restoreFocus) openBtn.focus();
                        }

                        openBtn.addEventListener('click', openDrawer);
                        if (closeBtn) {
                            closeBtn.addEventListener('click', function () { closeDrawer(true); });
                        }
                        backdrop.addEventListener('click', function () { closeDrawer(true); });

                        document.addEventListener('keydown', function (e) {
                            if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
                                closeDrawer(true);
                            }
                        });

                        drawer.addEventListener('click', function (e) {
                            if (e.target && e.target.closest('a.pk-nav-item')) {
                                closeDrawer(false);
                            }
                        });
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initPortalDrawer);
                    } else {
                        initPortalDrawer();
                    }
                })();
            </script>
        @endPushOnce
    @endif
</x-shop::layouts>
