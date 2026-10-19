<?php
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
function add_action( ...$args ) {}
function is_checkout() { return $GLOBALS['checkout']; }
function is_admin() { return $GLOBALS['admin'] ?? false; }
function blue_language() { return $GLOBALS['language'] ?? 'en'; }
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
$GLOBALS['checkout'] = true;
$GLOBALS['language'] = 'ar';
$cases = array(
    array( 'Tamara', 'paymob-33957-tamara-tamara-sar', 'تمارا' ),
    array( 'Pay later with Tabby', 'tabby_installments', 'ادفع لاحقًا مع تابي' ),
    array( 'Pay with Paymob', 'paymob', 'ادفع عبر باي موب' ),
    array( 'Split in up to 4 payments or Pay in full securely with Tamara', 'tamara', 'قسّم مشترياتك على ما يصل إلى 4 دفعات أو ادفع المبلغ كاملًا بأمان مع تمارا' ),
    array( 'Pay later with Tabby<br><img src="badge.svg">', 'tabby_installments', 'ادفع لاحقًا مع تابي<br><img src="badge.svg">' ),
);
foreach ( $cases as [ $input, $gateway, $expected ] ) {
    if ( $expected !== blue_checkout_payment_arabic( $input, $gateway ) ) {
        throw new RuntimeException( 'Arabic payment copy mismatch' );
    }
}
foreach ( array( 'english', 'admin', 'outside-checkout' ) as $scope ) {
    $GLOBALS['language'] = 'english' === $scope ? 'en' : 'ar';
    $GLOBALS['admin'] = 'admin' === $scope;
    $GLOBALS['checkout'] = 'outside-checkout' !== $scope;
    if ( 'Pay with Paymob' !== blue_checkout_payment_arabic( 'Pay with Paymob', 'paymob' ) ) {
        throw new RuntimeException( 'Translation leaked outside Arabic storefront checkout' );
    }
}
echo "PASS: eight payment translation checks\n";
