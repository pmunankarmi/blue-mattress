<?php
/**
 * Template Name: Contact Page
 * Template Post Type: page
 *
 * @package BlueMattress
 */

get_header();
$status  = sanitize_key( wp_unslash( $_GET['contact_status'] ?? '' ) );
$ref     = sanitize_text_field( wp_unslash( $_GET['contact_ref'] ?? '' ) );
$phone   = (string) blue_option( 'contact_phone', '+966 12 000 0000' );
$email   = (string) blue_option( 'contact_email', get_option( 'admin_email' ) );
$address = (string) blue_option( 'contact_address', blue_text( 'Prince Saud Al Faisal St, Ar Rawdah, Jeddah', 'شارع الأمير سعود الفيصل، الروضة، جدة' ) );
$hours   = (string) blue_option( 'contact_hours', blue_text( "Saturday–Thursday: 9:00–22:00\nFriday: 16:00–22:00", "السبت–الخميس: 9:00–22:00\nالجمعة: 16:00–22:00" ) );
$map     = (string) blue_option( 'contact_map_url' );
?>
<main id="primary" class="contact-page">
	<section class="ct-hero"><div class="container"><span class="eyebrow"><?php echo esc_html( blue_field( 'contact_eyebrow', blue_text( 'Contact', 'تواصل معنا' ) ) ); ?></span><h1 class="display h1 ct-hero-title"><?php echo esc_html( blue_field( 'contact_heading', blue_text( "Let's talk sleep.", 'لنتحدث عن النوم.' ) ) ); ?></h1><p class="lead ct-hero-lead"><?php echo esc_html( blue_field( 'contact_intro', blue_text( 'Whether you are choosing one mattress or furnishing a hotel, we are one message away.', 'سواء كنت تختار مرتبة واحدة أو تجهز فندقًا، فنحن على بُعد رسالة واحدة.' ) ) ); ?></p></div></section>

	<section class="ct-main" id="contact-form"><div class="container"><div class="ct-grid">
		<aside class="ct-info">
			<h2 class="display"><?php echo esc_html( blue_text( 'Visit or reach us', 'زرنا أو تواصل معنا' ) ); ?></h2>
			<div class="ct-info-list">
				<div class="ct-item"><span class="ct-ic" aria-hidden="true">⌖</span><div class="ct-item-body"><span class="ct-item-label"><?php echo esc_html( blue_text( 'Showroom', 'المعرض' ) ); ?></span><span class="ct-item-val"><?php echo nl2br( esc_html( $address ) ); ?></span></div></div>
				<div class="ct-item"><span class="ct-ic" aria-hidden="true">☎</span><div class="ct-item-body"><span class="ct-item-label"><?php echo esc_html( blue_text( 'Phone', 'الهاتف' ) ); ?></span><span class="ct-item-val"><a class="ct-ltr" href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></span></div></div>
				<div class="ct-item"><span class="ct-ic" aria-hidden="true">✉</span><div class="ct-item-body"><span class="ct-item-label"><?php echo esc_html( blue_text( 'Email', 'البريد الإلكتروني' ) ); ?></span><span class="ct-item-val"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span></div></div>
				<div class="ct-item"><span class="ct-ic" aria-hidden="true">◷</span><div class="ct-item-body"><span class="ct-item-label"><?php echo esc_html( blue_text( 'Opening hours', 'ساعات العمل' ) ); ?></span><span class="ct-item-val"><?php echo nl2br( esc_html( $hours ) ); ?></span></div></div>
			</div>
			<?php $socials = blue_social_links(); if ( $socials ) : ?><div class="ct-follow"><span class="ct-follow-label"><?php echo esc_html( blue_text( 'Follow Blue', 'تابع بلو' ) ); ?></span><div class="social-row on-light"><?php foreach ( $socials as $social ) : $social_icon = blue_social_icon_key( $social ); ?><a class="social-ic" href="<?php echo esc_url( $social['url'] ?? '' ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $social['label'] ?? '' ); ?>"><?php echo blue_social_icon_svg( $social_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed theme SVG. ?></a><?php endforeach; ?></div></div><?php endif; ?>
		</aside>

		<div class="ct-formcard">
			<?php if ( 'success' === $status ) : ?>
				<div class="ct-success"><span class="ct-success-badge" aria-hidden="true">✓</span><span class="eyebrow"><?php echo esc_html( blue_text( 'Message received', 'تم استلام الرسالة' ) ); ?></span><h2 class="ct-success-title"><?php echo esc_html( blue_text( 'Thank you — we will be in touch.', 'شكرًا لك — سنتواصل معك.' ) ); ?></h2><p class="ct-success-body"><?php echo esc_html( blue_text( 'A member of our team will review your message and reply using the contact details you provided.', 'سيراجع أحد أعضاء فريقنا رسالتك ويرد عبر بيانات التواصل التي قدمتها.' ) ); ?></p><?php if ( $ref ) : ?><div class="ct-ref-wrap"><span class="ct-ref-label"><?php echo esc_html( blue_text( 'Reference', 'المرجع' ) ); ?></span><strong class="ct-ref"><?php echo esc_html( $ref ); ?></strong></div><?php endif; ?><a class="btn" href="<?php echo esc_url( remove_query_arg( array( 'contact_status', 'contact_ref' ) ) ); ?>"><?php echo esc_html( blue_text( 'Send another message', 'أرسل رسالة أخرى' ) ); ?></a></div>
			<?php else : ?>
				<?php if ( $status ) : ?><div class="blue-form-alert" role="alert"><?php echo esc_html( blue_text( 'Please review the form and try again.', 'يرجى مراجعة النموذج والمحاولة مرة أخرى.' ) ); ?></div><?php endif; ?>
				<form id="ctForm" class="ct-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="blue_contact_submit"><?php wp_nonce_field( 'blue_contact_submit', 'blue_contact_nonce' ); ?><div class="blue-honeypot" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
					<header class="ct-form-head"><h2 class="ct-form-title"><?php echo esc_html( blue_text( 'How can we help?', 'كيف يمكننا مساعدتك؟' ) ); ?></h2><p class="ct-form-sub"><?php echo esc_html( blue_text( 'Tell us what you need and the right person will reply.', 'أخبرنا بما تحتاجه وسيرد عليك الشخص المناسب.' ) ); ?></p></header>
					<div class="ct-seg" role="radiogroup" aria-label="<?php echo esc_attr( blue_text( 'Enquiry type', 'نوع الاستفسار' ) ); ?>"><label><input type="radio" name="audience" value="personal" checked><span><?php echo esc_html( blue_text( 'Personal', 'شخصي' ) ); ?></span></label><label><input type="radio" name="audience" value="business"><span><?php echo esc_html( blue_text( 'Business / Wholesale', 'أعمال / جملة' ) ); ?></span></label></div>
					<div class="ct-field"><label for="ctName"><?php echo esc_html( blue_text( 'Name', 'الاسم' ) ); ?></label><input id="ctName" name="name" type="text" required minlength="2" autocomplete="name"></div>
					<div class="ct-row2"><div class="ct-field"><label for="ctEmail"><?php echo esc_html( blue_text( 'Email', 'البريد الإلكتروني' ) ); ?></label><input id="ctEmail" name="email" type="email" required autocomplete="email"></div><div class="ct-field"><label for="ctPhone"><?php echo esc_html( blue_text( 'Phone', 'الهاتف' ) ); ?></label><input id="ctPhone" name="phone" type="tel" autocomplete="tel" dir="ltr"></div></div>
					<div class="ct-field blue-company-field"><label for="ctCompany"><?php echo esc_html( blue_text( 'Company', 'الشركة' ) ); ?></label><input id="ctCompany" name="company" type="text" autocomplete="organization"></div>
					<div class="ct-row2"><div class="ct-field"><label for="ctCity"><?php echo esc_html( blue_text( 'City', 'المدينة' ) ); ?></label><input id="ctCity" name="city" type="text"></div><div class="ct-field"><label for="ctSubject"><?php echo esc_html( blue_text( 'Subject', 'الموضوع' ) ); ?></label><input id="ctSubject" name="subject" type="text"></div></div>
					<div class="ct-field"><label for="ctMessage"><?php echo esc_html( blue_text( 'Message', 'الرسالة' ) ); ?></label><textarea id="ctMessage" name="message" rows="5" required minlength="2"></textarea></div>
					<label class="ct-consent"><input type="checkbox" name="consent" value="1" required><span><?php echo esc_html( blue_text( 'I agree that Blue Mattress may contact me about this enquiry.', 'أوافق على أن تتواصل معي مراتب بلو بخصوص هذا الاستفسار.' ) ); ?></span></label>
					<button class="btn ct-submit" type="submit"><?php echo esc_html( blue_text( 'Send message', 'إرسال الرسالة' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></button>
				</form>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $map ) : ?><div class="ct-map-wrap"><div class="ct-map"><iframe src="<?php echo esc_url( $map ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo esc_attr( blue_text( 'Blue Mattress location', 'موقع مراتب بلو' ) ); ?>"></iframe><div class="ct-map-card"><span class="ct-map-eyebrow"><?php echo esc_html( blue_text( 'Blue Mattress showroom', 'معرض مراتب بلو' ) ); ?></span><b><?php echo esc_html( blue_text( 'Come try a Blue.', 'تعال وجرّب بلو.' ) ); ?></b><span class="ct-map-addr"><?php echo nl2br( esc_html( $address ) ); ?></span></div></div></div><?php endif; ?>
	<div class="ct-trust"><div class="ct-trust-item"><span aria-hidden="true">☾</span><div><b><?php echo esc_html( blue_text( 'Sleep specialists', 'مختصو نوم' ) ); ?></b><span><?php echo esc_html( blue_text( 'Practical, pressure-free help', 'مساعدة عملية بلا ضغط' ) ); ?></span></div></div><div class="ct-trust-item"><span aria-hidden="true">⌂</span><div><b><?php echo esc_html( blue_text( 'Kingdom-wide', 'في أنحاء المملكة' ) ); ?></b><span><?php echo esc_html( blue_text( 'Delivery and setup support', 'دعم التوصيل والتركيب' ) ); ?></span></div></div><div class="ct-trust-item"><span aria-hidden="true">◇</span><div><b><?php echo esc_html( blue_text( 'Business enquiries', 'استفسارات الأعمال' ) ); ?></b><span><?php echo esc_html( blue_text( 'Hospitality and wholesale', 'الضيافة والجملة' ) ); ?></span></div></div></div>
	</div></section>
</main>
<?php get_footer(); ?>
