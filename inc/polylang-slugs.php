<?php
/**
 * Shared page slugs for Polylang Free.
 *
 * WordPress normally makes translated page slugs unique by adding a suffix.
 * This module allows linked translations to keep the same slug, then limits
 * page lookups to the active language so WordPress can select the right page.
 *
 * The implementation is adapted from grappler/polylang-slug 0.2.3, narrowed
 * to pages and updated to avoid depending on Polylang's internal model API.
 *
 * @link    https://github.com/grappler/polylang-slug
 * @license GPL-2.0-or-later
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

const BLUE_SHARED_PAGE_SLUGS_SCHEMA = '1';

/** Return the language taxonomy ID used by Polylang. */
function blue_language_term_taxonomy_id( string $language ): int {
	$term = get_term_by( 'slug', sanitize_key( $language ), 'language' );
	return $term instanceof WP_Term ? (int) $term->term_taxonomy_id : 0;
}

/** Build the SQL fragments needed to limit a page query to one language. */
function blue_page_language_sql( string $language ): array {
	global $wpdb;

	$term_taxonomy_id = blue_language_term_taxonomy_id( $language );
	if ( $term_taxonomy_id < 1 ) {
		return array( '', '' );
	}

	$join  = " INNER JOIN {$wpdb->term_relationships} AS blue_slug_language ON blue_slug_language.object_id = {$wpdb->posts}.ID";
	$where = $wpdb->prepare( ' AND blue_slug_language.term_taxonomy_id = %d', $term_taxonomy_id );

	return array( $join, $where );
}

/** Find a page path without confusing it with a translation that shares it. */
function blue_get_page_by_path_in_language( string $page_path, string $language ): ?WP_Post {
	list( $join, $language_where ) = blue_page_language_sql( $language );
	if ( ! $join || ! $language_where ) {
		$page = get_page_by_path( $page_path, OBJECT, 'page' );
		return $page instanceof WP_Post ? $page : null;
	}

	$encoded_path = rawurlencode( urldecode( $page_path ) );
	$encoded_path = str_replace( array( '%2F', '%20' ), array( '/', ' ' ), $encoded_path );
	$path_parts   = array_values( array_filter( explode( '/', trim( $encoded_path, '/' ) ), 'strlen' ) );
	$path_parts   = array_map( 'sanitize_title_for_query', $path_parts );
	if ( ! $path_parts ) {
		return null;
	}

	global $wpdb;
	$placeholders = implode( ', ', array_fill( 0, count( $path_parts ), '%s' ) );
	$sql = "SELECT {$wpdb->posts}.ID, {$wpdb->posts}.post_name, {$wpdb->posts}.post_parent
		FROM {$wpdb->posts}{$join}
		WHERE {$wpdb->posts}.post_name IN ({$placeholders})
		AND {$wpdb->posts}.post_type = 'page'
		{$language_where}";
	$pages = $wpdb->get_results( $wpdb->prepare( $sql, $path_parts ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	if ( ! $pages ) {
		return null;
	}

	$reversed_parts = array_reverse( $path_parts );
	foreach ( $pages as $candidate ) {
		if ( $candidate->post_name !== $reversed_parts[0] ) {
			continue;
		}

		$matched_parts = 1;
		$parent_id     = (int) $candidate->post_parent;
		while ( $parent_id && isset( $reversed_parts[ $matched_parts ] ) ) {
			$parent = null;
			foreach ( $pages as $possible_parent ) {
				if ( (int) $possible_parent->ID === $parent_id && $possible_parent->post_name === $reversed_parts[ $matched_parts ] ) {
					$parent = $possible_parent;
					break;
				}
			}
			if ( ! $parent ) {
				break;
			}

			$parent_id = (int) $parent->post_parent;
			++$matched_parts;
		}

		if ( 0 === $parent_id && count( $reversed_parts ) === $matched_parts ) {
			$page = get_post( (int) $candidate->ID );
			return $page instanceof WP_Post ? $page : null;
		}
	}

	return null;
}

/** Find a top-level page by slug in one language. */
function blue_get_page_by_slug_in_language( string $slug, string $language ): ?WP_Post {
	return blue_get_page_by_path_in_language( $slug, $language );
}

/** Allow translated pages to reuse a slug when the same language has no match. */
function blue_allow_shared_page_slug( string $slug, int $post_id, string $post_status, string $post_type, int $post_parent, string $requested_slug ): string {
	if ( 'page' !== $post_type || $slug === $requested_slug || ! function_exists( 'pll_get_post_language' ) ) {
		return $slug;
	}

	$language = (string) pll_get_post_language( $post_id, 'slug' );
	if ( ! $language ) {
		return $slug;
	}

	global $wpdb;
	$attachment_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'attachment' AND ID != %d LIMIT 1",
			$requested_slug,
			$post_id
		)
	);
	if ( $attachment_id ) {
		return $slug;
	}

	list( $join, $language_where ) = blue_page_language_sql( $language );
	if ( ! $join || ! $language_where ) {
		return $slug;
	}

	$sql = "SELECT ID FROM {$wpdb->posts}{$join}
		WHERE post_name = %s
		AND post_type = %s
		AND ID != %d
		AND post_parent = %d
		{$language_where}
		LIMIT 1";
	$match = $wpdb->get_var( $wpdb->prepare( $sql, $requested_slug, $post_type, $post_id, $post_parent ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	return $match ? $slug : $requested_slug;
}
add_filter( 'wp_unique_post_slug', 'blue_allow_shared_page_slug', 10, 6 );

/** Resolve a public page path before WordPress consults its language-blind path cache. */
function blue_resolve_shared_page_path( WP_Query $query ): void {
	$page_path = (string) $query->get( 'pagename' );
	if ( is_admin() || wp_doing_ajax() || $query->get( 'suppress_filters' ) || ! $page_path || ! function_exists( 'blue_language' ) ) {
		return;
	}

	$page = blue_get_page_by_path_in_language( $page_path, blue_language() );
	if ( ! $page ) {
		return;
	}

	$query->set( 'page_id', $page->ID );
	$query->set( 'pagename', '' );
	$query->set( 'name', '' );
}
add_action( 'pre_get_posts', 'blue_resolve_shared_page_path', 5 );

/** Give every translated page the English source slug. */
function blue_sync_translated_page_slugs(): bool {
	if ( ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_get_post_translations' ) ) {
		return false;
	}

	$page_ids = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => array( 'publish', 'private', 'draft' ),
			'posts_per_page'   => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	$legacy_routes = (array) get_option( 'blue_legacy_page_slug_routes', array() );
	$failed        = false;

	foreach ( $page_ids as $page_id ) {
		$page_id = (int) $page_id;
		if ( 'en' !== pll_get_post_language( $page_id, 'slug' ) ) {
			continue;
		}

		$source = get_post( $page_id );
		if ( ! $source instanceof WP_Post || ! $source->post_name ) {
			continue;
		}

		$translations = pll_get_post_translations( $page_id );
		foreach ( $translations as $language => $translated_id ) {
			$translated_id = (int) $translated_id;
			if ( 'ar' !== $language || $translated_id === $page_id ) {
				continue;
			}

			$translation = get_post( $translated_id );
			if ( ! $translation instanceof WP_Post || $translation->post_name === $source->post_name ) {
				continue;
			}

			$old_uri = trim( (string) get_page_uri( $translated_id ), '/' );
			$new_uri = trim( (string) get_page_uri( $page_id ), '/' );
			$result  = wp_update_post(
				array(
					'ID'        => $translated_id,
					'post_name' => $source->post_name,
				),
				true
			);

			if ( is_wp_error( $result ) || $source->post_name !== get_post_field( 'post_name', $translated_id ) ) {
				$failed = true;
				continue;
			}
			if ( $old_uri && $new_uri && $old_uri !== $new_uri ) {
				$legacy_routes[ $old_uri ] = $new_uri;
			}
		}
	}

	if ( $failed ) {
		return false;
	}

	update_option( 'blue_legacy_page_slug_routes', $legacy_routes, false );
	update_option( 'blue_shared_page_slugs_schema', BLUE_SHARED_PAGE_SLUGS_SCHEMA, false );
	flush_rewrite_rules( false );
	return true;
}

/** Run the slug migration once after the theme update. */
function blue_maybe_sync_translated_page_slugs(): void {
	if ( BLUE_SHARED_PAGE_SLUGS_SCHEMA === get_option( 'blue_shared_page_slugs_schema' ) ) {
		return;
	}
	if ( current_user_can( 'manage_options' ) ) {
		blue_sync_translated_page_slugs();
	}
}
add_action( 'after_switch_theme', 'blue_maybe_sync_translated_page_slugs', 70 );
add_action( 'admin_init', 'blue_maybe_sync_translated_page_slugs', 70 );

/** Redirect the old suffixed page URLs recorded during migration. */
function blue_redirect_legacy_page_slug(): void {
	if ( is_admin() || wp_doing_ajax() || is_preview() || ! blue_is_arabic() ) {
		return;
	}

	$routes = (array) get_option( 'blue_legacy_page_slug_routes', array() );
	if ( ! $routes ) {
		return;
	}

	$request_path = trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' );
	$home_path    = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $home_path && ( $request_path === $home_path || str_starts_with( $request_path, $home_path . '/' ) ) ) {
		$request_path = ltrim( substr( $request_path, strlen( $home_path ) ), '/' );
	}
	if ( str_starts_with( $request_path, 'ar/' ) ) {
		$request_path = substr( $request_path, 3 );
	}

	foreach ( $routes as $old_uri => $new_uri ) {
		$old_uri = trim( (string) $old_uri, '/' );
		$new_uri = trim( (string) $new_uri, '/' );
		if ( ! $old_uri || ! $new_uri || ( $request_path !== $old_uri && ! str_starts_with( $request_path, $old_uri . '/' ) ) ) {
			continue;
		}

		$suffix = ltrim( substr( $request_path, strlen( $old_uri ) ), '/' );
		$target = trailingslashit( blue_language_home_url( 'ar' ) ) . trailingslashit( $new_uri );
		if ( $suffix ) {
			$target .= trailingslashit( $suffix );
		}
		if ( ! empty( $_GET ) ) {
			$query = wp_unslash( $_GET );
			unset( $query['lang'], $query['blue_lang'] );
			if ( $query ) {
				$target = add_query_arg( $query, $target );
			}
		}

		wp_safe_redirect( $target, 301, 'Blue Mattress' );
		exit;
	}
}
add_action( 'template_redirect', 'blue_redirect_legacy_page_slug', 1 );
