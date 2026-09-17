(function () {
  'use strict';

  const config = window.BlueAddressLookup || {};
  const isArabic = document.documentElement.lang.toLowerCase().startsWith('ar');
  const storageKey = 'blue-short-address';
  let mapsPromise;

  const text = (key, fallback) => config.strings?.[key] || fallback;

  const loadMaps = () => {
    if (window.google?.maps?.importLibrary) return Promise.resolve(window.google.maps);
    if (mapsPromise) return mapsPromise;

    mapsPromise = new Promise((resolve, reject) => {
      const callback = `blueGoogleMapsReady${Date.now()}`;
      const script = document.createElement('script');
      const params = new URLSearchParams({
        key: config.apiKey || '',
        libraries: 'places',
        language: config.language || (isArabic ? 'ar' : 'en'),
        region: 'SA',
        v: 'weekly',
        loading: 'async',
        callback
      });

      window[callback] = () => {
        delete window[callback];
        resolve(window.google.maps);
      };
      script.src = `https://maps.googleapis.com/maps/api/js?${params.toString()}`;
      script.async = true;
      script.onerror = () => {
        delete window[callback];
        mapsPromise = null;
        reject(new Error('maps-load-failed'));
      };
      document.head.appendChild(script);
    });

    return mapsPromise;
  };

  const normalizeCode = (value) => value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);

  const componentValue = (components, type, short = false) => {
    const component = (components || []).find((item) => (item.types || []).includes(type));
    if (!component) return '';
    return short
      ? (component.shortText || component.short_name || component.longText || component.long_name || '')
      : (component.longText || component.long_name || component.shortText || component.short_name || '');
  };

  const parseResult = (result, code) => {
    const components = result.addressComponents || result.address_components || [];
    const streetParts = [
      componentValue(components, 'street_number'),
      componentValue(components, 'route'),
      componentValue(components, 'neighborhood'),
      componentValue(components, 'sublocality_level_1')
    ].filter(Boolean);
    const street = [...new Set(streetParts)].join('، ');
    const location = result.location || result.geometry?.location;
    const lat = typeof location?.lat === 'function' ? location.lat() : location?.lat;
    const lng = typeof location?.lng === 'function' ? location.lng() : location?.lng;

    return {
      code,
      formattedAddress: result.formattedAddress || result.formatted_address || street,
      address1: street || result.formattedAddress || result.formatted_address || code,
      city: componentValue(components, 'locality')
        || componentValue(components, 'postal_town')
        || componentValue(components, 'administrative_area_level_2')
        || componentValue(components, 'sublocality_level_1'),
      state: componentValue(components, 'administrative_area_level_1'),
      postcode: componentValue(components, 'postal_code'),
      country: componentValue(components, 'country', true) || 'SA',
      lat,
      lng
    };
  };

  const searchAddress = async (code) => {
    await loadMaps();

    try {
      const { Place } = await window.google.maps.importLibrary('places');
      const response = await Place.searchByText({
        textQuery: `${code}, Saudi Arabia`,
        fields: ['formattedAddress', 'addressComponents', 'location'],
        language: config.language || (isArabic ? 'ar' : 'en'),
        region: 'sa',
        maxResultCount: 1
      });
      if (response.places?.length) return parseResult(response.places[0], code);
    } catch (error) {
      // Some existing Google projects only have Geocoding enabled; use it as
      // a compatible fallback before showing an error to the customer.
    }

    const { Geocoder } = await window.google.maps.importLibrary('geocoding');
    const geocoder = new Geocoder();
    const response = await geocoder.geocode({
      address: `${code}, Saudi Arabia`,
      componentRestrictions: { country: 'SA' },
      region: 'SA'
    });
    if (!response.results?.length) throw new Error('no-results');
    return parseResult(response.results[0], code);
  };

  const dispatchChange = (field) => {
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
  };

  const setField = (selector, value) => {
    const field = document.querySelector(selector);
    if (!field || !value) return;

    if (field.tagName === 'SELECT') {
      const normalized = value.toLowerCase();
      const matchingOption = Array.from(field.options).find((option) =>
        option.value.toLowerCase() === normalized || option.textContent.trim().toLowerCase() === normalized
      );
      if (matchingOption) field.value = matchingOption.value;
    } else {
      field.value = value;
    }
    dispatchChange(field);
  };

  const appendShortCode = (selector, code) => {
    const field = document.querySelector(selector);
    if (!field || !code) return;
    const existing = field.value.trim();
    if (!existing.toUpperCase().includes(code)) {
      field.value = existing ? `${existing} · ${code}` : code;
      dispatchChange(field);
    }
  };

  const fillWooAddress = (address, scope) => {
    if (scope === 'cart') {
      setField('#calc_shipping_country', address.country || 'SA');
      setField('#calc_shipping_state', address.state);
      setField('#calc_shipping_city', address.city);
      setField('#calc_shipping_postcode', address.postcode);
      try { sessionStorage.setItem(storageKey, JSON.stringify(address)); } catch (error) {}
      return;
    }

    const prefix = scope === 'shipping' ? 'shipping' : 'billing';
    setField(`#${prefix}_country`, address.country || 'SA');
    setField(`#${prefix}_address_1`, address.address1);
    appendShortCode(`#${prefix}_address_2`, address.code);
    setField(`#${prefix}_city`, address.city);
    setField(`#${prefix}_state`, address.state);
    setField(`#${prefix}_postcode`, address.postcode);
  };

  const mapUrl = (address) => {
    const query = Number.isFinite(address.lat) && Number.isFinite(address.lng)
      ? `${address.lat},${address.lng}`
      : address.formattedAddress;
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
  };

  const renderResult = (status, address) => {
    status.replaceChildren();
    status.className = 'blue-short-address__status is-success';
    const link = document.createElement('a');
    link.href = mapUrl(address);
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    link.textContent = address.formattedAddress || address.address1;
    status.appendChild(link);
  };

  const createLookup = (scope) => {
    const wrapper = document.createElement('div');
    const id = `blue-short-address-${scope}`;
    wrapper.className = `form-row form-row-wide blue-short-address blue-short-address--${scope}`;
    wrapper.dataset.blueShortAddress = scope;

    const label = document.createElement('label');
    label.htmlFor = id;
    label.textContent = text('label', 'Saudi Short Address');

    const controls = document.createElement('div');
    controls.className = 'blue-short-address__controls';

    const input = document.createElement('input');
    input.id = id;
    input.className = 'input-text blue-short-address__input';
    input.type = 'text';
    input.inputMode = 'text';
    input.autocomplete = 'off';
    // Allow pasted codes that contain spaces or a hyphen; the input handler
    // removes separators and keeps the canonical eight characters.
    input.maxLength = 12;
    input.placeholder = text('placeholder', 'JEZC7519');
    input.setAttribute('aria-describedby', `${id}-hint ${id}-status`);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'button blue-short-address__button';
    button.textContent = text('button', 'Find address');

    const hint = document.createElement('small');
    hint.id = `${id}-hint`;
    hint.className = 'blue-short-address__hint';
    hint.textContent = text('hint', 'Enter 4 letters and 4 numbers from your Saudi National Address.');

    const status = document.createElement('div');
    status.id = `${id}-status`;
    status.className = 'blue-short-address__status';
    status.setAttribute('aria-live', 'polite');

    let lookupTimer = 0;

    const lookup = async () => {
      window.clearTimeout(lookupTimer);
      if (button.disabled) return;
      const code = normalizeCode(input.value);
      input.value = code;
      if (!/^[A-Z]{4}[0-9]{4}$/.test(code)) {
        status.className = 'blue-short-address__status is-error';
        status.textContent = text('invalid', 'Enter a valid Short Address: 4 letters followed by 4 numbers.');
        input.focus();
        return;
      }

      button.disabled = true;
      wrapper.classList.add('is-loading');
      status.className = 'blue-short-address__status';
      status.textContent = text('loading', 'Finding your address…');
      try {
        const address = await searchAddress(code);
        fillWooAddress(address, scope);
        renderResult(status, address);
      } catch (error) {
        status.className = 'blue-short-address__status is-error';
        status.textContent = text('notFound', 'We could not find that Short Address. Check the code and try again.');
      } finally {
        button.disabled = false;
        wrapper.classList.remove('is-loading');
      }
    };

    button.addEventListener('click', lookup);
    input.addEventListener('input', () => {
      input.value = normalizeCode(input.value);
      window.clearTimeout(lookupTimer);
      if (/^[A-Z]{4}[0-9]{4}$/.test(input.value)) lookupTimer = window.setTimeout(lookup, 450);
    });
    input.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        lookup();
      }
    });

    controls.append(input, button);
    wrapper.append(label, controls, hint, status);
    return wrapper;
  };

  const addCheckoutLookup = (scope) => {
    const target = document.getElementById(`${scope}_address_1_field`);
    if (!target || document.querySelector(`[data-blue-short-address="${scope}"]`)) return;
    target.before(createLookup(scope));
  };

  const addCartLookup = () => {
    const form = document.querySelector('.woocommerce-shipping-calculator .shipping-calculator-form');
    if (!form || form.querySelector('[data-blue-short-address="cart"]')) return;
    form.prepend(createLookup('cart'));
  };

  const restoreCartAddress = () => {
    if (!document.body.classList.contains('woocommerce-checkout')) return;
    const address1 = document.getElementById('billing_address_1');
    if (!address1 || address1.value.trim() || address1.dataset.blueShortAddressRestored === 'true') return;

    try {
      const address = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
      if (!address?.code) return;
      address1.dataset.blueShortAddressRestored = 'true';
      const input = document.getElementById('blue-short-address-billing');
      if (input) input.value = address.code;
      fillWooAddress(address, 'billing');
      const status = document.getElementById('blue-short-address-billing-status');
      if (status) renderResult(status, address);
    } catch (error) {}
  };

  const init = () => {
    if (document.body.classList.contains('woocommerce-cart')) addCartLookup();
    if (document.body.classList.contains('woocommerce-checkout')) {
      addCheckoutLookup('billing');
      addCheckoutLookup('shipping');
      restoreCartAddress();
    }
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  if (window.jQuery) {
    window.jQuery(document.body).on('updated_checkout updated_wc_div', init);
  }
}());
