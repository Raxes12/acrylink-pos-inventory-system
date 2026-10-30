// Run with: node tests/browser/receipt-print.mjs
// Exercises the real receipt stylesheet and page module in installed Chrome.
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { readFile, mkdtemp, rm } from 'node:fs/promises';
import { spawn } from 'node:child_process';
import { tmpdir } from 'node:os';
import path from 'node:path';

const root = process.cwd();
const profile = await mkdtemp(path.join(tmpdir(), 'acry-receipt-'));
const server = createServer(async (request, response) => {
    const url = new URL(request.url, 'http://localhost');
    const files = {
        '/receipt.css': 'resources/css/views/pos-receipt-1.css',
        '/layout.css': 'resources/css/views/layouts-app-1.css',
        '/views/receipt.js': 'resources/js/views/pos-receipt-1.js',
        '/page-lifecycle.js': 'resources/js/page-lifecycle.js',
    };
    if (files[url.pathname]) {
        response.setHeader('Content-Type', url.pathname.endsWith('.css') ? 'text/css' : 'text/javascript');
        response.end(await readFile(path.join(root, files[url.pathname])));
        return;
    }
    const count = Number(url.searchParams.get('items') || 4);
    response.setHeader('Content-Type', 'text/html');
    response.end(`<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/layout.css"><link rel="stylesheet" href="/receipt.css"></head>
        <body><aside>Sidebar</aside><div class="main"><header>Toolbar</header><main class="content">
        <div class="alert">Sale completed</div><div class="receipt-shell"><div class="receipt-screen-header">Print Receipt</div>
        <div id="receipt" class="receipt-paper"><header class="receipt-header">
        <div class="receipt-company">VMC Auto Parts &amp; Accessories Trading</div><div>Ilagan City, Isabela</div></header>
        <div class="receipt-served-by">Served by System Admin</div><table class="receipt-table">
        <thead class="receipt-column-head"><tr><th>Item</th><th>Amount</th></tr></thead><tbody>
        ${Array.from({length: count}, (_, index) => `<tr><td><div class="item-name">AIR FILTER ${index} ${index % 2 ? 'WITH EXTRA LONG PRODUCT DESCRIPTION' : ''}</div>
        <div class="item-code">A-6012 / SAKURA / Unit: PCS</div><div class="item-calculation">2 ? 1,260.00</div></td>
        <td class="money">2,520.00</td></tr>`).join('')}</tbody></table>
        <div class="receipt-item-count">${count} items</div><section class="receipt-summary">
        <div class="summary-total"><span>TOTAL</span><strong>? 12,270.00</strong></div>
        <div class="summary-payment"><span>Cash</span><strong>12,500.00</strong></div>
        <div class="summary-change"><span>CHANGE</span><strong>? 230.00</strong></div></section>
        ${count > 4 ? '<section class="receipt-payment-note">Check No: 123456789<br>Bank: Sample Bank</section><section class="receipt-refunds"><div class="receipt-refund-entry"><div>Refund R-001<br>Returned damaged item</div><strong>-500.00</strong></div></section>' : ''}
        <footer class="receipt-footer"><strong>THANK YOU FOR YOUR PURCHASE</strong>
        <span>Please keep this receipt for returns and warranty concerns.</span><span class="receipt-order">Order RCP-0007</span>
        <span>Sep 25, 2026 12:31 PM</span></footer></div></div></main></div>
        <div id="toastStack">Notification</div><script type="module" src="/views/receipt.js"></script></body></html>`);
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
    socket.addEventListener('message', async event => {
        let payload;
        if (typeof event.data === 'string') payload = event.data;
        else if (typeof event.data?.text === 'function') payload = await event.data.text();
        else if (event.data instanceof ArrayBuffer) payload = new TextDecoder().decode(event.data);
        else if (ArrayBuffer.isView(event.data)) payload = new TextDecoder().decode(event.data);
        else payload = String(event.data);
        const message = JSON.parse(payload);
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

    let previousHeight = 0;
    for (const [items, width] of [[1, 1280], [4, 1280], [35, 390]]) {
        await send('Emulation.setEmulatedMedia', { media: 'screen' });
        await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });
        await send('Page.navigate', { url: `${origin}/?items=${items}` });
        let ready = false;
        for (let attempt = 0; attempt < 100; attempt++) {
            const result = await send('Runtime.evaluate', { expression: '!!document.getElementById("receiptPrintSize")?.textContent' });
            if (result.result?.result?.value) { ready = true; break; }
            await new Promise(resolve => setTimeout(resolve, 50));
        }
        assert.ok(ready, 'Receipt page module initialized');
        await send('Emulation.setEmulatedMedia', { media: 'print' });
        const measured = await send('Runtime.evaluate', { returnByValue: true, expression: `(() => {
            window.dispatchEvent(new Event('beforeprint'));
            const receipt = document.getElementById('receipt');
            return {
                width: receipt.getBoundingClientRect().width,
                height: receipt.getBoundingClientRect().height,
                page: document.getElementById('receiptPrintSize').textContent,
                header: getComputedStyle(document.querySelector('.receipt-column-head')).display,
                toolbar: getComputedStyle(document.querySelector('.receipt-screen-header')).display,
                clones: document.querySelectorAll('.receipt-print-measure').length
            };
        })()` });
        const result = measured.result.result.value;
        assert.ok(Math.abs(result.width - 70 * 96 / 25.4) < 1, 'Receipt content uses 70 mm inside 80 mm paper');
        assert.equal(result.header, 'none', 'Print keeps the screen item layout');
        assert.equal(result.toolbar, 'none', 'Print hides actions');
        assert.equal(result.clones, 0, 'Measurement is cleaned up');
        const height = Number(result.page.match(/80mm (\d+)mm/)[1]);
        assert.ok(height > previousHeight, 'Page grows with receipt content');
        assert.ok(height * 96 / 25.4 >= result.height, 'Page fits all receipt content');
        assert.ok(height * 96 / 25.4 - result.height < 12, 'No excessive trailing whitespace');
        previousHeight = height;
        const printed = await send('Page.printToPDF', { preferCSSPageSize: true, displayHeaderFooter: false, printBackground: true });
        assert.ok(printed.result?.data, JSON.stringify(printed.error));
        const pdf = Buffer.from(printed.result.data, 'base64').toString('latin1');
        assert.equal((pdf.match(/\/Type\s*\/Page\b/g) || []).length, 1, 'PDF contains one complete receipt page');
        const box = pdf.match(/\/MediaBox\s*\[0 0 ([\d.]+) ([\d.]+)\]/);
        assert.ok(box, 'PDF has page dimensions');
        assert.ok(Math.abs(Number(box[1]) - 80 * 72 / 25.4) < 1, 'PDF has correct 80 mm physical width');
        console.log(`PASS: ${items} item rows, ${width}px viewport, one 80 x ${height} mm PDF page`);
    }
} finally {
    socket?.close();
    chrome.kill();
    await new Promise(resolve => server.close(resolve));
    await new Promise(resolve => setTimeout(resolve, 500));
    await rm(profile, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 });
}
