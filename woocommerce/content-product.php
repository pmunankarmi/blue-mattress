<?php
/**
 * Product card used by WooCommerce archives and loops.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! $product || ! $product->is_visible() ) {
	return;
}

$kind = (string) blue_product_field( $product, 'product_kind', '' );
if ( ! $kind ) {
	$kind = blue_product_category_label( $product->get_id() );
}
$loop_name  = (string) wc_get_loop_prop( 'name' );
$is_home    = 'blue_home' === $loop_name;
$is_pairing = 'blue_pairings' === $loop_name;
$is_finder  = 'blue_finder' === $loop_name;
$is_feature = $is_home || $is_pairing || $is_finder;
$badge      = trim( (string) blue_product_field( $product, 'product_badge', '' ) );
$profile    = $is_finder && function_exists( 'blue_product_finder_profile' ) ? blue_product_finder_profile( $product ) : array();
$cut_video  = blue_product_cutaway_video( $product );
$cut_image  = blue_image_url( function_exists( 'get_field' ) ? get_field( 'product_cutaway_image', $product->get_id() ) : '' );
$layers     = function_exists( 'blue_product_technologies' ) ? blue_product_technologies( $product ) : array();
$anatomy    = $cut_video || count( $layers ) > 1;
?>
<li <?php wc_product_class( 'pcard', $product ); ?><?php if ( $is_finder ) : ?> data-finder-card data-product-id="<?php echo esc_attr( (string) $product->get_id() ); ?>" data-product-key="<?php echo esc_attr( (string) $profile['key'] ); ?>" data-product-name="<?php echo esc_attr( $product->get_name() ); ?>" data-feel="<?php echo esc_attr( (string) $profile['feel'] ); ?>" data-positions="<?php echo esc_attr( implode( ',', $profile['positions'] ) ); ?>" data-cooling="<?php echo esc_attr( (string) $profile['cooling'] ); ?>" data-motion="<?php echo esc_attr( (string) $profile['motion'] ); ?>" data-height="<?php echo esc_attr( (string) $profile['height'] ); ?>" data-price="<?php echo esc_attr( (string) $profile['price'] ); ?>"<?php endif; ?>>
	<div class="pcard-media"<?php if ( $anatomy ) : ?> data-anat data-product-id="<?php echo esc_attr( (string) $product->get_id() ); ?>"<?php if ( $cut_video ) : ?> data-anat-video="<?php echo esc_url( $cut_video ); ?>"<?php endif; ?><?php if ( $cut_image ) : ?> data-anat-poster="<?php echo esc_url( $cut_image ); ?>"<?php endif; ?> data-anat-layers="<?php echo esc_attr( wp_json_encode( array_map( static fn( $layer ) => (string) ( $layer['title'] ?? '' ), $layers ) ) ); ?>"<?php endif; ?>>
		<?php if ( $badge ) : ?><span class="pcard-badge"><span class="chip"><?php echo esc_html( $badge ); ?></span></span><?php elseif ( $product->is_on_sale() ) : ?><span class="pcard-badge"><span class="chip chip-sale"><?php echo esc_html( blue_text( 'Sale', 'تخفيض' ) ); ?></span></span><?php endif; ?>
		<?php woocommerce_template_loop_product_link_open(); ?>
		<?php woocommerce_template_loop_product_thumbnail(); ?>
		<?php woocommerce_template_loop_product_link_close(); ?>
		<?php woocommerce_template_loop_add_to_cart( array( 'class' => 'button pcard-quick' ) ); ?>
		<button class="pcard-wish" type="button" data-wish-product="<?php echo esc_attr( (string) $product->get_id() ); ?>" aria-label="<?php echo esc_attr( sprintf( blue_text( 'Save %s', 'حفظ %s' ), $product->get_name() ) ); ?>" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
	</div>
	<div class="pcard-body">
		<?php if ( $is_finder ) : ?><span class="finder-match" aria-live="polite"></span><?php endif; ?>
		<?php if ( $kind ) : ?><span class="pcard-kind"><?php echo esc_html( $kind ); ?></span><?php endif; ?>
		<h2 class="woocommerce-loop-product__title pcard-name"><a class="pcard-link" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
		<?php if ( $product->get_short_description() ) : ?><p class="pcard-tag"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 18 ) ); ?></p><?php endif; ?>
		<?php woocommerce_template_loop_rating(); ?>
		<div class="pcard-foot">
			<span class="pcard-price"><?php if ( $is_feature ) : ?><span class="from"><?php echo esc_html( blue_text( 'From', 'ابتداءً من' ) ); ?></span><?php endif; ?><?php woocommerce_template_loop_price(); ?></span>
			<a class="pcard-cta" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( blue_text( 'Details', 'التفاصيل' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a>
		</div>
	</div>
</li>
