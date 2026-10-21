<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
$socials = array(
	'linkedin' => array( get_theme_mod( 'hm_linkedin', 'https://linkedin.com/in/haroonmuneer' ), 'LinkedIn' ),
	'github'   => array( get_theme_mod( 'hm_github', 'https://github.com/haroonmuneer' ), 'GitHub' ),
	'x'        => array( get_theme_mod( 'hm_x', '' ), 'X' ),
	'youtube'  => array( get_theme_mod( 'hm_youtube', '' ), 'YouTube' ),
);
?>
</main>
<footer class="site-footer">
	<div class="hm-container">
		<div class="footer-top">
			<div class="footer-brand">
				<a class="hm-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_theme_mod( 'hm_logo_text', 'Haroon Muneer' ) ); ?></a>
				<small><?php echo esc_html( get_theme_mod( 'hm_tagline', 'Full Stack WordPress Developer | AI Automation Engineer' ) ); ?></small>
			</div>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'footer-nav',
				'depth'          => 1,
				'fallback_cb'    => function () {
					$items = array( '' => 'Home', 'about' => 'About', 'services' => 'Services', 'projects' => 'Projects', 'skills' => 'Skills', 'blog' => 'Blog', 'contact' => 'Contact' );
					echo '<ul class="footer-nav">';
					foreach ( $items as $slug => $label ) {
						echo '<li><a href="' . esc_url( $slug ? hm_page_url( $slug ) : home_url( '/' ) ) . '">' . esc_html( $label ) . '</a></li>';
					}
					echo '</ul>';
				},
			) );
			?>
			<div class="socials">
				<?php foreach ( $socials as $key => $s ) : if ( empty( $s[0] ) ) { continue; } ?>
					<a href="<?php echo esc_url( $s[0] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $s[1] ); ?>"><?php echo hm_icon( $key ); // phpcs:ignore ?></a>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_theme_mod( 'hm_logo_text', 'Haroon Muneer' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'hm-portfolio' ); ?></span>
			<span class="words"><span>Build</span><span>Automate</span><span>Grow</span></span>
		</div>
	</div>
</footer>
<button class="hm-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'hm-portfolio' ); ?>">&uarr;</button>
<?php wp_footer(); ?>
</body>
</html>
