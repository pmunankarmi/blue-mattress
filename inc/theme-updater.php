<?php
/**
 * Update the theme from versioned GitHub releases.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

const BLUE_GITHUB_REPOSITORY = 'pmunankarmi/blue-mattress';
const BLUE_GITHUB_RELEASE_ZIP = 'blue-mattress.zip';
const BLUE_GITHUB_CACHE_KEY   = 'blue_mattress_github_release_v3';
const BLUE_GITHUB_ADMIN_CHECK_KEY = 'blue_mattress_github_admin_check_v1';

/**
 * Build release metadata from the public repository when the GitHub API is
 * unavailable or rate limited by the web host.
 *
 * @return array<string, mixed>
 */
function blue_github_release_fallback(): array {
	$response = wp_safe_remote_get(
		'https://raw.githubusercontent.com/' . BLUE_GITHUB_REPOSITORY . '/master/style.css',
		array(
			'timeout' => 10,
			'headers' => array( 'User-Agent' => 'Blue-Mattress-WordPress-Theme' ),
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return array();
	}

	$stylesheet = wp_remote_retrieve_body( $response );
	if ( ! preg_match( '/^Version:\s*([^\r\n]+)$/mi', $stylesheet, $matches ) ) {
		return array();
	}

	return array(
		'tag_name' => 'v' . trim( $matches[1] ),
		'html_url' => 'https://github.com/' . BLUE_GITHUB_REPOSITORY . '/releases/latest',
		'assets'   => array(
			array(
				'name'                 => BLUE_GITHUB_RELEASE_ZIP,
				'browser_download_url' => 'https://github.com/' . BLUE_GITHUB_REPOSITORY . '/releases/latest/download/' . BLUE_GITHUB_RELEASE_ZIP,
			),
		),
	);
}

/**
 * Return the latest published GitHub release, cached to respect API limits.
 *
 * @return array<string, mixed>
 */
function blue_github_latest_release(): array {
	if ( is_admin() && isset( $_GET['force-check'] ) && current_user_can( 'update_themes' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core update screen refresh.
		delete_site_transient( BLUE_GITHUB_CACHE_KEY );
	}

	$cached = get_site_transient( BLUE_GITHUB_CACHE_KEY );
	if ( false !== $cached ) {
		return is_array( $cached ) ? $cached : array();
	}

	$response = wp_safe_remote_get(
		'https://api.github.com/repos/' . BLUE_GITHUB_REPOSITORY . '/releases/latest',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'               => 'application/vnd.github+json',
				'User-Agent'           => 'Blue-Mattress-WordPress-Theme',
				'X-GitHub-Api-Version' => '2022-11-28',
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$release = blue_github_release_fallback();
		set_site_transient( BLUE_GITHUB_CACHE_KEY, $release, HOUR_IN_SECONDS );
		return $release;
	}

	$release = json_decode( wp_remote_retrieve_body( $response ), true );
	$release = is_array( $release ) ? $release : array();
	if ( empty( $release['tag_name'] ) || ! blue_github_release_package( $release ) ) {
		$release = blue_github_release_fallback();
	}
	set_site_transient( BLUE_GITHUB_CACHE_KEY, $release, 6 * HOUR_IN_SECONDS );

	return $release;
}

/**
 * Locate the WordPress-ready ZIP attached to a GitHub release.
 */
function blue_github_release_package( array $release ): string {
	foreach ( $release['assets'] ?? array() as $asset ) {
		if ( BLUE_GITHUB_RELEASE_ZIP === ( $asset['name'] ?? '' ) ) {
			return esc_url_raw( (string) ( $asset['browser_download_url'] ?? '' ) );
		}
	}

	return '';
}

/**
 * Return normalized release details for WordPress update filters.
 *
 * @return array<string, string>
 */
function blue_github_update_details( string $stylesheet ): array {
	$release        = blue_github_latest_release();
	$latest_version = ltrim( (string) ( $release['tag_name'] ?? '' ), 'vV' );
	$package        = blue_github_release_package( $release );

	if ( ! $latest_version || ! $package ) {
		return array();
	}

	return array(
		'id'           => 'https://github.com/' . BLUE_GITHUB_REPOSITORY,
		'theme'        => $stylesheet,
		'version'      => $latest_version,
		'new_version'  => $latest_version,
		'url'          => add_query_arg( 'action', 'blue_theme_release_details', admin_url( 'admin-ajax.php' ) ),
		'package'      => $package,
		'requires'     => '6.4',
		'requires_php' => '8.0',
	);
}

/**
 * Supply updates through WordPress's supported custom Update URI pathway.
 *
 * @param array<string, mixed>|false $update Existing response.
 * @param array<string, mixed>       $theme_data Theme headers.
 * @param string                     $theme_stylesheet Theme directory.
 * @return array<string, mixed>|false
 */
function blue_github_update_uri_response( $update, array $theme_data, string $theme_stylesheet ) {
	if ( BLUE_GITHUB_REPOSITORY !== trim( (string) wp_parse_url( (string) ( $theme_data['UpdateURI'] ?? '' ), PHP_URL_PATH ), '/' ) ) {
		return $update;
	}

	$details = blue_github_update_details( $theme_stylesheet );
	return $details ?: $update;
}
add_filter( 'update_themes_github.com', 'blue_github_update_uri_response', 10, 3 );

/**
 * Inject the GitHub response whenever WordPress reads its theme update cache.
 *
 * This remains functional when the host cannot reach WordPress.org, because
 * core otherwise exits before running custom Update URI providers.
 *
 * @param mixed $transient Stored WordPress theme update data.
 * @return object
 */
function blue_github_read_update_transient( $transient ): object {
	static $injecting = false;
	if ( $injecting ) {
		return is_object( $transient ) ? $transient : new stdClass();
	}

	$injecting = true;
	if ( ! is_object( $transient ) ) {
		$transient = new stdClass();
	}
	$transient->checked   = isset( $transient->checked ) && is_array( $transient->checked ) ? $transient->checked : array();
	$transient->response  = isset( $transient->response ) && is_array( $transient->response ) ? $transient->response : array();
	$transient->no_update = isset( $transient->no_update ) && is_array( $transient->no_update ) ? $transient->no_update : array();

	$stylesheet      = get_template();
	$current_version = (string) wp_get_theme( $stylesheet )->get( 'Version' );
	$details         = blue_github_update_details( $stylesheet );
	$latest_version  = (string) ( $details['new_version'] ?? '' );
	$transient->checked[ $stylesheet ] = $current_version;

	if ( $latest_version && version_compare( $latest_version, $current_version, '>' ) ) {
		$transient->response[ $stylesheet ] = $details;
		unset( $transient->no_update[ $stylesheet ] );
	} elseif ( $details ) {
		$transient->no_update[ $stylesheet ] = $details;
		unset( $transient->response[ $stylesheet ] );
	}

	$injecting = false;
	return $transient;
}
add_filter( 'site_transient_update_themes', 'blue_github_read_update_transient', 20 );

/**
 * Refresh GitHub release metadata during normal admin use.
 *
 * WordPress otherwise refreshes theme updates on a long interval, which can
 * leave a newly published release hidden until an administrator presses
 * "Check again". Limit this refresh to once every 15 minutes per site.
 */
function blue_github_maybe_refresh_admin_updates(): void {
	if (
		! current_user_can( 'update_themes' ) ||
		wp_doing_ajax() ||
		isset( $_GET['force-check'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core's manual update refresh handles this request.
		get_site_transient( BLUE_GITHUB_ADMIN_CHECK_KEY )
	) {
		return;
	}

	set_site_transient( BLUE_GITHUB_ADMIN_CHECK_KEY, time(), 15 * MINUTE_IN_SECONDS );
	delete_site_transient( BLUE_GITHUB_CACHE_KEY );

	$updates = get_site_transient( 'update_themes' );
	if ( ! is_object( $updates ) ) {
		$updates = new stdClass();
	}

	$updates->last_checked = time();
	set_site_transient( 'update_themes', $updates );
}
add_action( 'admin_init', 'blue_github_maybe_refresh_admin_updates', 20 );

/** Format the latest GitHub release notes for WordPress admin screens. */
function blue_github_release_notes_html( array $release ): string {
	$notes = trim( str_replace( '**', '', (string) ( $release['body'] ?? '' ) ) );
	if ( ! $notes ) {
		$notes = __( 'Maintenance, compatibility and design improvements for the Blue Mattress theme.', 'blue-mattress' );
	}
	return wpautop( make_clickable( esc_html( $notes ) ) );
}

/** Render the iframe-safe page used by WordPress's View version details link. */
function blue_github_release_details_page(): void {
	if ( ! current_user_can( 'update_themes' ) ) {
		wp_die( esc_html__( 'You are not allowed to view theme updates.', 'blue-mattress' ), '', array( 'response' => 403 ) );
	}

	$release         = blue_github_latest_release();
	$theme           = wp_get_theme( get_template() );
	$latest_version  = ltrim( (string) ( $release['tag_name'] ?? $theme->get( 'Version' ) ), 'vV' );
	$published_at    = (string) ( $release['published_at'] ?? '' );
	$published_label = $published_at ? wp_date( get_option( 'date_format' ), strtotime( $published_at ) ) : '';
	$release_url     = esc_url( (string) ( $release['html_url'] ?? 'https://github.com/' . BLUE_GITHUB_REPOSITORY . '/releases' ) );
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo esc_html( sprintf( __( '%s version %s', 'blue-mattress' ), $theme->get( 'Name' ), $latest_version ) ); ?></title>
		<style>
			body{margin:0;padding:36px;background:#f6f7f7;color:#1d2327;font:15px/1.65 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.blue-release{max-width:760px;margin:auto;padding:32px;background:#fff;border:1px solid #dcdcde;border-radius:14px;box-shadow:0 8px 28px rgba(0,0,0,.07)}h1{margin:0 0 6px;font-size:28px}.meta{margin:0 0 26px;color:#646970}.notes{padding:20px 0;border-block:1px solid #dcdcde}.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:24px}.button{display:inline-block;padding:9px 16px;border-radius:999px;background:#2271b1;color:#fff;text-decoration:none;font-weight:600}.button.secondary{border:1px solid #2271b1;background:#fff;color:#2271b1}
		</style>
	</head>
	<body>
		<main class="blue-release">
			<h1><?php echo esc_html( $theme->get( 'Name' ) ); ?></h1>
			<p class="meta"><?php echo esc_html( sprintf( __( 'Version %s', 'blue-mattress' ), $latest_version ) ); ?><?php echo $published_label ? ' · ' . esc_html( $published_label ) : ''; ?></p>
			<h2><?php esc_html_e( 'Release details', 'blue-mattress' ); ?></h2>
			<div class="notes"><?php echo wp_kses_post( blue_github_release_notes_html( $release ) ); ?></div>
			<p><?php echo esc_html( sprintf( __( 'Requires WordPress %1$s or newer and PHP %2$s or newer.', 'blue-mattress' ), '6.4', '8.0' ) ); ?></p>
			<div class="actions"><a class="button" href="<?php echo $release_url; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View on GitHub', 'blue-mattress' ); ?></a><a class="button secondary" href="https://github.com/<?php echo esc_attr( BLUE_GITHUB_REPOSITORY ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Theme repository', 'blue-mattress' ); ?></a></div>
		</main>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'wp_ajax_blue_theme_release_details', 'blue_github_release_details_page' );

/** Supply Blue Mattress metadata to WordPress's standard theme details API. */
function blue_github_theme_information( $result, string $action, $args ) {
	$args = is_object( $args ) ? $args : (object) $args;
	if ( 'theme_information' !== $action || empty( $args->slug ) || ! in_array( $args->slug, array( 'blue-mattress', get_template(), get_stylesheet() ), true ) ) {
		return $result;
	}

	$release        = blue_github_latest_release();
	$theme          = wp_get_theme( get_template() );
	$latest_version = ltrim( (string) ( $release['tag_name'] ?? $theme->get( 'Version' ) ), 'vV' );

	return (object) array(
		'name'          => $theme->get( 'Name' ),
		'slug'          => get_template(),
		'version'       => $latest_version,
		'author'        => $theme->get( 'Author' ),
		'homepage'      => 'https://github.com/' . BLUE_GITHUB_REPOSITORY,
		'requires'      => '6.4',
		'requires_php'  => '8.0',
		'last_updated'  => (string) ( $release['published_at'] ?? '' ),
		'download_link' => blue_github_release_package( $release ),
		'sections'      => array(
			'description' => wpautop( esc_html( $theme->get( 'Description' ) ) ),
			'changelog'   => blue_github_release_notes_html( $release ),
		),
	);
}
add_filter( 'themes_api', 'blue_github_theme_information', 20, 3 );

add_filter(
	'pre_set_site_transient_update_themes',
	function ( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		$stylesheet      = get_template();
		$current_version = $transient->checked[ $stylesheet ] ?? wp_get_theme( $stylesheet )->get( 'Version' );
		$details         = blue_github_update_details( $stylesheet );
		$latest_version  = (string) ( $details['new_version'] ?? '' );

		if ( ! $latest_version || ! version_compare( $latest_version, (string) $current_version, '>' ) ) {
			return $transient;
		}

		$transient->response[ $stylesheet ] = $details;

		return $transient;
	}
);

add_action(
	'upgrader_process_complete',
	function ( $upgrader, array $options ): void {
		if ( 'theme' === ( $options['type'] ?? '' ) ) {
			delete_site_transient( BLUE_GITHUB_CACHE_KEY );
		}
	},
	10,
	2
);
