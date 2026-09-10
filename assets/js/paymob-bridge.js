(function ($) {
  'use strict';

  const checkoutSelector = '#paymob-elements';
  let retryTimer = 0;
  let refreshes = 0;
  let waitCycles = 0;
  const observedRoots = new WeakSet();

  const applyTheme = () => {
    if (!window.pxl_object || typeof window.pxl_object !== 'object') return;

    window.pxl_object.customize = Object.assign({}, window.pxl_object.customize || {}, {
      font_family: 'Poppins',
      font_size_label: '12',
      font_size_input_fields: '15',
      font_size_payment_button: '15',
      font_weight_label: '600',
      font_weight_input_fields: '400',
      font_weight_payment_button: '700',
      color_container: '#111A29',
      color_border_input_fields: '#3D485A',
      color_border_payment_button: '#6686EC',
      radius_border: '10',
      color_disabled: '#536075',
      color_error: '#E16363',
      color_primary: '#6686EC',
      color_input_fields: '#172234',
      text_color_for_label: '#A8B5CA',
      text_color_for_payment_button: '#07101F',
      text_color_for_input_fields: '#EEF3FB',
      color_for_text_placeholder: '#8E9BB0',
      width_of_container: '100',
      // Paymob uses this setting as the actual field height (and derives the
      // two-row secure card iframe from it), not as CSS padding.
      vertical_padding: '52',
      vertical_spacing_between_components: '14',
      container_padding: '0'
    });
  };

  const hasCardForm = (container) => {
    if (!container) return false;

    const interactive = 'iframe, input, button, select, [role="textbox"], [data-testid]';
    if (container.querySelector(interactive) || container.shadowRoot?.querySelector(interactive)) {
      return true;
    }

    // Paymob mounts its card fields inside a shadow root on the first child,
    // rather than on #paymob-elements itself.
    return Array.from(container.children).some((child) =>
      Boolean(child.shadowRoot?.querySelector(interactive))
    );
  };

  const dedupePaymentMethods = (container) => {
    if (!container) return;

    const roots = Array.from(container.children)
      .map((child) => child.shadowRoot)
      .filter(Boolean);

    roots.forEach((root) => {
      Array.from(root.querySelectorAll('div')).forEach((row) => {
        const choices = Array.from(row.children).filter((choice) => {
          const label = (choice.textContent || '').trim();
          return choice.tagName === 'DIV' && label && label.length <= 40 && Boolean(choice.querySelector('img'));
        });

        if (choices.length < 2) return;

        const seen = new Set();
        choices.forEach((choice) => {
          const label = (choice.textContent || '').trim().replace(/\s+/g, ' ').toLocaleLowerCase();
          if (seen.has(label)) {
            if (choice.dataset.bluePaymobDuplicate !== 'true') {
              choice.dataset.bluePaymobDuplicate = 'true';
              choice.setAttribute('aria-hidden', 'true');
              choice.style.setProperty('display', 'none', 'important');
            }
            return;
          }

          seen.add(label);
        });
      });

      if (!observedRoots.has(root)) {
        observedRoots.add(root);
        new MutationObserver(() => dedupePaymentMethods(container)).observe(root, {
          childList: true,
          subtree: true
        });
      }
    });
  };

  const setState = (container) => {
    if (!container) return false;
    dedupePaymentMethods(container);
    const ready = hasCardForm(container);
    container.classList.toggle('blue-paymob-ready', ready);
    container.classList.toggle('blue-paymob-loading', !ready);
    return ready;
  };

  const schedule = (delay) => {
    window.clearTimeout(retryTimer);
    retryTimer = window.setTimeout(repairPaymob, delay);
  };

  const repairPaymob = () => {
    const container = document.querySelector(checkoutSelector);
    if (!container) return;

    applyTheme();
    if (setState(container)) {
      refreshes = 0;
      waitCycles = 0;
      return;
    }

    if (typeof window.updateCheckoutData !== 'function' || typeof window.Pixel !== 'function') {
      waitCycles += 1;
      if (waitCycles < 40) schedule(500);
      return;
    }

    if (refreshes < 3) {
      refreshes += 1;
      window.updateCheckoutData(true);
      schedule(2400);
    }
  };

  $(document.body).on('updated_checkout payment_method_selected', function () {
    refreshes = 0;
    waitCycles = 0;
    schedule(150);
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { schedule(100); });
  } else {
    schedule(100);
  }
}(window.jQuery));
