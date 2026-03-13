(function () {
    var container = document.getElementById('toast-container');
    if (!container) {
        return;
    }

    function iconFor(type) {
        if (type === 'success') {
            return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
        if (type === 'error') {
            return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8v4m0 4h.01M10.29 3.86l-8 14A1 1 0 0 0 3.15 19h17.7a1 1 0 0 0 .86-1.5l-8-14a1 1 0 0 0-1.72 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
        return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 16v-4m0-4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    }

    function removeToast(toast) {
        toast.classList.add('toast-exit');
        window.setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 200);
    }

    function showToast(type, message, duration) {
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;

        var icon = document.createElement('div');
        icon.className = 'toast-icon';
        icon.innerHTML = iconFor(type);

        var content = document.createElement('div');
        content.className = 'toast-content';
        content.textContent = message;

        var close = document.createElement('button');
        close.className = 'toast-close';
        close.type = 'button';
        close.setAttribute('aria-label', 'Close notification');
        close.innerHTML = '&times;';
        close.addEventListener('click', function () {
            removeToast(toast);
        });

        toast.appendChild(icon);
        toast.appendChild(content);
        toast.appendChild(close);
        container.appendChild(toast);

        window.setTimeout(function () {
            removeToast(toast);
        }, duration || 3500);
    }

    var payload = [];
    try {
        payload = JSON.parse(container.getAttribute('data-toasts') || '[]');
    } catch (error) {
        payload = [];
    }

    payload.forEach(function (item) {
        var type = item.type === 'success' ? 'success' : 'error';
        if (typeof item.message === 'string' && item.message.trim() !== '') {
            showToast(type, item.message, 3500);
        }
    });

    window.DocTrackToast = {
        show: showToast
    };
})();

