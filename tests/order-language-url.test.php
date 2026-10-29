<?php
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
function add_action( ...$args ) {}
function get_option( $key ) { return 'https://blue-mattress.com'; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function add_query_arg( $query, $url ) { return $url . '?' . http_build_query( $query ); }
class WC_Order {
    private $language;
    public function __construct( $language ) { $this->language = $language; }
    public function get_meta( ...$args ) { return $this->language; }
}
require __DIR__ . '/../inc/bilingual.php';
$root = 'https://blue-mattress.com/';
$received = 'checkout/order-received/468/?key=wc_order_test';
$cases = array(
    array( $root . $received, 'en', $root . 'en/' . $received ),
    array( $root . 'en/' . $received, 'en', $root . 'en/' . $received ),
    array( $root . 'en/' . $received, 'ar', $root . $received ),
    array( $root . $received, '', $root . $received ),
    array( $root . $received, 'invalid', $root . $received ),
    array( 'https://staging.test/' . $received, 'en', 'https://staging.test/' . $received ),
    array( $root . 'checkout/order-pay/468/?pay_for_order=true&key=test', 'en', $root . 'en/checkout/order-pay/468/?pay_for_order=true&key=test' ),
);
foreach ( $cases as [ $url, $language, $expected ] ) {
    if ( blue_order_url_for_checkout_language( $url, new WC_Order( $language ) ) !== $expected ) {
        throw new RuntimeException( 'Order language URL mismatch: ' . $language );
    }
}
if ( blue_order_url_for_checkout_language( $root . $received, null ) !== $root . $received ) {
    throw new RuntimeException( 'Invalid order changed' );
}
echo "PASS: eight order-language URL checks\n";
