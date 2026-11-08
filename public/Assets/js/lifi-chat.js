(() => {
    const panel = document.querySelector('#lifi-panel');
    const toggle = document.querySelector('[data-lifi-toggle]');
    const close = document.querySelector('[data-lifi-close]');
    const form = document.querySelector('#lifi-form');
    const input = document.querySelector('#lifi-input');
    const messages = document.querySelector('#lifi-messages');

    if (!panel || !toggle || !close || !form || !input || !messages) {
        return;
    }

    let activeToken = '';

    const addMessage = (text, sender) => {
        const message = document.createElement('div');
        message.className = `lifi-message lifi-message-${sender}`;
        message.textContent = text;
        messages.append(message);
        messages.scrollTop = messages.scrollHeight;

        return message;
    };

    const setOpen = (isOpen) => {
        panel.hidden = !isOpen;
        toggle.setAttribute('aria-expanded', String(isOpen));
    };

    toggle.addEventListener('click', () => {
        setOpen(panel.hidden);
        if (!panel.hidden) {
            input.focus();
        }
    });

    close.addEventListener('click', () => {
        setOpen(false);
        toggle.focus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            setOpen(false);
            toggle.focus();
        }
    });

    document.querySelectorAll('[data-lifi-prompt]').forEach((button) => {
        button.addEventListener('click', () => {
            input.value = button.dataset.lifiPrompt;
            form.requestSubmit();
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();

        if (!message) {
            return;
        }

        addMessage(message, 'user');
        input.value = '';
        input.disabled = true;
        const pending = addMessage('Sebentar ya, aku cek dulu...', 'assistant');
        const tokenInMessage = message.match(/\bRSV-[A-Z0-9-]{4,24}\b/i)?.[0] ?? '';

        if (tokenInMessage) {
            activeToken = tokenInMessage.toUpperCase();
        }

        const asksAboutReservation = /\b(jadwal|reservasi|booking|status|pembayaran|bayar|foto|hasil|pesanan)\b/i.test(message);
        const asksAboutSpecificDate = /\b(?:\d{4}-\d{1,2}-\d{1,2}|\d{1,2}[\/.-]\d{1,2}[\/.-]\d{4})\b/.test(message);
        const token = tokenInMessage || (asksAboutReservation && !asksAboutSpecificDate ? activeToken : '');

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                },
                body: JSON.stringify({ message, token }),
            });
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Pesan belum bisa diproses.');
            }

            pending.textContent = result.reply;
        } catch (error) {
            pending.textContent = 'Maaf ya, pesanmu belum terkirim. Coba lagi sebentar, yuk.';
            console.error('Lifi chat request failed:', error);
        } finally {
            input.disabled = false;
            input.focus();
        }
    });

    try {
        if (!sessionStorage.getItem('lifi-greeted')) {
            sessionStorage.setItem('lifi-greeted', 'true');
            setOpen(true);
        }
    } catch (error) {
        setOpen(true);
    }
})();
