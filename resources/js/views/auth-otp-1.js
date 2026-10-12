(() => {
    const form = document.querySelector('[data-otp-form]');
    const digits = [...document.querySelectorAll('[data-otp-digit]')];
    const hidden = document.getElementById('otp-hidden');
    const submitBtn = document.querySelector('[data-otp-submit]');
    const submitLabel = document.querySelector('[data-otp-submit-label]');
    const timerEl = document.querySelector('[data-otp-timer]');
    const resendBtn = document.querySelector('[data-otp-resend]');

    if (!form || !digits.length || !hidden) return;

    const syncHidden = () => {
        hidden.value = digits.map((d) => d.value).join('');
        digits.forEach((d) => d.classList.toggle('is-filled', d.value.length > 0));
        if (submitBtn) submitBtn.disabled = hidden.value.length !== 6;
    };

    const fillFromString = (value) => {
        const chars = value.replace(/\D/g, '').slice(0, 6).split('');
        digits.forEach((digit, index) => {
            digit.value = chars[index] ?? '';
        });
        const focusIndex = Math.min(chars.length, digits.length - 1);
        digits[focusIndex]?.focus();
        syncHidden();
    };

    if (hidden.value) fillFromString(hidden.value);

    digits.forEach((digit, index) => {
        digit.addEventListener('input', () => {
            digit.value = digit.value.replace(/\D/g, '').slice(-1);
            if (digit.value && index < digits.length - 1) {
                digits[index + 1].focus();
            }
            syncHidden();
        });

        digit.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !digit.value && index > 0) {
                digits[index - 1].focus();
                digits[index - 1].value = '';
                syncHidden();
            }
            if (event.key === 'ArrowLeft' && index > 0) digits[index - 1].focus();
            if (event.key === 'ArrowRight' && index < digits.length - 1) digits[index + 1].focus();
        });

        digit.addEventListener('paste', (event) => {
            event.preventDefault();
            fillFromString(event.clipboardData?.getData('text') ?? '');
        });
    });

    syncHidden();

    const parsedExpiry = Date.parse(timerEl?.dataset.expiresAt ?? '');
    const expiresAt = Number.isNaN(parsedExpiry) ? Date.now() : parsedExpiry;
    const formatTime = (total) => {
        const mins = Math.floor(total / 60);
        const secs = total % 60;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    };

    const tick = () => {
        if (!timerEl) return;
        const secondsLeft = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
        if (secondsLeft <= 0) {
            timerEl.textContent = 'Code expired. Request a new one';
            timerEl.classList.add('is-expired');
            if (resendBtn) resendBtn.disabled = false;
            return;
        }
        timerEl.textContent = `Resend Code in ${formatTime(secondsLeft)}`;
        timerEl.classList.toggle('is-warning', secondsLeft <= 60);
        if (secondsLeft <= 60 && resendBtn) resendBtn.disabled = false;
        window.setTimeout(tick, 1000);
    };
    tick();

    form.addEventListener('submit', (event) => {
        syncHidden();
        if (hidden.value.length !== 6) {
            event.preventDefault();
            return;
        }
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        submitBtn?.classList.add('is-loading');
        submitBtn?.setAttribute('aria-busy', 'true');
        if (submitLabel) submitLabel.textContent = 'Verifying...';
    });
})();
