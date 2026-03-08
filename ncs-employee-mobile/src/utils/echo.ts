import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';
import { API_BASE_URL } from './constants';

// For Laravel Echo to find Pusher in React Native environment
(window as any).Pusher = Pusher;

let echoInstance: Echo<any> | null = null;

const getHost = () => {
    try {
        // API_BASE_URL is like 'http://127.0.0.1:8000/api/v1'
        const cleanUrl = API_BASE_URL.replace('/api/v1', '');
        const url = new URL(cleanUrl);
        return url.hostname;
    } catch (e) {
        return '127.0.0.1';
    }
};

/**
 * Initialize or get the existing Echo instance.
 * Reverb uses the Pusher protocol, so we use 'reverb' as the broadcaster.
 */
export const getEcho = (token?: string): Echo<any> | null => {
    if (echoInstance) return echoInstance;
    if (!token) return null;

    echoInstance = new Echo({
        broadcaster: 'reverb',
        key: 'ncsportalreverbkey', // Matches .env REVERB_APP_KEY
        wsHost: getHost(),
        wsPort: 8080,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: `${API_BASE_URL}/broadcasting/auth`,
        auth: {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json',
            },
        },
    });

    return echoInstance;
};

/**
 * Cleanup connection on logout or app close
 */
export const disconnectEcho = () => {
    if (echoInstance) {
        echoInstance.disconnect();
        echoInstance = null;
    }
};
