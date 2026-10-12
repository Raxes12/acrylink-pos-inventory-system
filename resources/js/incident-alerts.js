let activeIncidentModal = null;
let incidentTrigger = null;

const closeIncidentModal = () => {
    if (!activeIncidentModal) return;
    activeIncidentModal.hidden = true;
    activeIncidentModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('has-open-modal');
    activeIncidentModal = null;
    incidentTrigger?.focus?.();
};

document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-incident-action]');
    if (trigger) {
        const action = trigger.dataset.incidentAction;
        const modal = document.getElementById(`${action}AlertModal`);
        if (!modal) return;
        const form = modal.querySelector('[data-incident-form]');
        form.action = trigger.dataset.actionUrl;
        const context = modal.querySelector('[data-incident-context]');
        context.innerHTML = '';
        [
            ['Alert', trigger.dataset.alertTitle],
            ['Severity', trigger.dataset.severity],
            ['Event', trigger.dataset.eventType],
            ['Source', trigger.dataset.source],
            ['IP / Device', trigger.dataset.address],
            ['Detected', trigger.dataset.detected],
        ].filter(([, value]) => value).forEach(([label, value]) => {
            const row = document.createElement('div');
            const term = document.createElement('strong');
            term.textContent = `${label}: `;
            row.append(term, document.createTextNode(value));
            context.append(row);
        });
        incidentTrigger = trigger;
        activeIncidentModal = modal;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-open-modal');
        modal.querySelector('textarea, select, button')?.focus();
        return;
    }

    if (event.target.closest('[data-incident-close]') || (activeIncidentModal && event.target === activeIncidentModal)) closeIncidentModal();
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && activeIncidentModal) closeIncidentModal();
});
