<?php
/**
 * Shared helpers.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Get an ACF option safely, supporting optional language-specific field names. */
function blue_option( string $name, mixed $default = '' ): mixed {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$localized = get_field( $name . '_' . blue_language(), 'option' );
	if ( null !== $localized && false !== $localized && '' !== $localized ) {
		return $localized;
	}

	$value = get_field( $name, 'option' );
	return ( null === $value || false === $value || '' === $value ) ? $default : $value;
}

/** Get a page field, falling back to a supplied design default. */
function blue_field( string $name, mixed $default = '', int|false $post_id = false ): mixed {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name, $post_id ?: false );
		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	return $default;
}

/** Resolve a translated page template URL. */
function blue_page_url( string $template, string $fallback = '/' ): string {
	$args = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
			'lang'           => blue_language(),
		);
	$pages = get_posts( $args );
	if ( ! $pages ) {
		unset( $args['lang'] );
		$pages = get_posts( $args );
	}

	if ( $pages ) {
		return get_permalink( $pages[0] );
	}

	return blue_home_url( $fallback );
}

/** Build the language switcher from Polylang without failing when it is inactive. */
function blue_language_switcher(): void {
	if ( function_exists( 'pll_the_languages' ) ) {
		$languages = pll_the_languages( array( 'raw' => 1 ) );
		if ( $languages ) {
			echo '<div class="blue-language-switcher" aria-label="' . esc_attr__( 'Language', 'blue-mattress' ) . '">';
			foreach ( $languages as $language ) {
				$language['url'] = blue_current_url_for_language( $language['slug'], (string) ( $language['url'] ?? '' ) );
				printf(
					'<a hreflang="%1$s" lang="%1$s" href="%2$s" class="%3$s">%4$s</a>',
					esc_attr( $language['slug'] ),
					esc_url( $language['url'] ),
					$language['current_lang'] ? 'is-current' : '',
					esc_html( $language['name'] )
				);
			}
			echo '</div>';
			return;
		}
	}

	$current_url = blue_current_shared_object_url();
	if ( ! $current_url ) {
		$request_path = (string) ( wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ?: '/' );
		$current_url  = home_url( $request_path );
	}
	echo '<div class="blue-language-switcher" aria-label="' . esc_attr( blue_text( 'Language', 'اللغة' ) ) . '">';
	printf( '<a hreflang="en" lang="en" href="%1$s" class="%2$s">English</a>', esc_url( blue_current_url_for_language( 'en', $current_url ) ), blue_is_arabic() ? '' : 'is-current' );
	printf( '<a hreflang="ar" lang="ar" href="%1$s" class="%2$s">العربية</a>', esc_url( blue_current_url_for_language( 'ar', $current_url ) ), blue_is_arabic() ? 'is-current' : '' );
	echo '</div>';
}

/** Return an image URL from an ACF image field in any common return format. */
function blue_image_url( mixed $image, string $fallback = '' ): string {
	if ( is_array( $image ) && ! empty( $image['url'] ) ) {
		return (string) $image['url'];
	}
	if ( is_numeric( $image ) ) {
		return (string) wp_get_attachment_image_url( (int) $image, 'full' );
	}
	if ( is_string( $image ) && $image ) {
		return $image;
	}
	return $fallback;
}

/**
 * Return the editable cutaway animation for a mattress.
 *
 * ACF remains the source of truth. The bundled animation is only a design-pack
 * fallback, so a merchant can replace it per product without editing code.
 */
function blue_product_cutaway_video( WC_Product|int|null $product = null ): string {
	if ( is_int( $product ) ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product ) : null;
	}
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	if ( function_exists( 'get_field' ) ) {
		$video = get_field( 'product_cutaway_video', $product->get_id() );
		$url   = blue_image_url( $video );
		if ( $url ) {
			return $url;
		}
	}

	$identity = sanitize_title( $product->get_slug() . '-' . $product->get_name() );
	$aliases  = array(
		'horizon' => array( 'horizon', 'blue-1', 'blue1' ),
		'cloud'   => array( 'cloud', 'skin-care', 'skincare' ),
		'summit'  => array( 'summit', 'pure-latex', 'purelatex' ),
		'royal'   => array( 'royal', 'luna' ),
		'haven'   => array( 'haven', 'sky' ),
		'retro'   => array( 'retro' ),
		'comfy'   => array( 'comfy' ),
		'loft'    => array( 'loft', 'almanam', 'al-manam' ),
	);
	foreach ( $aliases as $asset => $needles ) {
		foreach ( $needles as $needle ) {
			if ( str_contains( $identity, $needle ) ) {
				$file = BLUE_THEME_DIR . '/assets/img/' . $asset . '-cut.mp4';
				return file_exists( $file ) ? BLUE_THEME_URI . '/assets/img/' . $asset . '-cut.mp4' : '';
			}
		}
	}

	return '';
}

/** Social option rows with a safe default shape. */
function blue_social_links(): array {
	$links = blue_option( 'social_links', array() );
	return is_array( $links ) ? $links : array();
}

/** Resolve a social icon from its selected value, label or URL. */
function blue_social_icon_key( array $social ): string {
	$key = sanitize_key( (string) ( $social['icon'] ?? '' ) );
	$haystack = strtolower( (string) ( $social['label'] ?? '' ) . ' ' . (string) ( $social['url'] ?? '' ) );
	$matches = array(
		'whatsapp' => array( 'whatsapp', 'wa.me' ),
		'instagram' => array( 'instagram' ),
		'facebook' => array( 'facebook', 'fb.com' ),
		'linkedin' => array( 'linkedin' ),
		'tiktok' => array( 'tiktok' ),
		'snapchat' => array( 'snapchat' ),
		'youtube' => array( 'youtube', 'youtu.be' ),
		'x' => array( 'twitter.com', 'x.com' ),
	);
	foreach ( $matches as $candidate => $needles ) {
		foreach ( $needles as $needle ) {
			if ( str_contains( $haystack, $needle ) ) {
				return $candidate;
			}
		}
	}
	return $key ?: 'link';
}

/** Return fixed, currentColor SVG markup for a social icon. */
function blue_social_icon_svg( string $icon ): string {
	$icons = array(
		'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>',
		'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4.5c-1-.2-2-.3-3-.3-3 0-5 1.8-5 5.2V12H6v4h3v6h4v-6h3.2l.6-4H13V9.8c0-1.2.4-1.8 1-1.8Z" fill="currentColor" stroke="none"/></svg>',
		'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="9" width="4" height="12"/><circle cx="5" cy="5" r="2" fill="currentColor" stroke="none"/><path d="M11 21V9h4v2c1-1.5 2.4-2.4 4.2-2.4 2.8 0 3.8 1.9 3.8 5.1V21h-4v-6.5c0-1.5-.4-2.5-1.8-2.5-1.6 0-2.2 1.1-2.2 3.2V21Z" fill="currentColor" stroke="none"/></svg>',
		'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3v11.2a4.7 4.7 0 1 1-4-4.6v3.3a1.7 1.7 0 1 0 1 1.5V3Zm0 0c.5 2.7 2.1 4.3 4.8 4.8"/></svg>',
		'snapchat' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3c3 0 4.6 2.2 4.6 5.2 0 1.4.4 2.4 1.8 3.2.7.4.6 1.2-.2 1.5-.5.2-1 .4-1.2.8-.4.7.3 1.5 1.3 1.9-.8.8-1.8.9-2.7.8-.8 1.1-2 1.7-3.6 1.7s-2.8-.6-3.6-1.7c-.9.1-1.9 0-2.7-.8 1-.4 1.7-1.2 1.3-1.9-.2-.4-.7-.6-1.2-.8-.8-.3-.9-1.1-.2-1.5 1.4-.8 1.8-1.8 1.8-3.2C7.4 5.2 9 3 12 3Z"/></svg>',
		'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12c0-2.5-.2-4.1-.6-4.8-.4-.7-1-1.2-1.8-1.4C17.2 5.4 15 5.3 12 5.3s-5.2.1-6.6.5c-.8.2-1.4.7-1.8 1.4C3.2 7.9 3 9.5 3 12s.2 4.1.6 4.8c.4.7 1 1.2 1.8 1.4 1.4.4 3.6.5 6.6.5s5.2-.1 6.6-.5c.8-.2 1.4-.7 1.8-1.4.4-.7.6-2.3.6-4.8Z"/><path d="m10 9 5 3-5 3Z" fill="currentColor" stroke="none"/></svg>',
		'x' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4 19 20M19 4 5 20"/></svg>',
		'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.4-4.7A8.5 8.5 0 1 1 20.5 11.6Z"/><path d="M8.2 7.7c.3-.5.5-.5.9-.5h.4c.2 0 .4.1.5.4l.8 2c.1.3 0 .5-.2.7l-.7.8c-.2.2-.1.5 0 .7.7 1.2 1.7 2.2 3 2.8.3.1.5.1.7-.1l.9-1c.2-.3.5-.3.8-.2l1.9.9c.3.1.4.3.4.6 0 .6-.3 1.3-.8 1.7-.6.5-1.4.8-2.2.7-1.4-.2-3.1-.9-4.8-2.4-1.4-1.3-2.5-2.8-2.9-4.2-.4-1.1 0-2.1.4-2.9Z"/></svg>',
	);
	return $icons[ $icon ] ?? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 14 21 3m0 0h-7m7 0v7M18 13v7H4V6h7"/></svg>';
}

/** Fixed SVG artwork for the editable homepage benefit icon choices. */
function blue_benefit_icon_svg( string $icon ): string {
	$icons = array(
		'trial'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.5 15.5A7.5 7.5 0 0 1 8.5 6.5a7 7 0 1 0 9 9Z"/><path d="m17.8 3.2.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8Z" fill="currentColor" stroke="none"/></svg>',
		'delivery' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="6.5" width="11" height="9.5" rx="1.2"/><path d="M13.5 9.5h3.6l3.4 3.4V16H14"/><circle cx="7" cy="17.8" r="1.8"/><circle cx="16.6" cy="17.8" r="1.8"/></svg>',
		'warranty' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 7 2.8v5.4c0 4.6-3 7.9-7 9.8-4-1.9-7-5.2-7-9.8V5.8Z"/><path d="m9 11.8 2.2 2.2 4.3-4.4"/></svg>',
		'payments' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="7" height="7" rx="1.6"/><rect x="13" y="4" width="7" height="7" rx="1.6"/><rect x="4" y="13" width="7" height="7" rx="1.6"/><rect x="13" y="13" width="7" height="7" rx="1.6"/></svg>',
	);
	return $icons[ $icon ] ?? $icons['trial'];
}
