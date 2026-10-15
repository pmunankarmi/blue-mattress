const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../assets/js/checkout-payment-redirect.js'), 'utf8');

function setup(url = '/?wc-ajax=checkout') {
    let filter;
    const redirects = [];
    let calls = 0;
    const window = { location: { href: 'https://shop.test/checkout/', origin: 'https://shop.test', assign: url => redirects.push(url) } };
    vm.runInNewContext(source, { URL, window, jQuery: { ajaxPrefilter: fn => { filter = fn; } } });
    const options = { url, success: () => { calls++; } };
    filter(options);
    return { options, redirects, calls: () => calls };
}
const response = { result: 'success', blue_account_payment_redirect: true, redirect: 'https://shop.test/checkout/order-pay/123/?pay_for_order=true&key=test' };
test('new account redirects to saved order without invoking resubmitting handlers', () => {
    const s = setup(); s.options.success(response);
    assert.deepEqual(s.redirects, [response.redirect]); assert.equal(s.calls(), 0);
});
for (const [name, data] of Object.entries({
    'existing customer success': { result: 'success', redirect: '/thank-you/' },
    'checkout error': { result: 'failure', messages: 'Email already exists' },
    'external target': { ...response, redirect: 'https://other.test/' },
    'script target': { ...response, redirect: 'javascript:alert(1)' },
    'invalid response': null,
})) {
    test(name + ' preserves native handling', () => {
        const s = setup(); s.options.success(data);
        assert.equal(s.calls(), 1); assert.equal(s.redirects.length, 0);
    });
}
test('unrelated AJAX is untouched', () => {
    const s = setup('/?wc-ajax=update_order_review'); s.options.success(response);
    assert.equal(s.calls(), 1); assert.equal(s.redirects.length, 0);
});
