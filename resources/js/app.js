import './bootstrap';
import './echo';

// Notificaciones en tiempo real (Laravel Echo + Reverb)
document.addEventListener('DOMContentLoaded', () => {
    const meta = document.querySelector('meta[name="auth-user-id"]');
    const userId = meta ? meta.getAttribute('content') : null;
    if (!window.Echo || !userId) return;

    const channel = window.Echo.private('App.Models.User.' + userId);
    channel.notification((payload) => {
        const message = payload.message || 'Nueva notificación';
        const btn = document.querySelector('#notifications-toggle');
        if (btn) {
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
        if (window.Swal && window.Swal.fire) {
            window.Swal.fire({
                icon: 'info',
                title: 'Notificación',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
        }
    });
});
