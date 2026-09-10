<?php
/**
 * Template Name: Our Story
 * Template Post Type: page
 *
 * @package BlueMattress
 */

get_header();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : blue_home_url( '/shop/' );
$finder_url = blue_page_url( 'page-mattress-finder.php', '/mattress-finder/' );
$stark_url  = blue_page_url( 'page-stark.php', '/stark/' );
$hero       = blue_image_url( blue_field( 'story_hero_image' ), BLUE_THEME_URI . '/assets/img/hero-dawn.jpg' );
$origin     = blue_image_url( blue_field( 'story_origin_image' ), BLUE_THEME_URI . '/assets/img/craft.jpg' );
$craft      = blue_image_url( blue_field( 'story_craft_image' ), BLUE_THEME_URI . '/assets/img/stark-craft.jpg' );
$values     = blue_field(
	'story_values',
	array(
		array( 'title' => blue_text( 'Honest comfort', 'راحة صادقة' ), 'description' => blue_text( 'Every layer has a job, and we name it clearly.', 'لكل طبقة وظيفة، ونذكرها بوضوح.' ) ),
		array( 'title' => blue_text( 'Made here', 'صُنعت هنا' ), 'description' => blue_text( 'Designed for Saudi homes and built with local manufacturing expertise.', 'مصممة للمنازل السعودية ومصنوعة بخبرة تصنيع محلية.' ) ),
		array( 'title' => blue_text( 'Better mornings', 'صباحات أفضل' ), 'description' => blue_text( 'We judge every material by what it does for tomorrow morning.', 'نقيّم كل مادة بما تقدمه لصباح الغد.' ) ),
	)
);
$timeline = blue_field(
	'story_timeline',
	array(
		array( 'year' => '1967', 'title' => blue_text( 'Industrial roots', 'جذور صناعية' ), 'description' => blue_text( 'The manufacturing knowledge behind Blue begins in Jeddah.', 'بدأت الخبرة التصنيعية خلف بلو في جدة.' ) ),
		array( 'year' => blue_text( 'Today', 'اليوم' ), 'title' => blue_text( 'A sleep brand of our own', 'علامة نوم خاصة بنا' ), 'description' => blue_text( 'Blue turns decades of foam, furniture and mattress experience into a focused collection.', 'تحوّل بلو عقودًا من خبرة الإسفنج والأثاث والمراتب إلى تشكيلة مركزة.' ) ),
		array( 'year' => blue_text( 'Next', 'المستقبل' ), 'title' => blue_text( 'Rest, refined', 'راحة تتطور' ), 'description' => blue_text( 'We keep testing materials, listening to sleepers and improving every detail.', 'نواصل اختبار المواد والاستماع للنائمين وتحسين كل تفصيل.' ) ),
	)
);
?>
<main id="primary" class="story-page">
	<section class="story-hero">
		<img class="story-hero-image" src="<?php echo esc_url( $hero ); ?>" alt="" fetchpriority="high">
		<div class="story-hero-veil" aria-hidden="true"></div>
		<div class="container story-hero-inner">
			<p class="eyebrow light"><?php echo esc_html( blue_field( 'story_eyebrow', blue_text( 'Our story', 'قصتنا' ) ) ); ?></p>
			<h1 class="display"><?php echo esc_html( blue_field( 'story_hero_heading', blue_text( 'Better sleep, built close to home.', 'نوم أفضل، صُنع قريبًا من البيت.' ) ) ); ?></h1>
			<p><?php echo esc_html( blue_field( 'story_hero_lead', blue_text( 'Blue is a Saudi sleep brand shaped by decades of manufacturing knowledge and one simple belief: a better night changes the day that follows.', 'بلو علامة نوم سعودية صاغتها عقود من المعرفة التصنيعية وإيمان بسيط: الليلة الأفضل تغيّر اليوم الذي يليها.' ) ) ); ?></p>
		</div>
	</section>

	<section class="story-origin section">
		<div class="container story-split">
			<figure><img src="<?php echo esc_url( $origin ); ?>" alt="<?php echo esc_attr( blue_text( 'Blue Mattress craftsmanship', 'حرفية مراتب بلو' ) ); ?>" loading="lazy"></figure>
			<div class="story-copy"><p class="eyebrow"><?php echo esc_html( blue_text( 'Why Blue', 'لماذا بلو' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_field( 'story_origin_heading', blue_text( 'Born from making. Focused on rest.', 'وُلدت من الصناعة. وتركّز على الراحة.' ) ) ); ?></h2><div class="story-rich"><?php echo wp_kses_post( blue_field( 'story_origin_body', blue_text( '<p>Long before Blue had a name, our teams were learning how foam behaves, how springs carry weight and how careful upholstery changes the feel of a bed.</p><p>We created Blue to bring that knowledge directly into the bedroom—with clear choices, useful technology and no mystery materials.</p>', '<p>قبل أن تحمل بلو اسمها، كانت فرقنا تتعلم كيف يتصرف الإسفنج، وكيف تحمل النوابض الوزن، وكيف تغير الخياطة المتقنة إحساس السرير.</p><p>أنشأنا بلو لننقل هذه المعرفة مباشرة إلى غرفة النوم—بخيارات واضحة وتقنيات مفيدة ومن دون مواد غامضة.</p>' ) ) ); ?></div></div>
		</div>
	</section>

	<section class="story-values section-tight"><div class="container"><div class="sec-head center"><p class="eyebrow"><?php echo esc_html( blue_text( 'What guides us', 'ما يوجّهنا' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_text( 'Simple standards. Felt every night.', 'معايير بسيطة. تشعر بها كل ليلة.' ) ); ?></h2></div><div class="story-value-grid"><?php foreach ( is_array( $values ) ? $values : array() as $index => $value ) : ?><article class="story-value"><span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3><?php echo esc_html( $value['title'] ?? '' ); ?></h3><p><?php echo esc_html( $value['description'] ?? '' ); ?></p></article><?php endforeach; ?></div></div></section>

	<section class="story-craft"><img src="<?php echo esc_url( $craft ); ?>" alt="" loading="lazy"><div class="story-craft-veil"></div><div class="container story-craft-copy"><p class="eyebrow light"><?php echo esc_html( blue_text( 'Our craft', 'حِرفتنا' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_field( 'story_craft_heading', blue_text( 'Machines bring precision. People bring judgment.', 'الآلات تمنح الدقة. والناس يمنحون الخبرة.' ) ) ); ?></h2><p><?php echo esc_html( blue_field( 'story_craft_text', blue_text( 'Every Blue combines modern manufacturing with the trained eye and hand of people who know how a finished mattress should feel.', 'تجمع كل مرتبة بلو بين التصنيع الحديث وعين ويد أشخاص يعرفون تمامًا كيف يجب أن يكون إحساس المرتبة النهائية.' ) ) ); ?></p></div></section>

	<section class="story-time section"><div class="container"><div class="sec-head"><p class="eyebrow"><?php echo esc_html( blue_text( 'The journey', 'المسيرة' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_text( 'Knowledge, carried forward.', 'معرفة تمتد إلى الأمام.' ) ); ?></h2></div><ol class="story-timeline"><?php foreach ( is_array( $timeline ) ? $timeline : array() as $item ) : ?><li><span class="story-year"><?php echo esc_html( $item['year'] ?? '' ); ?></span><div><h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3><p><?php echo esc_html( $item['description'] ?? '' ); ?></p></div></li><?php endforeach; ?></ol></div></section>

	<section class="story-stark section-tight"><div class="container story-stark-inner"><div><p class="eyebrow"><?php echo esc_html( blue_text( 'Behind Blue', 'خلف بلو' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_text( 'Backed by STARK.', 'بدعم من ستارك.' ) ); ?></h2><p class="lead"><?php echo esc_html( blue_text( 'Our mother company brings integrated expertise in mattresses, furniture and wood manufacturing from its facilities in Jeddah.', 'تقدم شركتنا الأم خبرة متكاملة في تصنيع المراتب والأثاث والأخشاب من منشآتها في جدة.' ) ); ?></p></div><a class="btn btn-ghost" href="<?php echo esc_url( $stark_url ); ?>"><?php echo esc_html( blue_text( 'Discover STARK', 'اكتشف ستارك' ) ); ?> <span class="arr"><?php echo esc_html( blue_text( '→', '←' ) ); ?></span></a></div></section>

	<section class="story-final on-navy section"><div class="container story-final-inner"><p class="eyebrow"><?php echo esc_html( blue_text( 'Your next night', 'ليلتك القادمة' ) ); ?></p><h2 class="display h2"><?php echo esc_html( blue_field( 'story_final_heading', blue_text( 'Find the Blue that feels like yours.', 'اعثر على مرتبة بلو التي تشبهك.' ) ) ); ?></h2><p><?php echo esc_html( blue_field( 'story_final_text', blue_text( 'Explore the collection or let four simple questions guide you.', 'استكشف التشكيلة أو دع أربعة أسئلة بسيطة ترشدك.' ) ) ); ?></p><div><a class="btn btn-light" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( blue_text( 'Shop mattresses', 'تسوّق المراتب' ) ); ?></a><a class="btn btn-ghost-light" href="<?php echo esc_url( $finder_url ); ?>"><?php echo esc_html( blue_text( 'Use the Mattress Finder', 'استخدم مرشد المراتب' ) ); ?></a></div></div></section>
</main>
<?php get_footer(); ?>
