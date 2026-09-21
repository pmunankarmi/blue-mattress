<?php
/**
 * Arabic classic-checkout wording supplied in checkout-messages-ar-en.xlsx.
 * Presentation only: WooCommerce still owns validation and error metadata.
 */
defined( 'ABSPATH' ) || exit;

function blue_checkout_required_message( string $message, string $label, string $key ): string {
	if ( ! blue_is_arabic() || ( is_admin() && ! wp_doing_ajax() ) ) {
		return $message;
	}
	$messages = array(
		'billing_first_name'  => 'يرجى إدخال الاسم الأول في بيانات الفاتورة',
		'billing_last_name'   => 'يرجى إدخال اسم العائلة في بيانات الفاتورة',
		'billing_address_1'   => 'يرجى إدخال العنوان في بيانات الفاتورة',
		'billing_email'       => 'يرجى إدخال البريد الإلكتروني في بيانات الفاتورة',
		'shipping_first_name' => 'يرجى إدخال الاسم الأول في بيانات الشحن',
		'shipping_last_name'  => 'يرجى إدخال اسم العائلة في بيانات الشحن',
		'shipping_address_1'  => 'يرجى إدخال عنوان الشحن',
	);
	return $messages[ $key ] ?? $message;
}
add_filter( 'woocommerce_checkout_required_field_notice', 'blue_checkout_required_message', 100, 3 );

function blue_checkout_terms_message( $data, $errors ): void {
	if ( ! blue_is_arabic() || ( is_admin() && ! wp_doing_ajax() ) || ! $errors instanceof WP_Error ) {
		return;
	}
	// Keep the error code, field ID and validation failure; replace only its text.
	if ( isset( $errors->errors['terms'][0] ) ) {
		$errors->errors['terms'][0] = 'يرجى الاطلاع على الشروط والأحكام والموافقة عليها لإتمام طلبك';
	}
}
add_action( 'woocommerce_after_checkout_validation', 'blue_checkout_terms_message', 100, 2 );
