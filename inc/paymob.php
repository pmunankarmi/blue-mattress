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

add_filter(
	'script_loader_src',
	function ( string $src ): string {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
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
