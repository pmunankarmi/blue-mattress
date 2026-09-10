<?php
/**
 * Update the theme from versioned GitHub releases.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

const BLUE_GITHUB_REPOSITORY = 'pmunankarmi/blue-mattress';
const BLUE_GITHUB_RELEASE_ZIP = 'blue-mattress.zip';
const BLUE_GITHUB_CACHE_KEY   = 'blue_mattress_github_release_v2';

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
		'url'          => esc_url_raw( (string) ( $release['html_url'] ?? '' ) ),
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
