<?php
/**
 * Update the theme from versioned GitHub releases.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

const BLUE_GITHUB_REPOSITORY = 'pmunankarmi/blue-mattress';
const BLUE_GITHUB_RELEASE_ZIP = 'blue-mattress.zip';
const BLUE_GITHUB_CACHE_KEY   = 'blue_mattress_github_release';

/**
 * Return the latest published GitHub release, cached to respect API limits.
 *
 * @return array<string, mixed>
 */
function blue_github_latest_release(): array {
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
		set_site_transient( BLUE_GITHUB_CACHE_KEY, array(), HOUR_IN_SECONDS );
		return array();
	}

	$release = json_decode( wp_remote_retrieve_body( $response ), true );
	$release = is_array( $release ) ? $release : array();
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

add_filter(
	'pre_set_site_transient_update_themes',
	function ( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		$stylesheet      = get_template();
		$current_version = $transient->checked[ $stylesheet ] ?? wp_get_theme( $stylesheet )->get( 'Version' );
		$release         = blue_github_latest_release();
		$latest_version  = ltrim( (string) ( $release['tag_name'] ?? '' ), 'vV' );
		$package         = blue_github_release_package( $release );

		if ( ! $latest_version || ! $package || ! version_compare( $latest_version, (string) $current_version, '>' ) ) {
			return $transient;
		}

		$transient->response[ $stylesheet ] = array(
			'theme'        => $stylesheet,
			'new_version'  => $latest_version,
			'url'          => esc_url_raw( (string) ( $release['html_url'] ?? '' ) ),
			'package'      => $package,
			'requires'     => '6.4',
			'requires_php' => '8.0',
		);

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
