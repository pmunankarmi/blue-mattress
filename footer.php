<?php
/**
 * Site footer.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : blue_home_url( '/shop/' );
$phone    = (string) blue_option( 'contact_phone' );
$email    = (string) blue_option( 'contact_email' );
$cr       = (string) blue_option( 'commercial_register', '7054657023' );
$vat      = (string) blue_option( 'vat_number', '314868318200003' );
$terms    = (string) blue_option( 'terms_page', blue_home_url( '/terms/' ) );
$privacy  = (string) blue_option( 'privacy_page', blue_home_url( '/privacy/' ) );
$map      = (string) blue_option( 'contact_map_url' );
$map_link = (string) blue_option( 'contact_map_link' );
$footer_note = (string) blue_option( 'footer_note' );
$newsletter_placeholder = (string) blue_option( 'newsletter_placeholder', blue_text( 'Email for sleep stories & offers', 'بريدك لقصص النوم والعروض' ) );
$newsletter_button = (string) blue_option( 'newsletter_button', blue_text( 'Join', 'اشترك' ) );
$footer_logo = blue_image_url( blue_option( 'footer_logo' ), BLUE_THEME_URI . '/assets/img/logo-white.png' );
$social_links = blue_social_links();
$whatsapp_url = (string) blue_option( 'whatsapp_url' );
if ( ! $whatsapp_url ) {
	foreach ( $social_links as $social ) {
		if ( 'whatsapp' === blue_social_icon_key( $social ) ) {
			$whatsapp_url = (string) ( $social['url'] ?? '' );
			break;
		}
	}
}
$footer_terms = taxonomy_exists( 'product_cat' ) ? get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 5 ) ) : array();
$footer_terms = is_wp_error( $footer_terms ) ? array() : $footer_terms;
?>
<footer class="site-footer">
	<div class="footer-glow"></div>
	<div class="container">
		<div class="footer-top">
			<div class="footer-brand">
				<img class="footer-logo" src="<?php echo esc_url( $footer_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<p class="footer-tagline"><?php echo esc_html( blue_option( 'footer_tagline', blue_text( "Feels like magic, but it's really just science.", 'إحساسٌ كالسحر، لكنه في الحقيقة مجرّد علم.' ) ) ); ?></p>
				<?php if ( $footer_note ) : ?><p class="footer-note"><?php echo esc_html( $footer_note ); ?></p><?php endif; ?>
				<form class="newsletter" id="newsletter" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="blue_newsletter_subscribe">
					<?php wp_nonce_field( 'blue_newsletter_subscribe', 'blue_newsletter_nonce' ); ?>
					<label class="sr-only" for="blue-newsletter-email"><?php echo esc_html( blue_text( 'Email address', 'البريد الإلكتروني' ) ); ?></label>
					<input id="blue-newsletter-email" type="email" name="email" required autocomplete="email" placeholder="<?php echo esc_attr( $newsletter_placeholder ); ?>">
					<button type="submit"><?php echo esc_html( $newsletter_button ); ?></button>
				</form>
				<?php if ( isset( $_GET['newsletter_status'] ) ) : ?><p class="footer-form-status" role="status"><?php echo esc_html( 'subscribed' === sanitize_key( wp_unslash( $_GET['newsletter_status'] ) ) ? blue_text( 'Thank you for subscribing.', 'شكرًا لاشتراكك.' ) : blue_text( 'Please enter a valid email address.', 'يرجى إدخال بريد إلكتروني صحيح.' ) ); ?></p><?php endif; ?>
				<div class="social-row">
					<?php foreach ( $social_links as $social ) : ?>
						<?php $social_icon = blue_social_icon_key( $social ); ?>
						<a class="social-ic social-<?php echo esc_attr( $social_icon ); ?>" href="<?php echo esc_url( $social['url'] ?? '' ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ?? '' ); ?>"><?php echo blue_social_icon_svg( $social_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed theme SVG. ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="fcol">
				<h4><?php echo esc_html( blue_text( 'Shop', 'تسوّق' ) ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( blue_text( 'Online store', 'المتجر الإلكتروني' ) ); ?></a></li>
					<?php foreach ( $footer_terms as $term ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( blue_product_term_name( $term ) ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( add_query_arg( 'on_sale', '1', $shop_url ) ); ?>"><?php echo esc_html( blue_option( 'footer_sale_label', blue_text( 'Sale', 'التخفيضات' ) ) ); ?></a></li>
				</ul>
			</div>
			<div class="fmap footer-contact">
				<h4><?php echo esc_html( blue_text( 'Find us', 'موقعنا' ) ); ?></h4>
				<?php if ( $map ) : ?><div class="fmap-frame"><iframe src="<?php echo esc_url( $map ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo esc_attr( blue_text( 'Blue Mattress showroom', 'معرض مراتب بلو' ) ); ?>"></iframe></div><?php endif; ?>
				<?php $address = (string) blue_option( 'contact_address', blue_text( 'Prince Saud Al Faisal St, Ar Rawdah, Jeddah', 'شارع الأمير سعود الفيصل، الروضة، جدة' ) ); ?>
				<?php if ( $map_link ) : ?><a class="fmap-link" href="<?php echo esc_url( $map_link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $address ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a><?php else : ?><p class="fmap-link"><?php echo esc_html( $address ); ?></p><?php endif; ?>
			</div>
			</div>
		<div class="footer-legal">
			<?php if ( $cr ) : ?><div class="legal-item"><img class="legal-cr" src="<?php echo esc_url( BLUE_THEME_URI . '/assets/img/cr-badge.png' ); ?>" width="64" height="64" alt=""><div class="legal-txt"><span><?php echo esc_html( blue_text( 'Commercial Register', 'السجل التجاري' ) ); ?></span><b dir="ltr"><?php echo esc_html( $cr ); ?></b></div></div><?php endif; ?>
			<?php if ( $vat ) : ?><div class="legal-item"><div class="legal-txt"><span><?php echo esc_html( blue_text( 'VAT Account Number', 'الرقم الضريبي' ) ); ?></span><b dir="ltr"><?php echo esc_html( $vat ); ?></b></div></div><?php endif; ?>
			<div class="pay-icons" aria-label="<?php echo esc_attr( blue_text( 'Payment methods', 'طرق الدفع' ) ); ?>">
				<img src="<?php echo esc_url( BLUE_THEME_URI . '/assets/img/pay-mada_mini.png' ); ?>" alt="mada">
				<img src="<?php echo esc_url( BLUE_THEME_URI . '/assets/img/pay-credit_card_mini.png' ); ?>" alt="Visa / Mastercard">
				<img src="<?php echo esc_url( BLUE_THEME_URI . '/assets/img/pay-apple_pay_mini.png' ); ?>" alt="Apple Pay">
				<img class="pay-tabby" src="<?php echo esc_url( BLUE_THEME_URI . '/assets/img/pay-tabby-badge.svg' ); ?>" alt="Tabby">
				<img class="pay-tamara" src="<?php echo esc_url( BLUE_THEME_URI . '/assets/img/pay-tamara-badge.svg' ); ?>" alt="Tamara">
			</div>
		</div>
		<div class="footer-bottom">
			<span><?php echo esc_html( blue_option( 'copyright', sprintf( blue_text( '© %d Blue Mattresses. All rights reserved.', '© %d مراتب بلو. جميع الحقوق محفوظة.' ), wp_date( 'Y' ) ) ) ); ?></span>
			<?php if ( $terms || $privacy ) : ?><span class="legal-links"><?php if ( $terms ) : ?><a href="<?php echo esc_url( $terms ); ?>"><?php echo esc_html( blue_text( 'Terms & Conditions', 'الشروط والأحكام' ) ); ?></a><?php endif; ?><?php echo $terms && $privacy ? ' · ' : ''; ?><?php if ( $privacy ) : ?><a href="<?php echo esc_url( $privacy ); ?>"><?php echo esc_html( blue_text( 'Privacy Policy', 'سياسة الخصوصية' ) ); ?></a><?php endif; ?></span><?php endif; ?>
		</div>
	</div>
</footer>
<?php if ( $whatsapp_url ) : ?>
	<a class="wa-fab" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( blue_text( 'Chat with us on WhatsApp', 'تحدث معنا عبر واتساب' ) ); ?>">
		<span class="wa-pulse" aria-hidden="true"></span><?php echo blue_social_icon_svg( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed theme SVG. ?>
	</a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
