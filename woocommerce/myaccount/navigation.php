<?php
/**
 * My Account navigation.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$display_name = $current_user->display_name ?: $current_user->user_login;
$first_char   = function_exists( 'mb_substr' ) ? mb_substr( $display_name, 0, 1 ) : substr( $display_name, 0, 1 );

do_action( 'woocommerce_before_account_navigation' );
?>
<aside class="blue-account-side">
	<div class="blue-account-identity">
		<span class="blue-account-avatar" aria-hidden="true"><?php echo esc_html( strtoupper( $first_char ) ); ?></span>
		<span class="blue-account-identity-copy">
			<strong><?php echo esc_html( $display_name ); ?></strong>
			<span><?php echo esc_html( $current_user->user_email ); ?></span>
		</span>
	</div>

	<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">
		<ul>
			<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
				<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"<?php echo wc_is_current_account_menu_item( $endpoint ) ? ' aria-current="page"' : ''; ?>>
						<span class="blue-account-nav-icon" aria-hidden="true"></span>
						<span><?php echo esc_html( $label ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</aside>
<?php do_action( 'woocommerce_after_account_navigation' ); ?>
