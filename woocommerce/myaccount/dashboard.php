<?php
/**
 * Customer account dashboard.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$display_name = $current_user->display_name ?: $current_user->user_login;
$orders       = wc_get_orders(
	array(
		'customer_id' => get_current_user_id(),
		'limit'       => 3,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
?>

<header class="blue-account-dashboard-head">
	<p class="eyebrow"><?php echo esc_html( blue_text( 'Your Blue account', 'حساب بلو الخاص بك' ) ); ?></p>
	<h2><?php echo esc_html( sprintf( blue_text( 'Good to see you, %s.', 'سعداء برؤيتك، %s.' ), $display_name ) ); ?></h2>
	<p><?php echo esc_html( blue_text( 'Track orders, update delivery details and keep your account information current.', 'تابع طلباتك وحدّث بيانات التوصيل ومعلومات حسابك.' ) ); ?></p>
</header>

<div class="blue-account-quicklinks">
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
		<span><?php echo esc_html( blue_text( 'Orders', 'الطلبات' ) ); ?></span>
		<small><?php echo esc_html( blue_text( 'View order history and status', 'عرض سجل الطلبات وحالتها' ) ); ?></small>
		<b aria-hidden="true">→</b>
	</a>
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>">
		<span><?php echo esc_html( blue_text( 'Addresses', 'العناوين' ) ); ?></span>
		<small><?php echo esc_html( blue_text( 'Manage billing and delivery', 'إدارة عناوين الفوترة والتوصيل' ) ); ?></small>
		<b aria-hidden="true">→</b>
	</a>
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>">
		<span><?php echo esc_html( blue_text( 'Account details', 'تفاصيل الحساب' ) ); ?></span>
		<small><?php echo esc_html( blue_text( 'Name, email and password', 'الاسم والبريد الإلكتروني وكلمة المرور' ) ); ?></small>
		<b aria-hidden="true">→</b>
	</a>
</div>

<section class="blue-recent-orders" aria-labelledby="blue-recent-orders-title">
	<div class="blue-account-section-head">
		<h3 id="blue-recent-orders-title"><?php echo esc_html( blue_text( 'Recent orders', 'الطلبات الأخيرة' ) ); ?></h3>
		<?php if ( $orders ) : ?>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php echo esc_html( blue_text( 'View all', 'عرض الكل' ) ); ?></a>
		<?php endif; ?>
	</div>

	<?php if ( $orders ) : ?>
		<ul class="blue-recent-order-list">
			<?php foreach ( $orders as $order ) : ?>
				<li>
					<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
						<span>
							<strong><?php echo esc_html( sprintf( blue_text( 'Order #%s', 'الطلب رقم %s' ), $order->get_order_number() ) ); ?></strong>
							<small><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></small>
						</span>
						<span class="blue-order-status"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
						<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<div class="blue-account-empty">
			<span aria-hidden="true">◇</span>
			<p><?php echo esc_html( blue_text( 'No orders yet. Your first better night can start here.', 'لا توجد طلبات بعد. يمكن أن تبدأ ليلتك الأفضل الأولى من هنا.' ) ); ?></p>
			<a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( blue_text( 'Shop mattresses', 'تسوق المراتب' ) ); ?></a>
		</div>
	<?php endif; ?>
</section>

<?php do_action( 'woocommerce_account_dashboard' ); ?>
<?php do_action( 'woocommerce_before_my_account' ); ?>
<?php do_action( 'woocommerce_after_my_account' ); ?>
