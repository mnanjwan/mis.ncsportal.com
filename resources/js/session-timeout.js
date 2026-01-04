/**
 * Session Timeout Handler
 * Automatically logs out users after 15 minutes of inactivity
 * Shows a warning 2 minutes before logout
 */

(function() {
    'use strict';

    // Configuration
    const SESSION_LIFETIME = 15 * 60 * 1000; // 15 minutes in milliseconds
    const WARNING_TIME = 13 * 60 * 1000; // 13 minutes (2 minutes before logout)
    const PING_INTERVAL = 5 * 60 * 1000; // Ping server every 5 minutes to keep session alive

    let inactivityTimer;
    let warningTimer;
    let pingInterval;
    let warningShown = false;
    let lastActivity = Date.now();

    // Events that indicate user activity
    const activityEvents = [
        'mousedown',
        'mousemove',
        'keypress',
        'scroll',
        'touchstart',
        'click',
        'keydown'
    ];

    /**
     * Reset the inactivity timer
     */
    function resetInactivityTimer() {
        lastActivity = Date.now();
        warningShown = false;

        // Clear existing timers
        clearTimeout(inactivityTimer);
        clearTimeout(warningTimer);

        // Set warning timer (at 13 minutes)
        warningTimer = setTimeout(showWarning, WARNING_TIME);

        // Set logout timer (at 15 minutes)
        inactivityTimer = setTimeout(logout, SESSION_LIFETIME);
    }

    /**
     * Show warning modal before logout
     */
    function showWarning() {
        if (warningShown) return;
        warningShown = true;

        // Calculate remaining time
        const remainingSeconds = Math.ceil((SESSION_LIFETIME - WARNING_TIME) / 1000);
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;

        // Check if SweetAlert2 is available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Session Timeout Warning',
                html: `You have been inactive for ${13} minutes.<br>You will be logged out in ${minutes}:${seconds.toString().padStart(2, '0')} if you don't take any action.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Stay Logged In',
                cancelButtonText: 'Logout Now',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                allowOutsideClick: false,
                allowEscapeKey: false,
                timer: remainingSeconds * 1000,
                timerProgressBar: true,
                didOpen: () => {
                    // Reset timer if user clicks "Stay Logged In"
                    Swal.getConfirmButton().addEventListener('click', () => {
                        resetInactivityTimer();
                        Swal.close();
                    });

                    // Logout if user clicks "Logout Now"
                    Swal.getCancelButton().addEventListener('click', () => {
                        logout();
                    });
                },
                willClose: () => {
                    // If modal closes without action, continue with logout
                    if (warningShown) {
                        setTimeout(logout, (SESSION_LIFETIME - WARNING_TIME));
                    }
                }
            });
        } else {
            // Fallback if SweetAlert2 is not available
            const message = `You have been inactive for 13 minutes. You will be logged out in ${minutes}:${seconds.toString().padStart(2, '0')} if you don't take any action.`;
            if (confirm(message + '\n\nClick OK to stay logged in, or Cancel to logout now.')) {
                resetInactivityTimer();
            } else {
                logout();
            }
        }
    }

    /**
     * Logout the user
     */
    function logout() {
        // Clear all timers
        clearTimeout(inactivityTimer);
        clearTimeout(warningTimer);
        clearInterval(pingInterval);

        // Redirect to login page
        window.location.href = '/login?timeout=1';
    }

    /**
     * Ping server to keep session alive
     */
    function pingServer() {
        // Only ping if user has been active recently (within last 5 minutes)
        const timeSinceLastActivity = Date.now() - lastActivity;
        if (timeSinceLastActivity < PING_INTERVAL) {
            fetch('/api/session/ping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            }).catch(error => {
                console.error('Session ping failed:', error);
            });
        }
    }

    /**
     * Initialize the session timeout handler
     */
    function init() {
        // Only initialize if user is authenticated
        if (!document.querySelector('meta[name="csrf-token"]')) {
            return;
        }

        // Set initial timers
        resetInactivityTimer();

        // Listen for user activity
        activityEvents.forEach(event => {
            document.addEventListener(event, resetInactivityTimer, true);
        });

        // Also listen for visibility change (when user switches tabs)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                resetInactivityTimer();
            }
        });

        // Set up periodic ping to keep session alive
        pingInterval = setInterval(pingServer, PING_INTERVAL);

        // Ping immediately
        pingServer();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

