import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const key = import.meta.env.VITE_REVERB_APP_KEY;
const wsHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
const wsPort = import.meta.env.VITE_REVERB_PORT || 8002;
const forceTLS = (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https';

// No conectar Echo si el host es solo accesible dentro de Docker (ej. "reverb")
// El navegador no puede resolver "reverb"; usar en producción VITE_REVERB_HOST=IP_PÚBLICA y rebuild
const hostReachableFromBrowser = typeof window !== 'undefined' && (
    wsHost === 'localhost' ||
    wsHost === '127.0.0.1' ||
    wsHost === window.location.hostname
);

if (key && hostReachableFromBrowser) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost,
        wsPort,
        wssPort: wsPort,
        forceTLS,
        enabled: true,
        disableStats: true,
    });
} else {
    window.Echo = null;
}
