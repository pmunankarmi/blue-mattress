<?php
/**
 * Variable product add-to-cart form with v2 size cards.
 *
 * The native WooCommerce selects remain in the form and continue to drive
 * stock, variation prices, images and add-to-cart validation.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' );
?>
<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<?php do_action( 'woocommerce_before_variations_table' ); ?>
		<div class="blue-variation-groups variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				$select_name = wc_variation_attribute_name( $attribute_name );
				$selected    = $selected_attributes[ $attribute_name ] ?? $product->get_variation_default_attribute( $attribute_name );
				?>
				<fieldset class="pd-sizes blue-variation-cards" data-attribute-name="<?php echo esc_attr( $select_name ); ?>">
					<legend class="pd-lab"><?php echo esc_html( wc_attribute_label( $attribute_name, $product ) ); ?></legend>
					<div class="pd-size-grid">
						<?php foreach ( $options as $option_index => $option ) : ?>
							<?php
							$option_slug  = (string) $option;
							$option_label = $option_slug;
							$term         = null;
							if ( taxonomy_exists( $attribute_name ) ) {
								$term = get_term_by( 'slug', $option_slug, $attribute_name );
								if ( $term && ! is_wp_error( $term ) ) {
									$option_label = $term->name;
								}
							}
							$option_label = apply_filters( 'woocommerce_variation_option_name', $option_label, $term, $attribute_name, $product );
							if ( ! blue_is_arabic() && str_contains( sanitize_title( $attribute_name ), 'size' ) ) {
								$option_label = ucwords( strtolower( $option_label ) );
							}
							$option_description = function_exists( 'blue_product_attribute_option_description' )
								? blue_product_attribute_option_description( $product, $attribute_name, $option_slug, $term instanceof WP_Term ? $term : null )
								: '';

							$matching_variation = null;
							foreach ( $available_variations as $variation_data ) {
								$variation_value = $variation_data['attributes'][ $select_name ] ?? '';
								if ( '' === $variation_value || (string) $variation_value === $option_slug ) {
									$matching_variation = $variation_data;
									break;
								}
							}

							$variation_product = $matching_variation ? wc_get_product( $matching_variation['variation_id'] ) : false;
							$dimensions        = '';
							$price_html        = '';
							if ( $variation_product ) {
								$width  = $variation_product->get_width();
								$length = $variation_product->get_length();
								if ( $width && $length ) {
									$unit       = (string) get_option( 'woocommerce_dimension_unit', 'cm' );
									$unit_label = function_exists( 'blue_dimension_unit_label' ) ? blue_dimension_unit_label( $unit ) : $unit;
									$dimensions = wc_format_localized_decimal( $width ) . ' × ' . wc_format_localized_decimal( $length ) . ' ' . $unit_label;
									if ( function_exists( 'blue_attribute_description_is_dimensions' ) && blue_attribute_description_is_dimensions( $option_description, (string) $width, (string) $length ) ) {
										$option_description = '';
									}
								}
								$price_html = wc_price( wc_get_price_to_display( $variation_product ) );
							}
							$is_selected = (string) $selected === $option_slug || ( ! $selected && 0 === $option_index );
							?>
							<label class="pd-size<?php echo $is_selected ? ' active' : ''; ?>">
								<input type="radio" name="blue_<?php echo esc_attr( $select_name ); ?>" value="<?php echo esc_attr( $option_slug ); ?>"<?php checked( $is_selected ); ?>>
								<span class="pd-size-l"><?php echo esc_html( $option_label ); ?></span>
								<?php if ( $option_description ) : ?><span class="pd-size-x"><?php echo esc_html( wp_strip_all_tags( $option_description ) ); ?></span><?php endif; ?>
								<?php if ( $dimensions ) : ?><span class="pd-size-d"><?php echo esc_html( $dimensions ); ?></span><?php endif; ?>
								<?php if ( $price_html ) : ?><span class="pd-size-p"><?php echo wp_kses_post( $price_html ); ?></span><?php endif; ?>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="blue-native-variation" aria-hidden="true">
						<?php
						wc_dropdown_variation_attribute_options(
							array(
								'options'   => $options,
								'attribute' => $attribute_name,
								'product'   => $product,
								'selected'  => $selected,
							)
						);
						?>
					</div>
				</fieldset>
			<?php endforeach; ?>
			<?php if ( $attribute_keys ) : ?><a class="reset_variations" href="#" aria-label="<?php esc_attr_e( 'Clear options', 'woocommerce' ); ?>"><?php esc_html_e( 'Clear', 'woocommerce' ); ?></a><?php endif; ?>
		</div>

		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
			do_action( 'woocommerce_before_single_variation' );
			do_action( 'woocommerce_single_variation' );
			do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>
<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
