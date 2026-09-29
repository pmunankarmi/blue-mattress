// Isolated DOM and Maps mocks: never request or transmit a real location.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../assets/js/address-lookup.js'), 'utf8')
  .replace('  if (document.readyState', '  window.testCreateLookup = createLookup; window.testResetShipping = resetShippingAddress; window.testRestoreCart = restoreCartAddress;\n  if (document.readyState');

class Element {
  constructor(tag = 'div') {
    this.tagName = tag.toUpperCase();
    this.children = [];
    this.dataset = {};
    this.listeners = {};
    this.attributes = {};
    this.value = '';
    this.isConnected = true;
    this.classList = { add() {}, remove() {}, contains() { return false; } };
  }
  append(...nodes) { this.children.push(...nodes); }
  appendChild(node) { this.append(node); }
  replaceChildren(...nodes) { this.children = nodes; }
  setAttribute(key, value) { this.attributes[key] = value; }
  removeAttribute(key) { delete this.attributes[key]; }
  addEventListener(type, handler) { this.listeners[type] = handler; }
  dispatchEvent() {}
}

function fixture({ scope = 'billing', error, fallbackWorks = false, country = 'SA', secure = true, supported = true, geocodeError = false, arabic = false } = {}) {
  let requests = 0;
  let geocodes = 0;
  const fields = {};
  for (const prefix of ['billing', 'shipping', 'calc_shipping']) {
    for (const key of ['country', 'state', 'city', 'postcode', 'address_1', 'address_2']) {
      fields['#' + prefix + '_' + key] = new Element('input');
    }
    const countrySelect = fields['#' + prefix + '_country'];
    countrySelect.tagName = 'SELECT';
    countrySelect.value = 'SA';
    countrySelect.options = [
      { value: 'SA', textContent: 'Saudi Arabia' },
      { value: 'NP', textContent: 'Nepal' }
    ];
  }
  const storage = new Map();
  const component = (type, value) => ({ types: [type], long_name: value, short_name: value });
  const window = {
    isSecureContext: secure, setTimeout, clearTimeout, requestAnimationFrame() {},
    BlueAddressLookup: { strings: arabic ? { locationButton: 'استخدام موقعي الحالي' } : {} },
    google: { maps: { importLibrary: async () => ({
      Geocoder: class {
        async geocode({ location }) {
          geocodes++;
          assert.equal(location.lat, 24.7);
          if (geocodeError) throw Error('unavailable');
          return { results: [{
            formatted_address: '123 Test Road, Riyadh',
            address_components: [
              component('country', country), component('locality', country === 'NP' ? 'Kathmandu' : 'Riyadh'),
              component('route', 'Test Road'), component('street_number', '123'),
              component('postal_code', '12345')
            ], geometry: { location }
          }] };
        }
      }
    }) } }
  };
  const navigator = supported ? { geolocation: {
    getCurrentPosition(resolve, reject, options) {
      requests++;
      assert.equal(options.timeout, options.enableHighAccuracy ? 30000 : 15000);
      assert.equal(options.maximumAge, options.enableHighAccuracy ? 0 : 60000);
      assert.equal(options.enableHighAccuracy, requests > 1);
      if (error && !(fallbackWorks && options.enableHighAccuracy)) reject({ code: error });
      else resolve({ coords: { latitude: 24.7, longitude: 46.7 } });
    }
  } } : {};
  vm.runInNewContext(source, {
    window, navigator, Event: class {},
    sessionStorage: { setItem: (k, v) => storage.set(k, v), getItem: (k) => storage.get(k) },
    document: {
      documentElement: { lang: arabic ? 'ar' : 'en' }, readyState: 'loading',
      createElement: (tag) => new Element(tag), addEventListener() {},
      getElementById: (id) => fields['#' + id] || null,
      body: { classList: { contains: (name) => name === 'woocommerce-checkout' } },
      querySelector: (selector) => fields[selector] || null
    }
  });
  const wrapper = window.testCreateLookup(scope);
  const nodes = (node) => [node, ...node.children.flatMap(nodes)];
  const button = nodes(wrapper).find((node) => node.className?.includes('location-button'));
  const status = nodes(wrapper).find((node) => node.id === 'blue-short-address-' + scope + '-status');
  return { window, wrapper, button, status, fields, storage,
    counts: () => ({ requests, geocodes }), click: () => button.listeners.click() };
}

test('location is opt-in and fills only the chosen checkout address', async () => {
  const f = fixture();
  assert.deepEqual(f.counts(), { requests: 0, geocodes: 0 });
  await f.click();
  assert.equal(f.fields['#billing_city'].value, 'Riyadh');
  assert.equal(f.fields['#billing_postcode'].value, '12345');
  assert.equal(f.fields['#shipping_city'].value, '');
  assert.equal(f.button.disabled, false);
  assert.equal(f.button.attributes['aria-busy'], undefined);
});

for (const scope of ['billing', 'shipping', 'cart']) {
  test('Nepal location fills allowed country and city for ' + scope, async () => {
    const f = fixture({ scope, country: 'NP' });
    await f.click();
    const prefix = scope === 'cart' ? 'calc_shipping' : scope;
    assert.equal(f.fields['#' + prefix + '_country'].value, 'NP');
    assert.equal(f.fields['#' + prefix + '_city'].value, 'Kathmandu');
    assert.equal(f.status.className, 'blue-short-address__status is-success');
  });
}

test('retries unavailable standard location with a longer high-accuracy request', async () => {
  const f = fixture({ error: 2, fallbackWorks: true });
  await f.click();
  assert.equal(f.counts().requests, 2);
  assert.equal(f.fields['#billing_city'].value, 'Riyadh');
});

test('denied permission is never retried', async () => {
  const f = fixture({ error: 1 });
  await f.click();
  assert.equal(f.counts().requests, 1);
});

test('cart fills shipping calculator and retains address for checkout', async () => {
  const f = fixture({ scope: 'cart' });
  await f.click();
  assert.equal(f.fields['#calc_shipping_city'].value, 'Riyadh');
  assert.equal(JSON.parse(f.storage.get('blue-short-address')).postcode, '12345');
});

test('shipping lookup does not replace billing fields and supports localized label', async () => {
  const f = fixture({ scope: 'shipping', arabic: true });
  assert.equal(f.button.textContent, 'استخدام موقعي الحالي');
  await f.click();
  assert.equal(f.fields['#shipping_city'].value, 'Riyadh');
  assert.equal(f.fields['#billing_city'].value, '');
});

test('different shipping address clears destination, but preserves billing and country', () => {
  const f = fixture();
  f.fields['#shipping_city'].value = 'Old city';
  f.fields['#shipping_address_1'].value = 'Old street';
  f.fields['#shipping_country'].value = 'SA';
  f.fields['#billing_city'].value = 'Billing city';
  f.window.testResetShipping();
  assert.equal(f.fields['#shipping_city'].value, '');
  assert.equal(f.fields['#shipping_address_1'].value, '');
  assert.equal(f.fields['#shipping_country'].value, 'SA');
  assert.equal(f.fields['#billing_city'].value, 'Billing city');
});

test('cart restoration no longer copies billing into the separate shipping address', () => {
  const f = fixture();
  f.storage.set('blue-short-address', JSON.stringify({ address1: 'Cart street', city: 'Riyadh', country: 'SA' }));
  f.window.testRestoreCart();
  assert.equal(f.fields['#billing_city'].value, 'Riyadh');
  assert.equal(f.fields['#shipping_city'].value, '');
});

for (const [name, options, message] of [
  ['denied', { error: 1 }, /permission was denied/],
  ['unavailable', { error: 2 }, /browser could not provide/],
  ['timeout', { error: 3 }, /timed out/],
  ['insecure', { secure: false }, /HTTPS/],
  ['unsupported', { supported: false }, /unavailable/],
  ['country not enabled in WooCommerce', { country: 'US' }, /country is not available/],
  ['Maps failure', { geocodeError: true }, /Google Maps could not find/]
]) {
  test(name + ' leaves manual address unchanged', async () => {
    const f = fixture(options);
    f.fields['#billing_city'].value = 'Manual city';
    await f.click();
    assert.equal(f.fields['#billing_city'].value, 'Manual city');
    assert.match(f.status.textContent, message);
    assert.notEqual(f.button.disabled, true);
  });
}
