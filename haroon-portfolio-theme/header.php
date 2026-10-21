<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0a0807">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
	<div class="hm-container header-inner">
		<?php if ( has_custom_logo() ) : ?>
			<span class="hm-logo"><?php the_custom_logo(); ?></span>
		<?php else : ?>
			<a class="hm-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><?php echo esc_html( get_theme_mod( 'hm_logo_text', 'Haroon Muneer' ) ); ?></a>
		<?php endif; ?>
		<nav class="hm-primary-nav" aria-label="<?php esc_attr_e( 'Main', 'hm-portfolio' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav-list',
				'fallback_cb'    => function () {
					$items = array( '' => 'Home', 'about' => 'About', 'services' => 'Services', 'projects' => 'Projects', 'skills' => 'Skills', 'blog' => 'Blog', 'contact' => 'Contact' );
					echo '<ul class="nav-list">';
					foreach ( $items as $slug => $label ) {
						$url = $slug ? hm_page_url( $slug ) : home_url( '/' );
						echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
					}
					echo '</ul>';
				},
			) );
			?>
		</nav>
		<a class="hm-btn header-cta" href="<?php echo esc_url( hm_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Hire Me', 'hm-portfolio' ); ?> <?php echo hm_icon( 'send' ); // phpcs:ignore ?></a>
		<button class="menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'hm-portfolio' ); ?>" aria-expanded="false">&#9776;</button>
	</div>
</header>
<main class="hm-main" id="content">
