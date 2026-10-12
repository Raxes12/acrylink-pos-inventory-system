// Page modules are cached by the browser, but their controls need a fresh
// initialization each time their HTML is mounted into the persistent shell.
const pages = new Map();
let disposers = [];
let mounting = false;
const key = url => new URL(url, location.href).pathname;

export function registerPage(url, initialize) {
    pages.set(key(url), initialize);
    if (!mounting) start(initialize);
}

function start(initialize) {
    const controller = new AbortController();
    const cleanup = [];
    const scope = {
        dispose: callback => cleanup.push(callback),
        listen(target, type, callback, options = {}) {
            target.addEventListener(type, callback, {
                ...(typeof options === 'boolean' ? { capture: options } : options),
                signal: controller.signal,
            });
        },
        setTimeout(callback, delay) {
            if (controller.signal.aborted) return;
            const id = window.setTimeout(callback, delay);
            cleanup.push(() => window.clearTimeout(id));
            return id;
        },
        requestAnimationFrame(callback) {
            if (controller.signal.aborted) return;
            const id = window.requestAnimationFrame(callback);
            cleanup.push(() => window.cancelAnimationFrame(id));
            return id;
        },
        async fetch(url, options = {}) {
            const request = new AbortController();
            const abort = () => request.abort();
            const signals = [controller.signal, options.signal].filter(Boolean);
            signals.forEach(signal => signal.addEventListener('abort', abort, { once: true }));
            if (signals.some(signal => signal.aborted)) abort();
            cleanup.push(() => {
                signals.forEach(signal => signal.removeEventListener('abort', abort));
            });
            return await window.fetch(url, { ...options, signal: request.signal });
        },
        expose(values) {
            Object.assign(window, values);
            cleanup.push(() => Object.entries(values).forEach(([name, value]) => {
                if (window[name] === value) delete window[name];
            }));
        },
    };
    disposers.push(() => {
        controller.abort();
        cleanup.reverse().forEach(dispose => dispose());
    });
    initialize(scope);
}

export function unmountPages() {
    disposers.forEach(dispose => dispose());
    disposers = [];
}

export async function mountPages(urls) {
    mounting = true;
    try {
        for (const url of new Set(urls)) {
            await import(/* @vite-ignore */ url);
            const initialize = pages.get(key(url));
            if (!initialize) throw new Error(`Missing page initializer: ${url}`);
            start(initialize);
        }
    } finally {
        mounting = false;
    }
}
