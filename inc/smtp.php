<?php
/**
 * Optional Microsoft 365 SMTP transport for WordPress mail.
 *
 * This module changes delivery only. WooCommerce continues to build and style
 * its own messages with its standard templates and settings.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Whether the Microsoft 365 transport is enabled in Theme Options. */
function blue_smtp_enabled(): bool {
	return (bool) blue_option( 'smtp_enabled', false );
}

/** Return the configured Microsoft 365 mailbox. */
function blue_smtp_email(): string {
	return sanitize_email( (string) blue_option( 'smtp_email', '' ) );
}

/** Read the SMTP password, preferring a server-side wp-config.php constant. */
function blue_smtp_password(): string {
	if ( defined( 'BLUE_SMTP_PASSWORD' ) && is_string( BLUE_SMTP_PASSWORD ) && '' !== BLUE_SMTP_PASSWORD ) {
		return BLUE_SMTP_PASSWORD;
	}

	return (string) blue_option( 'smtp_password', '' );
}

/** SMTP is usable only when it is enabled and both credentials are present. */
function blue_smtp_is_configured(): bool {
	return blue_smtp_enabled() && is_email( blue_smtp_email() ) && '' !== blue_smtp_password();
}

/** Use the authenticated Microsoft 365 mailbox as the WordPress From address. */
function blue_smtp_from_address( string $from ): string {
	return blue_smtp_is_configured() ? blue_smtp_email() : $from;
}
add_filter( 'wp_mail_from', 'blue_smtp_from_address' );

/** Use the editable sender name while preserving the WordPress default fallback. */
function blue_smtp_from_name( string $name ): string {
	if ( ! blue_smtp_is_configured() ) {
		return $name;
	}

	$configured = sanitize_text_field( (string) blue_option( 'smtp_from_name', '' ) );
	return '' !== $configured ? $configured : (string) get_bloginfo( 'name' );
}
add_filter( 'wp_mail_from_name', 'blue_smtp_from_name' );

/** Route wp_mail() through Microsoft 365 using STARTTLS on the submission port. */
function blue_configure_microsoft_365_smtp( $phpmailer ): void {
	if ( ! blue_smtp_is_configured() ) {
		return;
	}

	$email = blue_smtp_email();
	$phpmailer->isSMTP();
	$phpmailer->Host        = 'smtp.office365.com';
	$phpmailer->Port        = 587;
	$phpmailer->SMTPSecure  = 'tls';
	$phpmailer->SMTPAuth    = true;
	$phpmailer->SMTPAutoTLS = true;
	$phpmailer->Username    = $email;
	$phpmailer->Password    = blue_smtp_password();
	$phpmailer->Sender      = $email;
	$phpmailer->Timeout     = 30;
	$phpmailer->SMTPDebug   = 0;
}
add_action( 'phpmailer_init', 'blue_configure_microsoft_365_smtp' );

/** Keep the saved password out of the rendered Theme Options form. */
function blue_prepare_smtp_password_field( array $field ): array {
	$has_saved_password = '' !== (string) get_option( 'options_smtp_password', '' );
	$field['value'] = '';
	$field['placeholder'] = defined( 'BLUE_SMTP_PASSWORD' )
		? __( 'Configured in wp-config.php', 'blue-mattress' )
		: ( $has_saved_password
			? __( 'Saved — leave blank to keep it', 'blue-mattress' )
			: __( 'Enter the mailbox password', 'blue-mattress' ) );

	return $field;
}
add_filter( 'acf/prepare_field/key=field_blue_smtp_password', 'blue_prepare_smtp_password_field' );

/** Preserve the existing database password when the masked field is left blank. */
function blue_preserve_smtp_password( $value, $post_id ) {
	if ( ! in_array( $post_id, array( 'option', 'options' ), true ) || '' !== (string) $value ) {
		return $value;
	}

	return (string) get_option( 'options_smtp_password', '' );
}
add_filter( 'acf/update_value/key=field_blue_smtp_password', 'blue_preserve_smtp_password', 10, 2 );
