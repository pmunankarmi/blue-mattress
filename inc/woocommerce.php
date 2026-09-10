<?php
/**
 * WooCommerce integration.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Suppress WooCommerce's dismissible footer overlay; the theme renders the notice in the header. */
function blue_remove_default_store_notice(): void {
	remove_action( 'wp_footer', 'woocommerce_demo_store' );
}
add_action( 'wp', 'blue_remove_default_store_notice', 1 );
add_filter( 'woocommerce_demo_store', '__return_empty_string', PHP_INT_MAX );

/**
 * Return the current-language mattress category slug when Polylang is active.
 */
function blue_mattress_category_slug(): string {
	$term = get_term_by( 'slug', 'mattresses', 'product_cat' );
	if ( ! $term || is_wp_error( $term ) ) {
		$term = get_term_by( 'slug', 'mattress', 'product_cat' );
	}
	if ( ! $term || is_wp_error( $term ) ) {
		return 'mattresses';
	}

	if ( function_exists( 'pll_get_term' ) ) {
		$translated_id = pll_get_term( $term->term_id, blue_language() );
		$translated    = $translated_id ? get_term( $translated_id, 'product_cat' ) : false;
		if ( $translated && ! is_wp_error( $translated ) ) {
			return $translated->slug;
		}
	}

	return $term->slug;
}

/**
 * Query homepage products through the WooCommerce product data store.
 */
function blue_home_products( int $limit = 8 ): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$args = array(
		'status'     => 'publish',
		'limit'      => $limit,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'visibility' => 'visible',
		'category'   => array( blue_mattress_category_slug() ),
	);

	$products = wc_get_products( $args );
	if ( $products ) {
		return $products;
	}

	// Keep the page useful while a new store is still arranging its categories.
	unset( $args['category'] );
	return wc_get_products( $args );
}

/**
 * Resolve a product's 1–10 firmness from ACF or WooCommerce attributes.
 */
function blue_product_feel( $product ): ?int {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return null;
	}

	$feel = function_exists( 'get_field' ) ? get_field( 'product_feel', $product->get_id() ) : null;
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
		$defaults = array(
			'horizon' => 6,
			'cloud'   => 3,
			'summit'  => 8,
			'royal'   => 5,
			'retro'   => 6,
			'comfy'   => 5,
			'loft'    => 6,
			'haven'   => 5,
		);
		$feel = $defaults[ $product->get_slug() ] ?? null;
	}

	return is_numeric( $feel ) ? max( 1, min( 10, (int) $feel ) ) : null;
}

/** Stable V12 selector key derived from a WooCommerce product slug/name. */
function blue_product_finder_key( WC_Product $product ): string {
	$slug = sanitize_title( $product->get_slug() . '-' . $product->get_name() );
	$map  = array(
		'horizon' => array( 'horizon', 'blue-1', 'blue1' ),
		'royal'   => array( 'royal', 'luna' ),
		'comfy'   => array( 'comfy-zone', 'comfy' ),
		'loft'    => array( 'loft' ),
		'summit'  => array( 'summit', 'pure-latex' ),
		'cloud'   => array( 'cloud', 'skin-care' ),
		'retro'   => array( 'retro' ),
		'haven'   => array( 'haven', 'sky' ),
	);
	foreach ( $map as $key => $needles ) {
		foreach ( $needles as $needle ) {
			if ( str_contains( $slug, $needle ) ) {
				return $key;
			}
		}
	}
	return sanitize_key( $product->get_slug() );
}

/** Bilingual comparison summary for a mattress in the V12 selector. */
function blue_product_finder_best_for( WC_Product $product ): string {
	$labels = array(
		'horizon' => blue_text( 'Couples & most sleepers', 'الأزواج ومعظم النائمين' ),
		'royal'   => blue_text( 'Five-star luxury', 'فخامة فنادق الخمس نجوم' ),
		'comfy'   => blue_text( 'Couples who prefer different firmness', 'الأزواج المختلفون في تفضيل الصلابة' ),
		'loft'    => blue_text( 'Recovery & active lifestyles', 'التعافي وأصحاب النشاط' ),
		'summit'  => blue_text( 'Back & stomach sleepers', 'النوم على الظهر والبطن' ),
		'cloud'   => blue_text( 'Skin care while you sleep', 'العناية بالبشرة أثناء النوم' ),
		'retro'   => blue_text( 'Smart classic value', 'قيمة كلاسيكية ذكية' ),
		'haven'   => blue_text( 'Kids, teens & guest rooms', 'الأطفال والناشئة وغرف الضيوف' ),
	);
	$key = blue_product_finder_key( $product );
	return $labels[ $key ] ?? ( (string) blue_product_field( $product, 'product_kind', '' ) ?: blue_product_category_label( $product->get_id() ) );
}

/** Product data used by the PHP-rendered Mattress Finder cards and comparison. */
function blue_product_finder_profile( WC_Product $product ): array {
	$feel      = blue_product_feel( $product ) ?: 6;
	$positions = function_exists( 'get_field' ) ? get_field( 'product_sleep_positions', $product->get_id() ) : array();
	$cooling   = function_exists( 'get_field' ) ? get_field( 'product_cooling', $product->get_id() ) : null;
	$motion    = function_exists( 'get_field' ) ? get_field( 'product_motion_isolation', $product->get_id() ) : null;
	$height    = function_exists( 'get_field' ) ? get_field( 'product_height', $product->get_id() ) : null;

	if ( ! is_array( $positions ) || ! $positions ) {
		$positions = $feel <= 4 ? array( 'side', 'mixed' ) : ( $feel >= 8 ? array( 'back', 'stomach', 'mixed' ) : array( 'side', 'back', 'stomach', 'mixed' ) );
	}
	if ( ! is_numeric( $cooling ) ) {
		$cooling = 6;
		$technology = strtolower( $product->get_attribute( 'pa_technology' ) . ' ' . $product->get_name() . ' ' . $product->get_short_description() );
		if ( str_contains( $technology, 'cool' ) || str_contains( $technology, 'gel' ) || str_contains( $technology, 'latex' ) ) {
			$cooling = 9;
		}
	}
	if ( ! is_numeric( $motion ) ) {
		$motion = $product->is_type( 'variable' ) ? 8 : 6;
	}

	$price = $product->is_type( 'variable' ) ? (float) $product->get_variation_price( 'min', true ) : (float) $product->get_price();
	return array(
		'key'       => blue_product_finder_key( $product ),
		'feel'      => max( 1, min( 10, (int) $feel ) ),
		'positions' => array_values( array_map( 'sanitize_key', $positions ) ),
		'cooling'   => max( 1, min( 10, (int) $cooling ) ),
		'motion'    => max( 1, min( 10, (int) $motion ) ),
		'height'    => is_numeric( $height ) ? max( 1, (int) $height ) : 0,
		'price'     => max( 0, $price ),
	);
}

/** Main construction heading configured in the legacy product_core ACF fields. */
function blue_product_construction_title( $product ): string {
	$title = trim( (string) blue_product_field( $product, 'product_core', '' ) );
	return $title ?: blue_text( 'The technology inside, named.', 'التقنيات الداخلية، بالاسم.' );
}

/** Construction rows configured for a product in ACF Pro. */
function blue_product_technologies( $product ): array {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows         = get_field( 'product_layers', $product->get_id() );
	$technologies = array();
	foreach ( is_array( $rows ) ? $rows : array() as $row ) {
		$title       = blue_is_arabic() && ! empty( $row['title_ar'] ) ? $row['title_ar'] : ( $row['title'] ?? '' );
		$description = blue_is_arabic() && ! empty( $row['description_ar'] ) ? $row['description_ar'] : ( $row['description'] ?? '' );
		if ( $title ) {
			$technologies[] = array( 'title' => $title, 'description' => $description );
		}
	}
	return $technologies;
}

/**
 * Top-level product categories for the PHP-rendered homepage category grid.
 */
function blue_home_product_categories( int $limit = 6 ): array {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$exclude      = array();
	$default_term = get_term( (int) get_option( 'default_product_cat', 0 ), 'product_cat' );
	if ( $default_term instanceof WP_Term && in_array( $default_term->slug, array( 'uncategorized', 'uncategorised' ), true ) ) {
		$exclude[] = $default_term->term_id;
	}
	$terms   = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => $limit,
			'exclude'    => $exclude,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$order = array( 'mattresses' => 0, 'mattress' => 0, 'pillows' => 1, 'toppers' => 2, 'bedding' => 3 );
	usort(
		$terms,
		fn( WP_Term $a, WP_Term $b ): int => ( $order[ $a->slug ] ?? 99 ) <=> ( $order[ $b->slug ] ?? 99 )
	);
	return $terms;
}

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

/**
 * Keep Cart and Checkout on WooCommerce's maintained classic templates.
 * Existing block-based page content is converted only while rendering, so
 * coupons, shipping, gateways and order processing remain fully native.
 */
add_filter(
	'the_content',
	function ( string $content ): string {
		if ( ! in_the_loop() || ! is_main_query() || ! class_exists( 'WooCommerce' ) ) {
			return $content;
		}
		if ( function_exists( 'is_cart' ) && is_cart() && has_block( 'woocommerce/cart', $content ) ) {
			return '[woocommerce_cart]';
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() && has_block( 'woocommerce/checkout', $content ) ) {
			return '[woocommerce_checkout]';
		}
		return $content;
	},
	8
);

add_action(
	'woocommerce_before_main_content',
	function (): void {
		if ( function_exists( 'is_product' ) && is_product() ) {
			echo '<main id="primary" class="blue-product-main">';
			return;
		}
		echo '<main id="primary" class="blue-commerce"><div class="container">';
	},
	10
);

add_action(
	'woocommerce_after_main_content',
	function (): void {
		echo function_exists( 'is_product' ) && is_product() ? '</main>' : '</div></main>';
	},
	10
);

add_filter( 'loop_shop_columns', fn() => 3 );
add_filter( 'loop_shop_per_page', fn() => 12 );
add_action(
	'pre_get_posts',
	function ( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! function_exists( 'is_shop' ) || ! is_shop() || empty( $_GET['on_sale'] ) ) {
			return;
		}
		$query->set( 'post__in', array_merge( array( 0 ), wc_get_product_ids_on_sale() ) );
	}
);
add_filter(
	'woocommerce_catalog_orderby',
	function ( array $options ): array {
		if ( isset( $options['menu_order'] ) ) {
			$options['menu_order'] = blue_text( 'Featured', 'المميزة' );
		}
		return $options;
	}
);

/**
 * Products shown in the single-product "Pairs well with" section.
 *
 * ACF selections take priority. Cross-sells, upsells and WooCommerce related
 * products fill any remaining spaces so the section works before editors have
 * configured it.
 */
function blue_product_pairing_ids( WC_Product $product, int $limit = 3 ): array {
	$candidates = array();
	$selected   = function_exists( 'get_field' ) ? get_field( 'product_pairings', $product->get_id() ) : array();

	foreach ( is_array( $selected ) ? $selected : array_filter( array( $selected ) ) as $item ) {
		if ( is_a( $item, 'WC_Product' ) ) {
			$candidates[] = $item->get_id();
		} elseif ( is_a( $item, 'WP_Post' ) ) {
			$candidates[] = $item->ID;
		} else {
			$candidates[] = absint( $item );
		}
	}

	$candidates = array_merge(
		$candidates,
		$product->get_cross_sell_ids(),
		$product->get_upsell_ids(),
		wc_get_related_products( $product->get_id(), max( 12, $limit * 4 ) )
	);

	$ids = array();
	foreach ( array_unique( array_map( 'absint', $candidates ) ) as $candidate_id ) {
		if ( ! $candidate_id || $candidate_id === $product->get_id() ) {
			continue;
		}
		$candidate = wc_get_product( $candidate_id );
		if ( ! $candidate || 'publish' !== get_post_status( $candidate_id ) || ! $candidate->is_visible() ) {
			continue;
		}
		$ids[] = $candidate_id;
		if ( count( $ids ) >= $limit ) {
			break;
		}
	}

	return $ids;
}

/** Always display the Latin SAR code, including on Arabic pages. */
add_filter(
	'woocommerce_currency_symbol',
	function ( string $symbol, string $currency ): string {
		return 'SAR' === $currency ? 'SAR' : $symbol;
	},
	10,
	2
);
add_filter(
	'woocommerce_price_format',
	function ( string $format ): string {
		return 'SAR' === get_woocommerce_currency() ? '%1$s&nbsp;%2$s' : $format;
	}
);
add_filter( 'wc_get_price_decimals', fn(): int => 'SAR' === get_woocommerce_currency() ? 0 : (int) get_option( 'woocommerce_price_num_decimals', 2 ), 100 );
add_filter( 'wc_get_price_thousand_separator', fn( string $separator ): string => 'SAR' === get_woocommerce_currency() ? ',' : $separator, 100 );
add_filter( 'wc_get_price_decimal_separator', fn( string $separator ): string => 'SAR' === get_woocommerce_currency() ? '.' : $separator, 100 );

/* Match the reference product summary order: title, short description, price. */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 8 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 12 );

add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( array $fragments ): array {
		$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		$fragments['.blue-cart-count'] = sprintf( '<span class="blue-cart-count%s">%d</span>', $count ? ' show' : '', $count );
		return $fragments;
	}
);

/** Product feel and height details used inside the cart form. */
function blue_single_product_specs(): void {
	global $product;
	if ( ! $product || ! function_exists( 'get_field' ) ) {
		return;
	}
	$feel   = blue_product_feel( $product );
	$label  = blue_product_field( $product, 'product_feel_label', '' );
	$height = get_field( 'product_height', $product->get_id() );
	if ( blue_is_arabic() && $label ) {
		$arabic_feel_labels = array(
			'plush'       => 'ناعمة',
			'soft'        => 'ناعمة',
			'medium'      => 'متوسطة',
			'balanced'    => 'متوازنة',
			'medium firm' => 'متوسطة إلى صلبة',
			'firm'        => 'صلبة',
		);
		$label = $arabic_feel_labels[ strtolower( trim( (string) $label ) ) ] ?? $label;
	}
	if ( ! $feel && ! $height ) {
		return;
	}
	if ( $feel ) {
		$label = $label ?: ( $feel <= 4 ? blue_text( 'Plush', 'ناعمة' ) : ( $feel <= 7 ? blue_text( 'Medium', 'متوسطة' ) : blue_text( 'Firm', 'صلبة' ) ) );
		printf( '<div class="pd-feel"><div class="feel" style="--feel:%1$d"><div class="feel-track"></div><div class="feel-labels"><span>%2$s</span><span>%3$s</span></div></div><div class="pd-feel-meta"><span class="pd-feel-label">%4$s</span>%5$s</div></div>', absint( $feel ), esc_html( blue_text( 'Plush', 'ناعمة' ) ), esc_html( blue_text( 'Firm', 'صلبة' ) ), esc_html( $label ), $height ? '<span class="chip chip-soft">' . esc_html( absint( $height ) . ' ' . blue_text( 'cm tall', 'سم ارتفاعًا' ) ) . '</span>' : '' );
	} elseif ( $height ) {
		printf( '<p class="pd-height chip chip-soft">%s</p>', esc_html( absint( $height ) . ' ' . blue_text( 'cm tall', 'سم ارتفاعًا' ) ) );
	}
}

add_action( 'woocommerce_after_variations_table', 'blue_single_product_specs', 5 );
add_action(
	'woocommerce_before_add_to_cart_quantity',
	function (): void {
		global $product;
		if ( $product && $product->is_type( 'simple' ) ) {
			blue_single_product_specs();
		}
		echo '<span class="pd-qty-label">' . esc_html( blue_text( 'Quantity', 'الكمية' ) ) . '</span>';
	}
);

/** Reference heart control beside the primary add-to-cart button. */
add_action(
	'woocommerce_after_add_to_cart_button',
	function (): void {
		global $product;
		if ( ! $product ) {
			return;
		}
		printf(
			'<button class="pd-wish" type="button" data-wish-product="%1$d" aria-label="%2$s" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
			absint( $product->get_id() ),
			esc_attr( sprintf( blue_text( 'Save %s', 'حفظ %s' ), $product->get_name() ) )
		);
	}
);

/** Inline icons for the product-page assurance list. */
function blue_product_assurance_icon( string $icon ): string {
	$icons = array(
		'trial'    => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.5 14.3A8.5 8.5 0 0 1 9.7 3.5a7 7 0 1 0 10.8 10.8Z"/></svg>',
		'delivery' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 6.5h11v10H3zM14 10h3.3l3.2 3.3v3.2H14z"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>',
		'warranty' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m12 3 7 9-7 9-7-9 7-9Z"/></svg>',
		'returns'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m12 3 7 3v5c0 4.8-2.9 8.1-7 10-4.1-1.9-7-5.2-7-10V6l7-3Z"/><path d="m8.8 12 2 2 4.4-4.5"/></svg>',
	);
	return $icons[ $icon ] ?? '';
}

/** Product-page assurance items controlled by the three ACF checkboxes. */
function blue_product_assurances( $product ): array {
	$items = array(
		'trial'    => array( 'field' => 'product_assurance_trial', 'icon' => 'trial', 'label' => blue_text( '50-night trial', 'تجربة 50 ليلة' ) ),
		'delivery' => array( 'field' => 'product_assurance_delivery', 'icon' => 'delivery', 'label' => blue_text( 'Kingdom-wide delivery', 'توصيل لجميع أنحاء المملكة' ) ),
		'warranty' => array( 'field' => 'product_assurance_warranty', 'icon' => 'warranty', 'label' => blue_text( '10-year warranty', 'ضمان 10 سنوات' ) ),
	);
	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! function_exists( 'get_field' ) ) {
		return $items;
	}

	$product_id = $product->get_id();
	foreach ( $items as $key => $item ) {
		// Products created before these switches existed keep all three defaults.
		$has_saved_value = function_exists( 'metadata_exists' ) && metadata_exists( 'post', $product_id, $item['field'] );
		if ( $has_saved_value && ! (bool) get_field( $item['field'], $product_id ) ) {
			unset( $items[ $key ] );
		}
	}
	return $items;
}

add_action(
	'woocommerce_single_product_summary',
	function (): void {
		global $product;
		$assurances = blue_product_assurances( $product );
		if ( ! $assurances ) {
			return;
		}
		?>
		<ul class="pd-assure">
			<?php foreach ( $assurances as $assurance ) : ?>
				<li><span class="pd-assure-icon" aria-hidden="true"><?php echo blue_product_assurance_icon( $assurance['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns fixed theme SVG only. ?></span><?php echo esc_html( $assurance['label'] ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
	},
	35
);

add_action(
	'woocommerce_single_product_summary',
	function (): void {
		global $product;
		if ( ! $product ) {
			return;
		}
		$layers   = blue_product_technologies( $product );
		$delivery = blue_product_field( $product, 'product_delivery_returns', '' );
		$warranty = blue_product_field( $product, 'product_warranty', '' );
		?>
		<div class="pd-acc">
			<details class="pd-acc-item" open><summary><?php echo esc_html( blue_text( 'The story', 'القصة' ) ); ?></summary><div class="pd-acc-body"><?php echo wp_kses_post( wpautop( $product->get_description() ?: $product->get_short_description() ) ); ?></div></details>
			<?php if ( $layers ) : ?>
				<details class="pd-acc-item"><summary><?php echo esc_html( blue_text( 'Construction', 'التكوين' ) ); ?></summary><div class="pd-acc-body"><ol class="pd-acc-layers"><?php foreach ( $layers as $layer ) : ?><li><?php echo esc_html( $layer['title'] ?? '' ); ?></li><?php endforeach; ?></ol></div></details>
			<?php endif; ?>
			<details class="pd-acc-item"><summary><?php echo esc_html( blue_text( 'Delivery & returns', 'التوصيل والإرجاع' ) ); ?></summary><div class="pd-acc-body"><?php echo wp_kses_post( $delivery ? $delivery : wpautop( blue_text( 'Every Blue mattress is made to order in Jeddah and delivered across the Kingdom. Contact customer care to arrange an exchange or return under the current store policy.', 'تُصنع كل مرتبة بلو حسب الطلب في جدة وتُسلّم إلى أنحاء المملكة. تواصل مع خدمة العملاء لترتيب الاستبدال أو الإرجاع وفق سياسة المتجر الحالية.' ) ) ); ?></div></details>
			<details class="pd-acc-item"><summary><?php echo esc_html( blue_text( 'Warranty', 'الضمان' ) ); ?></summary><div class="pd-acc-body"><?php echo wp_kses_post( $warranty ? $warranty : wpautop( blue_text( 'Every Blue mattress carries a 10-year manufacturer warranty. See the Terms & Conditions for complete coverage details.', 'تتمتع كل مرتبة بلو بضمان مصنّع لمدة 10 سنوات. راجع الشروط والأحكام لتفاصيل التغطية الكاملة.' ) ) ); ?></div></details>
		</div>
		<?php
	},
	50
);

add_action(
	'woocommerce_after_single_product_summary',
	function (): void {
		global $product;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! function_exists( 'get_field' ) ) {
			return;
		}

		$wide_image = get_field( 'product_wide_image', $product->get_id() );
		$image_id   = is_array( $wide_image ) ? (int) ( $wide_image['ID'] ?? $wide_image['id'] ?? 0 ) : ( is_numeric( $wide_image ) ? (int) $wide_image : 0 );

		// Keep Retro useful immediately after updating: its last gallery image is
		// the lifestyle fallback until the dedicated wide image is selected in ACF.
		if ( ! $image_id && 'retro' === $product->get_slug() ) {
			$gallery_ids = $product->get_gallery_image_ids();
			$image_id    = $gallery_ids ? (int) end( $gallery_ids ) : 0;
		}

		$image_url = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'full' ) : blue_image_url( $wide_image );
		if ( ! $image_url ) {
			return;
		}
		$alt = sprintf( blue_text( '%s in a bedroom setting', '%s في غرفة نوم' ), $product->get_name() );
		?>
		<section class="pd-band" aria-label="<?php echo esc_attr( $alt ); ?>">
			<?php
			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'full', false, array( 'class' => 'pd-band-image', 'loading' => 'lazy', 'decoding' => 'async', 'alt' => $alt ) );
			} else {
				printf( '<img class="pd-band-image" src="%1$s" alt="%2$s" loading="lazy" decoding="async">', esc_url( $image_url ), esc_attr( $alt ) );
			}
			?>
		</section>
		<?php
	},
	7
);

add_action(
	'woocommerce_after_single_product_summary',
	function (): void {
		global $product;
		if ( ! $product || ! function_exists( 'get_field' ) ) {
			return;
		}
		$layers = blue_product_technologies( $product );
		$image  = get_field( 'product_cutaway_image', $product->get_id() );
		$video  = blue_product_cutaway_video( $product );
		$intro  = blue_product_field( $product, 'product_construction_intro', '' );
		$title  = blue_product_construction_title( $product );
		if ( ! $layers && ! $image && ! $video ) {
			return;
		}
		?>
		<section class="pd-construction on-navy section" id="construction">
			<div class="container blue-construction-grid">
				<div>
					<p class="eyebrow"><?php echo esc_html( sprintf( blue_text( 'Inside %s', 'داخل %s' ), $product->get_name() ) ); ?></p>
					<h2 class="display h2"><?php echo esc_html( $title ); ?></h2>
					<p class="lead"><?php echo esc_html( $intro ?: blue_text( 'No mystery materials or filler—every support layer is listed clearly.', 'لا مواد غامضة ولا حشو—كل طبقة دعم مذكورة بوضوح.' ) ); ?></p>
					<?php if ( $layers ) : ?>
						<ol class="blue-layer-list pd-layers">
							<?php foreach ( $layers as $index => $layer ) : ?>
								<li class="pd-layer"><span class="pd-layer-n"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><div><strong class="pd-layer-t"><?php echo esc_html( $layer['title'] ?? '' ); ?></strong><p class="pd-layer-d"><?php echo esc_html( $layer['description'] ?? '' ); ?></p></div></li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>
				<?php if ( $video || $image ) : ?>
					<figure class="pd-cut">
						<?php if ( $video ) : ?><video class="pd-cut-vid" src="<?php echo esc_url( $video ); ?>"<?php if ( $image ) : ?> poster="<?php echo esc_url( blue_image_url( $image ) ); ?>"<?php endif; ?> muted loop playsinline preload="metadata" aria-label="<?php echo esc_attr( sprintf( blue_text( 'Animated cross-section showing the layers inside %s', 'مقطع متحرك يوضح الطبقات داخل %s' ), $product->get_name() ) ); ?>"></video><?php else : ?><img src="<?php echo esc_url( blue_image_url( $image ) ); ?>" alt="<?php echo esc_attr( sprintf( blue_text( 'Cross-section showing the layers inside %s', 'مقطع يوضح الطبقات داخل %s' ), $product->get_name() ) ); ?>"><?php endif; ?>
					</figure>
				<?php endif; ?>
			</div>
		</section>
		<?php
	},
	8
);

add_action(
	'woocommerce_after_single_product_summary',
	function (): void {
		global $product;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		$pairing_ids = blue_product_pairing_ids( $product, 3 );
		if ( ! $pairing_ids ) {
			return;
		}

		$pairings = new WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'post__in'            => $pairing_ids,
				'orderby'             => 'post__in',
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( ! $pairings->have_posts() ) {
			return;
		}
		?>
		<section class="pd-pairings section" aria-labelledby="blue-pairings-heading">
			<div class="container">
				<p class="eyebrow"><?php echo esc_html( blue_text( 'Complete the bed', 'أكمل سريرك' ) ); ?></p>
				<h2 class="display h2" id="blue-pairings-heading"><?php echo esc_html( blue_text( 'Pairs well with', 'يتناسق بشكل رائع مع' ) ); ?></h2>
				<ul class="products columns-3 pd-pairings-grid">
					<?php
					wc_set_loop_prop( 'name', 'blue_pairings' );
					wc_set_loop_prop( 'columns', 3 );
					while ( $pairings->have_posts() ) {
						$pairings->the_post();
						wc_get_template_part( 'content', 'product' );
					}
					?>
				</ul>
			</div>
		</section>
		<?php
		wp_reset_postdata();
		wc_reset_loop();
	},
	20
);

add_action(
	'after_switch_theme',
	function (): void {
		if ( class_exists( 'WooCommerce' ) ) {
			update_option( 'woocommerce_currency', 'SAR' );
			update_option( 'woocommerce_currency_pos', 'left_space' );
		}
	}
);
