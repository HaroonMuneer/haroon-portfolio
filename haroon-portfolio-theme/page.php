<?php get_header(); ?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php if ( function_exists( 'hm_is_elementor_page' ) && hm_is_elementor_page( get_the_ID() ) ) : ?>
		<?php the_content(); ?>
	<?php else : ?>
		<article class="hm-article">
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</article>
	<?php endif; ?>
<?php endwhile; ?>
<?php get_footer();
