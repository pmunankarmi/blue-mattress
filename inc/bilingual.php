<?php
/**
 * Native bilingual storefront support for a shared WooCommerce catalog.
 *
 * Polylang Free remains responsible for page languages and /ar/ routing. This
 * layer deliberately keeps products, variations and product taxonomies shared,
 * then renders their language-specific ACF values on the server.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Read the theme-owned English-to-Arabic interface dictionary. */
function blue_translation_catalog(): array {
	static $catalog = null;
	if ( null !== $catalog ) {
		return $catalog;
	}

	$catalog = array();
	$file    = BLUE_THEME_DIR . '/languages/ui.json';
	if ( is_readable( $file ) ) {
		$json = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = is_string( $json ) ? json_decode( $json, true ) : null;
		if ( is_array( $data ) ) {
			$catalog = array_filter( $data, 'is_string' );
		}
	}

	return $catalog;
}

/** Temporarily force a language, primarily while WooCommerce builds an email. */
function blue_set_language_override( ?string $language ): void {
	$GLOBALS['blue_language_override'] = in_array( $language, array( 'en', 'ar' ), true ) ? $language : null;
}

/** Detect a language prefix when Polylang is unavailable. */
function blue_request_path_language(): ?string {
	$request_path = wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	$home_path    = wp_parse_url( (string) get_option( 'home', '' ), PHP_URL_PATH );
	$relative     = '/' . ltrim( (string) $request_path, '/' );
	if ( $home_path && '/' !== $home_path && str_starts_with( $relative, trailingslashit( $home_path ) ) ) {
		$relative = '/' . ltrim( substr( $relative, strlen( untrailingslashit( $home_path ) ) ), '/' );
	}
	return preg_match( '#^/ar(?:/|$)#i', $relative ) ? 'ar' : null;
}

/** Return the active storefront language. */
function blue_language(): string {
	$override = $GLOBALS['blue_language_override'] ?? null;
	if ( in_array( $override, array( 'en', 'ar' ), true ) ) {
		return $override;
	}

	$requested = isset( $_REQUEST['blue_lang'] ) ? sanitize_key( wp_unslash( $_REQUEST['blue_lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( in_array( $requested, array( 'en', 'ar' ), true ) ) {
		return $requested;
	}

	$path_language = blue_request_path_language();
	if ( $path_language ) {
		return $path_language;
	}

	if ( function_exists( 'pll_current_language' ) ) {
		$language = pll_current_language( 'slug' );
		if ( in_array( $language, array( 'en', 'ar' ), true ) ) {
			return $language;
		}
	}
	if ( ! is_admin() && ! wp_doing_ajax() && ! isset( $_GET['wc-ajax'] ) && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'en';
	}

	if ( function_exists( 'WC' ) && WC() && WC()->session ) {
		$session_language = WC()->session->get( 'blue_language' );
		if ( in_array( $session_language, array( 'en', 'ar' ), true ) ) {
			return $session_language;
		}
	}

	$cookie_language = isset( $_COOKIE['blue_language'] ) ? sanitize_key( wp_unslash( $_COOKIE['blue_language'] ) ) : '';
	if ( in_array( $cookie_language, array( 'en', 'ar' ), true ) ) {
		return $cookie_language;
	}

	return str_starts_with( determine_locale(), 'ar' ) ? 'ar' : 'en';
}

function blue_is_arabic(): bool {
	return 'ar' === blue_language();
}

/** Translate theme-owned interface text through JSON, with an inline fallback. */
function blue_text( string $english, string $arabic = '' ): string {
	if ( ! blue_is_arabic() ) {
		return $english;
	}
	$catalog = blue_translation_catalog();
	return $catalog[ $english ] ?? ( $arabic ?: $english );
}

/** Values passed to JavaScript for dynamic storefront interactions. */
function blue_js_strings(): array {
	return array(
		'from'       => blue_text( 'From', 'ابتداءً من' ),
		'details'    => blue_text( 'Details', 'التفاصيل' ),
		'quickAdd'   => blue_text( 'Add to cart', 'أضف إلى السلة' ),
		'saleBadge'  => blue_text( 'Sale', 'تخفيض' ),
		'plush'      => blue_text( 'Plush', 'ناعمة' ),
		'firm'       => blue_text( 'Firm', 'صلبة' ),
		'standard'   => blue_text( 'Standard', 'قياسي' ),
		'arr'        => blue_is_arabic() ? '←' : '→',
		'added'      => blue_text( 'Opening cart…', 'جاري فتح السلة…' ),
		'searchHint' => blue_text( 'Start typing to search products.', 'اكتب للبحث في المنتجات.' ),
		'searchNone' => blue_text( 'No products found.', 'لا توجد نتائج.' ),
		'decreaseQty'=> blue_text( 'Decrease quantity', 'تقليل الكمية' ),
		'increaseQty'=> blue_text( 'Increase quantity', 'زيادة الكمية' ),
	);
}

/** Keep WooCommerce entities shared instead of asking Polylang to duplicate them. */
add_filter(
	'pll_get_post_types',
	function ( array $post_types ): array {
		unset( $post_types['product'], $post_types['product_variation'], $post_types['shop_order'], $post_types['shop_coupon'] );
		return $post_types;
	},
	20
);
add_filter(
	'pll_get_taxonomies',
	function ( array $taxonomies ): array {
		foreach ( array_keys( $taxonomies ) as $taxonomy ) {
			if ( in_array( $taxonomy, array( 'product_cat', 'product_tag', 'product_shipping_class' ), true ) || str_starts_with( $taxonomy, 'pa_' ) ) {
				unset( $taxonomies[ $taxonomy ] );
			}
		}
		return $taxonomies;
	},
	20
);

/** Use Arabic locale for the native /ar/ fallback when Polylang is inactive. */
add_filter(
	'locale',
	function ( string $locale ): string {
		if ( ! is_admin() && ! function_exists( 'pll_current_language' ) && 'ar' === blue_request_path_language() ) {
			return 'ar';
		}
		return $locale;
	}
);

/** Determine whether a rewrite target belongs to the shared WooCommerce catalog. */
function blue_is_shared_woocommerce_rewrite( string $query ): bool {
	return (bool) preg_match(
		'/(?:[?&](?:product|product_cat|product_tag|product_shipping_class)=)|(?:[?&]post_type=product(?:&|$))|(?:[?&]taxonomy=pa_[^&]*)/',
		$query
	);
}

/**
 * Add Arabic copies of rewrite rules.
 *
 * Products and product taxonomies are intentionally excluded from Polylang so
 * one WooCommerce record can serve both languages. Polylang therefore does not
 * create /ar/ rules for those objects; the theme must always provide them.
 * Without Polylang, all WordPress rules are copied for the native fallback.
 */
add_filter( 'query_vars', function ( array $vars ): array { $vars[] = 'blue_lang'; return $vars; } );
add_filter(
	'rewrite_rules_array',
	function ( array $rules ): array {
		$polylang_active = function_exists( 'pll_current_language' );
		$arabic          = $polylang_active ? array() : array( 'ar/?$' => 'index.php?blue_lang=ar' );
		foreach ( $rules as $regex => $query ) {
			if ( str_starts_with( ltrim( $regex, '^' ), 'ar/' ) ) {
				continue;
			}
			if ( $polylang_active && ! blue_is_shared_woocommerce_rewrite( $query ) ) {
				continue;
			}
			$suffix = ( str_contains( $query, '?' ) ? '&' : '?' ) . 'blue_lang=ar';
			if ( $polylang_active && ! preg_match( '/[?&]lang=/', $query ) ) {
				$suffix .= '&lang=ar';
			}
			$arabic[ 'ar/' . ltrim( $regex, '^' ) ] = $query . $suffix;
		}
		return $arabic + $rules;
	},
	999
);

/** Storefront home URL for a requested language. */
function blue_language_home_url( string $language ): string {
	$language = 'ar' === $language ? 'ar' : 'en';
	if ( function_exists( 'pll_home_url' ) ) {
		$url = pll_home_url( $language );
		if ( $url ) {
			return $url;
		}
	}
	$home = (string) get_option( 'home' );
	if ( ! $home ) {
		$home = site_url( '/' );
	}
	return 'ar' === $language ? trailingslashit( $home ) . 'ar/' : trailingslashit( $home );
}

/** Convert a same-site URL between the shared English and Arabic routes. */
function blue_url_for_language( string $url, string $language ): string {
	$language = 'ar' === $language ? 'ar' : 'en';
	$parts    = wp_parse_url( $url );
	if ( ! is_array( $parts ) ) {
		return $url;
	}

	$target_home = blue_language_home_url( $language );
	$path        = (string) ( $parts['path'] ?? '/' );
	$relative    = ltrim( $path, '/' );
	foreach ( array( 'en', 'ar' ) as $candidate ) {
		$candidate_path = (string) ( wp_parse_url( blue_language_home_url( $candidate ), PHP_URL_PATH ) ?: '/' );
		$candidate_path = trim( $candidate_path, '/' );
		if ( $candidate_path && ( $relative === $candidate_path || str_starts_with( $relative, $candidate_path . '/' ) ) ) {
			$relative = ltrim( substr( $relative, strlen( $candidate_path ) ), '/' );
			break;
		}
	}

	$result = trailingslashit( $target_home ) . $relative;
	if ( isset( $parts['query'] ) && '' !== $parts['query'] ) {
		parse_str( $parts['query'], $query );
		unset( $query['lang'], $query['blue_lang'] );
		if ( $query ) {
			$result = add_query_arg( $query, $result );
		}
	}
	if ( isset( $parts['fragment'] ) ) {
		$result .= '#' . rawurlencode( $parts['fragment'] );
	}
	return $result;
}

function blue_home_url( string $path = '/' ): string {
	return blue_url_for_language( home_url( $path ), blue_language() );
}

/**
 * Return the current storefront destination in a requested language.
 *
 * Polylang's raw switcher URL can point back to the active WooCommerce page
 * because the theme deliberately shares the catalogue while cart, checkout
 * and account pages use translated page records with different slugs. Build
 * page destinations from the translated page URI instead of only swapping an
 * /ar/ prefix.
 */
function blue_current_url_for_language( string $language, string $fallback = '' ): string {
	$language = 'ar' === $language ? 'ar' : 'en';

	$shared_url = blue_current_shared_object_url();
	if ( $shared_url ) {
		return blue_url_for_language( $shared_url, $language );
	}

	if ( is_front_page() ) {
		return blue_language_home_url( $language );
	}

	$page_id = is_singular( 'page' ) ? (int) get_queried_object_id() : 0;
	if ( ! $page_id && function_exists( 'is_shop' ) && is_shop() && function_exists( 'wc_get_page_id' ) ) {
		$page_id = (int) wc_get_page_id( 'shop' );
	}

	if ( $page_id > 0 ) {
		$target_id = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $page_id, $language ) : 0;
		$target_id = $target_id > 0 ? $target_id : $page_id;
		$target_uri = trim( (string) get_page_uri( $target_id ), '/' );

		if ( $target_uri ) {
			$url = trailingslashit( blue_language_home_url( $language ) ) . trailingslashit( $target_uri );

			// Retain WooCommerce account endpoints such as /orders/ or /edit-account/.
			$current_uri  = trim( (string) get_page_uri( $page_id ), '/' );
			$current_home = (string) ( wp_parse_url( blue_language_home_url( blue_language() ), PHP_URL_PATH ) ?: '/' );
			$current_base = '/' . trim( trailingslashit( $current_home ) . $current_uri, '/' ) . '/';
			$request_path = (string) ( wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ?: '/' );
			if ( str_starts_with( trailingslashit( $request_path ), $current_base ) ) {
				$endpoint = trim( substr( trailingslashit( $request_path ), strlen( $current_base ) ), '/' );
				if ( $endpoint ) {
					$url .= trailingslashit( $endpoint );
				}
			}

			$request_query = (string) ( wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_QUERY ) ?: '' );
			if ( $request_query ) {
				parse_str( $request_query, $query );
				foreach ( array( 'lang', 'blue_lang', 'add-to-cart', 'remove_item', 'undo_item', '_wpnonce', 'wc-ajax' ) as $unsafe_key ) {
					unset( $query[ $unsafe_key ] );
				}
				if ( $query ) {
					$url = add_query_arg( $query, $url );
				}
			}

			return $url;
		}
	}

	if ( $fallback ) {
		return blue_url_for_language( $fallback, $language );
	}

	$request_path = (string) ( wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ?: '/' );
	return blue_url_for_language( home_url( $request_path ), $language );
}

/** Shared product/category URLs must retain the visitor's current language. */
add_filter(
	'post_type_link',
	function ( string $url, WP_Post $post ): string {
		return in_array( $post->post_type, array( 'product', 'product_variation' ), true ) ? blue_url_for_language( $url, blue_language() ) : $url;
	},
	20,
	2
);
add_filter(
	'page_link',
	function ( string $url, int $post_id ): string {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return $url;
		}
		$page_ids = array_values(
			array_filter(
				array_map( 'intval', array( wc_get_page_id( 'shop' ), wc_get_page_id( 'cart' ), wc_get_page_id( 'checkout' ), wc_get_page_id( 'myaccount' ), wc_get_page_id( 'terms' ) ) ),
				fn( int $page_id ): bool => $page_id > 0
			)
		);
		if ( ! in_array( $post_id, $page_ids, true ) ) {
			return $url;
		}
		if ( function_exists( 'pll_get_post' ) ) {
			$translated_id = (int) pll_get_post( $post_id, blue_language() );
			if ( $translated_id && $translated_id !== $post_id ) {
				return get_permalink( $translated_id );
			}
		}
		return blue_url_for_language( $url, blue_language() );
	},
	20,
	2
);
add_filter(
	'term_link',
	function ( string $url, WP_Term $term ): string {
		return in_array( $term->taxonomy, array( 'product_cat', 'product_tag' ), true ) || str_starts_with( $term->taxonomy, 'pa_' ) ? blue_url_for_language( $url, blue_language() ) : $url;
	},
	20,
	2
);

/** Give shared products valid language-switcher and hreflang destinations. */
function blue_current_shared_object_url(): string {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		$term = get_queried_object();
		return $term instanceof WP_Term ? (string) get_term_link( $term ) : '';
	}
	return '';
}
add_filter(
	'pll_translation_url',
	function ( $url, string $language ) {
		$shared_url = blue_current_shared_object_url();
		return $shared_url ? blue_url_for_language( $shared_url, $language ) : $url;
	},
	20,
	2
);
add_filter(
	'pll_the_language_link',
	function ( $url, string $language ) {
		$shared_url = blue_current_shared_object_url();
		return $shared_url ? blue_url_for_language( $shared_url, $language ) : $url;
	},
	20,
	2
);
add_filter(
	'pll_rel_hreflang_attributes',
	function ( array $links ): array {
		$shared_url = blue_current_shared_object_url();
		if ( $shared_url ) {
			$links['en']        = blue_url_for_language( $shared_url, 'en' );
			$links['ar']        = blue_url_for_language( $shared_url, 'ar' );
			$links['x-default'] = $links['en'];
		}
		return $links;
	}
);
add_action(
	'wp_head',
	function (): void {
		if ( function_exists( 'pll_current_language' ) ) {
			return;
		}
		$shared_url = blue_current_shared_object_url();
		if ( ! $shared_url ) {
			return;
		}
		printf( "\n<link rel=\"alternate\" hreflang=\"en\" href=\"%s\">", esc_url( blue_url_for_language( $shared_url, 'en' ) ) );
		printf( "\n<link rel=\"alternate\" hreflang=\"ar\" href=\"%s\">", esc_url( blue_url_for_language( $shared_url, 'ar' ) ) );
		printf( "\n<link rel=\"alternate\" hreflang=\"x-default\" href=\"%s\">\n", esc_url( blue_url_for_language( $shared_url, 'en' ) ) );
	},
	3
);

/** Persist language across WooCommerce AJAX, cart, checkout and account requests. */
add_action(
	'wp_loaded',
	function (): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		$language = blue_language();
		if ( function_exists( 'WC' ) && WC() && WC()->session ) {
			WC()->session->set( 'blue_language', $language );
		}
		if ( ! headers_sent() && ( $_COOKIE['blue_language'] ?? '' ) !== $language ) {
			if ( function_exists( 'wc_setcookie' ) ) {
				wc_setcookie( 'blue_language', $language, time() + YEAR_IN_SECONDS, is_ssl(), true );
			} else {
				setcookie( 'blue_language', $language, time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
			}
		}
	}
);

/** Whether product-facing values should be localized in this request. */
function blue_localize_product_request(): bool {
	// WooCommerce loads the variation editor through admin-ajax.php. Keeping all
	// admin requests unlocalized ensures product data and variation dropdowns
	// always show their canonical English values. Email generation may opt in via
	// the explicit language override below.
	return ! is_admin() || ! empty( $GLOBALS['blue_language_override'] );
}

/** Product ID helper that also accepts variations. */
function blue_base_product_id( $product ): int {
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
		return 0;
	}
	if ( method_exists( $product, 'is_type' ) && $product->is_type( 'variation' ) && method_exists( $product, 'get_parent_id' ) ) {
		return (int) $product->get_parent_id();
	}
	return (int) $product->get_id();
}

/** Read a bilingual ACF product field, falling back to its legacy shared field. */
function blue_product_field( $product, string $name, mixed $fallback = '' ): mixed {
	$product_id = blue_base_product_id( $product );
	if ( ! $product_id || ! function_exists( 'get_field' ) ) {
		return $fallback;
	}
	$localized = get_field( $name . '_' . blue_language(), $product_id );
	if ( null !== $localized && false !== $localized && '' !== $localized && array() !== $localized ) {
		return $localized;
	}
	$legacy = get_field( $name, $product_id );
	return ( null === $legacy || false === $legacy || '' === $legacy || array() === $legacy ) ? $fallback : $legacy;
}

/** Translate standard WooCommerce product content from ACF. */
function blue_product_content_value( $product, string $name, string $original ): string {
	if ( ! blue_localize_product_request() ) {
		return $original;
	}
	$value = blue_product_field( $product, $name, $original );
	return is_scalar( $value ) ? (string) $value : $original;
}
add_filter( 'woocommerce_product_get_name', fn( $value, $product ) => blue_product_content_value( $product, 'product_title', (string) $value ), 20, 2 );
add_filter( 'woocommerce_product_get_short_description', fn( $value, $product ) => blue_product_content_value( $product, 'product_short_description', (string) $value ), 20, 2 );
add_filter( 'woocommerce_product_get_description', fn( $value, $product ) => blue_product_content_value( $product, 'product_description', (string) $value ), 20, 2 );

/**
 * WooCommerce's single-product excerpt template reads post_excerpt directly
 * instead of WC_Product::get_short_description(), so localize that path too.
 */
add_filter(
	'woocommerce_short_description',
	function ( string $description ): string {
		if ( ! blue_localize_product_request() || ! function_exists( 'wc_get_product' ) ) {
			return $description;
		}
		global $product;
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			$product = wc_get_product( get_the_ID() );
		}
		return $product ? blue_product_content_value( $product, 'product_short_description', $description ) : $description;
	},
	5
);
add_filter(
	'woocommerce_product_variation_get_name',
	function ( string $name, $variation ): string {
		if ( ! blue_localize_product_request() || ! method_exists( $variation, 'get_parent_id' ) ) {
			return $name;
		}
		$parent_id = (int) $variation->get_parent_id();
		$parent    = get_post( $parent_id );
		if ( ! $parent ) {
			return $name;
		}
		$translated = blue_product_field( $variation, 'product_title', $parent->post_title );
		return $translated && $translated !== $parent->post_title ? str_replace( $parent->post_title, (string) $translated, $name ) : $name;
	},
	20,
	2
);
add_filter(
	'the_title',
	function ( string $title, int $post_id ): string {
		if ( ! $post_id || 'product' !== get_post_type( $post_id ) || ! blue_localize_product_request() || ! function_exists( 'wc_get_product' ) ) {
			return $title;
		}
		$product = wc_get_product( $post_id );
		return $product ? blue_product_content_value( $product, 'product_title', $title ) : $title;
	},
	20,
	2
);

/** Global ACF dictionary for WooCommerce attribute labels. */
function blue_global_attribute_dictionary(): array {
	static $cache = array();
	$language = blue_language();
	if ( isset( $cache[ $language ] ) ) {
		return $cache[ $language ];
	}

	$dictionary = array();
	$rows       = function_exists( 'get_field' ) ? get_field( 'global_attribute_translations', 'option' ) : array();
	foreach ( is_array( $rows ) ? $rows : array() as $row ) {
		$attribute = sanitize_key( str_replace( 'attribute_', '', (string) ( $row['attribute'] ?? '' ) ) );
		$label     = trim( (string) ( $row[ 'label_' . $language ] ?? '' ) );
		if ( $attribute ) {
			$dictionary[ $attribute ] = array( 'label' => $label, 'options' => array(), 'descriptions' => array() );
		}
	}
	$cache[ $language ] = $dictionary;
	return $dictionary;
}

/** ACF dictionary for global and product-specific attribute translations. */
function blue_product_attribute_dictionary( $product ): array {
	static $cache = array();
	$product_id = blue_base_product_id( $product );
	if ( ! $product_id || ! function_exists( 'get_field' ) ) {
		return blue_global_attribute_dictionary();
	}
	$language  = blue_language();
	$cache_key = $product_id . ':' . $language;
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$dictionary = blue_global_attribute_dictionary();
	$rows       = get_field( 'product_attribute_translations', $product_id );
	foreach ( is_array( $rows ) ? $rows : array() as $row ) {
		$attribute = sanitize_key( str_replace( 'attribute_', '', (string) ( $row['attribute'] ?? '' ) ) );
		if ( ! $attribute ) {
			continue;
		}
		$label = trim( (string) ( $row[ 'label_' . $language ] ?? '' ) );
		$options      = array();
		$descriptions = array();
		foreach ( is_array( $row['options'] ?? null ) ? $row['options'] : array() as $option ) {
			$value       = sanitize_title( (string) ( $option['value'] ?? '' ) );
			$text        = trim( (string) ( $option[ 'label_' . $language ] ?? '' ) );
			$description = trim( (string) ( $option[ 'description_' . $language ] ?? '' ) );
			if ( $value && $text ) {
				$options[ $value ] = $text;
			}
			if ( $value && $description ) {
				$descriptions[ $value ] = $description;
			}
		}
		if ( ! isset( $dictionary[ $attribute ] ) ) {
			$dictionary[ $attribute ] = array( 'label' => '', 'options' => array(), 'descriptions' => array() );
		}
		if ( $label ) {
			$dictionary[ $attribute ]['label'] = $label;
		}
		$dictionary[ $attribute ]['options'] = $options + $dictionary[ $attribute ]['options'];
		$dictionary[ $attribute ]['descriptions'] = $descriptions + ( $dictionary[ $attribute ]['descriptions'] ?? array() );
	}
	$cache[ $cache_key ] = $dictionary;
	return $dictionary;
}

/** Whether a taxonomy belongs to the shared WooCommerce catalog. */
function blue_is_shared_product_taxonomy( string $taxonomy ): bool {
	return in_array( $taxonomy, array( 'product_cat', 'product_tag', 'product_shipping_class' ), true ) || str_starts_with( $taxonomy, 'pa_' );
}

/** Return a translated term-meta value for a shared catalog term. */
function blue_product_term_arabic_value( WP_Term $term, string $value ): string {
	$field_names = array();
	if ( 'product_cat' === $term->taxonomy ) {
		$field_names[] = 'name' === $value ? 'product_category_name_ar' : 'product_category_description_ar';
	} elseif ( 'product_tag' === $term->taxonomy ) {
		$field_names[] = 'name' === $value ? 'product_tag_name_ar' : 'product_tag_description_ar';
	} elseif ( str_starts_with( $term->taxonomy, 'pa_' ) ) {
		$field_names[] = 'name' === $value ? 'product_attribute_term_name_ar' : 'product_attribute_term_description_ar';
	}
	foreach ( $field_names as $field_name ) {
		$translated = trim( (string) get_term_meta( $term->term_id, $field_name, true ) );
		if ( $translated ) {
			return $translated;
		}
	}
	return '';
}

/** Localized shared product category, tag and attribute-option labels. */
function blue_product_term_name( WP_Term $term ): string {
	if ( blue_is_arabic() && blue_is_shared_product_taxonomy( $term->taxonomy ) ) {
		$name = blue_product_term_arabic_value( $term, 'name' );
		if ( $name ) {
			return (string) $name;
		}
	}
	if ( ! blue_is_arabic() && 'product_cat' === $term->taxonomy && 'mattress' === $term->slug ) {
		return 'Mattresses';
	}
	return $term->name;
}

function blue_product_term_description( WP_Term $term ): string {
	if ( blue_is_arabic() && blue_is_shared_product_taxonomy( $term->taxonomy ) ) {
		$description = blue_product_term_arabic_value( $term, 'description' );
		if ( $description ) {
			return (string) $description;
		}
	}
	return $term->description;
}

/** Description for a variation option, with product overrides before term data. */
function blue_product_attribute_option_description( $product, string $attribute, string $option, ?WP_Term $term = null ): string {
	$dictionary = blue_product_attribute_dictionary( $product );
	$key        = sanitize_key( str_replace( 'attribute_', '', $attribute ) );
	$option_key = sanitize_title( $option );
	if ( ! empty( $dictionary[ $key ]['descriptions'][ $option_key ] ) ) {
		return (string) $dictionary[ $key ]['descriptions'][ $option_key ];
	}
	if ( ! $term ) {
		return '';
	}
	// Do not leak an English term description into an Arabic option card.
	return blue_is_arabic() ? blue_product_term_arabic_value( $term, 'description' ) : $term->description;
}

/** Whether an option description only repeats its WooCommerce dimensions. */
function blue_attribute_description_is_dimensions( string $description, string $width, string $length ): bool {
	if ( ! $description || ! $width || ! $length || ! preg_match_all( '/\d+(?:[.,]\d+)?/u', $description, $matches ) || count( $matches[0] ) < 2 ) {
		return false;
	}
	$found    = array_map( fn( $value ) => (float) str_replace( ',', '.', $value ), array_slice( $matches[0], 0, 2 ) );
	$expected = array( (float) $width, (float) $length );
	sort( $found, SORT_NUMERIC );
	sort( $expected, SORT_NUMERIC );
	return abs( $found[0] - $expected[0] ) < 0.001 && abs( $found[1] - $expected[1] ) < 0.001;
}

/** Localized display label for the configured WooCommerce dimension unit. */
function blue_dimension_unit_label( string $unit ): string {
	if ( ! blue_is_arabic() ) {
		return $unit;
	}
	$units = array( 'mm' => 'ملم', 'cm' => 'سم', 'm' => 'م', 'in' => 'بوصة', 'yd' => 'ياردة' );
	return $units[ $unit ] ?? $unit;
}

function blue_product_category_label( int $product_id ): string {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! is_array( $terms ) ) {
		return '';
	}
	return implode( ', ', array_map( 'blue_product_term_name', $terms ) );
}

add_filter(
	'woocommerce_page_title',
	function ( string $title ): string {
		$term = get_queried_object();
		return $term instanceof WP_Term && blue_is_shared_product_taxonomy( $term->taxonomy ) ? blue_product_term_name( $term ) : $title;
	}
);

/** Localize catalog terms returned by WordPress and WooCommerce on the frontend. */
function blue_localized_product_term( WP_Term $term ): WP_Term {
	if ( ! blue_is_shared_product_taxonomy( $term->taxonomy ) || ! blue_is_arabic() ) {
		return $term;
	}
	$localized              = clone $term;
	$localized->name        = blue_product_term_name( $term );
	$localized->description = blue_product_term_description( $term );
	return $localized;
}

add_filter(
	'get_term',
	function ( $term ) {
		if ( ! $term instanceof WP_Term || is_admin() ) {
			return $term;
		}
		return blue_localized_product_term( $term );
	},
	20
);
add_filter(
	'get_terms',
	function ( $terms ) {
		if ( ! is_array( $terms ) || is_admin() ) {
			return $terms;
		}
		$has_shared_terms = (bool) array_filter( $terms, fn( $term ) => $term instanceof WP_Term && blue_is_shared_product_taxonomy( $term->taxonomy ) );
		if ( ! $has_shared_terms || ! blue_is_arabic() ) {
			return $terms;
		}
		return array_map( fn( $term ) => $term instanceof WP_Term ? blue_localized_product_term( $term ) : $term, $terms );
	},
	20
);
add_filter(
	'get_the_terms',
	function ( $terms, int $post_id, string $taxonomy ) {
		if ( ! is_array( $terms ) || ! blue_is_shared_product_taxonomy( $taxonomy ) || ! blue_is_arabic() ) {
			return $terms;
		}
		return array_map( fn( $term ) => $term instanceof WP_Term ? blue_localized_product_term( $term ) : $term, $terms );
	},
	20,
	3
);
add_filter(
	'woocommerce_get_breadcrumb',
	function ( array $crumbs ): array {
		if ( ! blue_is_arabic() ) {
			return $crumbs;
		}
		$terms = array();
		if ( function_exists( 'is_product' ) && is_product() ) {
			$product_terms = get_the_terms( get_queried_object_id(), 'product_cat' );
			$terms = is_array( $product_terms ) ? $product_terms : array();
		} elseif ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			$term = get_queried_object();
			$terms = $term instanceof WP_Term ? array( $term ) : array();
		}
		foreach ( $crumbs as &$crumb ) {
			foreach ( $terms as $term ) {
				if ( isset( $crumb[0] ) && $crumb[0] === $term->name ) {
					$crumb[0] = blue_product_term_name( $term );
				}
			}
		}
		unset( $crumb );
		return $crumbs;
	}
);
add_filter(
	'woocommerce_attribute_label',
	function ( string $label, string $name, $product = null ): string {
		if ( ! blue_localize_product_request() ) {
			return $label;
		}
		$dictionary = blue_product_attribute_dictionary( $product );
		$key        = sanitize_key( str_replace( 'attribute_', '', $name ) );
		return ! empty( $dictionary[ $key ]['label'] ) ? $dictionary[ $key ]['label'] : blue_text( $label );
	},
	20,
	3
);
add_filter(
	'woocommerce_variation_option_name',
	function ( string $label, $term = null, string $attribute = '', $product = null ): string {
		if ( ! blue_localize_product_request() ) {
			return $label;
		}
		if ( ! $product && isset( $GLOBALS['product'] ) ) {
			$product = $GLOBALS['product'];
		}
		$dictionary = blue_product_attribute_dictionary( $product );
		$key        = sanitize_key( str_replace( 'attribute_', '', $attribute ) );
		$value      = $term instanceof WP_Term ? $term->slug : $label;
		$option_key = sanitize_title( (string) $value );
		if ( ! empty( $dictionary[ $key ]['options'][ $option_key ] ) ) {
			return $dictionary[ $key ]['options'][ $option_key ];
		}
		return $term instanceof WP_Term ? blue_product_term_name( $term ) : $label;
	},
	20,
	4
);

/** Translate selected common WooCommerce UI strings from the JSON dictionary. */
add_filter(
	'gettext',
	function ( string $translation, string $original, string $domain ): string {
		if ( ! blue_is_arabic() || ( is_admin() && ! wp_doing_ajax() && empty( $GLOBALS['blue_language_override'] ) ) ) {
			return $translation;
		}
		if ( ! in_array( $domain, array( 'woocommerce', 'blue-mattress' ), true ) ) {
			return $translation;
		}
		$catalog = blue_translation_catalog();
		return $catalog[ $original ] ?? $translation;
	},
	20,
	3
);

/** Guarantee Arabic messages for WooCommerce's dynamic cart/variation scripts. */
add_filter(
	'woocommerce_get_script_data',
	function ( $params, string $handle ) {
		if ( ! blue_is_arabic() || ! is_array( $params ) ) {
			return $params;
		}
		if ( 'wc-add-to-cart' === $handle ) {
			$params['i18n_view_cart'] = blue_text( 'View cart', 'عرض السلة' );
		}
		if ( 'wc-add-to-cart-variation' === $handle ) {
			$params['i18n_no_matching_variations_text'] = blue_text( 'Sorry, no products matched your selection. Please choose a different combination.', 'عذرًا، لا توجد منتجات تطابق اختيارك. يرجى اختيار مجموعة مختلفة.' );
			$params['i18n_make_a_selection_text']       = blue_text( 'Please select some product options before adding this product to your cart.', 'يرجى اختيار خيارات المنتج قبل إضافته إلى السلة.' );
			$params['i18n_unavailable_text']            = blue_text( 'Sorry, this product is unavailable. Please choose a different combination.', 'عذرًا، هذا المنتج غير متوفر. يرجى اختيار مجموعة مختلفة.' );
			$params['i18n_reset_alert_text']            = blue_text( 'Your selection has been reset. Please select some product options before adding this product to your cart.', 'تمت إعادة ضبط اختيارك. يرجى اختيار خيارات المنتج قبل إضافته إلى السلة.' );
		}
		return $params;
	},
	20,
	2
);

/** Store the checkout language on the single shared WooCommerce order. */
add_action(
	'woocommerce_checkout_create_order',
	function ( WC_Order $order ): void {
		$order->update_meta_data( '_blue_language', blue_language() );
	},
	10
);
add_action(
	'woocommerce_store_api_checkout_update_order_from_request',
	function ( WC_Order $order ): void {
		$order->update_meta_data( '_blue_language', blue_language() );
	},
	10
);

/** Switch customer emails to the language saved with their order. */
add_filter(
	'woocommerce_allow_switching_email_locale',
	function ( bool $allow, $email ): bool {
		if ( ! is_object( $email ) || ! method_exists( $email, 'is_customer_email' ) || ! $email->is_customer_email() || ! isset( $email->object ) || ! $email->object instanceof WC_Order ) {
			return $allow;
		}
		$language = $email->object->get_meta( '_blue_language' );
		if ( ! in_array( $language, array( 'en', 'ar' ), true ) ) {
			return $allow;
		}
		blue_set_language_override( $language );
		$GLOBALS['blue_email_locale_switched'][ spl_object_id( $email ) ] = switch_to_locale( 'ar' === $language ? 'ar' : 'en_US' );
		return false;
	},
	20,
	2
);
add_filter(
	'woocommerce_allow_restoring_email_locale',
	function ( bool $allow, $email ): bool {
		$key = is_object( $email ) ? spl_object_id( $email ) : 0;
		if ( $key && isset( $GLOBALS['blue_email_locale_switched'][ $key ] ) ) {
			if ( $GLOBALS['blue_email_locale_switched'][ $key ] ) {
				restore_previous_locale();
			}
			unset( $GLOBALS['blue_email_locale_switched'][ $key ] );
			blue_set_language_override( null );
			return false;
		}
		return $allow;
	},
	20,
	2
);

/** Create missing Arabic translations of WooCommerce's four core pages. */
function blue_ensure_woocommerce_page_translations(): void {
	if ( ! function_exists( 'wc_get_page_id' ) || ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) {
		return;
	}
	$languages = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array();
	if ( ! is_array( $languages ) || ! in_array( 'ar', $languages, true ) ) {
		return;
	}

	$pages = array(
		'shop'      => 'المتجر',
		'cart'      => 'السلة',
		'checkout'  => 'إتمام الطلب',
		'myaccount' => 'حسابي',
	);
	foreach ( $pages as $page_key => $arabic_title ) {
		$source_id = (int) wc_get_page_id( $page_key );
		$source    = $source_id > 0 ? get_post( $source_id ) : null;
		if ( ! $source instanceof WP_Post ) {
			continue;
		}
		$source_language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $source_id, 'slug' ) : '';
		if ( ! $source_language ) {
			$source_language = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : 'en';
			pll_set_post_language( $source_id, $source_language ?: 'en' );
		}
		$arabic_id = (int) pll_get_post( $source_id, 'ar' );
		if ( ! $arabic_id ) {
			$slug     = $source->post_name . '-ar';
			$existing = get_page_by_path( $slug, OBJECT, 'page' );
			$arabic_id = $existing instanceof WP_Post ? $existing->ID : (int) wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $arabic_title,
					'post_name'    => $slug,
					'post_content' => $source->post_content,
					'post_excerpt' => $source->post_excerpt,
				)
			);
		}
		if ( ! $arabic_id ) {
			continue;
		}
		pll_set_post_language( $arabic_id, 'ar' );
		$template = get_post_meta( $source_id, '_wp_page_template', true );
		if ( $template ) {
			update_post_meta( $arabic_id, '_wp_page_template', $template );
		}
		$translations = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( $source_id ) : array();
		$translations[ $source_language ?: 'en' ] = $source_id;
		$translations['ar'] = $arabic_id;
		pll_save_post_translations( $translations );
	}
	update_option( 'blue_woocommerce_pages_language_version', BLUE_THEME_VERSION, false );
}
add_action(
	'admin_init',
	function (): void {
		if ( current_user_can( 'edit_pages' ) && BLUE_THEME_VERSION !== get_option( 'blue_woocommerce_pages_language_version' ) ) {
			blue_ensure_woocommerce_page_translations();
		}
	},
	30
);

/** Flush the native fallback routes only when this layer changes. */
add_action(
	'admin_init',
	function (): void {
		if ( current_user_can( 'manage_options' ) && BLUE_THEME_VERSION !== get_option( 'blue_bilingual_rewrite_version' ) ) {
			flush_rewrite_rules( false );
			update_option( 'blue_bilingual_rewrite_version', BLUE_THEME_VERSION, false );
		}
	}
);
