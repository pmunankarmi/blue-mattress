<?php
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
function add_action( ...$args ) {}
function is_user_logged_in() { return $GLOBALS['logged_in']; }
function wp_doing_ajax() { return $GLOBALS['ajax']; }
function WC() { return $GLOBALS['wc']; }
function wp_send_json( $response ) { $GLOBALS['response'] = $response; throw new RuntimeException( 'json-sent' ); }
class WC_Order {
    public $customer = 42;
    public $method = 'paymob-pixel';
    public $payable = true;
    function get_customer_id() { return $this->customer; }
    function get_id() { return 123; }
    function get_payment_method() { return $this->method; }
    function needs_payment() { return $this->payable; }
    function get_checkout_payment_url() { return 'https://shop.test/checkout/order-pay/123/?key=test'; }
}
class Session {
    public $order_awaiting_payment;
    public $reload_checkout = true;
    public $refresh_totals = true;
    function set( $key, $value ) { $this->$key = $value; }
}
require __DIR__ . '/../inc/paymob.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
foreach ( array( 'new', 'logged-in', 'guest', 'wrong-owner', 'other-gateway', 'paid', 'non-ajax' ) as $case ) {
    $GLOBALS['logged_in'] = 'logged-in' === $case;
    $GLOBALS['ajax'] = 'non-ajax' !== $case;
    $GLOBALS['wc'] = (object) array( 'session' => new Session() );
    $GLOBALS['response'] = null;
    $order = new WC_Order();
    Blue_New_Account_Payment::start();
    if ( 'guest' !== $case ) { Blue_New_Account_Payment::customer_created( 42 ); }
    if ( 'wrong-owner' === $case ) { $order->customer = 99; }
    if ( 'other-gateway' === $case ) { $order->method = 'cod'; }
    if ( 'paid' === $case ) { $order->payable = false; }
    try { Blue_New_Account_Payment::redirect( 123, array(), $order ); }
    catch ( RuntimeException $e ) { if ( 'json-sent' !== $e->getMessage() ) { throw $e; } }
    check( ( 'new' === $case ) === ( null !== $GLOBALS['response'] ), $case );
    if ( 'new' === $case ) {
        check( $GLOBALS['response']['redirect'] === $order->get_checkout_payment_url(), 'payment URL' );
        check( WC()->session->order_awaiting_payment === 123, 'same order' );
        check( ! isset( WC()->session->reload_checkout ), 'clear stale reload flag' );
    }
    echo "PASS: $case\n";
}
