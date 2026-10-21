<?php get_header(); ?>
<section class="hm-archive"><div class="hm-container">
	<h1 class="hm-archive-title">404</h1>
	<p><?php esc_html_e( 'That page could not be found.', 'hm-portfolio' ); ?></p>
	<p><a class="hm-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'hm-portfolio' ); ?></a></p>
</div></section>
<?php get_footer();
