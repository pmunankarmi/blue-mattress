<?php
/**
 * Theme setup, assets, Customizer, and Polylang registration.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function (): void {
		load_theme_textdomain( 'blue-mattress', BLUE_THEME_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 220, 'flex-height' => true, 'flex-width' => true ) );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'blue-mattress' ),
				'footer'  => __( 'Footer Menu', 'blue-mattress' ),
			)
		);
	},
	5
);

/** Apply the reference navigation styling to menus created in wp-admin. */
add_filter(
	'nav_menu_link_attributes',
	function ( array $atts, WP_Post $menu_item, stdClass $args ): array {
		if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
			return $atts;
		}

		$classes = preg_split( '/\s+/', (string) ( $atts['class'] ?? '' ), -1, PREG_SPLIT_NO_EMPTY ) ?: array();
		$classes[] = 'nav-link';
		$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
		$item_url  = untrailingslashit( (string) $menu_item->url );
		if ( $shop_url && untrailingslashit( $shop_url ) === $item_url ) {
			$classes[] = 'nav-store';
		}
		$atts['class'] = implode( ' ', array_unique( $classes ) );
		return $atts;
	},
	10,
	3
);

/** Use the classic editor throughout this classic PHP theme. */
add_filter( 'use_block_editor_for_post', '__return_false', 100 );
add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );
add_filter( 'use_widgets_block_editor', '__return_false', 100 );
add_action(
	'wp_enqueue_scripts',
	function (): void {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	100
);

/** Allow trusted administrators to upload sanitized SVG artwork. */
add_filter(
	'upload_mimes',
	function ( array $mimes ): array {
		if ( current_user_can( 'manage_options' ) ) {
			$mimes['svg'] = 'image/svg+xml';
		}
		return $mimes;
	}
);

add_filter(
	'wp_check_filetype_and_ext',
	function ( array $data, string $file, string $filename, ?array $mimes ): array {
		if ( current_user_can( 'manage_options' ) && 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
			$data['ext']             = 'svg';
			$data['type']            = 'image/svg+xml';
			$data['proper_filename'] = $filename;
		}
		return $data;
	},
	10,
	4
);

add_filter(
	'wp_handle_upload_prefilter',
	function ( array $file ): array {
		$extension = strtolower( pathinfo( (string) ( $file['name'] ?? '' ), PATHINFO_EXTENSION ) );
		if ( 'svg' !== $extension ) {
			return $file;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			$file['error'] = __( 'Only administrators may upload SVG files.', 'blue-mattress' );
			return $file;
		}

		$svg = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $svg || false === stripos( $svg, '<svg' ) || preg_match( '/<(?:script|foreignObject|iframe|object|embed|audio|video)\b/i', $svg ) || preg_match( '/\son[a-z]+\s*=|(?:href|src)\s*=\s*["\']\s*(?:javascript:|data:text\/html)/i', $svg ) ) {
			$file['error'] = __( 'This SVG contains unsupported or unsafe markup.', 'blue-mattress' );
		}
		return $file;
	}
);

add_action(
	'customize_register',
	function ( WP_Customize_Manager $customizer ): void {
		$customizer->add_setting(
			'site_logo',
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$customizer->add_control(
			new WP_Customize_Media_Control(
				$customizer,
				'site_logo',
				array(
					'label'       => __( 'Site logo', 'blue-mattress' ),
					'description' => __( 'Used in the header and footer. Falls back to the WordPress Custom Logo, then the bundled brand mark.', 'blue-mattress' ),
					'section'     => 'title_tagline',
					'mime_type'   => 'image',
				)
			)
		);
	}
);

/** Return the exact Customizer logo requested by the theme. */
function blue_site_logo( string $class = 'brand-logo' ): string {
	$logo_id = (int) get_theme_mod( 'site_logo', 0 );
	if ( ! $logo_id ) {
		$logo_id = (int) get_theme_mod( 'custom_logo', 0 );
	}
	if ( $logo_id ) {
		$image = wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => $class, 'alt' => get_bloginfo( 'name' ) ) );
		if ( $image ) {
			return $image;
		}
	}

	return sprintf( '<img class="%1$s" src="%2$s" width="438" height="250" alt="%3$s">', esc_attr( $class ), esc_url( BLUE_THEME_URI . '/assets/img/logo-color.png' ), esc_attr( get_bloginfo( 'name' ) ) );
}

add_action(
	'wp_enqueue_scripts',
	function (): void {
		wp_enqueue_style( 'blue-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Cairo:wght@300..900&display=swap', array(), null );
		wp_enqueue_style( 'blue-main', BLUE_THEME_URI . '/assets/css/main.css', array(), BLUE_THEME_VERSION );
		wp_enqueue_style( 'blue-anatomy', BLUE_THEME_URI . '/assets/css/anatomy.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		wp_enqueue_style( 'blue-dark', BLUE_THEME_URI . '/assets/css/dark.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		wp_enqueue_style( 'blue-wp', BLUE_THEME_URI . '/assets/css/wordpress.css', array( 'blue-main' ), BLUE_THEME_VERSION );

		if ( is_page_template( 'page-home.php' ) ) {
			wp_enqueue_style( 'blue-home', BLUE_THEME_URI . '/assets/css/home.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		}
		if ( is_page_template( 'page-contact.php' ) ) {
			wp_enqueue_style( 'blue-contact', BLUE_THEME_URI . '/assets/css/contact.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		}
		if ( is_page_template( 'page-stark.php' ) ) {
			wp_enqueue_style( 'blue-stark', BLUE_THEME_URI . '/assets/css/stark.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		}
		if ( is_page_template( 'page-our-story.php' ) ) {
			wp_enqueue_style( 'blue-story', BLUE_THEME_URI . '/assets/css/story.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		}
		if ( is_page_template( 'page-legal.php' ) ) {
			wp_enqueue_style( 'blue-legal', BLUE_THEME_URI . '/assets/css/legal.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		}
		if ( is_page_template( 'page-mattress-finder.php' ) ) {
			wp_enqueue_style( 'blue-selector', BLUE_THEME_URI . '/assets/css/selector.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		}
		if ( class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_account_page() ) ) {
			wp_enqueue_style( 'blue-shop', BLUE_THEME_URI . '/assets/css/shop.css', array( 'blue-main' ), BLUE_THEME_VERSION );
			wp_enqueue_style( 'blue-product', BLUE_THEME_URI . '/assets/css/product.css', array( 'blue-main' ), BLUE_THEME_VERSION );
		}
		if ( class_exists( 'WooCommerce' ) && is_account_page() ) {
			wp_enqueue_style( 'blue-account', BLUE_THEME_URI . '/assets/css/account.css', array( 'blue-main', 'blue-wp' ), BLUE_THEME_VERSION );
			wp_enqueue_script( 'blue-account', BLUE_THEME_URI . '/assets/js/account.js', array(), BLUE_THEME_VERSION, true );
		}
		if ( class_exists( 'WooCommerce' ) && ( is_cart() || is_checkout() ) ) {
			wp_enqueue_style( 'blue-commerce-flow', BLUE_THEME_URI . '/assets/css/commerce.css', array( 'blue-main', 'blue-wp' ), BLUE_THEME_VERSION );
			if ( is_checkout() && ! is_order_received_page() ) {
				wp_enqueue_script( 'blue-paymob-bridge', BLUE_THEME_URI . '/assets/js/paymob-bridge.js', array( 'jquery' ), BLUE_THEME_VERSION, true );
			}
		}

		wp_enqueue_script( 'blue-gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );
		wp_enqueue_script( 'blue-scroll-trigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array( 'blue-gsap' ), '3.12.5', true );
		wp_enqueue_script( 'blue-theme', BLUE_THEME_URI . '/assets/js/theme.js', array( 'blue-scroll-trigger' ), BLUE_THEME_VERSION, true );
		wp_localize_script( 'blue-theme', 'BlueTheme', blue_frontend_data() );
		wp_enqueue_script( 'blue-anatomy', BLUE_THEME_URI . '/assets/js/anatomy-wp.js', array( 'blue-theme' ), BLUE_THEME_VERSION, true );

		if ( is_page_template( 'page-home.php' ) ) {
			wp_enqueue_script( 'blue-home', BLUE_THEME_URI . '/assets/js/home.js', array( 'blue-theme' ), BLUE_THEME_VERSION, true );
		}
		if ( class_exists( 'WooCommerce' ) && is_product() ) {
			wp_enqueue_script( 'blue-product-wp', BLUE_THEME_URI . '/assets/js/product-wp.js', array( 'jquery', 'wc-add-to-cart-variation', 'blue-theme' ), BLUE_THEME_VERSION, true );
		}
		if ( is_page_template( 'page-mattress-finder.php' ) ) {
			wp_enqueue_script( 'blue-finder', BLUE_THEME_URI . '/assets/js/finder.js', array( 'blue-theme' ), BLUE_THEME_VERSION, true );
		}
		if ( is_page_template( 'page-contact.php' ) ) {
			wp_enqueue_script( 'blue-contact', BLUE_THEME_URI . '/assets/js/contact-wp.js', array( 'blue-theme' ), BLUE_THEME_VERSION, true );
		}
	}
);

add_filter(
	'body_class',
	function ( array $classes ): array {
		$classes[] = blue_is_arabic() ? 'blue-ar' : 'blue-en';
		if ( is_page_template( 'page-home.php' ) ) {
			$classes[] = 'blue-overlay-header';
		}
		if ( is_page_template( 'page-mattress-finder.php' ) ) {
			$classes[] = 'blue-finder-page';
		}
		if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
			$classes[] = 'blue-shop-archive';
		}
		if ( function_exists( 'is_product' ) && is_product() ) {
			$classes[] = 'blue-single-product';
		}
		if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
			$classes[] = 'blue-commerce-flow';
		}
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$classes[] = 'blue-account-page';
			$classes[] = is_user_logged_in() ? 'blue-account-user' : 'blue-account-guest';
		}
		return $classes;
	}
);

/** Reliable bilingual defaults keep the Anatomy section usable before ACF is populated. */
/** Assemble the product data consumed by the homepage/finder interactions. */
function blue_frontend_data(): array {
	$products = array();
	$feel_defaults = array(
		'horizon' => 6,
		'cloud'   => 3,
		'summit'  => 8,
		'royal'   => 5,
		'retro'   => 6,
		'comfy'   => 5,
		'loft'    => 6,
		'haven'   => 5,
	);
	if ( function_exists( 'wc_get_products' ) ) {
		foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => 50, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $product ) {
			$category_terms = get_the_terms( $product->get_id(), 'product_cat' );
			$category       = 'mattresses';
			if ( $category_terms && ! is_wp_error( $category_terms ) ) {
				$category = $category_terms[0]->slug;
				foreach ( $category_terms as $category_term ) {
					$canonical_term = $category_term;
					if ( function_exists( 'pll_get_term' ) ) {
						$english_term_id = pll_get_term( $category_term->term_id, 'en' );
						$english_term    = $english_term_id ? get_term( $english_term_id, 'product_cat' ) : false;
						if ( $english_term && ! is_wp_error( $english_term ) ) {
							$canonical_term = $english_term;
						}
					}
					$category = $canonical_term->slug;
					if ( in_array( $category, array( 'mattresses', 'pillows', 'toppers', 'bedding' ), true ) ) {
						break;
					}
				}
			}
			$image          = wp_get_attachment_image_url( $product->get_image_id(), 'large' ) ?: wc_placeholder_img_src( 'large' );
			$gallery        = array_values( array_filter( array_map( fn( $id ) => wp_get_attachment_image_url( $id, 'large' ), $product->get_gallery_image_ids() ) ) );
			$feel           = function_exists( 'get_field' ) ? get_field( 'product_feel', $product->get_id() ) : null;
			if ( ! is_numeric( $feel ) ) {
				foreach ( array( 'pa_firmness', 'firmness', 'pa_feel' ) as $attribute ) {
					$value = $product->get_attribute( $attribute );
					if ( $value && preg_match( '/(?:^|\D)(10|[1-9])(?:\D|$)/', $value, $matches ) ) {
						$feel = (int) $matches[1];
						break;
					}
				}
			}
			if ( ! is_numeric( $feel ) ) {
				$feel = $feel_defaults[ $product->get_slug() ] ?? ( 'mattresses' === $category ? 5 : null );
			}
			$kind           = blue_product_field( $product, 'product_kind', '' );
			$layers         = function_exists( 'get_field' ) ? get_field( 'product_layers', $product->get_id() ) : array();
			$layer_data     = array();
			foreach ( is_array( $layers ) ? $layers : array() as $layer ) {
				$title        = blue_is_arabic() && ! empty( $layer['title_ar'] ) ? $layer['title_ar'] : ( $layer['title'] ?? '' );
				$description  = blue_is_arabic() && ! empty( $layer['description_ar'] ) ? $layer['description_ar'] : ( $layer['description'] ?? '' );
				$layer_data[] = array( (string) $title, (string) $description );
			}
			$feel_label = (string) blue_product_field( $product, 'product_feel_label', '' );
			if ( ! $feel_label && is_numeric( $feel ) ) {
				$feel_label = (int) $feel <= 4 ? blue_text( 'Plush', 'ناعمة' ) : ( (int) $feel <= 7 ? blue_text( 'Balanced', 'متوازنة' ) : blue_text( 'Firm', 'صلبة' ) );
			}
			$products[] = array(
				'id'        => $product->get_slug(),
				'productId' => $product->get_id(),
				'cat'       => $category,
				'name'      => $product->get_name(),
				'kind'      => $kind ?: blue_product_category_label( $product->get_id() ),
				'tag'       => wp_strip_all_tags( $product->get_short_description() ),
				'badge'     => $product->is_on_sale() ? blue_text( 'Sale', 'تخفيض' ) : '',
				'feel'      => is_numeric( $feel ) ? max( 1, min( 10, (int) $feel ) ) : null,
				'feelLabel' => $feel_label,
				'rating'    => (float) $product->get_average_rating(),
				'reviews'   => (int) $product->get_review_count(),
				'sale'      => $product->is_on_sale(),
				'img'       => $image,
				'gallery'   => $gallery,
				'cut'       => blue_image_url( function_exists( 'get_field' ) ? get_field( 'product_cutaway_image', $product->get_id() ) : '', $image ),
				'cutVideo'  => blue_product_cutaway_video( $product ),
				'prices'    => array( 'default' => (float) wc_get_price_to_display( $product ) ),
				'layers'    => $layer_data,
				'story'     => wp_strip_all_tags( $product->get_description() ),
				'url'       => get_permalink( $product->get_id() ),
				'addUrl'    => $product->is_type( 'simple' ) ? $product->add_to_cart_url() : get_permalink( $product->get_id() ),
			);
		}
	}

	$categories = array();
	if ( taxonomy_exists( 'product_cat' ) ) {
		$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0 ) );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$category_id = $term->slug;
				if ( function_exists( 'pll_get_term' ) ) {
					$english_term_id = pll_get_term( $term->term_id, 'en' );
					$english_term    = $english_term_id ? get_term( $english_term_id, 'product_cat' ) : false;
					if ( $english_term && ! is_wp_error( $english_term ) ) {
						$category_id = $english_term->slug;
					}
				}
				$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				$fallback      = current( array_filter( $products, fn( $item ) => $item['cat'] === $category_id ) );
				$categories[] = array(
					'id'    => $category_id,
					'label' => blue_product_term_name( $term ),
					'blurb' => wp_strip_all_tags( blue_product_term_description( $term ) ),
					'img'   => wp_get_attachment_image_url( $thumbnail_id, 'large' ) ?: ( $fallback['img'] ?? wc_placeholder_img_src( 'large' ) ),
					'url'   => get_term_link( $term ),
				);
			}
		}
	}

	$reviews = array();
	foreach ( get_comments( array( 'status' => 'approve', 'type' => 'review', 'number' => 12 ) ) as $comment ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $comment->comment_post_ID ) : false;
		if ( ! $product ) {
			continue;
		}
		$reviews[] = array(
			'stars'   => max( 1, (int) get_comment_meta( $comment->comment_ID, 'rating', true ) ),
			'title'   => blue_text( 'Verified customer review', 'تقييم عميل موثّق' ),
			'body'    => wp_strip_all_tags( $comment->comment_content ),
			'name'    => $comment->comment_author,
			'city'    => '',
			'product' => $product->get_name(),
		);
	}

	$anatomy_rows = function_exists( 'get_field' ) && is_page() ? get_field( 'anatomy_layers', get_queried_object_id() ) : array();
	$anatomy_rows = is_array( $anatomy_rows ) ? $anatomy_rows : array();
	$anatomy_layers = array();
	foreach ( $anatomy_rows as $layer ) {
		$title       = is_array( $layer ) ? ( $layer['title'] ?? ( $layer[0] ?? '' ) ) : '';
		$description = is_array( $layer ) ? ( $layer['description'] ?? ( $layer[1] ?? '' ) ) : '';
		$visual      = is_array( $layer ) ? sanitize_key( (string) ( $layer['visual_style'] ?? '' ) ) : '';
		$image_id    = is_array( $layer ) ? (int) ( $layer['image'] ?? 0 ) : 0;
		$image_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
		if ( $title ) {
			$anatomy_layers[] = array(
				'title'       => wp_strip_all_tags( (string) $title ),
				'description' => wp_strip_all_tags( (string) $description ),
				'visual'      => $visual,
				'image'       => $image_url ? esc_url_raw( $image_url ) : '',
			);
		}
	}

	$home_content = array(
		'zones'                  => array(
			'plush'    => (string) blue_field( 'feel_zone_plush' ),
			'balanced' => (string) blue_field( 'feel_zone_balanced' ),
			'firm'     => (string) blue_field( 'feel_zone_firm' ),
		),
		'feelRecommendation'     => (string) blue_field( 'feel_recommendation_template' ),
		'feelRecommendationFallback' => (string) blue_field( 'feel_recommendation_fallback' ),
		'anatomyAlt'             => (string) blue_field( 'anatomy_alt_text' ),
		'reviewDotLabel'         => (string) blue_field( 'reviews_dot_label' ),
	);

	return array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'homeUrl'      => blue_home_url( '/' ),
		'shopUrl'      => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : blue_home_url( '/shop/' ),
		'cartUrl'      => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : blue_home_url( '/cart/' ),
		'checkoutUrl'  => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : blue_home_url( '/checkout/' ),
		'contactUrl'   => blue_page_url( 'page-contact.php', '/contact/' ),
		'finderUrl'    => blue_page_url( 'page-mattress-finder.php', '/mattress-finder/' ),
		'starkUrl'     => blue_page_url( 'page-stark.php', '/stark/' ),
		'storyUrl'     => blue_page_url( 'page-our-story.php', '/our-story/' ),
		'language'     => blue_language(),
		'isArabic'     => blue_is_arabic(),
		'products'     => $products,
		'categories'   => $categories,
		'reviews'      => $reviews,
		'anatomy'      => array( 'layers' => $anatomy_layers ),
		'home'         => $home_content,
		'cartCount'    => function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
		'currency'     => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : 'SAR',
		'strings'      => blue_js_strings(),
		'socials'      => blue_social_links(),
		'nonce'        => wp_create_nonce( 'blue-theme' ),
	);
}

add_action(
	'init',
	function (): void {
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( 'blue-announcement', (string) blue_option( 'announcement_en', 'Free delivery and setup across Saudi Arabia.' ), 'Blue Mattress' );
			pll_register_string( 'blue-footer-tagline', (string) blue_option( 'footer_tagline_en', 'Feels like magic, but it is really just science.' ), 'Blue Mattress' );
		}
	}
);

/** Create the theme's core content pages without duplicating existing slugs. */
function blue_ensure_core_pages(): void {
		$pages = array(
			'mattress-finder' => array( 'title' => 'Mattress Finder', 'title_ar' => 'مرشد المراتب', 'template' => 'page-mattress-finder.php' ),
			'our-story'       => array( 'title' => 'Our Story', 'title_ar' => 'قصتنا', 'template' => 'page-our-story.php' ),
			'stark'           => array( 'title' => 'STARK', 'title_ar' => 'ستارك', 'template' => 'page-stark.php' ),
			'contact'         => array( 'title' => 'Contact', 'title_ar' => 'تواصل معنا', 'template' => 'page-contact.php' ),
		);
		$default_ids = array();
		foreach ( $pages as $slug => $page ) {
			$existing = get_page_by_path( $slug, OBJECT, 'page' );
			$page_id  = $existing instanceof WP_Post ? $existing->ID : wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $page['title'],
					'post_name'    => $slug,
					'post_content' => '',
				),
				true
			);
			if ( ! is_wp_error( $page_id ) && $page_id ) {
				$default_ids[ $slug ] = (int) $page_id;
				update_post_meta( (int) $page_id, '_wp_page_template', $page['template'] );
				if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_default_language' ) && function_exists( 'pll_get_post_language' ) && ! pll_get_post_language( (int) $page_id ) ) {
					$default_language = pll_default_language( 'slug' );
					if ( $default_language ) {
						pll_set_post_language( (int) $page_id, $default_language );
					}
				}
			}
		}

		if ( function_exists( 'pll_languages_list' ) && function_exists( 'pll_default_language' ) && function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' ) ) {
			$default_language = pll_default_language( 'slug' );
			$languages        = pll_languages_list( array( 'fields' => 'slug' ) );
			foreach ( is_array( $languages ) ? $languages : array() as $language ) {
				if ( ! $language || $language === $default_language ) {
					continue;
				}
				foreach ( $pages as $slug => $page ) {
					$default_id = $default_ids[ $slug ] ?? 0;
					if ( ! $default_id ) {
						continue;
					}
					$translated_id = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $default_id, $language ) : 0;
					if ( ! $translated_id ) {
						$translated = get_page_by_path( $slug . '-' . $language, OBJECT, 'page' );
						$translated_id = $translated instanceof WP_Post ? $translated->ID : (int) wp_insert_post(
							array(
								'post_type'    => 'page',
								'post_status'  => 'publish',
								'post_title'   => 'ar' === $language ? $page['title_ar'] : $page['title'],
								'post_name'    => $slug . '-' . $language,
								'post_content' => '',
							)
						);
					}
					if ( $translated_id ) {
						update_post_meta( $translated_id, '_wp_page_template', $page['template'] );
						pll_set_post_language( $translated_id, $language );
						$translations = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( $default_id ) : array( $default_language => $default_id );
						$translations[ $default_language ] = $default_id;
						$translations[ $language ] = $translated_id;
						pll_save_post_translations( $translations );
					}
				}
			}
		}
		update_option( 'blue_core_pages_version', BLUE_THEME_VERSION, false );
}
add_action( 'after_switch_theme', 'blue_ensure_core_pages' );
add_action(
	'admin_init',
	function (): void {
		if ( current_user_can( 'edit_pages' ) && BLUE_THEME_VERSION !== get_option( 'blue_core_pages_version' ) ) {
			blue_ensure_core_pages();
		}
	}
);

add_action(
	'admin_notices',
	function (): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$missing = array();
		if ( ! class_exists( 'WooCommerce' ) ) {
			$missing[] = 'WooCommerce';
		}
		if ( ! function_exists( 'pll_current_language' ) ) {
			$missing[] = 'Polylang Free';
		}
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			$missing[] = 'ACF Pro';
		}
		if ( $missing ) {
			printf( '<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>', esc_html__( 'Blue Mattress:', 'blue-mattress' ), esc_html( sprintf( __( 'Install and activate: %s.', 'blue-mattress' ), implode( ', ', $missing ) ) ) );
		}
	}
);
