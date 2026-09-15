<?php
/**
 * One-time migration of bundled presentation media into WordPress uploads.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

const BLUE_THEME_MEDIA_MAP_OPTION = 'blue_theme_media_attachment_ids_v1';
const BLUE_THEME_MEDIA_SOURCE     = 'https://raw.githubusercontent.com/pmunankarmi/blue-mattress/v2.8.0/assets/img/';

/** Return the immutable list of presentation media migrated from theme 2.8.0. */
function blue_theme_media_manifest(): array {
	return array(
		'cat-bedding.jpg',
		'cat-pillows.jpg',
		'cat-toppers.jpg',
		'cloud-cut.jpg',
		'cloud-cut.mp4',
		'cloud-life.jpg',
		'cloud-macro.jpg',
		'comfy-cut.jpg',
		'comfy-cut.mp4',
		'comfy-life.png',
		'cr-badge.png',
		'craft.jpg',
		'haven-cut.jpg',
		'haven-cut.mp4',
		'haven-life.jpg',
		'haven-macro.jpg',
		'hero-dawn.jpg',
		'hero-sea.jpg',
		'hero-sea.mp4',
		'horizon-cut.jpg',
		'horizon-cut.mp4',
		'horizon-life.jpg',
		'horizon-macro.jpg',
		'lifestyle-sleep.jpg',
		'loft-cut.jpg',
		'loft-cut.mp4',
		'loft-life.png',
		'logo-color.png',
		'logo-white.png',
		'mattress-cloud.jpg',
		'mattress-comfy.png',
		'mattress-haven.jpg',
		'mattress-horizon.jpg',
		'mattress-loft.jpg',
		'mattress-reef.jpg',
		'mattress-retro.png',
		'mattress-royal.jpg',
		'mattress-summit.jpg',
		'pay-apple_pay_mini.png',
		'pay-credit_card_mini.png',
		'pay-mada_mini.png',
		'pay-tabby-badge.svg',
		'pay-tabby-wordmark.svg',
		'pay-tabby_installment_mini.png',
		'pay-tamara-badge.svg',
		'pay-tamara-wordmark.svg',
		'pay-tamara_installment_mini.png',
		'reef-cut.jpg',
		'reef-life.jpg',
		'reef-macro.jpg',
		'retro-cut.jpg',
		'retro-cut.mp4',
		'retro-life.png',
		'royal-cut.jpg',
		'royal-cut.mp4',
		'royal-life.jpg',
		'royal-macro.jpg',
		'stark-bed.jpg',
		'stark-craft.jpg',
		'stark-factory.jpg',
		'stark-wood.jpg',
		'summit-cut.jpg',
		'summit-cut.mp4',
		'summit-life.jpg',
		'summit-macro.jpg',
	);
}

/** Return the pinned source URL used until an asset has been migrated. */
function blue_theme_media_source_url( string $filename ): string {
	$filename = basename( $filename );
	return in_array( $filename, blue_theme_media_manifest(), true ) ? BLUE_THEME_MEDIA_SOURCE . rawurlencode( $filename ) : '';
}

/** Read the filename-to-attachment map created by the migration. */
function blue_theme_media_map(): array {
	$map = get_option( BLUE_THEME_MEDIA_MAP_OPTION, array() );
	return is_array( $map ) ? array_map( 'absint', $map ) : array();
}

/** Resolve a migrated theme asset, retaining a safe fallback during migration. */
function blue_media_asset_url( string $filename, string $fallback = '' ): string {
	$filename = basename( $filename );
	$map      = blue_theme_media_map();
	if ( ! empty( $map[ $filename ] ) ) {
		$url = wp_get_attachment_url( $map[ $filename ] );
		if ( $url ) {
			return (string) $url;
		}
	}

	$theme_file = BLUE_THEME_DIR . '/assets/img/' . $filename;
	if ( is_file( $theme_file ) ) {
		return BLUE_THEME_URI . '/assets/img/' . rawurlencode( $filename );
	}

	$source_url = blue_theme_media_source_url( $filename );
	return $source_url ?: $fallback;
}

/** MIME type for theme media, including formats WordPress does not upload by default. */
function blue_theme_media_mime( string $filename ): string {
	return array(
		'avif' => 'image/avif',
		'gif'  => 'image/gif',
		'jpeg' => 'image/jpeg',
		'jpg'  => 'image/jpeg',
		'mp4'  => 'video/mp4',
		'png'  => 'image/png',
		'svg'  => 'image/svg+xml',
		'webp' => 'image/webp',
	)[ strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ] ?? 'application/octet-stream';
}

/** Import one bundled file and return its attachment ID. */
function blue_import_theme_media_file( string $filename ) {
	$filename         = basename( $filename );
	if ( ! in_array( $filename, blue_theme_media_manifest(), true ) ) {
		return new WP_Error( 'blue_missing_media', __( 'The requested theme media file is not in the migration manifest.', 'blue-mattress' ) );
	}

	$map = blue_theme_media_map();
	if ( ! empty( $map[ $filename ] ) && get_post( $map[ $filename ] ) ) {
		return $map[ $filename ];
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_blue_theme_media_filename',
			'meta_value'     => $filename,
		)
	);
	if ( $existing ) {
		$map[ $filename ] = (int) $existing[0];
		update_option( BLUE_THEME_MEDIA_MAP_OPTION, $map, false );
		return $map[ $filename ];
	}

	$source           = BLUE_THEME_DIR . '/assets/img/' . $filename;
	$temporary_source = '';
	if ( ! is_file( $source ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$temporary_source = download_url( blue_theme_media_source_url( $filename ), 300 );
		if ( is_wp_error( $temporary_source ) ) {
			return $temporary_source;
		}
		$source = $temporary_source;
	}

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		if ( $temporary_source ) {
			wp_delete_file( $temporary_source );
		}
		return new WP_Error( 'blue_upload_directory', $uploads['error'] );
	}
	if ( ! wp_mkdir_p( $uploads['path'] ) ) {
		if ( $temporary_source ) {
			wp_delete_file( $temporary_source );
		}
		return new WP_Error( 'blue_upload_directory', __( 'WordPress could not create the upload directory.', 'blue-mattress' ) );
	}

	$stored_name = wp_unique_filename( $uploads['path'], sanitize_file_name( $filename ) );
	$stored_file = trailingslashit( $uploads['path'] ) . $stored_name;
	if ( ! copy( $source, $stored_file ) ) {
		if ( $temporary_source ) {
			wp_delete_file( $temporary_source );
		}
		return new WP_Error( 'blue_copy_media', __( 'WordPress could not copy the theme media file.', 'blue-mattress' ) );
	}
	if ( $temporary_source ) {
		wp_delete_file( $temporary_source );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => blue_theme_media_mime( $stored_name ),
			'post_title'     => sanitize_text_field( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$stored_file
	);
	if ( is_wp_error( $attachment_id ) ) {
		wp_delete_file( $stored_file );
		return $attachment_id;
	}

	update_post_meta( $attachment_id, '_blue_theme_media_filename', $filename );
	if ( str_starts_with( blue_theme_media_mime( $stored_name ), 'image/' ) && 'image/svg+xml' !== blue_theme_media_mime( $stored_name ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$metadata = wp_generate_attachment_metadata( $attachment_id, $stored_file );
		if ( is_array( $metadata ) ) {
			wp_update_attachment_metadata( $attachment_id, $metadata );
		}
	}

	$map[ $filename ] = (int) $attachment_id;
	update_option( BLUE_THEME_MEDIA_MAP_OPTION, $map, false );
	return (int) $attachment_id;
}

/** Theme-admin migration screen. */
add_action(
	'admin_menu',
	function (): void {
		add_theme_page( __( 'Theme Media', 'blue-mattress' ), __( 'Theme Media', 'blue-mattress' ), 'upload_files', 'blue-theme-media', 'blue_theme_media_admin_page' );
	}
);

function blue_theme_media_admin_page(): void {
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_die( esc_html__( 'You are not allowed to migrate media.', 'blue-mattress' ) );
	}
	$manifest = blue_theme_media_manifest();
	$map      = blue_theme_media_map();
	$done     = count( array_intersect( $manifest, array_keys( array_filter( $map ) ) ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Theme Media Migration', 'blue-mattress' ); ?></h1>
		<p><?php esc_html_e( 'Copies the presentation media from the pinned 2.8.0 archive into the WordPress Media Library. Existing files are reused and the operation can safely be resumed.', 'blue-mattress' ); ?></p>
		<p id="blue-media-status"><strong><?php echo esc_html( sprintf( '%1$d / %2$d', $done, count( $manifest ) ) ); ?></strong></p>
		<p><button type="button" class="button button-primary" id="blue-media-start"<?php disabled( $done >= count( $manifest ) ); ?>><?php esc_html_e( 'Start migration', 'blue-mattress' ); ?></button></p>
	</div>
	<script>
	(() => {
		const button = document.getElementById('blue-media-start');
		const status = document.getElementById('blue-media-status');
		if (!button) return;
		button.addEventListener('click', async () => {
			button.disabled = true;
			while (true) {
				const body = new URLSearchParams({ action: 'blue_migrate_theme_media', _ajax_nonce: <?php echo wp_json_encode( wp_create_nonce( 'blue_migrate_theme_media' ) ); ?> });
				try {
					const response = await fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
					const result = await response.json();
					if (!result.success) throw new Error(result.data?.message || 'Migration failed.');
					status.innerHTML = `<strong>${result.data.done} / ${result.data.total}</strong>${result.data.file ? ` — ${result.data.file}` : ''}`;
					if (result.data.complete) break;
				} catch (error) {
					status.textContent = error.message;
					button.disabled = false;
					break;
				}
			}
		});
	})();
	</script>
	<?php
}

/** Remind administrators until every presentation asset lives in Media Library. */
add_action(
	'admin_notices',
	function (): void {
		$is_migration_page = isset( $_GET['page'] ) && 'blue-theme-media' === sanitize_key( wp_unslash( $_GET['page'] ) );
		if ( ! current_user_can( 'upload_files' ) || $is_migration_page ) {
			return;
		}

		$manifest = blue_theme_media_manifest();
		$map      = blue_theme_media_map();
		$done     = count( array_intersect( $manifest, array_keys( array_filter( $map ) ) ) );
		if ( $done >= count( $manifest ) ) {
			return;
		}

		$url = admin_url( 'themes.php?page=blue-theme-media' );
		?>
		<div class="notice notice-info"><p>
			<?php
			printf(
				wp_kses(
					/* translators: 1: completed count, 2: total count, 3: migration URL. */
					__( 'Blue Mattress media migration is incomplete (%1$d/%2$d). <a href="%3$s">Finish the migration</a>.', 'blue-mattress' ),
					array( 'a' => array( 'href' => array() ) )
				),
				(int) $done,
				count( $manifest ),
				esc_url( $url )
			);
			?>
		</p></div>
		<?php
	}
);

/** Import one pending file per AJAX request to avoid server timeouts. */
add_action(
	'wp_ajax_blue_migrate_theme_media',
	function (): void {
		check_ajax_referer( 'blue_migrate_theme_media' );
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to migrate media.', 'blue-mattress' ) ), 403 );
		}

		$manifest = blue_theme_media_manifest();
		$map      = blue_theme_media_map();
		$pending  = array_values( array_diff( $manifest, array_keys( array_filter( $map ) ) ) );
		$file     = $pending[0] ?? '';
		if ( $file ) {
			$result = blue_import_theme_media_file( $file );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message(), 'file' => $file ) );
			}
		}

		$map       = blue_theme_media_map();
		$done      = count( array_intersect( $manifest, array_keys( array_filter( $map ) ) ) );
		$remaining = max( 0, count( $manifest ) - $done );
		wp_send_json_success(
			array(
				'file'     => $file,
				'done'     => $done,
				'total'    => count( $manifest ),
				'complete' => 0 === $remaining,
			)
		);
	}
);
