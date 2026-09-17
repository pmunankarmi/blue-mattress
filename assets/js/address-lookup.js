(function () {
  'use strict';

  const config = window.BlueAddressLookup || {};
  const isArabic = document.documentElement.lang.toLowerCase().startsWith('ar');
  const storageKey = 'blue-short-address';
  const saudiCenter = { lat: 23.8859, lng: 45.0792 };
  let mapsPromise;

  const text = (key, fallback) => config.strings?.[key] || fallback;

  const withTimeout = (promise, duration = 15000) => Promise.race([
    promise,
    new Promise((resolve, reject) => {
      window.setTimeout(() => reject(new Error('maps-timeout')), duration);
    })
  ]);

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

  const searchGoogleAddress = async (query, shortCode = '') => {
    await loadMaps();

    try {
      const { Place } = await window.google.maps.importLibrary('places');
      const response = await withTimeout(Place.searchByText({
        textQuery: query,
        fields: ['formattedAddress', 'addressComponents', 'location'],
        language: config.language || (isArabic ? 'ar' : 'en'),
        region: 'sa',
        maxResultCount: 1
      }));
      if (response.places?.length) return parseResult(response.places[0], shortCode);
    } catch (error) {
      // Some existing Google projects only have Geocoding enabled; use it as
      // a compatible fallback before showing an error to the customer.
    }

    const { Geocoder } = await window.google.maps.importLibrary('geocoding');
    const geocoder = new Geocoder();
    const response = await withTimeout(geocoder.geocode({
      address: `${query}, Saudi Arabia`,
      componentRestrictions: { country: 'SA' },
      region: 'SA'
    }));
    if (!response.results?.length) throw new Error('no-results');
    return parseResult(response.results[0], shortCode);
  };

  const searchAddress = (code) => searchGoogleAddress(code, code);

  const reverseGeocode = async (location) => {
    await loadMaps();
    const { Geocoder } = await window.google.maps.importLibrary('geocoding');
    const geocoder = new Geocoder();
    const response = await withTimeout(geocoder.geocode({ location, region: 'SA' }));
    if (!response.results?.length) throw new Error('no-results');
    return parseResult(response.results[0], '');
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

  const syncShortCode = (selector, code) => {
    const field = document.querySelector(selector);
    if (!field) return;
    const existing = field.value
      .replace(/\b[A-Z]{4}[0-9]{4}\b/gi, '')
      .replace(/\s*[·|,-]\s*$/, '')
      .trim();
    field.value = code ? (existing ? `${existing} · ${code}` : code) : existing;
    dispatchChange(field);
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
    syncShortCode(`#${prefix}_address_2`, address.code);
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

  const createMapPicker = async (canvas, status, scope, getShortCode, searchInput, searchButton) => {
    await loadMaps();
    const { Map } = await window.google.maps.importLibrary('maps');
    const map = new Map(canvas, {
      center: saudiCenter,
      zoom: 5,
      clickableIcons: false,
      fullscreenControl: true,
      mapTypeControl: false,
      streetViewControl: false
    });
    const marker = new window.google.maps.Marker({
      map,
      position: saudiCenter,
      visible: false
    });

    const showAddress = (address, zoom = 17) => {
      if (!Number.isFinite(address.lat) || !Number.isFinite(address.lng)) return;
      const position = { lat: address.lat, lng: address.lng };
      marker.setPosition(position);
      marker.setVisible(true);
      map.setCenter(position);
      map.setZoom(zoom);
    };

    const searchMap = async () => {
      const query = searchInput.value.trim();
      if (!query || searchButton.disabled) {
        if (!query) {
          status.className = 'blue-short-address__status is-error';
          status.textContent = text('mapSearchEmpty', 'Enter a place or address to search the map.');
          searchInput.focus();
        }
        return;
      }

      searchButton.disabled = true;
      canvas.classList.add('is-loading');
      status.className = 'blue-short-address__status';
      status.textContent = text('mapSearchLoading', 'Searching Google Maps…');
      try {
        const normalized = normalizeCode(query);
        const shortCode = /^[A-Z]{4}[0-9]{4}$/.test(normalized) ? normalized : '';
        const address = await searchGoogleAddress(query, shortCode);
        fillWooAddress(address, scope);
        showAddress(address);
        renderResult(status, address);
      } catch (error) {
        status.className = 'blue-short-address__status is-error';
        status.textContent = text('mapSearchError', 'No Google Maps result was found. Try a nearby landmark or a more complete address.');
      } finally {
        searchButton.disabled = false;
        canvas.classList.remove('is-loading');
      }
    };

    searchButton.addEventListener('click', searchMap);
    searchInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        searchMap();
      }
    });

    map.addListener('click', async (event) => {
      const location = event.latLng;
      marker.setPosition(location);
      marker.setVisible(true);
      status.className = 'blue-short-address__status';
      status.textContent = text('mapLoading', 'Getting the selected address…');
      canvas.classList.add('is-loading');
      try {
        const address = await reverseGeocode(location);
        const shortCode = normalizeCode(getShortCode?.() || '');
        if (/^[A-Z]{4}[0-9]{4}$/.test(shortCode)) address.code = shortCode;
        fillWooAddress(address, scope);
        showAddress(address, Math.max(map.getZoom() || 17, 16));
        renderResult(status, address);
      } catch (error) {
        status.className = 'blue-short-address__status is-error';
        status.textContent = text('mapError', 'We could not read that map location. Choose another point or enter the address manually.');
      } finally {
        canvas.classList.remove('is-loading');
      }
    });

    if (window.ResizeObserver) {
      let lastWidth = canvas.offsetWidth;
      new ResizeObserver(() => {
        if (!canvas.offsetWidth || canvas.offsetWidth === lastWidth) return;
        lastWidth = canvas.offsetWidth;
        window.google.maps.event.trigger(map, 'resize');
        if (marker.getVisible()) map.setCenter(marker.getPosition());
      }).observe(canvas);
    }

    return { showAddress };
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

    const mapHint = document.createElement('p');
    mapHint.className = 'blue-short-address__map-hint';
    mapHint.textContent = text('mapHint', 'Or choose your exact location on the map. The address fields will be filled automatically.');

    const mapSearch = document.createElement('div');
    mapSearch.className = 'blue-short-address__map-search';

    const mapSearchInput = document.createElement('input');
    mapSearchInput.className = 'input-text blue-short-address__map-search-input';
    mapSearchInput.type = 'search';
    mapSearchInput.autocomplete = 'off';
    mapSearchInput.placeholder = text('mapSearchPlaceholder', 'Search place or address');
    mapSearchInput.setAttribute('aria-label', text('mapSearchLabel', 'Search Google Maps'));

    const mapSearchButton = document.createElement('button');
    mapSearchButton.type = 'button';
    mapSearchButton.className = 'button blue-short-address__map-search-button';
    mapSearchButton.textContent = text('mapSearchButton', 'Search map');

    mapSearch.append(mapSearchInput, mapSearchButton);

    const mapCanvas = document.createElement('div');
    mapCanvas.className = 'blue-short-address__map';
    mapCanvas.setAttribute('role', 'application');
    mapCanvas.setAttribute('aria-label', text('mapLabel', 'Choose delivery address on map'));

    let mapControllerPromise;
    let restoredAddress;

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
        if (mapControllerPromise) {
          mapControllerPromise.then((controller) => controller?.showAddress(address)).catch(() => {});
        }
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
    wrapper.append(label, controls, hint, status, mapHint, mapSearch, mapCanvas);
    window.requestAnimationFrame(() => {
      mapControllerPromise = createMapPicker(
        mapCanvas,
        status,
        scope,
        () => input.value,
        mapSearchInput,
        mapSearchButton
      ).then((controller) => {
        if (restoredAddress) controller.showAddress(restoredAddress);
        return controller;
      }).catch(() => {
        mapCanvas.classList.add('is-unavailable');
        mapCanvas.textContent = text('mapUnavailable', 'The map is temporarily unavailable. You can still enter your address manually.');
        return null;
      });
    });
    wrapper.blueRestoreAddress = (address) => {
      restoredAddress = address;
      input.value = address.code || '';
      if (address.formattedAddress) mapSearchInput.value = address.formattedAddress;
      fillWooAddress(address, scope);
      renderResult(status, address);
      if (mapControllerPromise) {
        mapControllerPromise.then((controller) => controller?.showAddress(address)).catch(() => {});
      }
    };
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
    if (!address1 || address1.dataset.blueShortAddressRestored === 'true') return;

    try {
      const address = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
      if (!address?.address1 && !address?.formattedAddress) return;
      ['billing', 'shipping'].forEach((scope) => {
        const field = document.getElementById(`${scope}_address_1`);
        if (field) field.dataset.blueShortAddressRestored = 'true';
        const lookup = document.querySelector(`[data-blue-short-address="${scope}"]`);
        if (lookup?.blueRestoreAddress) lookup.blueRestoreAddress(address);
        else fillWooAddress(address, scope);
      });
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
