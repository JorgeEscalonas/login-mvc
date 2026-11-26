(() => {
    const renderPage = (html) => {
        const page = new DOMParser().parseFromString(html, 'text/html');
        const main = page.querySelector('main');

        if (!main) {
            throw new Error('El servidor devolvió una respuesta inesperada.');
        }

        document.title = page.title;
        document.body.className = page.body.className;
        document.body.innerHTML = page.body.innerHTML;
        document.querySelector('main')?.focus();
    };

    const showError = (message) => {
        const main = document.querySelector('main');
        if (!main) return;

        let alert = main.querySelector('.alert');
        if (!alert) {
            alert = document.createElement('p');
            alert.className = 'alert';
            main.prepend(alert);
        }
        alert.setAttribute('role', 'alert');
        alert.textContent = message;
    };

    const loadPage = async (url, addHistory = false) => {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        renderPage(await response.text());

        if (addHistory) {
            history.pushState({}, '', response.url);
        }
    };

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('.menu-toggle');
        const close = event.target.closest('[data-sidebar-close]');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        if (!sidebar) return;
        if (!toggle && !close) return;

        const isOpen = toggle ? !sidebar.classList.contains('is-open') : false;
        sidebar.classList.toggle('is-open', isOpen);
        overlay?.classList.toggle('is-visible', isOpen);
        document.querySelector('.menu-toggle')?.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0) return;
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        if (link.target || link.hasAttribute('download')) return;

        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;

        event.preventDefault();
        document.querySelector('.sidebar')?.classList.remove('is-open');
        document.querySelector('.sidebar-overlay')?.classList.remove('is-visible');
        document.querySelector('.menu-toggle')?.setAttribute('aria-expanded', 'false');
        try {
            await loadPage(url.href, true);
        } catch {
            window.location.assign(url.href);
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post') return;
        if (new URL(form.action, window.location.href).origin !== window.location.origin) return;

        event.preventDefault();
        const button = event.submitter;
        const originalText = button?.textContent;
        if (button) {
            button.disabled = true;
            button.textContent = 'Enviando...';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            renderPage(await response.text());

            if (response.redirected) {
                history.pushState({}, '', response.url);
            }
        } catch {
            if (button) {
                button.disabled = false;
                button.textContent = originalText;
            }
            showError('No se pudo conectar con el servidor. Inténtalo de nuevo.');
        }
    });

    window.addEventListener('popstate', async () => {
        try {
            await loadPage(window.location.href);
        } catch {
            window.location.reload();
        }
    });
})();