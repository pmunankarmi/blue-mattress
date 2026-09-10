<?php
/**
 * Template Name: Mattress Finder
 * Template Post Type: page
 *
 * @package BlueMattress
 */

get_header();

$finder_products = function_exists( 'blue_home_products' ) ? blue_home_products( 8 ) : array();
$finder_profiles = array();
foreach ( $finder_products as $finder_product ) {
	$finder_profiles[ $finder_product->get_id() ] = blue_product_finder_profile( $finder_product );
}

$finder_icon = static function ( string $key ): string {
	$paths = array(
		'me'       => '<circle cx="12" cy="7.6" r="3.1"/><path d="M5.4 19.5c.8-3.6 3.5-5.4 6.6-5.4s5.8 1.8 6.6 5.4"/>',
		'two'      => '<circle cx="9" cy="8.2" r="2.9"/><path d="M3.2 19.5c.7-3.2 3-4.9 5.8-4.9s5.1 1.7 5.8 4.9"/><circle cx="16.6" cy="7.6" r="2.4"/><path d="M16.2 14.7c2.4.3 4.1 1.9 4.6 4.5"/>',
		'child'    => '<circle cx="12" cy="10.2" r="3"/><circle cx="8.6" cy="6.8" r="1.3"/><circle cx="15.4" cy="6.8" r="1.3"/><path d="M6.8 19.5c.7-2.9 2.7-4.4 5.2-4.4s4.5 1.5 5.2 4.4"/>',
		'guest'    => '<path d="M3 18.5v-8m0 5h18v3m-18-6h16a2 2 0 0 1 2 2v1"/><path d="M5 12.5v-2A1.5 1.5 0 0 1 6.5 9H8a1.5 1.5 0 0 1 1.5 1.5v2"/>',
		'side'     => '<circle cx="6.3" cy="11.6" r="2"/><path d="M8.8 12.8c2.2-1.6 3.8-1.7 5.8-.4s3.9 1.1 5.4-.4M3.5 17.5h17"/>',
		'back'     => '<circle cx="6.3" cy="12.4" r="2"/><path d="M8.9 13.4h11.6M3.5 17.5h17"/>',
		'stomach'  => '<circle cx="6.3" cy="13" r="2"/><path d="M8.8 13.2c1.8-1.4 3.2-2 5-1s4.2 1.1 6.7.3M3.5 17.5h17"/>',
		'mixed'    => '<path d="M4.5 8h12.2m0 0-2.4-2.4M16.7 8l-2.4 2.4M19.5 16H7.3m0 0 2.4-2.4M7.3 16l2.4 2.4"/>',
		'soft'     => '<path d="M7.3 16.5a3.6 3.6 0 1 1 .5-7.2 4.6 4.6 0 0 1 8.9.9 3 3 0 0 1-.5 6.3z"/>',
		'balanced' => '<path d="M4 12h5m6 0h5"/><circle cx="12" cy="12" r="3"/>',
		'firm'     => '<rect x="4" y="9.8" width="16" height="4.4" rx="1.2"/>',
		'unsure'   => '<path d="M9.3 9.2a2.8 2.8 0 1 1 3.9 2.6c-.9.4-1.2 1-1.2 2M12 17.3v.2"/>',
		'very'     => '<path d="M10.4 4.9a1.7 1.7 0 0 1 3.4 0v7.2a3.7 3.7 0 1 1-3.4 0zM12.1 9v5.4"/>',
		'sometimes'=> '<circle cx="12" cy="13.4" r="3.1"/><path d="M12 6.2v2M5.2 13.4h-2m17.6 0h-2M7 8.4l1.4 1.4m8.6-1.4-1.4 1.4"/>',
		'rarely'   => '<path d="M12 4.8v14.4M6.2 8.4l11.6 7.2M17.8 8.4 6.2 15.6"/>',
		'b1'       => '<circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2.6"/>',
		'b2'       => '<ellipse cx="12" cy="7.4" rx="6.2" ry="2.5"/><path d="M5.8 7.4v9.2c0 1.4 2.8 2.5 6.2 2.5s6.2-1.1 6.2-2.5V7.4M5.8 12c0 1.4 2.8 2.5 6.2 2.5s6.2-1.1 6.2-2.5"/>',
		'b3'       => '<path d="M7.2 4.8h9.6l3 4.8-7.8 9.6-7.8-9.6zM4.2 9.6h15.6M12 19.2 9.2 9.6l2.8-4.8 2.8 4.8z"/>',
		'all'      => '<rect x="4.2" y="4.2" width="6.4" height="6.4" rx="1.4"/><rect x="13.4" y="4.2" width="6.4" height="6.4" rx="1.4"/><rect x="4.2" y="13.4" width="6.4" height="6.4" rx="1.4"/><rect x="13.4" y="13.4" width="6.4" height="6.4" rx="1.4"/>',
	);
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $key ] ?? '' ) . '</svg>';
};

$finder_dots = static function ( int $value ): string {
	$html = '<span class="dots" aria-hidden="true">';
	for ( $index = 1; $index <= 5; $index++ ) {
		$html .= '<i' . ( $index <= (int) ceil( $value / 2 ) ? ' class="on"' : '' ) . '></i>';
	}
	return $html . '</span>';
};

$budget_under   = function_exists( 'wc_price' ) ? wp_strip_all_tags( wc_price( 3000 ) ) : 'SAR 3,000';
$budget_between = function_exists( 'wc_price' ) ? wp_strip_all_tags( wc_price( 5000 ) ) : 'SAR 5,000';
$finder_count   = count( $finder_products );
?>
<main id="primary" class="blue-mattress-finder">
	<section class="sel-top">
		<div class="container">
			<div class="sel-hero">
				<span class="eyebrow"><?php echo esc_html( blue_field( 'finder_eyebrow', blue_text( 'Mattress Selector', 'مرشد المراتب' ) ) ); ?></span>
				<h1 class="display h1"><?php echo esc_html( blue_field( 'finder_heading', blue_text( 'Find the one you will sleep on.', 'اعثر على المرتبة التي تناسب نومك.' ) ) ); ?></h1>
				<p class="lead"><?php echo esc_html( blue_field( 'finder_intro', blue_text( 'Two ways to decide — take the quiz, or compare the collection.', 'طريقتان لتحسم قرارك — جرّب المرشد، أو قارن المجموعة.' ) ) ); ?></p>
			</div>
			<div class="mode-switch sel-switch" id="blueFinderSwitch" role="group" aria-label="<?php echo esc_attr( blue_text( 'Choose how to decide', 'اختر طريقتك في الاختيار' ) ); ?>">
				<button class="seg-btn is-on" type="button" data-finder-mode="guided" aria-pressed="true"><?php echo esc_html( blue_text( 'Guided', 'موجّه' ) ); ?></button>
				<button class="seg-btn" type="button" data-finder-mode="compare" aria-pressed="false"><?php echo esc_html( blue_text( 'Compare', 'قارن' ) ); ?></button>
			</div>
			<p class="sel-note" id="blueFinderModeNote"><?php echo esc_html( blue_text( 'Answer five quick questions and we will match you.', 'أجب عن خمسة أسئلة سريعة، ونرشّح لك الأنسب.' ) ); ?></p>
		</div>
	</section>

	<section class="sel-pane" id="pane-guided" data-finder-pane="guided">
		<div class="container">
			<div class="wiz" id="blueFinderQuiz" data-best-label="<?php echo esc_attr( blue_text( 'Your match', 'اخترناها لك' ) ); ?>" data-match-label="<?php echo esc_attr( blue_text( 'match', 'تطابق' ) ); ?>">
				<div class="wiz-bar" aria-hidden="true"><span class="wiz-fill" id="blueFinderProgress"></span></div>
				<div class="wiz-step-panel" data-finder-step="who">
					<p class="wiz-progtext"><?php echo esc_html( blue_text( 'Question 1 of 5', 'السؤال 1 من 5' ) ); ?></p>
					<h2 class="display wiz-q" tabindex="-1"><?php echo esc_html( blue_text( 'Who is this bed for?', 'لمن هذا السرير؟' ) ); ?></h2>
					<div class="wiz-opts">
						<?php foreach ( array( 'me' => blue_text( 'Just me', 'لي وحدي' ), 'two' => blue_text( 'Two of us', 'لنا نحن الاثنين' ), 'child' => blue_text( 'A child', 'لطفل' ), 'guest' => blue_text( 'Guest room', 'غرفة الضيوف' ) ) as $value => $label ) : ?>
							<button class="wiz-opt" type="button" data-finder-answer="who" data-value="<?php echo esc_attr( $value ); ?>"><span class="wiz-ico"><?php echo $finder_icon( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="wiz-step-panel" data-finder-step="position" hidden>
					<p class="wiz-progtext"><?php echo esc_html( blue_text( 'Question 2 of 5', 'السؤال 2 من 5' ) ); ?></p>
					<h2 class="display wiz-q" tabindex="-1"><?php echo esc_html( blue_text( 'How do you sleep?', 'كيف تنام عادةً؟' ) ); ?></h2>
					<div class="wiz-opts">
						<?php foreach ( array( 'side' => blue_text( 'On my side', 'على جنبي' ), 'back' => blue_text( 'On my back', 'على ظهري' ), 'stomach' => blue_text( 'On my stomach', 'على بطني' ), 'mixed' => blue_text( 'It varies', 'أتقلّب كثيرًا' ) ) as $value => $label ) : ?>
							<button class="wiz-opt" type="button" data-finder-answer="position" data-value="<?php echo esc_attr( $value ); ?>"><span class="wiz-ico"><?php echo $finder_icon( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="wiz-step-panel" data-finder-step="feel" hidden>
					<p class="wiz-progtext"><?php echo esc_html( blue_text( 'Question 3 of 5', 'السؤال 3 من 5' ) ); ?></p>
					<h2 class="display wiz-q" tabindex="-1"><?php echo esc_html( blue_text( 'What feel do you like?', 'أي إحساس تفضّل؟' ) ); ?></h2>
					<div class="wiz-opts">
						<?php foreach ( array( 'soft' => blue_text( 'Softer side', 'إلى الليونة' ), 'balanced' => blue_text( 'In the middle', 'بين بين' ), 'firm' => blue_text( 'Firmer side', 'إلى الصلابة' ), 'unsure' => blue_text( 'Not sure', 'لست متأكدًا' ) ) as $value => $label ) : ?>
							<button class="wiz-opt" type="button" data-finder-answer="feel" data-value="<?php echo esc_attr( $value ); ?>"><span class="wiz-ico"><?php echo $finder_icon( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="wiz-step-panel" data-finder-step="temperature" hidden>
					<p class="wiz-progtext"><?php echo esc_html( blue_text( 'Question 4 of 5', 'السؤال 4 من 5' ) ); ?></p>
					<h2 class="display wiz-q" tabindex="-1"><?php echo esc_html( blue_text( 'Do you sleep hot?', 'هل تشعر بالحرارة أثناء النوم؟' ) ); ?></h2>
					<div class="wiz-opts cols-3">
						<?php foreach ( array( 'very' => blue_text( 'Very', 'كثيرًا' ), 'sometimes' => blue_text( 'Sometimes', 'أحيانًا' ), 'rarely' => blue_text( 'Rarely', 'نادرًا' ) ) as $value => $label ) : ?>
							<button class="wiz-opt" type="button" data-finder-answer="temperature" data-value="<?php echo esc_attr( $value ); ?>"><span class="wiz-ico"><?php echo $finder_icon( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="wiz-step-panel" data-finder-step="budget" hidden>
					<p class="wiz-progtext"><?php echo esc_html( blue_text( 'Question 5 of 5', 'السؤال 5 من 5' ) ); ?></p>
					<h2 class="display wiz-q" tabindex="-1"><?php echo esc_html( blue_text( 'Budget for this bed?', 'كم ميزانيتك لهذا السرير؟' ) ); ?></h2>
					<div class="wiz-opts">
						<?php
						$budget_options = array(
							'b1'  => sprintf( blue_text( 'Under %s', 'أقل من %s' ), $budget_under ),
							'b2'  => sprintf( blue_text( '%1$s – %2$s', 'من %1$s إلى %2$s' ), $budget_under, $budget_between ),
							'b3'  => sprintf( blue_text( 'Above %s', 'أكثر من %s' ), $budget_between ),
							'all' => blue_text( 'Show me everything', 'أرني كل الخيارات' ),
						);
						foreach ( $budget_options as $value => $label ) :
							?>
							<button class="wiz-opt" type="button" data-finder-answer="budget" data-value="<?php echo esc_attr( $value ); ?>"><span class="wiz-ico"><?php echo $finder_icon( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="wiz-nav"><button class="wiz-back" id="blueFinderBack" type="button" hidden><?php echo esc_html( blue_text( '← Back', 'رجوع →' ) ); ?></button></div>
			</div>

			<div class="finder-results-section woocommerce" id="finder-results" hidden>
				<div class="finder-results-head">
					<div><p class="eyebrow"><?php echo esc_html( blue_text( 'Your match', 'اخترناها لك' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_text( 'Made for the way you sleep.', 'مصممة لطريقة نومك.' ) ); ?></h2><p class="lead" id="blueFinderSummary" aria-live="polite"></p></div>
					<button class="btn btn-ghost" id="blueFinderReset" type="button"><?php echo esc_html( blue_text( 'Start over', 'ابدأ من جديد' ) ); ?></button>
				</div>
				<?php if ( $finder_products ) : ?>
					<?php wc_set_loop_prop( 'name', 'blue_finder' ); wc_set_loop_prop( 'columns', 3 ); ?>
					<ul class="products columns-3 finder-product-grid" id="blueFinderResults">
						<?php
						foreach ( $finder_products as $finder_product ) {
							$post_object = get_post( $finder_product->get_id() );
							if ( ! $post_object ) {
								continue;
							}
							$GLOBALS['post']    = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							$GLOBALS['product'] = $finder_product;
							setup_postdata( $post_object );
							wc_get_template_part( 'content', 'product' );
						}
						wp_reset_postdata();
						wc_reset_loop();
						?>
					</ul>
				<?php else : ?>
					<p class="blue-empty"><?php echo esc_html( blue_text( 'Add published mattress products to WooCommerce to show matches.', 'أضف منتجات مراتب منشورة في ووكومرس لعرض النتائج.' ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="sel-pane" id="pane-compare" data-finder-pane="compare" hidden>
		<div class="container">
			<div class="cmp-hl">
				<span class="hl-title"><?php echo esc_html( blue_text( 'Highlight what matters to me', 'أبرِز ما يهمّك' ) ); ?></span>
				<div class="chip-row" id="blueFinderHighlights" role="group" aria-label="<?php echo esc_attr( blue_text( 'Highlight what matters to me', 'أبرِز ما يهمّك' ) ); ?>">
					<?php foreach ( array( 'cooling' => blue_text( 'Cooling', 'التبريد' ), 'motion' => blue_text( 'Motion', 'عزل الحركة' ), 'firm' => blue_text( 'Firm support', 'الدعم الصلب' ), 'value' => blue_text( 'Value', 'أفضل قيمة' ), 'soft' => blue_text( 'Softness', 'النعومة' ) ) as $key => $label ) : ?>
						<button class="chip-btn" type="button" data-finder-highlight="<?php echo esc_attr( $key ); ?>" aria-pressed="false"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
				<p class="hl-callout" id="blueFinderHighlightCallout" data-prefix="<?php echo esc_attr( blue_text( 'Best for your priorities:', 'الأنسب لأولوياتك:' ) ); ?>" hidden></p>
			</div>
			<h2 class="sr-only"><?php echo esc_html( sprintf( blue_text( 'Compare %d mattresses', 'قارن %d مراتب' ), $finder_count ) ); ?></h2>

			<?php if ( $finder_products ) : ?>
				<div class="cmp-scroll">
					<div class="cmp-grid" id="blueFinderCompareGrid" style="--cmp-cols:<?php echo esc_attr( (string) $finder_count ); ?>">
						<div class="cmp-cell cmp-head" data-col="0" aria-hidden="true"></div>
						<?php foreach ( $finder_products as $index => $finder_product ) : $profile = $finder_profiles[ $finder_product->get_id() ]; ?>
							<div class="cmp-cell cmp-head" data-col="<?php echo esc_attr( (string) ( $index + 1 ) ); ?>" data-compare-product data-product-id="<?php echo esc_attr( (string) $finder_product->get_id() ); ?>" data-product-name="<?php echo esc_attr( $finder_product->get_name() ); ?>" data-feel="<?php echo esc_attr( (string) $profile['feel'] ); ?>" data-cooling="<?php echo esc_attr( (string) $profile['cooling'] ); ?>" data-motion="<?php echo esc_attr( (string) $profile['motion'] ); ?>" data-price="<?php echo esc_attr( (string) $profile['price'] ); ?>">
								<span class="cmp-chip finder-match-chip" hidden><?php echo esc_html( blue_text( 'Your match', 'اخترناها لك' ) ); ?></span>
								<a href="<?php echo esc_url( $finder_product->get_permalink() ); ?>"><?php echo wp_kses_post( $finder_product->get_image( 'woocommerce_thumbnail', array( 'alt' => $finder_product->get_name() ) ) ); ?></a>
								<span class="cmp-name"><a href="<?php echo esc_url( $finder_product->get_permalink() ); ?>"><?php echo esc_html( $finder_product->get_name() ); ?></a></span>
								<span class="cmp-from"><?php echo esc_html( blue_text( 'From', 'ابتداءً من' ) ); ?> <?php echo wp_kses_post( wc_price( $profile['price'] ) ); ?></span>
								<a class="cmp-cta" href="<?php echo esc_url( $finder_product->get_permalink() ); ?>"><?php echo esc_html( blue_text( 'Details', 'التفاصيل' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a>
							</div>
						<?php endforeach; ?>
						<?php
						$compare_rows = array(
							'feel'   => blue_text( 'Feel', 'الإحساس' ),
							'height' => blue_text( 'Height', 'الارتفاع' ),
							'best'   => blue_text( 'Best for', 'الأنسب لـ' ),
							'cool'   => blue_text( 'Cooling', 'التبريد' ),
							'motion' => blue_text( 'Motion isolation', 'عزل الحركة' ),
							'trial'  => blue_text( 'Trial & warranty', 'التجربة والضمان' ),
							'price'  => blue_text( 'Price from', 'السعر يبدأ من' ),
						);
						foreach ( $compare_rows as $row_key => $row_label ) :
							?>
							<div class="cmp-cell cmp-lab" data-col="0" data-row="<?php echo esc_attr( $row_key ); ?>"><?php echo esc_html( $row_label ); ?></div>
							<?php foreach ( $finder_products as $index => $finder_product ) : $profile = $finder_profiles[ $finder_product->get_id() ]; ?>
								<div class="cmp-cell" data-col="<?php echo esc_attr( (string) ( $index + 1 ) ); ?>" data-row="<?php echo esc_attr( $row_key ); ?>">
									<?php if ( 'feel' === $row_key ) : ?>
										<div class="feel" style="--feel:<?php echo esc_attr( (string) $profile['feel'] ); ?>"><div class="feel-track"></div></div><span class="cmp-feellab"><?php echo esc_html( blue_product_field( $finder_product, 'product_feel_label', blue_text( 'Balanced', 'متوازنة' ) ) ); ?></span>
									<?php elseif ( 'height' === $row_key ) : ?>
										<?php echo $profile['height'] ? esc_html( $profile['height'] . ' ' . blue_text( 'cm', 'سم' ) ) : '—'; ?>
									<?php elseif ( 'best' === $row_key ) : ?>
										<?php echo esc_html( blue_product_finder_best_for( $finder_product ) ); ?>
									<?php elseif ( 'cool' === $row_key ) : ?>
										<?php echo $finder_dots( $profile['cooling'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php elseif ( 'motion' === $row_key ) : ?>
										<?php echo $finder_dots( $profile['motion'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php elseif ( 'trial' === $row_key ) : ?>
										<?php echo esc_html( blue_text( '50 nights · 10 years', '50 ليلة · 10 سنوات' ) ); ?>
									<?php else : ?>
										<span class="cmp-price"><?php echo wp_kses_post( wc_price( $profile['price'] ) ); ?></span>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="cmp-cards" id="blueFinderCompareCards">
					<?php foreach ( $finder_products as $finder_product ) : $profile = $finder_profiles[ $finder_product->get_id() ]; ?>
						<article class="cmp-card" data-compare-product data-product-id="<?php echo esc_attr( (string) $finder_product->get_id() ); ?>" data-product-name="<?php echo esc_attr( $finder_product->get_name() ); ?>" data-feel="<?php echo esc_attr( (string) $profile['feel'] ); ?>" data-cooling="<?php echo esc_attr( (string) $profile['cooling'] ); ?>" data-motion="<?php echo esc_attr( (string) $profile['motion'] ); ?>" data-price="<?php echo esc_attr( (string) $profile['price'] ); ?>">
							<?php echo wp_kses_post( $finder_product->get_image( 'woocommerce_thumbnail', array( 'alt' => $finder_product->get_name() ) ) ); ?>
							<div class="cmp-card-head"><span class="cmp-chip finder-match-chip" hidden><?php echo esc_html( blue_text( 'Your match', 'اخترناها لك' ) ); ?></span><span class="cmp-name"><?php echo esc_html( $finder_product->get_name() ); ?></span><span class="cmp-from"><?php echo esc_html( blue_text( 'From', 'ابتداءً من' ) ); ?> <?php echo wp_kses_post( wc_price( $profile['price'] ) ); ?></span></div>
							<div class="cmp-row" data-row="feel"><span class="k"><?php echo esc_html( blue_text( 'Feel', 'الإحساس' ) ); ?></span><span class="v"><?php echo esc_html( blue_product_field( $finder_product, 'product_feel_label', blue_text( 'Balanced', 'متوازنة' ) ) ); ?></span></div>
							<div class="cmp-row" data-row="height"><span class="k"><?php echo esc_html( blue_text( 'Height', 'الارتفاع' ) ); ?></span><span class="v"><?php echo $profile['height'] ? esc_html( $profile['height'] . ' ' . blue_text( 'cm', 'سم' ) ) : '—'; ?></span></div>
							<div class="cmp-row" data-row="best"><span class="k"><?php echo esc_html( blue_text( 'Best for', 'الأنسب لـ' ) ); ?></span><span class="v"><?php echo esc_html( blue_product_finder_best_for( $finder_product ) ); ?></span></div>
							<div class="cmp-row" data-row="cool"><span class="k"><?php echo esc_html( blue_text( 'Cooling', 'التبريد' ) ); ?></span><span class="v"><?php echo $finder_dots( $profile['cooling'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></div>
							<div class="cmp-row" data-row="motion"><span class="k"><?php echo esc_html( blue_text( 'Motion', 'عزل الحركة' ) ); ?></span><span class="v"><?php echo $finder_dots( $profile['motion'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></div>
							<div class="cmp-row" data-row="trial"><span class="k"><?php echo esc_html( blue_text( 'Trial & warranty', 'التجربة والضمان' ) ); ?></span><span class="v"><?php echo esc_html( blue_text( '50 nights · 10 years', '50 ليلة · 10 سنوات' ) ); ?></span></div>
							<div class="cmp-row" data-row="price"><span class="k"><?php echo esc_html( blue_text( 'Price from', 'السعر يبدأ من' ) ); ?></span><span class="v cmp-price"><?php echo wp_kses_post( wc_price( $profile['price'] ) ); ?></span></div>
							<div class="cmp-card-cta"><a class="btn btn-sm" href="<?php echo esc_url( $finder_product->get_permalink() ); ?>"><?php echo esc_html( blue_text( 'Details', 'التفاصيل' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a></div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="blue-empty"><?php echo esc_html( blue_text( 'Add published mattress products to WooCommerce to compare them.', 'أضف منتجات مراتب منشورة في ووكومرس لمقارنتها.' ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="finder-help on-navy section-tight"><div class="container finder-help-inner"><div><p class="eyebrow"><?php echo esc_html( blue_text( 'Still deciding?', 'ما زلت محتارًا؟' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_text( 'Talk it through with a sleep specialist.', 'تحدث مع أحد مختصي النوم.' ) ); ?></h2></div><a class="btn btn-light" href="<?php echo esc_url( blue_page_url( 'page-contact.php', '/contact/' ) ); ?>"><?php echo esc_html( blue_text( 'Contact us', 'تواصل معنا' ) ); ?></a></div></section>
</main>
<?php get_footer(); ?>
