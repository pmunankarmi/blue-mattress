(function ($) {
  'use strict';

  const checkoutSelector = '#paymob-elements';
  let retryTimer = 0;
  let refreshes = 0;
  let waitCycles = 0;
  const observedRoots = new WeakSet();

  const getTheme = () => {
    const dark = document.documentElement.dataset.theme === 'dark';

    return dark ? {
      mode: 'dark',
      container: '#111A29',
      field: '#172234',
      border: '#3D485A',
      label: '#A8B5CA',
      text: '#EEF3FB',
      placeholder: '#8E9BB0',
      disabled: '#536075',
      error: '#E16363'
    } : {
      mode: 'light',
      container: '#FFFFFF',
      field: '#F8FAFD',
      border: '#CBD3DF',
      label: '#5F6F86',
      text: '#0E1F38',
      placeholder: '#7A879A',
      disabled: '#AEB8C7',
      error: '#C74949'
    };
  };

  const applyTheme = () => {
    if (!window.pxl_object || typeof window.pxl_object !== 'object') return;

    const theme = getTheme();

    window.pxl_object.customize = Object.assign({}, window.pxl_object.customize || {}, {
      font_family: 'Poppins',
      font_size_label: '12',
      // A 16px input prevents iOS Safari from zooming the checkout viewport.
      font_size_input_fields: window.matchMedia('(max-width: 700px)').matches ? '16' : '15',
      font_size_payment_button: '15',
      font_weight_label: '600',
      font_weight_input_fields: '400',
      font_weight_payment_button: '700',
      color_container: theme.container,
      color_border_input_fields: theme.border,
      color_border_payment_button: '#6686EC',
      radius_border: '10',
      color_disabled: theme.disabled,
      color_error: theme.error,
      color_primary: '#6686EC',
      color_input_fields: theme.field,
      text_color_for_label: theme.label,
      text_color_for_payment_button: '#07101F',
      text_color_for_input_fields: theme.text,
      color_for_text_placeholder: theme.placeholder,
      width_of_container: '100',
      // Paymob uses this setting as the actual field height (and derives the
      // two-row secure card iframe from it), not as CSS padding.
      vertical_padding: '52',
      vertical_spacing_between_components: '14',
      container_padding: '0'
    });
  };

  const applyShadowTheme = (root) => {
    const theme = getTheme();
    const rootContainer = Array.from(root.children).find((child) => child.tagName !== 'STYLE');

    if (rootContainer) {
      rootContainer.dataset.bluePaymobRoot = 'true';
      rootContainer.style.setProperty('background-color', theme.container, 'important');
      rootContainer.style.setProperty('color', theme.text, 'important');
      rootContainer.style.setProperty('color-scheme', theme.mode, 'important');
    }

    let themeStyle = root.querySelector('style[data-blue-paymob-theme]');
    if (!themeStyle) {
      themeStyle = document.createElement('style');
      themeStyle.dataset.bluePaymobTheme = 'true';
      root.appendChild(themeStyle);
    }

    const rules = `
      [data-blue-paymob-root="true"],
      [data-blue-paymob-card-information="true"] {
        background-color: ${theme.container} !important;
        color: ${theme.text} !important;
        color-scheme: ${theme.mode} !important;
      }
      [data-blue-paymob-root="true"] p,
      [data-blue-paymob-root="true"] label,
      [data-blue-paymob-card-information="true"] span {
        color: ${theme.label} !important;
      }
      [data-blue-paymob-root="true"] input#name {
        border-color: ${theme.border} !important;
        background-color: ${theme.field} !important;
        color: ${theme.text} !important;
        -webkit-text-fill-color: ${theme.text} !important;
        font-size: ${window.matchMedia('(max-width: 700px)').matches ? '16px' : '15px'} !important;
      }
      [data-blue-paymob-root="true"] input#name::placeholder {
        color: ${theme.placeholder} !important;
        opacity: 1 !important;
      }
    `;

    if (themeStyle.textContent !== rules) themeStyle.textContent = rules;
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
      applyShadowTheme(root);
      let singleCardSelectorHidden = false;
      Array.from(root.querySelectorAll('div')).forEach((row) => {
        const choices = Array.from(row.children).filter((choice) => {
          const label = (choice.textContent || '').trim();
          return choice.tagName === 'DIV' && label && label.length <= 40 && Boolean(choice.querySelector('img, svg'));
        });
        const choiceLabels = choices.map((choice) => (choice.textContent || '').trim().replace(/\s+/g, ' ').toLocaleLowerCase());
        const uniqueLabels = new Set(choiceLabels);

        if (choices.length && 1 === uniqueLabels.size && /^(card|بطاقة|البطاقة)$/i.test(choiceLabels[0])) {
          row.dataset.bluePaymobSingleCard = 'true';
          row.setAttribute('aria-hidden', 'true');
          row.style.setProperty('display', 'none', 'important');
          singleCardSelectorHidden = true;
          return;
        }

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

      if (singleCardSelectorHidden) {
        Array.from(root.querySelectorAll('div, p, span, label')).forEach((label) => {
          const text = (label.textContent || '').trim().replace(/\s+/g, ' ').toLocaleLowerCase();
          if ('payment method' === text || 'طريقة الدفع' === text) {
            label.setAttribute('aria-hidden', 'true');
            label.style.setProperty('display', 'none', 'important');
          }
        });

        const cardInformationLabel = Array.from(root.querySelectorAll('p')).find((label) =>
          /^(card information|معلومات البطاقة)$/i.test((label.textContent || '').trim())
        );
        const cardInformation = cardInformationLabel?.parentElement?.parentElement;
        if (cardInformation) {
          cardInformation.dataset.bluePaymobCardInformation = 'true';
          cardInformation.style.setProperty('box-sizing', 'border-box', 'important');
          cardInformation.style.setProperty('padding-inline', '20px', 'important');
        }
      }

      root.querySelectorAll('iframe').forEach((frame) => {
        const theme = getTheme();
        frame.style.setProperty('border', '0', 'important');
        frame.style.setProperty('border-right', `1px solid ${theme.border}`, 'important');
        frame.style.setProperty('border-radius', '10px 10px 0 0', 'important');
        frame.style.setProperty('box-sizing', 'border-box', 'important');
        frame.style.setProperty('background-color', theme.field, 'important');
        frame.style.setProperty('clip-path', 'inset(0 round 10px 10px 0 0)', 'important');
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

  // Set the palette as soon as Paymob's localized settings are available so
  // the secure iframe is created with the correct theme on its first render.
  applyTheme();

  $(document.body).on('updated_checkout payment_method_selected', function () {
    refreshes = 0;
    waitCycles = 0;
    schedule(150);
  });

  new MutationObserver(function (mutations) {
    if (!mutations.some((mutation) => mutation.attributeName === 'data-theme')) return;
    applyTheme();
    dedupePaymentMethods(document.querySelector(checkoutSelector));
  }).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['data-theme']
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { schedule(100); });
  } else {
    schedule(100);
  }
}(window.jQuery));
