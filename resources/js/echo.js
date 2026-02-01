import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const key = import.meta.env.VITE_REVERB_APP_KEY;
const wsHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
const wsPort = import.meta.env.VITE_REVERB_PORT || 8080;
const forceTLS = (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https';

if (key) {
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
