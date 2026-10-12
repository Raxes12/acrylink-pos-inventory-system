import { mountPages, unmountPages } from './page-lifecycle.js';

const sidebar = document.getElementById('appSidebar');
const progress = document.getElementById('routeProgress');
let pending;
let committing = false;
let queuedVisit;
let skeleton;

function startLoading(request, label) {
    skeleton?.remove();
    if (progress) {
        progress.hidden = false;
        progress.setAttribute('aria-label', `Loading ${label}`);
    }
    const main = document.getElementById('main-content');
    main.setAttribute('aria-busy', 'true');
    // Cover the outgoing page immediately. Waiting before mounting this layer
    // makes slower destinations appear to show stale content after a nav click.
    if (pending !== request || !main.isConnected) return;
    skeleton = document.createElement('div');
    skeleton.className = 'route-loading-skeleton';
    skeleton.setAttribute('role', 'status');
    skeleton.setAttribute('aria-label', `Loading ${label}`);
    skeleton.innerHTML = '<span class="route-skeleton-title"></span><span class="route-skeleton-subtitle"></span><span class="route-skeleton-cards"><i></i><i></i><i></i></span><span class="route-skeleton-list"><i></i><i></i><i></i></span>';
    main.appendChild(skeleton);
}

function stopLoading() {
    skeleton?.remove();
    skeleton = null;
    if (progress) progress.hidden = true;
    document.getElementById('main-content')?.removeAttribute('aria-busy');
}

function pageStyles(doc) {
    const marker = doc.querySelector('meta[name="page-styles-start"]');
    const nodes = [];
    for (let node = marker?.nextElementSibling; node; node = node.nextElementSibling) {
        if (node.matches('link, style')) nodes.push(node);
    }
    return nodes;
}

async function loadStyles(doc) {
    const previous = pageStyles(document);
    const incoming = pageStyles(doc);
    const sameStyle = (a, b) => a.tagName === b.tagName
        && (a.matches('link') ? a.href === b.href : a.textContent === b.textContent);
    if (previous.length === incoming.length && previous.every((node, index) => sameStyle(node, incoming[index]))) return;
    const next = incoming.map(node => node.cloneNode(true));
    await Promise.all(next.map(node => new Promise((resolve, reject) => {
        if (node.matches('link[rel="stylesheet"]')) {
            node.onload = resolve;
            node.onerror = reject;
        } else resolve();
        document.head.appendChild(node);
    })));
    previous.forEach(node => node.remove());
}

function syncSidebar(next) {
    const currentLinks = [...sidebar.querySelectorAll('.nav-link')];
    const nextLinks = [...next.querySelectorAll('.nav-link')];
    // A changed account or permission set needs a complete shell refresh.
    if (currentLinks.length !== nextLinks.length || currentLinks.some((link, index) => link.href !== nextLinks[index].href)) {
        throw new Error('Navigation permissions changed');
    }
    currentLinks.forEach((link, index) => {
        const incoming = nextLinks[index];
        const active = incoming.classList.contains('active');
        link.classList.toggle('active', active);
        if (active) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
        link.querySelector('.nav-badge')?.remove();
        const badge = incoming.querySelector('.nav-badge');
        if (badge) link.appendChild(badge.cloneNode(true));
    });
    for (const selector of ['.user-avatar', '.user-name', '.user-role']) {
        sidebar.querySelector(selector).textContent = next.querySelector(selector).textContent;
    }
}

async function visit(url, { historyVisit = false, trigger = null } = {}) {
    // Finish mounting one page before starting another set of page scripts.
    if (committing) {
        queuedVisit = { url, historyVisit, trigger };
        return;
    }
    pending?.abort();
    const request = new AbortController();
    pending = request;
    const label = trigger?.querySelector('.nav-text')?.textContent?.trim()
        || trigger?.textContent?.trim() || 'page';
    if (trigger?.classList.contains('nav-link')) {
        sidebar.querySelectorAll('.nav-link.active').forEach(link => {
            link.classList.remove('active');
            link.removeAttribute('aria-current');
        });
        trigger.classList.add('active');
        trigger.setAttribute('aria-current', 'page');
    }
    startLoading(request, label);
    if (!historyVisit) window.scrollTo(0, 0);
    try {
        const response = await fetch(url, {
            signal: request.signal,
            credentials: 'same-origin',
            headers: { Accept: 'text/html' },
        });
        if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) throw new Error('Full navigation required');
        const next = new DOMParser().parseFromString(await response.text(), 'text/html');
        if (request.signal.aborted) return;
        const main = next.getElementById('main-content');
        const nextSidebar = next.getElementById('appSidebar');
        const scripts = next.getElementById('page-scripts');
        if (!main || !nextSidebar || !scripts) throw new Error('Destination has a different layout');
        committing = true;
        await loadStyles(next);
        const modules = [...main.querySelectorAll('script[type="module"][src]'), ...scripts.querySelectorAll('script[type="module"][src]')].map(script => script.src);
        syncSidebar(nextSidebar);
        document.dispatchEvent(new Event('app:before-navigate'));
        unmountPages();
        document.getElementById('main-content').replaceWith(main);
        document.getElementById('page-scripts').replaceWith(scripts);
        // Keep controls with persistent listeners (clock, theme, mobile menu).
        const header = next.querySelector('.topbar');
        for (const id of ['mobileMenuToggle', 'liveClock', 'themeToggle']) {
            header.querySelector(`#${id}`)?.replaceWith(document.getElementById(id));
        }
        document.querySelector('.topbar').replaceWith(header);
        document.title = next.title;
        const csrf = next.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) {
            document.querySelector('meta[name="csrf-token"]').content = csrf;
            sidebar.querySelector('input[name="_token"]').value = csrf;
        }
        const destination = new URL(response.url);
        destination.hash = new URL(url, location.href).hash;
        if (historyVisit) history.replaceState({}, '', destination);
        else history.pushState({}, '', destination);
        await mountPages(modules);
        document.dispatchEvent(new Event('app:navigated'));
        main.focus({ preventScroll: true });
        const anchor = destination.hash && document.getElementById(decodeURIComponent(destination.hash.slice(1)));
        if (anchor) anchor.scrollIntoView();
        else window.scrollTo(0, 0);
    } catch (error) {
        if (error.name !== 'AbortError') location.assign(url);
    } finally {
        if (pending === request) {
            stopLoading();
            pending = null;
            committing = false;
            if (queuedVisit) {
                const next = queuedVisit;
                queuedVisit = null;
                visit(next.url, { historyVisit: next.historyVisit, trigger: next.trigger });
            }
        }
    }
}

if (sidebar) {
    sidebar.querySelector('.nav-link.active')?.setAttribute('aria-current', 'page');
    window.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('download') || link.hasAttribute('data-full-navigation') || (link.target && link.target !== '_self')) return;
        const url = new URL(link.href, location.href);
        if (url.origin !== location.origin || !['http:', 'https:'].includes(url.protocol)) return;
        if (/\/(export|download)(\/|$)|\.(pdf|csv|xlsx?)$/i.test(url.pathname)) return;
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
        event.preventDefault();
        visit(url.href, { trigger: link });
    });
    window.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'get') return;
        if (form.hasAttribute('data-full-navigation') || form.hasAttribute('data-no-loading')) return;
        if (form.target && form.target !== '_self') return;

        const destination = new URL(form.action || location.href, location.href);
        if (destination.origin !== location.origin) return;
        const data = event.submitter
            ? new FormData(form, event.submitter)
            : new FormData(form);
        destination.search = new URLSearchParams(data).toString();
        event.preventDefault();
        visit(destination.href, { trigger: event.submitter });
    });
    window.addEventListener('popstate', () => visit(location.href, { historyVisit: true }));
}
