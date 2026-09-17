<?php
/**
 * Branded presentation for every WooCommerce HTML email.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Return the current email logo without duplicating it inside the theme. */
function blue_woocommerce_email_logo_url(): string {
	$footer_logo = function_exists( 'blue_option' ) ? blue_option( 'footer_logo' ) : '';
	$logo_url    = function_exists( 'blue_image_url' ) ? blue_image_url( $footer_logo ) : '';
	if ( $logo_url ) {
		return $logo_url;
	}

	$logo_id = (int) get_theme_mod( 'site_logo', 0 );
	if ( ! $logo_id ) {
		$logo_id = (int) get_theme_mod( 'custom_logo', 0 );
	}
	if ( $logo_id ) {
		$logo_url = (string) wp_get_attachment_image_url( $logo_id, 'full' );
	}

	return $logo_url ?: blue_media_asset_url( 'logo-white.png' );
}

/** Use the configured WordPress/Theme Options logo when WooCommerce has none. */
add_filter(
	'option_woocommerce_email_header_image',
	function ( $value ) {
		return trim( (string) $value ) ?: blue_woocommerce_email_logo_url();
	}
);

/**
 * Apply a single responsive design to all WooCommerce HTML email classes.
 * WooCommerce runs this CSS through its email CSS inliner before sending.
 */
add_filter(
	'woocommerce_email_styles',
	function ( string $css, $email = null ): string {
		$is_arabic = function_exists( 'blue_is_arabic' ) && blue_is_arabic();
		$align     = $is_arabic ? 'right' : 'left';
		$opposite  = $is_arabic ? 'left' : 'right';
		$tracking  = $is_arabic ? '0' : '-0.02em';
		$font      = $is_arabic
			? 'Tahoma, Arial, sans-serif'
			: '-apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif';

		$brand_css = <<<CSS

body {
	margin: 0 !important;
	padding: 0 !important;
	width: 100% !important;
	background: #eef2f8 !important;
	color: #172234 !important;
	font-family: {$font} !important;
	-webkit-font-smoothing: antialiased;
}
#wrapper {
	width: 100% !important;
	padding: 36px 14px !important;
	background: #eef2f8 !important;
}
#template_container {
	width: 640px !important;
	max-width: 640px !important;
	border: 0 !important;
	border-radius: 24px !important;
	background: #ffffff !important;
	box-shadow: 0 18px 48px rgba(8, 20, 40, 0.13) !important;
	overflow: hidden !important;
}
#template_header,
#template_footer {
	border: 0 !important;
	background: #0b1628 !important;
}
#template_header_image {
	padding: 30px 42px 0 !important;
	text-align: {$align} !important;
}
#template_header_image img {
	display: inline-block !important;
	width: auto !important;
	height: auto !important;
	max-width: 126px !important;
	max-height: 58px !important;
	margin: 0 !important;
}
#header_wrapper {
	padding: 25px 42px 34px !important;
}
#header_wrapper h1 {
	margin: 0 !important;
	color: #ffffff !important;
	font-family: {$font} !important;
	font-size: 30px !important;
	font-weight: 700 !important;
	line-height: 1.3 !important;
	letter-spacing: {$tracking} !important;
	text-align: {$align} !important;
	text-shadow: none !important;
}
#template_body,
#body_content {
	background: #ffffff !important;
}
#body_content_inner {
	padding: 40px 42px 34px !important;
	color: #43516a !important;
	font-family: {$font} !important;
	font-size: 15px !important;
	line-height: 1.75 !important;
	text-align: {$align} !important;
}
#body_content_inner > p:first-child {
	margin-top: 0 !important;
	color: #293750 !important;
	font-size: 16px !important;
}
#body_content h2,
#body_content h3,
#body_content h4 {
	margin: 30px 0 14px !important;
	color: #101c31 !important;
	font-family: {$font} !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	text-align: {$align} !important;
}
#body_content h2 {
	padding-bottom: 12px !important;
	border-bottom: 2px solid #6686ec !important;
	font-size: 21px !important;
}
#body_content h3 { font-size: 18px !important; }
#body_content p { margin: 0 0 18px !important; }
#body_content a,
#template_footer a {
	color: #4f73df !important;
	font-weight: 600 !important;
	text-decoration: none !important;
}
#body_content .button,
#body_content a.button,
#body_content a.button.alt {
	display: inline-block !important;
	margin: 8px 0 20px !important;
	padding: 13px 24px !important;
	border: 0 !important;
	border-radius: 999px !important;
	background: #6686ec !important;
	color: #081225 !important;
	font-weight: 700 !important;
	line-height: 1.35 !important;
	text-align: center !important;
	text-decoration: none !important;
}
#body_content table {
	width: 100% !important;
	margin: 18px 0 26px !important;
	border: 1px solid #dce4ef !important;
	border-collapse: separate !important;
	border-spacing: 0 !important;
	border-radius: 14px !important;
	background: #ffffff !important;
	overflow: hidden !important;
}
#body_content table th,
#body_content table td,
#body_content table .td {
	padding: 13px 15px !important;
	border: 0 !important;
	border-bottom: 1px solid #e5ebf3 !important;
	color: #43516a !important;
	font-family: {$font} !important;
	font-size: 14px !important;
	line-height: 1.55 !important;
	text-align: {$align} !important;
	vertical-align: top !important;
}
#body_content table th {
	background: #f3f6fb !important;
	color: #172234 !important;
	font-weight: 700 !important;
}
#body_content table tr:last-child > th,
#body_content table tr:last-child > td { border-bottom: 0 !important; }
#body_content table td:last-child,
#body_content table th:last-child { text-align: {$opposite} !important; }
#body_content .order_item td { background: #ffffff !important; }
#body_content .order_item small { color: #77849a !important; }
#body_content address {
	margin: 8px 0 24px !important;
	padding: 20px !important;
	border: 1px solid #dce4ef !important;
	border-radius: 14px !important;
	background: #f7f9fc !important;
	color: #43516a !important;
	font-style: normal !important;
	line-height: 1.7 !important;
	text-align: {$align} !important;
}
#template_footer td {
	padding: 26px 42px 30px !important;
}
#credit {
	margin: 0 !important;
	padding: 0 !important;
	color: #aeb9ca !important;
	font-family: {$font} !important;
	font-size: 12px !important;
	line-height: 1.7 !important;
	text-align: center !important;
}
#credit p { margin: 0 !important; }
@media screen and (max-width: 680px) {
	#wrapper { padding: 16px 8px !important; }
	#template_container { width: 100% !important; border-radius: 16px !important; }
	#template_header_image { padding: 24px 24px 0 !important; }
	#header_wrapper { padding: 22px 24px 28px !important; }
	#header_wrapper h1 { font-size: 24px !important; }
	#body_content_inner { padding: 30px 24px 24px !important; }
	#body_content table th,
	#body_content table td,
	#body_content table .td { padding: 11px 10px !important; font-size: 13px !important; }
	#template_footer td { padding: 24px !important; }
}
CSS;

		return $css . $brand_css;
	},
	999,
	2
);
