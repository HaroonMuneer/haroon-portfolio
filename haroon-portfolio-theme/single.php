<?php get_header(); ?>
<?php while ( have_posts() ) : the_post(); ?>
	<article class="hm-article">
		<span class="meta"><?php echo esc_html( get_the_date() ); ?> &middot; <?php echo esc_html( get_the_category_list( ', ' ) ? wp_strip_all_tags( get_the_category_list( ', ' ) ) : '' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) : ?><div class="hm-featured"><?php the_post_thumbnail( 'large' ); ?></div><?php endif; ?>
		<?php the_content(); ?>
		<p><a class="hm-btn" href="<?php echo esc_url( hm_page_url( 'blog' ) ); ?>">&larr; <?php esc_html_e( 'All articles', 'hm-portfolio' ); ?></a></p>
	</article>
<?php endwhile; ?>
<?php get_footer();
