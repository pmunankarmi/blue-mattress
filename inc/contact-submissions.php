<?php
/**
 * Contact form processing and private admin submission screen.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function (): void {
		register_post_type(
			'blue_contact',
			array(
				'labels' => array(
					'name'          => __( 'Contact Submissions', 'blue-mattress' ),
					'singular_name' => __( 'Contact Submission', 'blue-mattress' ),
					'menu_name'     => __( 'Contact Submissions', 'blue-mattress' ),
					'all_items'     => __( 'All Submissions', 'blue-mattress' ),
					'view_item'     => __( 'View Submission', 'blue-mattress' ),
					'search_items'  => __( 'Search Submissions', 'blue-mattress' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'menu_icon'           => 'dashicons-email-alt2',
				'supports'            => array( 'title' ),
				'capabilities'        => array_merge(
					array_fill_keys(
						array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts' ),
						'manage_options'
					),
					array( 'create_posts' => 'do_not_allow' )
				),
				'map_meta_cap'        => false,
				'exclude_from_search' => true,
			)
		);
	}
);

add_action( 'admin_post_nopriv_blue_contact_submit', 'blue_handle_contact_submission' );
add_action( 'admin_post_blue_contact_submit', 'blue_handle_contact_submission' );
add_action( 'admin_post_nopriv_blue_newsletter_subscribe', 'blue_handle_newsletter_subscription' );
add_action( 'admin_post_blue_newsletter_subscribe', 'blue_handle_newsletter_subscription' );

/** Store footer newsletter signups in the existing private submissions screen. */
function blue_handle_newsletter_subscription(): void {
	$referer = wp_get_referer() ?: blue_home_url( '/' );
	$referer = remove_query_arg( 'newsletter_status', $referer );
	if ( ! isset( $_POST['blue_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['blue_newsletter_nonce'] ) ), 'blue_newsletter_subscribe' ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter_status', 'invalid', $referer ) . '#newsletter' );
		exit;
	}

	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter_status', 'invalid', $referer ) . '#newsletter' );
		exit;
	}

	$reference = 'BLU-N-' . wp_date( 'ymd' ) . '-' . strtoupper( wp_generate_password( 4, false, false ) );
	$post_id   = wp_insert_post(
		array(
			'post_type'   => 'blue_contact',
			'post_status' => 'private',
			'post_title'  => sprintf( '%s — %s', $reference, $email ),
		),
		true
	);
	if ( ! is_wp_error( $post_id ) ) {
		$fields = array(
			'reference' => $reference,
			'type'      => 'newsletter',
			'email'     => $email,
			'subject'   => 'Newsletter subscription',
			'message'   => 'Subscribed from the website footer.',
			'language'  => blue_language(),
			'submitted' => current_time( 'mysql' ),
		);
		foreach ( $fields as $key => $value ) {
			update_post_meta( $post_id, '_blue_' . $key, $value );
		}
	}

	wp_safe_redirect( add_query_arg( 'newsletter_status', 'subscribed', $referer ) . '#newsletter' );
	exit;
}

function blue_contact_redirect( string $status, string $reference = '' ): never {
	$url = wp_get_referer() ?: blue_page_url( 'page-contact.php', '/contact/' );
	$url = remove_query_arg( array( 'contact_status', 'contact_ref' ), $url );
	$url = add_query_arg( array_filter( array( 'contact_status' => $status, 'contact_ref' => $reference ) ), $url );
	wp_safe_redirect( $url . '#contact-form' );
	exit;
}

function blue_handle_contact_submission(): void {
	if ( ! isset( $_POST['blue_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['blue_contact_nonce'] ) ), 'blue_contact_submit' ) ) {
		blue_contact_redirect( 'invalid' );
	}

	if ( ! empty( $_POST['website'] ) ) {
		blue_contact_redirect( 'success' );
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$rate_key = 'blue_contact_' . md5( $ip );
	if ( get_transient( $rate_key ) ) {
		blue_contact_redirect( 'rate_limited' );
	}

	$type    = isset( $_POST['audience'] ) && 'business' === $_POST['audience'] ? 'business' : 'personal';
	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$city    = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	$company = sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) );

	if ( mb_strlen( $name ) < 2 || ! is_email( $email ) || mb_strlen( $message ) < 2 || empty( $_POST['consent'] ) ) {
		blue_contact_redirect( 'validation' );
	}

	$reference = 'BLU-C-' . wp_date( 'ymd' ) . '-' . strtoupper( wp_generate_password( 4, false, false ) );
	$post_id   = wp_insert_post(
		array(
			'post_type'   => 'blue_contact',
			'post_status' => 'private',
			'post_title'  => sprintf( '%s — %s', $reference, $company ?: $name ),
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		blue_contact_redirect( 'error' );
	}

	$fields = compact( 'reference', 'type', 'name', 'email', 'phone', 'city', 'subject', 'message', 'company' );
	$fields['language']  = blue_language();
	$fields['submitted'] = current_time( 'mysql' );
	foreach ( $fields as $key => $value ) {
		update_post_meta( $post_id, '_blue_' . $key, $value );
	}
	set_transient( $rate_key, 1, MINUTE_IN_SECONDS );

	$to      = sanitize_email( (string) blue_option( 'contact_notification_email', get_option( 'admin_email' ) ) );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );
	$body    = "Reference: {$reference}\nType: {$type}\nName: {$name}\nCompany: {$company}\nEmail: {$email}\nPhone: {$phone}\nCity: {$city}\nSubject: {$subject}\n\nMessage:\n{$message}\n";
	wp_mail( $to ?: get_option( 'admin_email' ), sprintf( '[Blue Mattress] New contact submission %s', $reference ), $body, $headers );

	blue_contact_redirect( 'success', $reference );
}

add_action(
	'add_meta_boxes_blue_contact',
	function (): void {
		add_meta_box( 'blue_contact_details', __( 'Submission Details', 'blue-mattress' ), 'blue_render_contact_details', 'blue_contact', 'normal', 'high' );
	}
);

function blue_render_contact_details( WP_Post $post ): void {
	$labels = array(
		'reference' => __( 'Reference', 'blue-mattress' ),
		'type'      => __( 'Enquiry type', 'blue-mattress' ),
		'name'      => __( 'Name', 'blue-mattress' ),
		'company'   => __( 'Company', 'blue-mattress' ),
		'email'     => __( 'Email', 'blue-mattress' ),
		'phone'     => __( 'Phone', 'blue-mattress' ),
		'city'      => __( 'City', 'blue-mattress' ),
		'subject'   => __( 'Subject', 'blue-mattress' ),
		'message'   => __( 'Message', 'blue-mattress' ),
		'language'  => __( 'Language', 'blue-mattress' ),
		'submitted' => __( 'Submitted', 'blue-mattress' ),
	);
	echo '<table class="widefat striped"><tbody>';
	foreach ( $labels as $key => $label ) {
		$value = (string) get_post_meta( $post->ID, '_blue_' . $key, true );
		if ( '' === $value ) {
			continue;
		}
		printf( '<tr><th style="width:180px">%s</th><td>%s</td></tr>', esc_html( $label ), 'message' === $key ? nl2br( esc_html( $value ) ) : esc_html( $value ) );
	}
	echo '</tbody></table>';
}

add_filter(
	'manage_blue_contact_posts_columns',
	function (): array {
		return array(
			'cb'        => '<input type="checkbox">',
			'title'     => __( 'Reference / Sender', 'blue-mattress' ),
			'blue_type' => __( 'Type', 'blue-mattress' ),
			'blue_email'=> __( 'Email', 'blue-mattress' ),
			'blue_city' => __( 'City', 'blue-mattress' ),
			'date'      => __( 'Date', 'blue-mattress' ),
		);
	}
);

add_action(
	'manage_blue_contact_posts_custom_column',
	function ( string $column, int $post_id ): void {
		$map = array( 'blue_type' => 'type', 'blue_email' => 'email', 'blue_city' => 'city' );
		if ( isset( $map[ $column ] ) ) {
			echo esc_html( (string) get_post_meta( $post_id, '_blue_' . $map[ $column ], true ) );
		}
	},
	10,
	2
);
