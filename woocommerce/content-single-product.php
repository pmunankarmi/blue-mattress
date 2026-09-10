<?php
/**
 * Single-product layout based on the supplied v2 reference.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );
if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$kind = (string) blue_product_field( $product, 'product_kind', '' );
$core_technologies = function_exists( 'blue_product_technologies' ) ? blue_product_technologies( $product ) : array();
$core_count        = count( $core_technologies );
$cut_video         = blue_product_cutaway_video( $product );
$cut_image         = blue_image_url( function_exists( 'get_field' ) ? get_field( 'product_cutaway_image', $product->get_id() ) : '' );
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'blue-product-detail', $product ); ?>>
	<div class="pd-crumbs">
		<nav class="container woocommerce-breadcrumb" aria-label="<?php echo esc_attr( blue_text( 'Breadcrumb', 'مسار التنقل' ) ); ?>">
			<a href="<?php echo esc_url( blue_home_url( '/' ) ); ?>"><?php echo esc_html( blue_text( 'Home', 'الرئيسية' ) ); ?></a><span class="pd-crumb-sep">/</span>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( blue_text( 'Shop', 'المتجر' ) ); ?></a>
			<?php
			$product_terms = get_the_terms( $product->get_id(), 'product_cat' );
			$product_term  = is_array( $product_terms ) ? reset( $product_terms ) : false;
			if ( $product_term instanceof WP_Term ) :
				$term_link = get_term_link( $product_term );
				if ( ! is_wp_error( $term_link ) ) :
					?>
					<span class="pd-crumb-sep">/</span><a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( blue_product_term_name( $product_term ) ); ?></a>
					<?php
				endif;
			endif;
			?>
			<span class="pd-crumb-sep">/</span><span aria-current="page"><?php echo esc_html( $product->get_name() ); ?></span>
		</nav>
	</div>

	<section class="pd container">
		<div class="pd-gallery"<?php if ( $cut_video ) : ?> data-product-cut-video="<?php echo esc_url( $cut_video ); ?>"<?php if ( $cut_image ) : ?> data-product-cut-poster="<?php echo esc_url( $cut_image ); ?>"<?php endif; ?><?php endif; ?>>
			<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
			<?php if ( $core_count ) : ?>
				<a class="pd-tech-tile" href="#construction"><span class="pd-ti-n display"><?php echo esc_html( (string) $core_count ); ?></span><span class="pd-ti-t"><?php echo esc_html( blue_text( 'core technologies', 'تقنيات أساسية' ) ); ?></span><span class="pd-ti-cta"><?php echo esc_html( blue_text( 'See the technology', 'شاهد التقنية' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></span></a>
			<?php endif; ?>
		</div>
		<aside class="summary entry-summary pd-buy" aria-label="<?php echo esc_attr( blue_text( 'Purchase options', 'خيارات الشراء' ) ); ?>">
			<?php if ( $kind ) : ?><p class="eyebrow pd-kind"><?php echo esc_html( $kind ); ?></p><?php endif; ?>
			<?php do_action( 'woocommerce_single_product_summary' ); ?>
		</aside>
	</section>

	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
</div>
<?php do_action( 'woocommerce_after_single_product' ); ?>
