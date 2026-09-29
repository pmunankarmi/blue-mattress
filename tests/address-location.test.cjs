// Isolated DOM and Maps mocks: never request or transmit a real location.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../assets/js/address-lookup.js'), 'utf8')
  .replace('  if (document.readyState', '  window.testCreateLookup = createLookup;\n  if (document.readyState');

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

function fixture({ scope = 'billing', error, country = 'SA', secure = true, supported = true, geocodeError = false, arabic = false } = {}) {
  let requests = 0;
  let geocodes = 0;
  const fields = {};
  for (const prefix of ['billing', 'shipping', 'calc_shipping']) {
    for (const key of ['country', 'state', 'city', 'postcode', 'address_1', 'address_2']) {
      fields['#' + prefix + '_' + key] = new Element('input');
    }
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
              component('country', country), component('locality', 'Riyadh'),
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
      assert.equal(options.timeout, 15000);
      if (error) reject({ code: error });
      else resolve({ coords: { latitude: 24.7, longitude: 46.7 } });
    }
  } } : {};
  vm.runInNewContext(source, {
    window, navigator, Event: class {},
    sessionStorage: { setItem: (k, v) => storage.set(k, v) },
    document: {
      documentElement: { lang: arabic ? 'ar' : 'en' }, readyState: 'loading',
      createElement: (tag) => new Element(tag), addEventListener() {},
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

for (const [name, options, message] of [
  ['denied', { error: 1 }, /permission was denied/],
  ['unavailable', { error: 2 }, /could not detect/],
  ['timeout', { error: 3 }, /timed out/],
  ['insecure', { secure: false }, /HTTPS/],
  ['unsupported', { supported: false }, /unavailable/],
  ['outside Saudi Arabia', { country: 'US' }, /outside Saudi/],
  ['Maps failure', { geocodeError: true }, /could not detect/]
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
