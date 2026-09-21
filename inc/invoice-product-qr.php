<?php
/**
 * Public product links on PDF invoices (not tax-verification QR codes).
 *
 * Uses WP Overnight's PDF-only hook; email content and totals are untouched.
 */
defined( 'ABSPATH' ) || exit;

/** Generate an embedded PNG without contacting a third-party QR service. */
function blue_product_qr_image( string $url ): string {
	static $images = array();
	if ( isset( $images[ $url ] ) ) {
		return $images[ $url ];
	}
	if ( ! function_exists( 'imagepng' ) || strlen( $url ) > 500 || ! preg_match( '#^https?://#i', $url ) ) {
		return '';
	}
	require_once __DIR__ . '/vendor/product-qrcode.php';
	try {
		$qr = \BlueMattress\ProductQR\QRCode::getMinimumQRCode( $url, BLUE_PRODUCT_QR_ERROR_CORRECT_LEVEL_M );
		$image = $qr->createImage( 5, 4 );
		if ( ! $image ) {
			return '';
		}
		ob_start();
		imagepng( $image );
		$png = ob_get_clean();
		unset( $image );
		$images[ $url ] = 'data:image/png;base64,' . base64_encode( $png );
		return $images[ $url ];
	} catch ( \Throwable $error ) {
		// A product-link enhancement must never prevent invoice delivery.
		return '';
	}
}

/** Add one public product QR per order line, below its existing description. */
function blue_invoice_product_qr( $document_type, $item, $order ): void {
	if ( 'invoice' !== $document_type || ! is_array( $item ) || empty( $item['product_id'] ) ) {
		return;
	}
	$product = wc_get_product( (int) $item['product_id'] );
	if ( ! $product ) {
		return;
	}
	$product_id = $product->get_parent_id() ?: $product->get_id();
	if ( 'publish' !== get_post_status( $product_id ) || post_password_required( $product_id ) ) {
		return;
	}
	$url = get_permalink( $product_id );
	if ( ! $url ) {
		return;
	}
	// Keep long, percent-encoded Arabic slugs scannable using WordPress's public short URL.
	if ( strlen( $url ) > 200 ) {
		$url = add_query_arg( 'p', $product_id, home_url( '/' ) );
	}
	$image = blue_product_qr_image( $url );
	if ( '' === $image ) {
		return;
	}
	echo '<div style="margin-top:8px;page-break-inside:avoid;">';
	echo '<img src="' . esc_attr( $image ) . '" alt="View product details" style="width:28mm;height:28mm;" />';
	echo '</div>';
}
add_action( 'wpo_wcpdf_after_item_meta', 'blue_invoice_product_qr', 10, 3 );
