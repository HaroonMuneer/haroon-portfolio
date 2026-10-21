<?php get_header(); ?>
<section class="hm-archive"><div class="hm-container">
	<h1 class="hm-archive-title"><?php
		if ( is_search() ) { printf( esc_html__( 'Search: %s', 'hm-portfolio' ), esc_html( get_search_query() ) ); }
		elseif ( is_archive() ) { the_archive_title(); }
		else { esc_html_e( 'Latest articles', 'hm-portfolio' ); }
	?></h1>
	<?php if ( have_posts() ) : ?>
		<div class="hm-posts-grid">
		<?php while ( have_posts() ) : the_post(); ?>
			<article class="hm-post-card">
				<?php if ( has_post_thumbnail() ) : ?><a class="hm-post-thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'hm-card' ); ?></a><?php endif; ?>
				<div class="hm-post-body">
					<small><?php echo esc_html( get_the_date() ); ?></small>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
					<span class="read"><?php esc_html_e( 'Read article', 'hm-portfolio' ); ?> &rarr;</span>
				</div>
			</article>
		<?php endwhile; ?>
		</div>
		<div class="hm-pagination"><?php echo wp_kses_post( paginate_links( array( 'type' => 'plain' ) ) ); ?></div>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'hm-portfolio' ); ?></p>
	<?php endif; ?>
</div></section>
<?php get_footer();
