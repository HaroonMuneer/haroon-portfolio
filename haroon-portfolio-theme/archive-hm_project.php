<?php get_header(); ?>
<section class="hm-archive"><div class="hm-container">
	<h1 class="hm-archive-title"><?php echo is_tax() ? esc_html( single_term_title( '', false ) ) : esc_html__( 'Selected work', 'hm-portfolio' ); ?></h1>
	<?php if ( have_posts() ) : ?>
		<div class="hm-sc-grid"><?php while ( have_posts() ) { the_post(); echo hm_project_card_html( get_the_ID() ); } // phpcs:ignore ?></div>
		<div class="hm-pagination"><?php echo wp_kses_post( paginate_links( array( 'type' => 'plain' ) ) ); ?></div>
	<?php else : ?>
		<p><?php esc_html_e( 'No projects found.', 'hm-portfolio' ); ?></p>
	<?php endif; ?>
</div></section>
<?php get_footer();
