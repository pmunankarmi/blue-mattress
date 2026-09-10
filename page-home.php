<?php
/**
 * Template Name: Home Page
 * Template Post Type: page
 *
 * @package BlueMattress
 */

get_header();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : blue_home_url( '/shop/' );
$finder_url = blue_page_url( 'page-mattress-finder.php', '/mattress-finder/' );
$hero_video      = blue_image_url( blue_field( 'hero_video' ) );
$hero_image      = blue_image_url( blue_field( 'hero_poster' ), BLUE_THEME_URI . '/assets/img/hero-sea.jpg' );
$hero_mobile_image = blue_image_url( blue_field( 'hero_mobile_poster' ) );
$hero_video_type = $hero_video ? ( wp_check_filetype( $hero_video )['type'] ?: 'video/mp4' ) : '';
$show_hero_caption = true;
if ( function_exists( 'get_field' ) && metadata_exists( 'post', get_queried_object_id(), 'hero_show_caption' ) ) {
	$show_hero_caption = (bool) get_field( 'hero_show_caption' );
}
$lifestyle_img = blue_image_url( blue_field( 'lifestyle_image' ), BLUE_THEME_URI . '/assets/img/lifestyle-sleep.jpg' );
$home_stark_img = blue_image_url( blue_field( 'home_stark_image' ), BLUE_THEME_URI . '/assets/img/stark-factory.jpg' );
$stark_url  = blue_page_url( 'page-stark.php', '/stark/' );
$home_products   = function_exists( 'blue_home_products' ) ? blue_home_products( 8 ) : array();
$home_categories = function_exists( 'blue_home_product_categories' ) ? blue_home_product_categories( 6 ) : array();
$blue_one_url       = $shop_url;
$anatomy_product_id = blue_field( 'anatomy_product' );
$anatomy_product_id = $anatomy_product_id instanceof WP_Post ? $anatomy_product_id->ID : (int) $anatomy_product_id;
if ( ! $anatomy_product_id ) {
	$blue_one = get_page_by_path( 'blue-1', OBJECT, 'product' ) ?: get_page_by_path( 'horizon', OBJECT, 'product' );
	$anatomy_product_id = $blue_one ? $blue_one->ID : 0;
}
if ( $anatomy_product_id ) {
	$blue_one_id  = function_exists( 'pll_get_post' ) ? ( pll_get_post( $anatomy_product_id, blue_language() ) ?: $anatomy_product_id ) : $anatomy_product_id;
	$blue_one_url = get_permalink( $blue_one_id );
}
$benefits = blue_field( 'home_benefits', array() );
$benefits = is_array( $benefits ) ? $benefits : array();
$home_stark_stats = blue_field( 'home_stark_stats', array() );
$home_stark_stats = is_array( $home_stark_stats ) ? $home_stark_stats : array();
?>
<main id="primary">
	<section class="hero">
		<div class="hero-bg<?php echo $hero_mobile_image ? ' has-mobile-banner' : ''; ?>">
			<picture class="hero-picture">
				<?php if ( $hero_mobile_image ) : ?>
					<source media="(max-width: 680px)" srcset="<?php echo esc_url( $hero_mobile_image ); ?>">
				<?php endif; ?>
				<img class="hero-image" src="<?php echo esc_url( $hero_image ); ?>" alt="" fetchpriority="high">
			</picture>
			<?php if ( $hero_video ) : ?>
				<video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="<?php echo esc_url( $hero_image ); ?>">
					<source src="<?php echo esc_url( $hero_video ); ?>" type="<?php echo esc_attr( $hero_video_type ); ?>">
				</video>
			<?php endif; ?>
		</div>
		<div class="hero-veil" aria-hidden="true"></div>
		<?php if ( $show_hero_caption ) : ?>
			<div class="hero-inner container-wide">
				<h1 class="display h-hero hero-title"><span class="hero-line"><span class="hero-line-in"><?php echo esc_html( blue_field( 'hero_heading' ) ); ?></span></span></h1>
				<p class="hero-lead"><?php echo esc_html( blue_field( 'hero_lead' ) ); ?></p>
				<div class="hero-ctas">
					<a class="btn" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( blue_field( 'hero_primary_cta' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a>
					<a class="btn btn-light" href="<?php echo esc_url( $finder_url ); ?>"><?php echo esc_html( blue_field( 'hero_secondary_cta' ) ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<div class="hero-cue" aria-hidden="true"><span class="hero-cue-line"></span><span><?php echo esc_html( blue_field( 'hero_scroll_label' ) ); ?></span></div>
	</section>

	<section class="vprops" id="trial">
		<h2 class="sr-only"><?php echo esc_html( blue_field( 'home_benefits_heading' ) ); ?></h2>
		<div class="container">
			<div class="vp-card" data-reveal>
				<?php foreach ( $benefits as $benefit ) :
					?>
					<div class="vp-item"><?php echo blue_benefit_icon_svg( sanitize_key( (string) ( $benefit['icon'] ?? 'trial' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed theme SVG. ?><h3><?php echo esc_html( $benefit['title'] ?? '' ); ?></h3><p><?php echo esc_html( $benefit['description'] ?? '' ); ?></p></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="lineup section woocommerce" id="lineup">
		<div class="container lu-head">
			<div class="sec-head">
				<p class="eyebrow" data-reveal><?php echo esc_html( blue_field( 'collection_eyebrow' ) ); ?></p>
				<h2 class="display h2" data-reveal><?php echo esc_html( blue_field( 'collection_heading' ) ); ?></h2>
				<p class="lead" data-reveal><?php echo esc_html( blue_field( 'collection_intro' ) ); ?></p>
			</div>
			<div class="lu-nav"><button class="lu-btn" id="luPrev" type="button" aria-label="<?php echo esc_attr( blue_text( 'Previous', 'السابق' ) ); ?>">‹</button><button class="lu-btn" id="luNext" type="button" aria-label="<?php echo esc_attr( blue_text( 'Next', 'التالي' ) ); ?>">›</button></div>
		</div>
		<?php if ( $home_products ) : ?>
			<?php
			wc_set_loop_prop( 'name', 'blue_home' );
			wc_set_loop_prop( 'columns', 4 );
			?>
			<ul class="products columns-4 lu-track" id="luTrack" tabindex="0" aria-label="<?php echo esc_attr( blue_text( 'Mattress collection', 'تشكيلة المراتب' ) ); ?>">
				<?php
				foreach ( $home_products as $home_product ) {
					$post_object = get_post( $home_product->get_id() );
					if ( ! $post_object ) {
						continue;
					}
					$GLOBALS['post']    = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['product'] = $home_product; // WooCommerce's default loop template reads this global.
					setup_postdata( $post_object );
					wc_get_template_part( 'content', 'product' );
				}
				wp_reset_postdata();
				wc_reset_loop();
				?>
			</ul>
		<?php else : ?>
			<div class="container blue-empty"><?php echo esc_html( blue_field( 'collection_empty_message' ) ); ?></div>
		<?php endif; ?>
		<div class="container"><div class="lu-progress" aria-hidden="true"><i id="luBar"></i></div></div>
	</section>

	<section class="anatomy on-navy" id="anatomy">
		<div class="container an-grid">
			<div class="an-copy">
				<p class="eyebrow" data-reveal><?php echo esc_html( blue_field( 'anatomy_eyebrow' ) ); ?></p>
				<h2 class="display h2" data-reveal><?php echo esc_html( blue_field( 'anatomy_heading' ) ); ?></h2>
				<p class="an-sub"><?php echo esc_html( blue_field( 'anatomy_intro' ) ); ?></p>
				<div class="an-active" id="anActive"><span class="an-num" id="anNum">01</span><span class="an-name" id="anName"></span><p class="an-desc" id="anDesc"></p></div>
				<ol class="an-list" id="anList"></ol>
				<a class="btn btn-light btn-sm an-cta" href="<?php echo esc_url( $blue_one_url ); ?>"><?php echo esc_html( blue_field( 'anatomy_cta_label' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a>
			</div>
			<div class="an-stage" data-reveal></div>
		</div>
	</section>

	<section class="feelzone section" id="feel">
		<div class="container">
			<div class="sec-head center"><p class="eyebrow"><?php echo esc_html( blue_field( 'feel_eyebrow' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_field( 'feel_heading' ) ); ?></h2><p class="lead"><?php echo esc_html( blue_field( 'feel_intro' ) ); ?></p></div>
			<div class="fz-slider" data-reveal><div class="fz-readout" id="fzReadout"><span class="fz-num" id="fzNum">6</span><span class="fz-zone" id="fzZone"></span></div><input type="range" id="fzRange" min="1" max="10" step="1" value="6" dir="ltr" aria-label="<?php echo esc_attr( blue_text( 'Firmness, from 1 plush to 10 firm', 'الصلابة، من 1 ناعمة إلى 10 صلبة' ) ); ?>"><div class="fz-scale" dir="ltr" aria-hidden="true"><span><?php echo esc_html( blue_field( 'feel_scale_plush' ) ); ?></span><span><?php echo esc_html( blue_field( 'feel_scale_balanced' ) ); ?></span><span><?php echo esc_html( blue_field( 'feel_scale_firm' ) ); ?></span></div></div>
			<p class="fz-rec" id="fzRec" aria-live="polite"></p>
			<div class="fz-tiles" id="fzTiles" data-reveal>
				<?php
				$feel_count = 0;
				foreach ( $home_products as $home_product ) :
					$feel = function_exists( 'blue_product_feel' ) ? blue_product_feel( $home_product ) : null;
					if ( null === $feel ) {
						continue;
					}
					$feel_label = (string) blue_product_field( $home_product, 'product_feel_label', '' );
					if ( ! $feel_label ) {
						$feel_label = $feel <= 4 ? blue_text( 'Plush', 'ناعمة' ) : ( $feel <= 7 ? blue_text( 'Balanced', 'متوازنة' ) : blue_text( 'Firm', 'صلبة' ) );
					}
					$feel_count++;
					?>
					<a class="fz-tile" href="<?php echo esc_url( $home_product->get_permalink() ); ?>" data-product-id="<?php echo esc_attr( $home_product->get_slug() ); ?>" data-product-name="<?php echo esc_attr( $home_product->get_name() ); ?>" data-product-tag="<?php echo esc_attr( wp_strip_all_tags( $home_product->get_short_description() ) ); ?>" data-feel="<?php echo esc_attr( (string) $feel ); ?>">
						<span class="fz-tname"><?php echo esc_html( $home_product->get_name() ); ?></span>
						<span class="fz-tfeel"><?php echo esc_html( $feel_label ); ?> · <?php echo esc_html( (string) $feel ); ?>/10</span>
						<span class="fz-tprice"><?php echo wp_kses_post( $home_product->get_price_html() ); ?></span>
					</a>
				<?php endforeach; ?>
				<?php if ( 0 === $feel_count ) : ?><p class="blue-empty"><?php echo esc_html( blue_field( 'feel_empty_message' ) ); ?></p><?php endif; ?>
			</div>
			<p class="fz-more"><?php echo esc_html( blue_field( 'feel_more_text' ) ); ?> <a href="<?php echo esc_url( $finder_url ); ?>"><?php echo esc_html( blue_field( 'feel_cta_label' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a></p>
		</div>
	</section>

	<section class="cats section-tight">
		<div class="container-wide">
			<div class="sec-head"><p class="eyebrow"><?php echo esc_html( blue_field( 'categories_eyebrow' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_field( 'categories_heading' ) ); ?></h2><p class="lead"><?php echo esc_html( blue_field( 'categories_intro' ) ); ?></p></div>
			<div class="cats-grid" id="catsGrid">
				<?php foreach ( $home_categories as $category_index => $home_category ) : ?>
					<?php
					$category_link = get_term_link( $home_category, 'product_cat' );
					if ( is_wp_error( $category_link ) ) {
						continue;
					}
					$description = wp_strip_all_tags( blue_product_term_description( $home_category ) );
					$wide        = in_array( $home_category->slug, array( 'mattresses', 'bedding' ), true ) || 0 === $category_index;
					?>
					<a class="cat-tile<?php echo $wide ? ' wide' : ''; ?>" href="<?php echo esc_url( $category_link ); ?>" data-reveal style="--rd:<?php echo esc_attr( number_format( $category_index * 0.08, 2 ) ); ?>s">
						<?php woocommerce_subcategory_thumbnail( $home_category ); ?>
						<span class="cat-veil" aria-hidden="true"></span>
						<span class="cat-info"><span class="cat-name display"><?php echo esc_html( blue_product_term_name( $home_category ) ); ?></span><?php if ( $description ) : ?><span class="cat-blurb"><?php echo esc_html( $description ); ?></span><?php endif; ?></span>
						<span class="cat-arr" aria-hidden="true"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span>
					</a>
				<?php endforeach; ?>
				<?php if ( ! $home_categories ) : ?><p class="blue-empty"><?php echo esc_html( blue_field( 'categories_empty_message' ) ); ?></p><?php endif; ?>
			</div>
		</div>
	</section>

	<section class="craft on-navy section" id="craft">
		<div class="container craft-grid">
			<div class="craft-media" data-reveal><div class="craft-frame"><img id="craftImg" src="<?php echo esc_url( $home_stark_img ); ?>" alt="<?php echo esc_attr( blue_field( 'home_stark_image_alt' ) ); ?>" loading="lazy"></div></div>
			<div class="craft-copy" id="story">
				<p class="eyebrow" data-reveal><?php echo esc_html( blue_field( 'home_stark_eyebrow' ) ); ?></p>
				<h2 class="display h2" data-reveal><?php echo esc_html( blue_field( 'home_stark_heading' ) ); ?></h2>
				<p class="craft-p" data-reveal><?php echo esc_html( blue_field( 'home_stark_intro' ) ); ?></p>
				<p class="craft-p" data-reveal><?php echo esc_html( blue_field( 'home_stark_materials' ) ); ?></p>
				<?php if ( $home_stark_stats ) : ?><div class="craft-stats" data-reveal><?php foreach ( $home_stark_stats as $stat ) : ?><div class="cs-item"><span class="cs-top"><b class="cs-num" data-count="<?php echo esc_attr( (string) (int) ( $stat['value'] ?? 0 ) ); ?>"><?php echo esc_html( (string) (int) ( $stat['value'] ?? 0 ) ); ?></b><?php if ( ! empty( $stat['unit'] ) ) : ?><span class="cs-unit"><?php echo esc_html( (string) $stat['unit'] ); ?></span><?php endif; ?></span><?php if ( ! empty( $stat['label'] ) ) : ?><span class="cs-label"><?php echo esc_html( (string) $stat['label'] ); ?></span><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
				<a class="btn btn-ghost-light btn-sm craft-cta" href="<?php echo esc_url( $stark_url ); ?>" data-reveal><?php echo esc_html( blue_field( 'home_stark_cta_label' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a>
			</div>
		</div>
	</section>

	<section class="reviews section" id="reviews"><div class="container"><div class="sec-head center"><p class="eyebrow"><?php echo esc_html( blue_field( 'reviews_eyebrow' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_field( 'reviews_heading' ) ); ?></h2><p class="lead"><?php echo esc_html( blue_field( 'reviews_intro' ) ); ?></p></div></div><div class="rv-track" id="rvTrack"></div><div class="container"><div class="rv-dots" id="rvDots"></div></div></section>

	<section class="lifest"><div class="lf-media"><img id="lfImg" src="<?php echo esc_url( $lifestyle_img ); ?>" alt=""></div><div class="lf-veil"></div><div class="lf-inner container"><p class="lf-quote display"><?php echo esc_html( blue_field( 'lifestyle_quote' ) ); ?></p><a class="btn btn-light" href="<?php echo esc_url( $finder_url ); ?>"><?php echo esc_html( blue_field( 'lifestyle_cta_label' ) ); ?></a></div></section>

	<section class="finalcta section"><div class="container fc-grid"><div class="fc-copy"><h2 class="display h1"><?php echo esc_html( blue_field( 'final_cta_heading' ) ); ?></h2><p class="lead"><?php echo esc_html( blue_field( 'final_cta_text' ) ); ?></p></div><div class="fc-actions"><a class="btn" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( blue_field( 'final_primary_cta' ) ); ?></a><a class="btn btn-ghost" href="<?php echo esc_url( $finder_url ); ?>"><?php echo esc_html( blue_field( 'final_secondary_cta' ) ); ?></a></div></div></section>
</main>
<?php get_footer(); ?>
