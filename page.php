<?php
/**
 * Default page template.
 *
 * @package BlueMattress
 */

get_header();
?>
<main id="primary" class="blue-content section">
	<div class="container blue-prose">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1 class="display h1"><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
		<?php endwhile; ?>
	</div>
</main>
<?php get_footer(); ?>

