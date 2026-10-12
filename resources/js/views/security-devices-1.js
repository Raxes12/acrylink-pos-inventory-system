import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
let AUTHORIZED_DEVICES = [];
const refreshDevices = () => {
    try {
        const payload = document.getElementById('authorizedDevicesData')?.textContent?.trim();
        AUTHORIZED_DEVICES = JSON.parse(payload || '[]');
        const initialPayload = document.getElementById('initialEditingDeviceData')?.textContent?.trim();
        const initialDevice = JSON.parse(initialPayload || 'null');
        if (initialDevice && !AUTHORIZED_DEVICES.some(device => String(device.id) === String(initialDevice.id))) {
            AUTHORIZED_DEVICES.push(initialDevice);
        }
    } catch (error) {
        AUTHORIZED_DEVICES = [];
    }
};
refreshDevices();
scope.listen(document, 'app:filtered', refreshDevices);

let activeModal = null;
let modalTrigger = null;

const actionFromTemplate = (form, id) => (form.dataset.actionTemplate || '').replace('__ID__', encodeURIComponent(id));
const urlFromTemplate = (element, id) => (element.dataset.editTemplate || '').replace('__ID__', encodeURIComponent(id));
const findDevice = id => {
    const deviceId = String(id);
    return AUTHORIZED_DEVICES.find(device => String(device.id) === deviceId);
};
const setText = (id, value) => {
    const element = document.getElementById(id);
    if (element) element.textContent = value || '-';
};
const statusClass = status => {
    if (status === 'authorized') return 'status-active';
    if (status === 'blocked') return 'status-inactive';
    return 'status-low';
};
const label = value => String(value || '-').replace(/_/g, ' ').replace(/^\w/, character => character.toUpperCase());
const syncDeviceMetricSelection = value => {
    document.querySelectorAll('[data-device-status-filter]').forEach(card => {
        const active = card.dataset.deviceStatusFilter === value;
        card.classList.toggle('is-active', active);
        card.setAttribute('aria-pressed', String(active));
    });
};
const assignedUserLabel = device => {
    const user = device.assigned_user;
    if (!user) return 'Any active user';

    return user.username
        ? `${user.name || user.username} (${user.username})`
        : user.name || 'Assigned user';
};
const dateLabel = value => {
    if (!value) return '-';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleString(undefined, {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
const showModal = (modal, trigger) => {
    if (!modal) return;

    activeModal = modal;
    modalTrigger = trigger || document.activeElement;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('has-open-modal');
    modal.querySelector('.modal-close-btn, input, select, button, a')?.focus();
};
const hideModal = (modal, restoreFocus = true) => {
    if (!modal) return;

    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');

    if (activeModal === modal) {
        activeModal = null;
        document.body.classList.remove('has-open-modal');
    }

    if (restoreFocus) modalTrigger?.focus?.();
};

function openDeviceView(id, trigger = null) {
    const deviceId = String(id);
    const device = findDevice(deviceId);
    const modal = document.getElementById('viewDeviceModal');
    if (!device || !modal) return false;

    setText('viewDeviceName', device.device_name);
    setText('viewDeviceIp', device.ip_address);
    setText('viewDeviceMac', device.mac_address);
    setText('viewDeviceUser', assignedUserLabel(device));
    setText('viewDeviceCreated', dateLabel(device.created_at));
    setText('viewDeviceUpdated', dateLabel(device.updated_at));

    const status = document.getElementById('viewDeviceStatus');
    if (status) {
        status.className = `status ${statusClass(device.status)}`;
        status.textContent = label(device.status);
    }

    const editLink = document.getElementById('viewDeviceEditLink');
    if (editLink) {
        editLink.href = urlFromTemplate(editLink, deviceId) || editLink.href;
        editLink.dataset.editDevice = deviceId;
    }

    showModal(modal, trigger);
    return true;
}
function closeDeviceView() {
    hideModal(document.getElementById('viewDeviceModal'));
}

function openDeviceEdit(id, trigger = null) {
    const deviceId = String(id);
    const d = findDevice(deviceId);
    if (!d) return false;
    const f = document.getElementById('editDeviceForm');
    if (!f) return false;
    f.action = actionFromTemplate(f, deviceId);
    f.querySelector('[name=device_name]').value = d.device_name || '';
    f.querySelector('[name=ip_address]').value = d.ip_address || '';
    f.querySelector('[name=mac_address]').value = d.mac_address || '';
    f.querySelector('[name=assigned_user_id]').value = d.assigned_user_id || '';
    f.querySelector('[name=status]').value = d.status || 'authorized';
    f.querySelector('[name=_device_id]').value = deviceId;
    showModal(document.getElementById('editDeviceModal'), trigger);
    return true;
}
function closeDeviceEdit() { hideModal(document.getElementById('editDeviceModal')); }
function openDeviceRegister(trigger = null) { showModal(document.getElementById('registerDeviceModal'), trigger); }
function closeDeviceRegister() { hideModal(document.getElementById('registerDeviceModal')); }

const closeActiveModal = () => {
    if (!activeModal) return;
    if (activeModal.id === 'viewDeviceModal') closeDeviceView();
    else if (activeModal.id === 'registerDeviceModal') closeDeviceRegister();
    else closeDeviceEdit();
};

scope.expose({
    openDeviceView,
    closeDeviceView,
    openDeviceEdit,
    closeDeviceEdit,
    openDeviceRegister,
    closeDeviceRegister,
});

scope.listen(document, 'click', event => {
    const metric = event.target.closest('[data-device-status-filter]');
    if (metric) {
        const statusSelect = document.querySelector('.security-filter-bar [name="status"]');
        if (statusSelect) {
            statusSelect.value = metric.dataset.deviceStatusFilter || '';
            syncDeviceMetricSelection(statusSelect.value);
            statusSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        return;
    }

    const register = event.target.closest('[data-open-device-register]');
    if (register) {
        openDeviceRegister(register);
        return;
    }

    const view = event.target.closest('[data-view-device]');
    if (view && openDeviceView(view.dataset.viewDevice, view)) {
        event.preventDefault();
        return;
    }

    if (event.target.closest('[data-close-device-view]')) {
        closeDeviceView();
        return;
    }

    if (event.target.closest('[data-close-device-edit]')) {
        closeDeviceEdit();
        return;
    }

    if (event.target.closest('[data-close-device-register]')) {
        closeDeviceRegister();
        return;
    }

    if (event.target.classList.contains('modal-backdrop')) {
        if (event.target.id === 'viewDeviceModal') closeDeviceView();
        else if (event.target.id === 'registerDeviceModal') closeDeviceRegister();
        else closeDeviceEdit();
        return;
    }

    const edit = event.target.closest('[data-edit-device]');
    if (!edit) return;
    if (openDeviceEdit(edit.dataset.editDevice, edit)) {
        event.preventDefault();
        hideModal(document.getElementById('viewDeviceModal'), false);
    }
});

scope.listen(document, 'change', event => {
    if (event.target.matches('.security-filter-bar [name="status"]')) {
        syncDeviceMetricSelection(event.target.value);
    }
});

scope.listen(document, 'keydown', event => {
    if (event.key !== 'Escape' || !activeModal) return;

    closeActiveModal();
});

const pageState = document.getElementById('devicePageState');
if (pageState?.dataset.openRegister === 'true') {
    scope.requestAnimationFrame(() => openDeviceRegister());
} else if (pageState?.dataset.initialEditDevice) {
    scope.requestAnimationFrame(() => openDeviceEdit(pageState.dataset.initialEditDevice));
}

});
