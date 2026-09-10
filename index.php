<?php
/**
 * Posts and fallback template.
 *
 * @package BlueMattress
 */

get_header();
?>
<main id="primary" class="blue-content section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<header class="sec-head"><h1 class="display h1"><?php echo esc_html( is_home() ? get_bloginfo( 'name' ) : get_the_archive_title() ); ?></h1></header>
			<div class="blue-post-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'blue-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?></a><?php endif; ?>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing was found.', 'blue-mattress' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>

