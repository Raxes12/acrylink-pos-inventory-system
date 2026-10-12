(() => {
    const key = 'vmc-theme', body = document.body;
    const saved = localStorage.getItem(key);
    const init = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const apply = t => {
        body.setAttribute('data-theme', t);
        const btn = document.getElementById('themeToggle');
        if (btn) {
            const label = t === 'dark' ? 'Light Mode' : 'Dark Mode';
            const labelEl = btn.querySelector('[data-theme-toggle-label]');
            if (labelEl) labelEl.textContent = label;
            btn.dataset.theme = t;
            btn.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false');
            btn.setAttribute('aria-label', `Switch to ${t === 'dark' ? 'light' : 'dark'} theme`);
            btn.setAttribute('title', `Switch to ${t === 'dark' ? 'light' : 'dark'} theme`);
        }
    };
    apply(init);
    document.addEventListener('click', event => {
        if (!event.target.closest('#themeToggle')) return;
        const n = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem(key, n);
        apply(n);
    });
    document.addEventListener('app:navigated', () => apply(body.getAttribute('data-theme') || init));
})();
(() => {
    const origins = new WeakMap();
    const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const p = v => String(v).padStart(2,'0');
    const tick = () => {
        const el = document.getElementById('liveClock');
        if (!el) return;
        if (!origins.has(el)) origins.set(el, { server: new Date(el.dataset.serverTime), started: Date.now() });
        const origin = origins.get(el);
        const dateEl = el.querySelector('[data-live-clock-date]');
        const timeEl = el.querySelector('[data-live-clock-time]');
        const d = new Date(origin.server.getTime() + (Date.now() - origin.started));
        let h = d.getHours(), suffix = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        if (dateEl) dateEl.textContent = `${mn[d.getMonth()]} ${p(d.getDate())}, ${d.getFullYear()}`;
        if (timeEl) timeEl.textContent = `${p(h)}:${p(d.getMinutes())} ${suffix}`;
    };
    tick(); setInterval(tick, 1000);
})();
(() => {
    const closeAll = (except = null, restoreFocus = false) => document.querySelectorAll('[data-topbar-menu][open]').forEach(menu => {
        if (menu === except) return;
        menu.removeAttribute('open');
        if (restoreFocus) menu.querySelector('summary')?.focus();
    });
    document.addEventListener('click', event => {
        const summary = event.target.closest('[data-topbar-menu] > summary');
        if (summary) {
            closeAll(summary.parentElement);
            return;
        }
        if (!event.target.closest('[data-topbar-menu]')) closeAll();
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeAll(null, true); });
    document.addEventListener('app:before-navigate', () => closeAll());
})();
(() => {
    const body = document.body;
    const toggle = document.getElementById('mobileMenuToggle');
    const closeButton = document.getElementById('mobileSidebarClose');
    const sidebar = document.getElementById('appSidebar');
    const scrim = document.getElementById('sidebarScrim');
    const mobileQuery = window.matchMedia('(max-width: 1060px)');

    if (!toggle || !sidebar || !scrim) return;

    const sidebarNav = sidebar.querySelector('.sidebar-nav');
    const sidebarScrollKey = 'vmc-sidebar-scroll';
    const sidebarCollapsedKey = 'acrylink-sidebar-collapsed';
    const desktopCollapse = document.getElementById('desktopSidebarToggle');
    const groupStateKey = 'acrylink-sidebar-groups';
    let groupState = {};
    try { groupState = JSON.parse(localStorage.getItem(groupStateKey) || '{}'); } catch { groupState = {}; }
    sidebar.querySelectorAll('[data-nav-group]').forEach(group => {
        const button = group.querySelector('.nav-section-toggle');
        const items = group.querySelector('.nav-group-items');
        const name = group.dataset.navGroup;
        if (!button || !items || !name) return;
        const routeActive = group.classList.contains('is-route-active');
        const defaultOpen = ['sales', 'inventory'].includes(name);
        const open = routeActive || (Object.hasOwn(groupState, name) ? groupState[name] : defaultOpen);
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
        items.hidden = !open;
        button.addEventListener('click', () => {
            const next = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', next ? 'true' : 'false');
            items.hidden = !next;
            groupState[name] = next;
            localStorage.setItem(groupStateKey, JSON.stringify(groupState));
        });
    });
    sidebar.querySelectorAll('.nav-link').forEach(link => {
        const label = link.querySelector('.nav-text')?.textContent?.trim();
        if (label) link.setAttribute('title', label);
    });
    if (sidebarNav) {
        const savedScroll = Number(sessionStorage.getItem(sidebarScrollKey));
        if (Number.isFinite(savedScroll)) sidebarNav.scrollTop = savedScroll;
        sidebarNav.addEventListener('scroll', () => {
            sessionStorage.setItem(sidebarScrollKey, String(sidebarNav.scrollTop));
        }, { passive: true });
        window.addEventListener('pagehide', () => {
            sessionStorage.setItem(sidebarScrollKey, String(sidebarNav.scrollTop));
        });
    }

    const setCollapsed = collapsed => {
        const allowed = !mobileQuery.matches && collapsed;
        body.classList.toggle('sidebar-collapsed', allowed);
        desktopCollapse?.setAttribute('aria-pressed', allowed ? 'true' : 'false');
        const label = allowed ? 'Expand sidebar' : 'Collapse sidebar';
        desktopCollapse?.setAttribute('aria-label', label);
        desktopCollapse?.setAttribute('title', label);
        if (!mobileQuery.matches) localStorage.setItem(sidebarCollapsedKey, allowed ? 'true' : 'false');
    };

    setCollapsed(localStorage.getItem(sidebarCollapsedKey) === 'true');
    desktopCollapse?.addEventListener('click', () => setCollapsed(!body.classList.contains('sidebar-collapsed')));

    const setOpen = open => {
        body.classList.toggle('sidebar-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        scrim.hidden = !open;

        if (mobileQuery.matches) {
            sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
        } else {
            sidebar.removeAttribute('aria-hidden');
        }
    };

    const close = () => setOpen(false);
    document.addEventListener('app:before-navigate', close);

    toggle.addEventListener('click', () => {
        setOpen(!body.classList.contains('sidebar-open'));
    });
    closeButton?.addEventListener('click', close);
    scrim.addEventListener('click', close);
    sidebar.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (mobileQuery.matches) close();
        });
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && body.classList.contains('sidebar-open')) close();
    });

    const syncLayout = () => {
        if (!mobileQuery.matches) {
            close();
            sidebar.removeAttribute('aria-hidden');
            setCollapsed(localStorage.getItem(sidebarCollapsedKey) === 'true');
            return;
        }

        body.classList.remove('sidebar-collapsed');
        sidebar.setAttribute('aria-hidden', body.classList.contains('sidebar-open') ? 'false' : 'true');
    };

    if (typeof mobileQuery.addEventListener === 'function') {
        mobileQuery.addEventListener('change', syncLayout);
    } else {
        mobileQuery.addListener(syncLayout);
    }

    syncLayout();
})();
(() => {
    const stack = document.getElementById('toastStack');
    const confirmModal = document.getElementById('confirmActionModal');
    const confirmTitle = document.getElementById('confirmActionTitle');
    const confirmMessage = document.getElementById('confirmActionMessage');
    const confirmCancel = document.getElementById('confirmActionCancel');
    const confirmSubmit = document.getElementById('confirmActionSubmit');
    let pendingConfirmForm = null;
    let pendingConfirmSubmitter = null;
    let lastConfirmTrigger = null;

    const closeConfirmModal = () => {
        if (!confirmModal) return;
        confirmModal.hidden = true;
        pendingConfirmForm = null;
        pendingConfirmSubmitter = null;
        if (lastConfirmTrigger?.isConnected) lastConfirmTrigger.focus();
        lastConfirmTrigger = null;
    };

    const openConfirmModal = (form, submitter) => {
        if (!confirmModal || !confirmTitle || !confirmMessage || !confirmSubmit) return false;
        pendingConfirmForm = form;
        pendingConfirmSubmitter = submitter || null;
        lastConfirmTrigger = submitter || document.activeElement;
        confirmTitle.textContent = form.dataset.confirmTitle || 'Are you sure?';
        confirmMessage.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
        confirmSubmit.textContent = form.dataset.confirmButton || 'Confirm';
        confirmSubmit.className = `btn ${form.dataset.confirmButtonClass || 'btn-danger'}`;
        confirmModal.hidden = false;
        setTimeout(() => confirmSubmit?.focus(), 20);
        return true;
    };

    confirmCancel?.addEventListener('click', closeConfirmModal);
    document.addEventListener('app:before-navigate', closeConfirmModal);
    confirmModal?.addEventListener('click', event => {
        if (event.target === confirmModal) closeConfirmModal();
    });
    confirmSubmit?.addEventListener('click', () => {
        if (!pendingConfirmForm) return;
        const form = pendingConfirmForm;
        const submitter = pendingConfirmSubmitter;
        form.dataset.confirmed = 'true';
        confirmModal.hidden = true;
        pendingConfirmForm = null;
        pendingConfirmSubmitter = null;
        if (typeof form.requestSubmit === 'function') {
            const canUseSubmitter = submitter?.matches?.('button[type="submit"], input[type="submit"]') && submitter.form === form;
            form.requestSubmit(canUseSubmitter ? submitter : undefined);
        } else {
            form.submit();
        }
    });

    const toastMeta = {
        success: { title: 'Success', icon: 'bi-check-lg' },
        error: { title: 'Something went wrong', icon: 'bi-exclamation-lg' },
        warning: { title: 'Please check', icon: 'bi-exclamation-triangle' },
        info: { title: 'Notification', icon: 'bi-info-lg' },
    };

    window.vmcToast = (message, type = 'info', options = {}) => {
        if (!stack || !message) return;
        const safeType = Object.hasOwn(toastMeta, type) ? type : 'info';
        const meta = toastMeta[safeType];
        const duration = Number(options.duration) > 0
            ? Number(options.duration)
            : (safeType === 'error' ? 6500 : 4500);
        const toast = document.createElement('div');
        toast.className = `toast toast-${safeType}`;
        toast.setAttribute('role', safeType === 'error' ? 'alert' : 'status');
        toast.style.setProperty('--toast-duration', `${duration}ms`);

        const icon = document.createElement('span');
        icon.className = 'toast-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = `<i class="bi ${meta.icon}"></i>`;

        const content = document.createElement('span');
        content.className = 'toast-content';
        const title = document.createElement('strong');
        title.className = 'toast-title';
        title.textContent = options.title || meta.title;
        const body = document.createElement('span');
        body.className = 'toast-message';
        body.textContent = String(message);
        content.append(title, body);

        const dismiss = document.createElement('button');
        dismiss.type = 'button';
        dismiss.className = 'toast-dismiss';
        dismiss.setAttribute('aria-label', 'Dismiss notification');
        dismiss.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';

        const progress = document.createElement('span');
        progress.className = 'toast-progress';
        progress.setAttribute('aria-hidden', 'true');
        toast.append(icon, content, dismiss, progress);
        stack.appendChild(toast);

        while (stack.children.length > 4) stack.firstElementChild?.remove();

        let closed = false;
        let timeoutId;
        let startedAt;
        let remaining = duration;
        let paused = true;
        const close = () => {
            if (closed) return;
            closed = true;
            window.clearTimeout(timeoutId);
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 190);
        };
        const resume = () => {
            if (closed || !paused) return;
            paused = false;
            startedAt = Date.now();
            toast.classList.remove('is-paused');
            timeoutId = window.setTimeout(close, remaining);
        };
        const pause = () => {
            if (closed || paused) return;
            paused = true;
            window.clearTimeout(timeoutId);
            remaining = Math.max(0, remaining - (Date.now() - startedAt));
            toast.classList.add('is-paused');
        };
        dismiss.addEventListener('click', close);
        if (stack.classList.contains('is-pos-context')) {
            toast.addEventListener('click', close);
            toast.title = 'Click to dismiss';
        }
        toast.addEventListener('mouseenter', pause);
        toast.addEventListener('mouseleave', () => {
            if (!toast.contains(document.activeElement)) resume();
        });
        toast.addEventListener('focusin', pause);
        toast.addEventListener('focusout', event => {
            if (!toast.contains(event.relatedTarget) && !toast.matches(':hover')) resume();
        });
        resume();
        return toast;
    };

    const syncToastContext = () => stack?.classList.toggle('is-pos-context', Boolean(document.querySelector('.pos-layout')));
    const consumeFlashToasts = () => document.querySelectorAll('[data-flash-toast]').forEach(source => {
        if (source.dataset.toastConsumed === 'true') return;
        source.dataset.toastConsumed = 'true';
        window.vmcToast(source.textContent.trim(), source.dataset.toastType || 'info');
        source.remove();
    });
    syncToastContext();
    consumeFlashToasts();
    document.addEventListener('app:navigated', () => {
        syncToastContext();
        consumeFlashToasts();
    });

    document.addEventListener('click', event => {
        const btn = event.target.closest('.btn, .btn-theme, .btn-sign-out, .mobile-menu-toggle, .mobile-sidebar-close');
        if (!btn || btn.disabled) return;
        const rect = btn.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);
        ripple.className = 'btn-ripple';
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
        ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 520);
    });

    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-confirm-trigger]');
        const triggerForm = trigger?.closest('form[data-confirm]');
        if (triggerForm) {
            event.preventDefault();
            event.stopImmediatePropagation();
            openConfirmModal(triggerForm, trigger);
            return;
        }

        const submitter = event.target.closest('button[type="submit"], input[type="submit"]');
        const form = submitter?.form;
        if (!form?.dataset.confirm || form.dataset.confirmed === 'true') return;
        event.preventDefault();
        event.stopImmediatePropagation();
        openConfirmModal(form, submitter);
    }, true);

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.dataset.confirm || form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        openConfirmModal(form, event.submitter || form.querySelector('button[type="submit"], input[type="submit"], .btn[type="submit"]'));
    }, true);

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented || form.dataset.noLoading === 'true') return;
        const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"], .btn[type="submit"]');
        if (!submitter || submitter.classList.contains('is-submit-loading')) return;
        submitter.classList.add('is-submit-loading');
        submitter.disabled = true;
    });

    document.addEventListener('keydown', event => {
        const visibleDialogs = [...document.querySelectorAll('[role="dialog"], [role="alertdialog"]')]
            .filter(dialog => dialog.getClientRects().length > 0 && !dialog.closest('[hidden]'));
        const activeDialog = visibleDialogs.at(-1);

        if (event.key === 'Tab' && activeDialog) {
            const focusable = [...activeDialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')]
                .filter(element => element.getClientRects().length > 0 && element.getAttribute('aria-hidden') !== 'true');
            if (!focusable.length) {
                event.preventDefault();
                activeDialog.setAttribute('tabindex', '-1');
                activeDialog.focus();
                return;
            }
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
            return;
        }

        if (event.key !== 'Escape') return;
        if (confirmModal && !confirmModal.hidden) {
            closeConfirmModal();
            return;
        }
        if (!activeDialog) return;
        const closeControl = [...activeDialog.querySelectorAll('button, [role="button"]')].find(control =>
            [...control.attributes].some(attribute => attribute.name.startsWith('data-close-') || attribute.name.startsWith('data-cancel-'))
        );
        closeControl?.click();
    });
})();

(() => {
    let observedWrappers = new WeakSet();
    const syncScrollableTable = wrapper => {
        const isScrollable = wrapper.scrollWidth > wrapper.clientWidth + 1;

        if (isScrollable) {
            if (!wrapper.hasAttribute('tabindex')) {
                wrapper.tabIndex = 0;
                wrapper.dataset.autoTableFocus = 'true';
            }
            if (!wrapper.hasAttribute('aria-label')) {
                wrapper.setAttribute('aria-label', 'Scrollable data table');
                wrapper.dataset.autoTableLabel = 'true';
            }
            return;
        }

        if (wrapper.dataset.autoTableFocus === 'true') {
            wrapper.removeAttribute('tabindex');
            delete wrapper.dataset.autoTableFocus;
        }
        if (wrapper.dataset.autoTableLabel === 'true') {
            wrapper.removeAttribute('aria-label');
            delete wrapper.dataset.autoTableLabel;
        }
    };

    const resizeObserver = 'ResizeObserver' in window
        ? new ResizeObserver(entries => entries.forEach(entry => syncScrollableTable(entry.target)))
        : null;

    const registerScrollableTables = () => {
        document.querySelectorAll('.table-wrap').forEach(wrapper => {
            syncScrollableTable(wrapper);
            if (resizeObserver && !observedWrappers.has(wrapper)) {
                observedWrappers.add(wrapper);
                resizeObserver.observe(wrapper);
            }
        });
    };

    registerScrollableTables();
    document.addEventListener('app:before-navigate', () => {
        resizeObserver?.disconnect();
        observedWrappers = new WeakSet();
    });
    new MutationObserver(registerScrollableTables).observe(document.body, { childList: true, subtree: true });
    if (!resizeObserver) window.addEventListener('resize', registerScrollableTables, { passive: true });
})();
