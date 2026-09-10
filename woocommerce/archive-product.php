<?php
/**
 * Product archive and product-category layout.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url      = wc_get_page_permalink( 'shop' );
$archive_title = is_shop() ? blue_text( 'The Store', 'المتجر' ) : woocommerce_page_title( false );
$archive_intro = is_shop()
	? blue_text( 'Everything for the best third of your life.', 'كل ما تحتاجه لأفضل ثلث من حياتك.' )
	: ( ( $queried_term = get_queried_object() ) instanceof WP_Term ? wp_strip_all_tags( blue_product_term_description( $queried_term ) ) : wp_strip_all_tags( term_description() ) );
$terms         = function_exists( 'blue_home_product_categories' ) ? blue_home_product_categories( 20 ) : array();
$current_term  = is_product_category() ? get_queried_object() : null;
?>
<main id="primary" class="blue-catalog">
	<section class="shophero">
		<div class="container">
			<?php woocommerce_breadcrumb( array( 'delimiter' => '<span class="sep">/</span>' ) ); ?>
			<h1 class="display shophero-title"><?php echo esc_html( $archive_title ); ?></h1>
			<?php if ( $archive_intro ) : ?><p class="lead shophero-sub"><?php echo esc_html( $archive_intro ); ?></p><?php endif; ?>
		</div>
	</section>

	<div class="fbar">
		<div class="container-wide fbar-row">
			<nav class="fbar-pills" aria-label="<?php echo esc_attr( blue_text( 'Product categories', 'فئات المنتجات' ) ); ?>">
				<a class="fbar-pill<?php echo is_shop() ? ' is-on' : ''; ?>" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( blue_text( 'All', 'الكل' ) ); ?></a>
				<?php foreach ( $terms as $term ) : ?>
					<?php $term_link = get_term_link( $term, 'product_cat' ); ?>
					<?php if ( ! is_wp_error( $term_link ) ) : ?>
						<a class="fbar-pill<?php echo $current_term && (int) $current_term->term_id === (int) $term->term_id ? ' is-on' : ''; ?>" href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( blue_product_term_name( $term ) ); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
			<div class="fbar-sort"><span class="fbar-sort-label"><?php echo esc_html( blue_text( 'Sort', 'ترتيب' ) ); ?></span><?php woocommerce_catalog_ordering(); ?></div>
		</div>
	</div>

	<section class="shopgrid">
		<div class="container-wide">
			<?php woocommerce_output_all_notices(); ?>
			<?php if ( woocommerce_product_loop() ) : ?>
				<?php woocommerce_product_loop_start(); ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php wc_get_template_part( 'content', 'product' ); ?>
				<?php endwhile; ?>
				<?php woocommerce_product_loop_end(); ?>
				<?php woocommerce_pagination(); ?>
			<?php else : ?>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php endif; ?>
		</div>
	</section>

	<section class="shopfoot">
		<div class="container shopfoot-inner">
			<p class="eyebrow"><?php echo esc_html( blue_text( 'Sleep, guided', 'نوم باختيار مدروس' ) ); ?></p>
			<h2 class="display h2"><?php echo esc_html( blue_text( 'Not sure where to start?', 'لست متأكدًا من أين تبدأ؟' ) ); ?></h2>
			<p class="lead shopfoot-lead"><?php echo esc_html( blue_text( 'Five questions. Sixty seconds. One bed that fits the way you actually sleep.', 'خمسة أسئلة. ستون ثانية. وسرير يناسب طريقة نومك فعلًا.' ) ); ?></p>
			<div class="shopfoot-ctas">
				<a class="btn" href="<?php echo esc_url( blue_page_url( 'page-mattress-finder.php', '/mattress-finder/' ) ); ?>"><?php echo esc_html( blue_text( 'Open the Mattress Selector', 'افتح مرشد اختيار المرتبة' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( blue_home_url( '/#anatomy' ) ); ?>"><?php echo esc_html( blue_text( 'See what is inside a Blue', 'شاهد ما بداخل مرتبة بلو' ) ); ?></a>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
