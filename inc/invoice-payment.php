<?php
/**
 * English payment-method text for PDF documents only.
 * Never changes stored orders, checkout labels, or WooCommerce emails.
 */
defined( 'ABSPATH' ) || exit;

/** Resolve the actual method before considering a possibly localized saved title. */
function blue_pdf_payment_title( $order ): string {
	$method = (string) $order->get_payment_method();
	$title  = trim( wp_strip_all_tags( (string) $order->get_payment_method_title() ) );
	$names  = array(
		'bacs' => 'Direct bank transfer',
		'cod' => 'Cash on delivery',
		'cheque' => 'Check payments',
		'paypal' => 'PayPal',
		'paymob-pixel' => 'Debit/Credit Card Payment',
		'paymob-main' => 'Paymob',
	);
	// Dedicated wallet gateway IDs are authoritative even for older Arabic titles.
	if ( preg_match( '/(?:^|[-_])apple[-_]?pay(?:[-_]|$)/i', $method ) ) {
		return 'Apple Pay';
	}
	if ( preg_match( '/(?:^|[-_])google[-_]?pay(?:[-_]|$)/i', $method ) ) {
		return 'Google Pay';
	}
	if ( isset( $names[ $method ] ) ) {
		return $names[ $method ];
	}
	if ( false !== strpos( $method, 'tabby' ) ) {
		return 'Tabby';
	}
	if ( false !== strpos( $method, 'tamara' ) ) {
		return 'Tamara';
	}
	$arabic_titles = array(
		'الدفع ببطاقة الخصم/الائتمان' => 'Debit/Credit Card Payment',
		'الدفع عند الاستلام' => 'Cash on delivery',
	);
	if ( isset( $arabic_titles[ $title ] ) ) {
		return $arabic_titles[ $title ];
	}
	if ( preg_match( '/\p{Arabic}/u', $title ) ) {
		return false !== strpos( $method, 'paymob' ) ? 'Paymob' : 'Payment';
	}
	return $title;
}

/** Payment row alongside PDF totals. */
function blue_pdf_payment_method_english( $totals, $order, $document_type ) {
	if ( isset( $totals['payment_method'] ) && is_a( $order, 'WC_Order' ) ) {
		$totals['payment_method']['label'] = 'Payment method';
		$totals['payment_method']['value'] = blue_pdf_payment_title( $order );
	}
	return $totals;
}
add_filter( 'wpo_wcpdf_woocommerce_totals', 'blue_pdf_payment_method_english', 100, 3 );

/** Separate payment field in the PDF header/document details. */
function blue_pdf_header_payment_method_english( $title, $document ) {
	$order = $document->order ?? null;
	if ( is_a( $order, 'WC_Order_Refund' ) ) {
		$order = wc_get_order( $order->get_parent_id() );
	}
	return is_a( $order, 'WC_Order' ) ? blue_pdf_payment_title( $order ) : $title;
}
add_filter( 'wpo_wcpdf_payment_method', 'blue_pdf_header_payment_method_english', 100, 2 );
