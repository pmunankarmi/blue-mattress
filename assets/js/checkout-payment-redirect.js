/* Redirect only the server-confirmed new-account order; never resubmit checkout. */
(function ($) {
    'use strict';
    $.ajaxPrefilter(function (options) {
        var endpoint;
        try {
            endpoint = new URL(options.url, window.location.href);
        } catch (error) {
            return;
        }
        if (endpoint.origin !== window.location.origin ||
            endpoint.searchParams.get('wc-ajax') !== 'checkout') {
            return;
        }
        var originalSuccess = options.success;
        options.success = function (response) {
            if (response && response.result === 'success' &&
                response.blue_account_payment_redirect === true &&
                typeof response.redirect === 'string') {
                var target;
                try {
                    target = new URL(response.redirect, window.location.href);
                } catch (error) {
                    target = null;
                }
                if (target && target.origin === window.location.origin &&
                    /^https?:$/.test(target.protocol)) {
                    // Skip Pixel's checkout-success handlers: some resubmit the form.
                    // The destination is Woo's keyed payment URL for the saved order.
                    window.location.assign(target.href);
                    return;
                }
            }
            var args = arguments;
            var context = this;
            var callbacks = Array.isArray(originalSuccess) ? originalSuccess : [originalSuccess];
            callbacks.forEach(function (callback) {
                if (typeof callback === 'function') {
                    callback.apply(context, args);
                }
            });
        };
    });
})(jQuery);
