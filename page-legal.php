<?php
/**
 * Template Name: Legal Page
 * Template Post Type: page
 *
 * @package BlueMattress
 */

get_header();
?>
<main id="primary"><section class="legal-hero"><div class="container"><p class="eyebrow"><?php echo esc_html( blue_text( 'Legal', 'قانوني' ) ); ?></p><h1 class="display h2"><?php the_title(); ?></h1><p><?php echo esc_html( sprintf( blue_text( 'Last updated: %s', 'آخر تحديث: %s' ), get_the_modified_date() ) ); ?></p></div></section><div class="container"><article class="legal-body"><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></article></div></main>
<?php get_footer(); ?>

