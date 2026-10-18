<?php
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
function add_action( ...$args ) {}
function is_checkout() { return $GLOBALS['checkout']; }
function blue_media_asset_url( $name ) { return 'https://shop.test/uploads/' . $name; }
function esc_url( $url ) { return htmlspecialchars( $url, ENT_QUOTES ); }
require __DIR__ . '/../inc/paymob.php';
$GLOBALS['checkout'] = true;
foreach ( array( 'tamara', 'paymob-33957-tamara-tamara-sar' ) as $gateway ) {
    $html = blue_checkout_tamara_icon( 'old-icon', $gateway );
    if ( false === strpos( $html, 'pay-tamara-badge.svg' ) || substr_count( $html, '<img' ) !== 1 ) {
        throw new RuntimeException( 'Tamara logo missing or duplicated' );
    }
}
foreach ( array( 'tabby', 'paymob-pixel', 'cod' ) as $gateway ) {
    if ( 'original' !== blue_checkout_tamara_icon( 'original', $gateway ) ) {
        throw new RuntimeException( 'Unrelated gateway changed' );
    }
}
$GLOBALS['checkout'] = false;
if ( 'original' !== blue_checkout_tamara_icon( 'original', 'tamara' ) ) {
    throw new RuntimeException( 'Non-checkout icon changed' );
}
echo "PASS: six Tamara icon checks\n";
