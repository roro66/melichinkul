import './bootstrap';
import './echo';

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[ch]));
}

function showLicenseReminder(payload) {
    if (!payload || !payload.slot || !window.Swal) return;
    const storageKey = 'melichinkul-license-reminder-slot';
    if (localStorage.getItem(storageKey) === payload.slot) return;
    localStorage.setItem(storageKey, payload.slot);

    const items = (payload.items || []).map((item) => {
        const title = escapeHtml(item.title);
        const message = escapeHtml(item.message);
        const href = escapeHtml(item.url || payload.url || '/alertas');
        return `<li style="margin-top:8px"><a href="${href}"><strong>${title}</strong></a><br>${message}</li>`;
    }).join('');

    window.Swal.fire({
        icon: 'warning',
        title: payload.title || 'Licencias de conducir por vencer',
        html: `<p style="text-align:left">${escapeHtml(payload.message)}</p><ul style="text-align:left;margin-top:12px;padding-left:1.1rem">${items}</ul>`,
        confirmButtonText: 'Ver alertas',
        showCancelButton: true,
        cancelButtonText: 'Entendido',
    }).then((result) => {
        if (result.isConfirmed && payload.url) {
            window.location.href = payload.url;
        }
    });
}

function bumpNotificationBadge() {
    const btn = document.querySelector('#notifications-toggle');
    if (!btn) return;
    let badge = btn.querySelector('.absolute');
    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'absolute -top-0.5 -right-0.5 min-w-[1.25rem] h-5 px-1 inline-flex items-center justify-center rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
        btn.appendChild(badge);
    }
    const n = parseInt(badge.textContent, 10) || 0;
    badge.textContent = n + 1 > 99 ? '99+' : n + 1;
    badge.style.display = '';
}

// Notificaciones en tiempo real (Laravel Echo + Reverb)
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('meta[name="auth-is-administrator"]')) {
        fetch('/alertas/licencias-aviso', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
                if (data && data.show) showLicenseReminder(data);
            })
            .catch(() => {});
    }

    const meta = document.querySelector('meta[name="auth-user-id"]');
    const userId = meta ? meta.getAttribute('content') : null;
    if (!window.Echo || !userId) return;

    const channel = window.Echo.private('App.Models.User.' + userId);
    channel.notification((payload) => {
        const isLicense = payload.reminder_kind === 'license';
        const isCirculation = payload.reminder_kind === 'circulation';
        if (isLicense) {
            showLicenseReminder(payload);
        } else if (isCirculation && window.showCirculationReminder) {
            window.showCirculationReminder(payload);
        }
        bumpNotificationBadge();
        if (!isLicense && !isCirculation && window.Swal && window.Swal.fire) {
            window.Swal.fire({
                icon: 'info',
                title: 'Notificación',
                text: payload.message || 'Nueva notificación',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
        }
    });
});
