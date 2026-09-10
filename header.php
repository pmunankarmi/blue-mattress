<?php
/**
 * Site header.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : blue_home_url( '/shop/' );
$cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : blue_home_url( '/cart/' );
$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$cart_count  = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<!doctype html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr( is_rtl() || blue_is_arabic() ? 'rtl' : 'ltr' ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#F7F4EE">
	<script>try{document.documentElement.dataset.theme=localStorage.getItem('blue-theme')||'light'}catch(e){}</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?><?php echo is_page_template( 'page-home.php' ) ? ' data-header="overlay"' : ''; ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#primary"><?php echo esc_html( blue_text( 'Skip to content', 'تخطَّ إلى المحتوى' ) ); ?></a>

<a class="annbar" href="<?php echo esc_url( $shop_url ); ?>">
	<?php echo esc_html( blue_option( 'announcement', blue_text( 'Free delivery and setup across Saudi Arabia.', 'توصيل وتركيب مجاني في جميع أنحاء المملكة العربية السعودية.' ) ) ); ?>
</a>
<header class="site-header<?php echo is_page_template( 'page-home.php' ) ? '' : ' solid'; ?>" id="siteHeader">
	<nav class="nav-row" aria-label="<?php esc_attr_e( 'Main navigation', 'blue-mattress' ); ?>">
		<a class="brand" href="<?php echo esc_url( blue_home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php echo wp_kses_post( blue_site_logo( 'brand-logo brand-logo--color' ) ); ?>
		</a>

		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav-links',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
		} else {
			?>
			<ul class="nav-links">
				<li><a class="nav-link nav-store" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( blue_text( 'Online Store', 'المتجر الإلكتروني' ) ); ?></a></li>
				<li><a class="nav-link" href="<?php echo esc_url( blue_page_url( 'page-mattress-finder.php', '/mattress-finder/' ) ); ?>"><?php echo esc_html( blue_text( 'Mattress Finder', 'مرشد المراتب' ) ); ?></a></li>
				<li><a class="nav-link" href="<?php echo esc_url( blue_page_url( 'page-our-story.php', '/our-story/' ) ); ?>"><?php echo esc_html( blue_text( 'Our Story', 'قصتنا' ) ); ?></a></li>
				<li><a class="nav-link" href="<?php echo esc_url( blue_page_url( 'page-stark.php', '/stark/' ) ); ?>"><?php echo esc_html( blue_text( 'STARK', 'ستارك' ) ); ?></a></li>
				<li><a class="nav-link" href="<?php echo esc_url( blue_page_url( 'page-contact.php', '/contact/' ) ); ?>"><?php echo esc_html( blue_text( 'Contact', 'تواصل معنا' ) ); ?></a></li>
			</ul>
			<?php
		}
		?>

		<div class="nav-actions">
			<button class="icon-btn" type="button" id="searchBtn" aria-label="<?php echo esc_attr( blue_text( 'Search', 'بحث' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/></svg>
			</button>
			<button class="icon-btn theme-toggle" type="button" id="themeBtn" aria-label="<?php echo esc_attr( blue_text( 'Toggle dark mode', 'تبديل الوضع الليلي' ) ); ?>">
				<svg class="th-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 14.2A8 8 0 1 1 9.8 4 6.4 6.4 0 0 0 20 14.2z"/></svg>
				<svg class="th-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="4.1"/><path d="M12 2.6v2.3M12 19.1v2.3M4.4 4.4L6 6m12 12 1.6 1.6M2.6 12h2.3M19.1 12h2.3M4.4 19.6 6 18M18 6l1.6-1.6"/></svg>
			</button>
			<?php blue_language_switcher(); ?>
			<a class="icon-btn blue-account-link" href="<?php echo esc_url( $account_url ); ?>" aria-label="<?php echo esc_attr( blue_text( 'My account', 'حسابي' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.7-4 3-6 7-6s6.3 2 7 6"/></svg>
			</a>
			<a class="icon-btn" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php echo esc_attr( blue_text( 'Cart', 'السلة' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 7h12l1.2 12.2a1.5 1.5 0 0 1-1.5 1.8H6.3a1.5 1.5 0 0 1-1.5-1.8L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
				<span class="blue-cart-count<?php echo $cart_count ? ' show' : ''; ?>"><?php echo absint( $cart_count ); ?></span>
			</a>
			<button class="icon-btn nav-burger" type="button" id="burgerBtn" aria-expanded="false" aria-controls="mobileMenu" aria-label="<?php echo esc_attr( blue_text( 'Menu', 'القائمة' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
			</button>
		</div>
	</nav>
</header>

<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
	<button class="icon-btn mm-close" id="mmClose" aria-label="<?php echo esc_attr( blue_text( 'Close', 'إغلاق' ) ); ?>">×</button>
	<a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( blue_text( 'Online Store', 'المتجر الإلكتروني' ) ); ?></a>
	<a href="<?php echo esc_url( blue_page_url( 'page-mattress-finder.php', '/mattress-finder/' ) ); ?>"><?php echo esc_html( blue_text( 'Mattress Finder', 'مرشد المراتب' ) ); ?></a>
	<a href="<?php echo esc_url( blue_page_url( 'page-our-story.php', '/our-story/' ) ); ?>"><?php echo esc_html( blue_text( 'Our Story', 'قصتنا' ) ); ?></a>
	<a href="<?php echo esc_url( blue_page_url( 'page-stark.php', '/stark/' ) ); ?>"><?php echo esc_html( blue_text( 'STARK', 'ستارك' ) ); ?></a>
	<a href="<?php echo esc_url( blue_page_url( 'page-contact.php', '/contact/' ) ); ?>"><?php echo esc_html( blue_text( 'Contact', 'تواصل معنا' ) ); ?></a>
	<?php blue_language_switcher(); ?>
</div>

<div class="search-overlay" id="searchOverlay" aria-hidden="true">
	<div class="search-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( blue_text( 'Product search', 'بحث المنتجات' ) ); ?>">
		<form class="search-form" role="search" method="get" action="<?php echo esc_url( blue_home_url( '/' ) ); ?>">
			<input type="search" id="searchInput" name="s" placeholder="<?php echo esc_attr( blue_text( 'Search products…', 'ابحث عن المنتجات…' ) ); ?>">
			<input type="hidden" name="post_type" value="product">
			<button class="icon-btn search-x" type="button" id="searchClose" aria-label="<?php echo esc_attr( blue_text( 'Close', 'إغلاق' ) ); ?>">×</button>
		</form>
		<div class="search-body" id="searchBody"></div>
	</div>
</div>
<div class="toast" id="toast" role="status"><span class="tick">✓</span><span id="toastMsg"></span></div>
