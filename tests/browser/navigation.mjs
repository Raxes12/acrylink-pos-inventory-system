// Run with: node tests/browser/navigation.mjs
// Uses an installed Chrome (or CHROME_PATH), no browser test dependency.
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { readFile, mkdtemp, rm } from 'node:fs/promises';
import { spawn } from 'node:child_process';
import { tmpdir } from 'node:os';
import path from 'node:path';

const root = process.cwd();
const profile = await mkdtemp(path.join(tmpdir(), 'acry-navigation-'));
let auditRequests = 0;
let filterRequests = 0;
const pages = {
    dashboard: '<span data-count="42">0</span>',
    audit: `<form id="auditLiveFilter" action="/audit" data-results-url="/audit/results">
        <input name="search"><span data-audit-status></span></form>
        <div id="auditResults"><div class="rules-pagination"><a href="/audit?page=2">Next</a></div></div>`,
    suppliers: '<div class="supplier-form"><select class="supplier-payment-method"><option>cash</option><option>check</option></select><div class="supplier-check-note">Check details</div></div>',
    catalog: '<form action="/catalog" data-live-filter><input name="search" type="search"><a href="/catalog" data-live-filter-clear>Clear</a><span data-live-filter-status></span></form><div data-live-results><span class="result">Initial</span><nav class="pagination"><a href="/catalog?page=2">Next</a></nav></div>',
};
const modules = { dashboard: 'dashboard-index-1', audit: 'audit-index-1', suppliers: 'suppliers-_form-1', catalog: 'audit-index-1' };
const manifest = JSON.parse(await readFile(path.join(root, 'public/build/manifest.json'), 'utf8'));
const asset = name => `/build/${manifest[name].file}`;
const html = (page, query = '') => `<!doctype html><html><head><meta name="csrf-token" content="test"><title>${page}</title>
    <script type="module" src="${asset('resources/js/app.js')}"></script><meta name="page-styles-start">
    <style>.sidebar-nav {height:80px;overflow:auto}.nav-link{display:block;height:60px}</style></head><body>
    <aside id="appSidebar"><span class="user-avatar">A</span><span class="user-name">Admin</span><span class="user-role">Admin</span>
    <button id="mobileSidebarClose"></button><nav class="sidebar-nav">${Object.keys(pages).map(name => `<a class="nav-link ${name === page ? 'active' : ''}" href="/${name}">${name}</a>`).join('')}</nav>
    <form><input name="_token" value="test"></form></aside><div id="sidebarScrim" hidden></div>
    <header class="topbar"><button id="mobileMenuToggle"></button><span class="page-label">${page}</span>
    <span id="liveClock" data-server-time="2026-09-20T12:00:00Z"></span><button id="themeToggle"></button></header>
    <main id="main-content" tabindex="-1">${page === 'catalog' ? pages.catalog.replace('Initial', new URLSearchParams(query).get('page') === '2' ? 'Page 2' : (new URLSearchParams(query).get('search') || 'Initial')) : pages[page]}</main><div id="toastStack"></div>
    <div id="page-scripts" hidden><script type="module" src="${asset(`resources/js/views/${modules[page]}.js`)}"></script></div></body></html>`;
const server = createServer(async (request, response) => {
    try {
        const url = new URL(request.url, 'http://localhost');
        if (url.pathname === '/audit/results') {
            auditRequests++;
            response.setHeader('Content-Type', 'application/json');
            response.end(JSON.stringify({ html: '<div id="auditResults">Updated</div>' }));
        } else if (url.pathname.startsWith('/build/')) {
            const file = path.resolve(root, 'public', `.${url.pathname}`);
            assert.ok(file.startsWith(path.join(root, 'public', 'build') + path.sep));
            response.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css');
            response.end(await readFile(file));
        } else if (pages[url.pathname.slice(1)]) {
            if (url.pathname === '/catalog' && url.search) filterRequests++;
            response.setHeader('Content-Type', 'text/html');
            response.end(html(url.pathname.slice(1), url.search));
        } else { response.writeHead(404).end(); }
    } catch (error) { response.writeHead(500).end(error.message); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const origin = `http://127.0.0.1:${server.address().port}`;
const chrome = spawn(process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', [
    '--headless=new', '--no-first-run', '--no-default-browser-check', '--remote-debugging-port=0',
    `--user-data-dir=${profile}`, 'about:blank',
], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
let socket;
try {
    const endpoint = await new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('Chrome startup timeout')), 20000);
        chrome.once('error', reject);
        chrome.stderr.on('data', chunk => {
            const match = chunk.toString().match(/DevTools listening on (ws:\/\/[^\s]+)/);
            if (match) { clearTimeout(timeout); resolve(match[1]); }
        });
    });
    const targets = await (await fetch(`http://${new URL(endpoint).host}/json/list`)).json();
    socket = new WebSocket(targets.find(target => target.type === 'page').webSocketDebuggerUrl);
    await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
    let id = 0;
    const calls = new Map();
    const errors = [];
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data);
        if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails.text);
        if (message.id) {
            calls.get(message.id)?.(message);
            calls.delete(message.id);
        }
    });
    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error(`Browser command timed out: ${method}`)), 15000);
        calls.set(++id, message => { clearTimeout(timeout); resolve(message); });
        socket.send(JSON.stringify({ id, method, params }));
    });
    console.log('Chrome connected; checking navigation...');
    await send('Runtime.enable');
    await send('Page.navigate', { url: `${origin}/dashboard` });
    let ready = false;
    for (let attempt = 0; attempt < 100; attempt++) {
        const result = await send('Runtime.evaluate', { expression: '!!window.vmcToast && document.readyState === "complete"' });
        if (result.result?.result?.value) { ready = true; break; }
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    assert.ok(ready, 'Application boots');
    const result = await send('Runtime.evaluate', { awaitPromise: true, returnByValue: true, expression: `(async () => {
        const assert = (value, message) => { if (!value) throw new Error(message); };
        const sidebar = document.getElementById('appSidebar');
        const nav = sidebar.querySelector('nav');
        const theme = document.getElementById('themeToggle');
        nav.scrollTop = 40;
        const scroll = nav.scrollTop;
        theme.click();
        const selectedTheme = document.body.dataset.theme;
        const wait = () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Navigation timeout')), 5000);
            document.addEventListener('app:navigated', () => { clearTimeout(timeout); resolve(); }, {once:true});
        });
        const visit = async page => { const done = wait(); sidebar.querySelector('[href="/' + page + '"]').click(); await done;
            assert(document.getElementById('appSidebar') === sidebar, 'Sidebar was replaced');
            assert(nav.scrollTop === scroll, 'Sidebar scroll moved');
            assert(sidebar.querySelector('[aria-current="page"]').pathname === '/' + page, 'Wrong active link');
            assert(document.body.dataset.theme === selectedTheme, 'Theme changed');
        };
        await visit('audit');
        await visit('dashboard');
        await visit('audit');
        document.querySelector('#auditResults a').click();
        for (let i = 0; i < 50 && document.getElementById('auditResults').textContent !== 'Updated'; i++) await new Promise(r => setTimeout(r, 20));
        assert(document.getElementById('auditResults').textContent === 'Updated', 'Audit pagination failed');
        await visit('suppliers');
        assert(document.querySelector('.supplier-check-note').style.display === 'none', 'Supplier controls did not initialize');
        const select = document.querySelector('.supplier-payment-method'); select.value = 'check'; select.dispatchEvent(new Event('change'));
        assert(document.querySelector('.supplier-check-note').style.display === '', 'Supplier change handler failed');
        await visit('catalog');
        const filterForm = document.querySelector('[data-live-filter]');
        const search = filterForm.querySelector('input');
        search.value = 'bolt'; search.dispatchEvent(new Event('input', { bubbles: true }));
        for (let i = 0; i < 50 && document.querySelector('.result').textContent !== 'bolt'; i++) await new Promise(r => setTimeout(r, 20));
        assert(document.querySelector('.result').textContent === 'bolt', 'Live filter failed');
        assert(document.querySelector('[data-live-filter]') === filterForm, 'Filter form was replaced');
        assert(document.getElementById('appSidebar') === sidebar, 'Live filter replaced sidebar');
        document.querySelector('[data-live-results] .pagination a').click();
        for (let i = 0; i < 50 && document.querySelector('.result').textContent !== 'Page 2'; i++) await new Promise(r => setTimeout(r, 20));
        assert(document.querySelector('.result').textContent === 'Page 2', 'Live pagination failed');
        assert(location.search.includes('page=2'), 'Pagination URL did not update');
        let done = wait(); history.back(); await done;
        assert(location.pathname === '/catalog', 'Back failed');
        assert(!window.toggleSupplierCheckFields, 'Old page globals leaked');
        done = wait(); history.forward(); await done;
        assert(location.pathname === '/catalog', 'Forward failed');
        assert(document.getElementById('appSidebar') === sidebar, 'History replaced sidebar');
        assert(document.getElementById('themeToggle') === theme, 'Theme control replaced');
        done = wait();
        sidebar.querySelector('[href="/dashboard"]').click();
        sidebar.querySelector('[href="/audit"]').click();
        await done;
        assert(location.pathname === '/audit', 'Latest rapid navigation did not win');
        assert(document.getElementById('appSidebar') === sidebar, 'Rapid navigation replaced sidebar');
        return 'Sidebar identity, scroll, active links, theme, repeat visits, page controls, cleanup, Back/Forward passed';
    })()` });
    assert.equal(result.result?.exceptionDetails, undefined, JSON.stringify(result.result?.exceptionDetails));
    assert.deepEqual(errors, [], 'No uncaught browser errors');
    assert.equal(auditRequests, 1, 'Audit listeners must not duplicate or trigger full navigation');
    assert.ok(filterRequests >= 2, 'Live filters did not request filtered pages');
    console.log(result.result.result.value);
} finally {
    socket?.close();
    chrome.kill();
    server.closeAllConnections();
    server.close();
    await new Promise(resolve => {
        if (chrome.exitCode !== null || chrome.signalCode !== null) return resolve();
        chrome.once('exit', resolve);
        setTimeout(resolve, 3000);
    });
    if (path.dirname(profile) === path.resolve(tmpdir()) && path.basename(profile).startsWith('acry-navigation-')) {
        await rm(profile, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 });
    }
}
