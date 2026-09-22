<?php
/**
 * Presentation only for WooCommerce's standard HTML notifications.
 *
 * WooCommerce still renders its templates and inlines these styles. Keep this
 * separate from SMTP, invoice PDFs, email content and notification triggers.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Append the brand's email-safe styles without replacing Woo's default CSS. */
function blue_woocommerce_email_styles( $css, $email = null ) {
	if ( is_object( $email ) && is_callable( array( $email, 'get_email_type' ) ) && 'plain' === $email->get_email_type() ) {
		return $css;
	}

	// System fonts include Arabic glyphs; WooCommerce retains its own RTL alignment.
	return $css . "\n" . <<<'CSS'
/* Blue Mattress notification styling — no external fonts or images. */
#wrapper, #outer_wrapper {
	background-color: #f7f4ee;
}
#template_container {
	background-color: #ffffff;
	border: 1px solid #e3e8ef;
	border-radius: 16px;
	box-shadow: none;
	overflow: hidden;
}
#template_header {
	background-color: #edf3fb;
	color: #0a1830;
	border-radius: 16px 16px 0 0;
	border-bottom: 3px solid #3d63c9;
}
#header_wrapper {
	padding: 30px 32px;
}
#template_header h1, #header_wrapper h1 {
	color: #0a1830;
	font-family: Arial, Tahoma, sans-serif;
	font-size: 28px;
	font-weight: 700;
	line-height: 1.35;
	letter-spacing: normal;
	text-shadow: none;
}
#body_content {
	background-color: #ffffff;
}
#body_content > table > tbody > tr > td {
	padding: 32px;
}
#body_content_inner, #body_content_inner p, #body_content_inner li {
	color: #44536b;
	font-family: Arial, Tahoma, sans-serif;
	font-size: 15px;
	line-height: 1.7;
}
#body_content_inner h2, #body_content_inner h3 {
	color: #0a1830;
	font-family: Arial, Tahoma, sans-serif;
	font-weight: 700;
	line-height: 1.4;
}
#body_content_inner h2 { font-size: 20px; margin: 24px 0 16px; }
#body_content_inner h3 { font-size: 17px; }
#body_content_inner a { color: #3d63c9; }
#body_content_inner a.button, #body_content_inner a.button:visited {
	display: inline-block;
	background-color: #3d63c9;
	border: 1px solid #3d63c9;
	border-radius: 8px;
	color: #ffffff !important;
	padding: 13px 22px;
	font-family: Arial, Tahoma, sans-serif;
	font-size: 15px;
	font-weight: 700;
	line-height: 1.5;
	text-decoration: none;
}
#body_content_inner table.td {
	border: 1px solid #e3e8ef;
	border-spacing: 0;
}
#body_content_inner table.td th, #body_content_inner table.td td {
	border-color: #e3e8ef;
	padding: 12px;
	color: #0a1830;
	font-family: Arial, Tahoma, sans-serif;
	line-height: 1.5;
}
#body_content_inner table.td thead th {
	background-color: #edf3fb;
	font-weight: 700;
}
#body_content_inner table.td tfoot th, #body_content_inner table.td tfoot td {
	background-color: #f8fafc;
}
#body_content_inner .address {
	border: 1px solid #e3e8ef;
	border-radius: 8px;
	padding: 16px;
	color: #44536b;
	font-family: Arial, Tahoma, sans-serif;
	font-style: normal;
	line-height: 1.7;
	word-break: normal;
	overflow-wrap: break-word;
}
#template_footer #credit, #template_footer #credit a {
	color: #617087;
	font-family: Arial, Tahoma, sans-serif;
	font-size: 12px;
	line-height: 1.7;
}
@media only screen and (max-width: 600px) {
	#header_wrapper { padding: 24px 20px !important; }
	#header_wrapper h1 { font-size: 24px !important; }
	#body_content > table > tbody > tr > td { padding: 24px 20px !important; }
	#body_content_inner table.td th, #body_content_inner table.td td { padding: 8px !important; }
}
CSS;
}
add_filter( 'woocommerce_email_styles', 'blue_woocommerce_email_styles', 20, 2 );

