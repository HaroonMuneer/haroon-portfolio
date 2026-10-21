<?php get_header(); ?>
<?php while ( have_posts() ) : the_post();
	$id    = get_the_ID();
	$kind  = get_post_meta( $id, 'hm_kind', true );
	$stack = get_post_meta( $id, 'hm_stack', true );
	$url   = get_post_meta( $id, 'hm_url', true );
	$tags  = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $id, 'hm_tags', true ) ) ) );
	?>
	<article class="hm-article hm-project-single">
		<span class="meta"><?php echo esc_html( $kind ); ?></span>
		<h1><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) : ?><div class="hm-featured"><?php the_post_thumbnail( 'large' ); ?></div><?php endif; ?>
		<?php the_content(); ?>
		<?php if ( $stack ) : ?><p><strong><?php esc_html_e( 'Stack:', 'hm-portfolio' ); ?></strong> <?php echo esc_html( $stack ); ?></p><?php endif; ?>
		<?php if ( $tags ) : ?><div class="hm-sc-tags"><?php foreach ( $tags as $t ) { echo '<span>' . esc_html( $t ) . '</span>'; } ?></div><?php endif; ?>
		<p class="hm-btn-row">
			<?php if ( $url ) : ?><a class="hm-btn" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit site', 'hm-portfolio' ); ?> &nearr;</a><?php endif; ?>
			<a class="hm-btn" href="<?php echo esc_url( get_post_type_archive_link( 'hm_project' ) ); ?>">&larr; <?php esc_html_e( 'All projects', 'hm-portfolio' ); ?></a>
		</p>
		<nav class="hm-pagination"><?php previous_post_link( '%link', '&larr; %title' ); ?> <?php next_post_link( '%link', '%title &rarr;' ); ?></nav>
	</article>
<?php endwhile; ?>
<?php get_footer();
