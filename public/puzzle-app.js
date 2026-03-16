(() => {
    const root = document.querySelector('[data-enhanced-puzzle]');

    if (!(root instanceof HTMLElement)) {
        return;
    }

    let busy = false;

    document.addEventListener(
        'submit',
        async (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement) || !root.contains(form)) {
                return;
            }

            if (form.dataset.enhancedBypass === 'true') {
                delete form.dataset.enhancedBypass;
                return;
            }

            event.preventDefault();

            if (busy) {
                return;
            }

            busy = true;
            root.classList.remove('is-fresh');
            root.classList.add('is-updating');

            const submitter = event.submitter instanceof HTMLButtonElement ? event.submitter : null;

            if (submitter) {
                submitter.disabled = true;
            }

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }

                root.innerHTML = await response.text();
                root.classList.remove('is-updating');
                root.classList.add('is-fresh');

                window.setTimeout(() => {
                    root.classList.remove('is-fresh');
                }, 240);
            } catch (error) {
                console.error(error);
                form.dataset.enhancedBypass = 'true';
                form.submit();
            } finally {
                busy = false;

                if (submitter) {
                    submitter.disabled = false;
                }
            }
        },
        true,
    );
})();
