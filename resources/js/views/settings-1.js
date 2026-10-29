const input = document.querySelector('[data-logo-input]');
const preview = document.getElementById('businessLogoPreview');
const fileLabel = document.querySelector('[data-logo-file]');
const reset = document.querySelector('[data-logo-reset]');
const dropzone = document.querySelector('[data-logo-dropzone]');
const originalLogo = preview?.src;
let previewUrl;

const allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];
const maximumBytes = 2 * 1024 * 1024;

const setPreview = (url) => {
    if (preview && url) preview.src = url;
};

const showFile = (file) => {
    fileLabel?.classList.remove('is-selected', 'is-error');

    if (!allowedTypes.includes(file.type)) {
        if (fileLabel) fileLabel.textContent = 'Please choose a PNG, JPG, or WEBP image.';
        fileLabel?.classList.add('is-error');
        input.value = '';
        return;
    }

    if (file.size > maximumBytes) {
        if (fileLabel) fileLabel.textContent = 'This image is larger than 2 MB.';
        fileLabel?.classList.add('is-error');
        input.value = '';
        return;
    }

    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    setPreview(previewUrl);
    if (fileLabel) fileLabel.textContent = `${file.name} selected`;
    fileLabel?.classList.add('is-selected');
    if (reset) reset.checked = false;
};

input?.addEventListener('change', () => {
    const file = input.files?.[0];
    if (file) showFile(file);
});

dropzone?.addEventListener('click', () => input?.click());
dropzone?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        input?.click();
    }
});

['dragenter', 'dragover'].forEach((eventName) => dropzone?.addEventListener(eventName, (event) => {
    event.preventDefault();
    dropzone.classList.add('is-dragging');
}));

['dragleave', 'drop'].forEach((eventName) => dropzone?.addEventListener(eventName, (event) => {
    event.preventDefault();
    dropzone.classList.remove('is-dragging');
}));

dropzone?.addEventListener('drop', (event) => {
    const file = event.dataTransfer?.files?.[0];
    if (!file || !input) return;

    const transfer = new DataTransfer();
    transfer.items.add(file);
    input.files = transfer.files;
    showFile(file);
});

reset?.addEventListener('change', () => {
    if (reset.checked) {
        input.value = '';
        setPreview(reset.dataset.defaultLogo);
        if (fileLabel) fileLabel.textContent = 'Default logo selected. Save to apply.';
        fileLabel?.classList.remove('is-selected', 'is-error');
        return;
    }

    setPreview(originalLogo);
    if (fileLabel) fileLabel.textContent = 'PNG, JPG, or WEBP · Maximum 2 MB';
    fileLabel?.classList.remove('is-selected', 'is-error');
});

window.addEventListener('pagehide', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
});
