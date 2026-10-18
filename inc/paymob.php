<?php
/**
 * Paymob checkout compatibility.
 *
 * Some privacy filters block a JavaScript URL containing "pixel" even though
 * it is the payment form renderer. Serve the installed plugin asset through a
 * neutral, same-origin URL and keep the plugin as the single source of truth.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Display Tamara branding without changing gateway availability or payments. */
function blue_checkout_tamara_icon( $icon, $gateway_id ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout()
		|| false === strpos( (string) $gateway_id, 'tamara' ) ) {
		return $icon;
	}
	return '<img class="blue-checkout-tamara-logo" src="' . esc_url( blue_media_asset_url( 'pay-tamara-badge.svg' ) ) . '" alt="Tamara" width="85" height="28" />';
}
add_filter( 'woocommerce_gateway_icon', 'blue_checkout_tamara_icon', 30, 2 );

add_filter(
	'script_loader_src',
	function ( string $src ): string {
		if ( ! function_exists( 'is_checkout' )
			|| ( ! is_checkout() && ! ( is_singular() && has_block( 'woocommerce/checkout' ) ) ) ) {
			return $src;
		}

		if ( false !== strpos( $src, '/paymob-pixel_block.js' ) ) {
			return BLUE_THEME_URI . '/assets/js/checkout-renderer.js?ver=' . rawurlencode( BLUE_THEME_VERSION );
		}

		if ( false !== strpos( $src, 'cdn.jsdelivr.net/npm/paymob-pixel@1.2.7/main.js' ) ) {
			return BLUE_THEME_URI . '/assets/js/checkout-sdk.js?ver=' . rawurlencode( BLUE_THEME_VERSION );
		}

		return $src;
	},
	20
);
/**
 * A newly registered customer needs a fresh page/session before Pixel payment.
 * Stay inside Woo's validated classic checkout; never create a second order.
 */
final class Blue_New_Account_Payment {
	private static $checkout_started = false;
	private static $customer_id = 0;

	public static function start(): void {
		self::$checkout_started = ! is_user_logged_in() && wp_doing_ajax();
		self::$customer_id = 0;
	}

	public static function customer_created( $customer_id ): void {
		if ( self::$checkout_started ) {
			self::$customer_id = (int) $customer_id;
		}
	}

	public static function redirect( $order_id, $posted_data, $order ): void {
		if ( ! self::$checkout_started || ! self::$customer_id
			|| ! $order instanceof WC_Order
			|| (int) $order->get_customer_id() !== self::$customer_id
			|| (int) $order->get_id() !== (int) $order_id
			|| 'paymob-pixel' !== $order->get_payment_method()
			|| ! $order->needs_payment() ) {
			return;
		}

		// Keep the cart/order relationship for Woo's normal payment/retry handling.
		WC()->session->set( 'order_awaiting_payment', $order_id );
		unset( WC()->session->reload_checkout, WC()->session->refresh_totals );
		wp_send_json( array(
			'result' => 'success',
			'redirect' => $order->get_checkout_payment_url(),
			'blue_account_payment_redirect' => true,
		) );
	}
}
add_action( 'woocommerce_before_checkout_process', array( 'Blue_New_Account_Payment', 'start' ) );
add_action( 'woocommerce_created_customer', array( 'Blue_New_Account_Payment', 'customer_created' ), 10, 1 );
add_action( 'woocommerce_checkout_order_processed', array( 'Blue_New_Account_Payment', 'redirect' ), PHP_INT_MAX, 3 );

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) && ! is_order_received_page() ) {
		wp_enqueue_script(
			'blue-checkout-payment-redirect',
			BLUE_THEME_URI . '/assets/js/checkout-payment-redirect.js',
			array( 'jquery', 'wc-checkout' ),
			BLUE_THEME_VERSION,
			true
		);
	}
}, 30 );
