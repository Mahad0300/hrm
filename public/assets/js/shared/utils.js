/**
 * Shared JS Utilities
 */
(function() {
    'use strict';

    // Intercept window.fetch to automatically append X-CSRF-Token header
    const originalFetch = window.fetch;
    window.fetch = function(input, init) {
        init = init || {};
        const method = (init.method || 'GET').toUpperCase();
        
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            init.headers = init.headers || {};
            const token = window.HRM ? window.HRM.csrfToken : '';
            if (token) {
                if (init.headers instanceof Headers) {
                    init.headers.set('X-CSRF-Token', token);
                } else if (Array.isArray(init.headers)) {
                    const hasToken = init.headers.some(h => h[0].toLowerCase() === 'x-csrf-token');
                    if (!hasToken) {
                        init.headers.push(['X-CSRF-Token', token]);
                    }
                } else {
                    init.headers['X-CSRF-Token'] = token;
                }
            }
        }
        return originalFetch(input, init);
    };

    // Setup jQuery AJAX to automatically include X-CSRF-Token header
    const setupJQueryCSRF = () => {
        if (window.jQuery) {
            window.jQuery.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    const method = (settings.type || 'GET').toUpperCase();
                    if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
                        const token = window.HRM ? window.HRM.csrfToken : '';
                        if (token) {
                            xhr.setRequestHeader('X-CSRF-Token', token);
                        }
                    }
                }
            });
        }
    };
    setupJQueryCSRF();
    document.addEventListener("DOMContentLoaded", setupJQueryCSRF);

    window.HRM_UTILS = {
        escapeHtml(text) {
            if (text == null) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        },

        timeAgo(dateVal) {
            if (!dateVal) return '—';
            const date = new Date(dateVal);
            if (isNaN(date.getTime())) return dateVal;
            
            const seconds = Math.floor((new Date() - date) / 1000);
            if (seconds < 0) return 'just now';
            
            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60,
                second: 1
            };
            
            for (const [unit, value] of Object.entries(intervals)) {
                const count = Math.floor(seconds / value);
                if (count >= 1) {
                    return count === 1 ? `1 ${unit} ago` : `${count} ${unit}s ago`;
                }
            }
            return 'just now';
        }
    };
})();
