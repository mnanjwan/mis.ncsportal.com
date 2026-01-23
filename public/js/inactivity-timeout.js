/**
 * Inactivity Timeout Handler
 * Logs out user after period of inactivity
 * Shows alert modal when timeout is reached
 */

(function() {
    'use strict';

    // Configuration
    // ============================================
    // PRODUCTION: 15 minutes (900000ms)
    // ============================================
    const INACTIVITY_TIMEOUT = 1 * 60 * 1000; // 15 minutes
    const WARNING_TIME = 0; // Show warning immediately when timeout is reached
    
    let inactivityTimer;
    let warningShown = false;
    let originalFavicon;
    let faviconLink;
    let animatedFaviconInterval;

    // Initialize
    function init() {
        try {
            // Get favicon link element
            faviconLink = document.querySelector("link[rel*='icon']") || document.createElement('link');
            if (!document.querySelector("link[rel*='icon']")) {
                faviconLink.type = 'image/svg+xml';
                faviconLink.rel = 'icon';
                document.getElementsByTagName('head')[0].appendChild(faviconLink);
            }
            originalFavicon = faviconLink.href;

            // Set animated favicon
            setAnimatedFavicon();

            // Reset timer on any user activity
            resetTimer();

            // Track user activity
            const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
            events.forEach(event => {
                document.addEventListener(event, resetTimer, true);
            });

            // Handle visibility change (tab switching)
            document.addEventListener('visibilitychange', handleVisibilityChange);
            
            // Debug log (remove in production if needed)
            console.log('✅ Inactivity timeout initialized. Timeout:', INACTIVITY_TIMEOUT / 1000, 'seconds');
            
            // Set a global flag to verify script loaded
            window.inactivityTimeoutLoaded = true;
        } catch (error) {
            console.error('Error initializing inactivity timeout:', error);
        }
    }

    // Reset the inactivity timer
    function resetTimer() {
        // Clear existing timer
        clearTimeout(inactivityTimer);

        // If warning was shown, hide it and reset
        if (warningShown) {
            hideWarning();
        }

        // Set new timer
        inactivityTimer = setTimeout(() => {
            showWarning();
        }, INACTIVITY_TIMEOUT);
    }

    // Show warning modal
    function showWarning() {
        if (warningShown) return;
        warningShown = true;

        // Create modal overlay
        const modal = document.createElement('div');
        modal.id = 'inactivity-warning-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-in;
        `;

        modal.innerHTML = `
            <div style="
                background: white;
                border-radius: 12px;
                padding: 2rem;
                max-width: 500px;
                width: 90%;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease-out;
            ">
                <div style="
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 1.5rem;
                    background: #fee2e2;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    animation: pulse 2s ease-in-out infinite;
                ">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 style="
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #1f2937;
                    margin-bottom: 1rem;
                ">Session Timeout Warning</h2>
                <p style="
                    color: #6b7280;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                ">Your session has been inactive for too long. Click anywhere to continue, or you will be automatically logged out.</p>
                <div style="
                    background: #fef3c7;
                    border: 1px solid #fbbf24;
                    border-radius: 8px;
                    padding: 1rem;
                    margin-bottom: 1.5rem;
                ">
                    <p style="
                        color: #92400e;
                        font-size: 0.875rem;
                        margin: 0;
                    ">⚠️ You will be logged out automatically if you don't interact with the page.</p>
                </div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from {
                    transform: translateY(20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(modal);

        // Logout on any click after warning is shown
        const handleClick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            logout();
        };

        // Add click listener to modal
        modal.addEventListener('click', handleClick);
        
        // Also listen for any other interaction
        document.addEventListener('click', handleClick, true);
        document.addEventListener('keydown', handleClick, true);
    }

    // Hide warning (if user becomes active before timeout)
    function hideWarning() {
        const modal = document.getElementById('inactivity-warning-modal');
        if (modal) {
            modal.remove();
        }
        warningShown = false;
    }

    // Handle tab visibility
    function handleVisibilityChange() {
        if (document.hidden) {
            // Tab is hidden, pause timer (optional - you might want to keep it running)
            // For now, we'll keep it running
        } else {
            // Tab is visible again, reset timer
            resetTimer();
        }
    }

    // Set animated favicon
    function setAnimatedFavicon() {
        try {
            // Use absolute path from root to handle subdirectories
            const basePath = window.location.pathname.split('/').slice(0, -1).join('/') || '';
            const animatedFaviconPath = basePath + '/favicon-animated.svg';
            faviconLink.href = animatedFaviconPath;
        } catch (error) {
            console.error('Error setting animated favicon:', error);
        }
    }

    // Logout function
    function logout() {
        try {
            // Create logout form
            const form = document.createElement('form');
            form.method = 'POST';
            
            // Get base URL from current location to handle subdirectories
            const baseUrl = window.location.origin;
            const logoutPath = baseUrl + '/logout';
            form.action = logoutPath;
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content') || csrfToken.content;
                form.appendChild(csrfInput);
            } else {
                console.error('CSRF token not found');
            }
            
            document.body.appendChild(form);
            form.submit();
        } catch (error) {
            console.error('Error during logout:', error);
            // Fallback: redirect to login
            window.location.href = '/login';
        }
    }

    // Initialize when DOM is ready
    try {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    } catch (error) {
        console.error('Inactivity timeout initialization error:', error);
    }
})();
