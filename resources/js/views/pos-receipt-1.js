import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    const receipt = document.getElementById('receipt');
    if (!receipt) return;

    const printSize = document.createElement('style');
    printSize.id = 'receiptPrintSize';
    document.head.appendChild(printSize);

    function updateReceiptPaperSize() {
        const isPrintMedia = window.matchMedia('print').matches;
        const measurement = isPrintMedia ? receipt : receipt.cloneNode(true);

        if (!isPrintMedia) {
            measurement.removeAttribute('id');
            measurement.querySelectorAll('[id]').forEach(element => element.removeAttribute('id'));
            measurement.classList.add('receipt-print-measure');
            measurement.setAttribute('aria-hidden', 'true');
            measurement.inert = true;
            receipt.parentElement.appendChild(measurement);
        }

        try {
            const receiptBottom = measurement.getBoundingClientRect().bottom;
            const descendantBottom = [...measurement.querySelectorAll('*')].reduce(
                (bottom, element) => Math.max(bottom, element.getBoundingClientRect().bottom),
                receiptBottom,
            );
            const renderedHeight = descendantBottom - measurement.getBoundingClientRect().top;
            const height = Math.max(measurement.scrollHeight, measurement.offsetHeight, renderedHeight);
            // Include the 2 mm zigzag edge and a small rounding allowance. The
            // resulting custom page remains one continuous thermal receipt.
            const paperHeight = Math.ceil(height * 25.4 / 96) + 3;
            printSize.textContent = `@media print { @page { size: 80mm ${paperHeight}mm; margin: 0; } }`;
        } finally {
            if (!isPrintMedia) measurement.remove();
        }
    }

    scope.listen(window, 'beforeprint', updateReceiptPaperSize);
    scope.listen(window, 'load', updateReceiptPaperSize);
    receipt.querySelectorAll('img').forEach(img => scope.listen(img, 'load', updateReceiptPaperSize));
    updateReceiptPaperSize();
    scope.dispose(() => printSize.remove());
});
