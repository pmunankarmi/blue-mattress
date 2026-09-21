<?php
/**
 * English payment-method text for PDF documents only.
 * Never changes stored orders, checkout labels, or WooCommerce emails.
 */
defined( 'ABSPATH' ) || exit;

function blue_pdf_payment_method_english( $totals, $order, $document_type ) {
	if ( ! isset( $totals['payment_method'] ) || ! is_a( $order, 'WC_Order' ) ) {
		return $totals;
	}

	$method = (string) $order->get_payment_method();
	$title  = trim( wp_strip_all_tags( (string) $order->get_payment_method_title() ) );
	$names  = array(
		'bacs'   => 'Direct bank transfer',
		'cod'    => 'Cash on delivery',
		'cheque' => 'Check payments',
		'paypal' => 'PayPal',
	);
	$arabic_titles = array(
		'الدفع ببطاقة الخصم/الائتمان' => 'Debit/Credit Card Payment',
		'الدفع عند الاستلام' => 'Cash on delivery',
	);
	if ( isset( $arabic_titles[ $title ] ) ) {
		$title = $arabic_titles[ $title ];
	} elseif ( isset( $names[ $method ] ) ) {
		$title = $names[ $method ];
	} elseif ( false !== strpos( $method, 'tabby' ) ) {
		$title = 'Tabby';
	} elseif ( false !== strpos( $method, 'tamara' ) ) {
		$title = 'Tamara';
	} elseif ( preg_match( '/\\p{Arabic}/u', $title ) ) {
		// Do not guess a card/wallet type from a provider that supports both.
		$title = false !== strpos( $method, 'paymob' ) ? 'Paymob' : 'Payment';
	}
	$totals['payment_method']['label'] = 'Payment method';
	$totals['payment_method']['value'] = $title;
	return $totals;
}
add_filter( 'wpo_wcpdf_woocommerce_totals', 'blue_pdf_payment_method_english', 100, 3 );
