<?php
/**
 * Haroon Muneer Portfolio: theme setup.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'HM_VERSION', '1.0.0' );

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'hm-portfolio', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 320, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'hm-portfolio' ),
		'footer'  => __( 'Footer Menu', 'hm-portfolio' ),
	) );
	add_image_size( 'hm-card', 800, 560, true );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'hm-theme', get_template_directory_uri() . '/assets/css/theme.css', array(), HM_VERSION );
	wp_enqueue_style( 'hm-style', get_stylesheet_uri(), array( 'hm-theme' ), HM_VERSION );
	wp_enqueue_script( 'hm-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), HM_VERSION, true );
}, 20 );

// Preload the display font so the hero title does not flash.
add_action( 'wp_head', function () {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_template_directory_uri() . '/assets/fonts/bodoni-moda-latin-opsz-normal.woff2' ) );
}, 1 );

add_filter( 'body_class', function ( $c ) { $c[] = 'hm-theme'; return $c; } );

// Allow SVG/woff2 uploads is intentionally NOT enabled; PNG/JPG artwork is used instead.

/** Helpers */
function hm_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
}

function hm_icon( $name ) {
	$icons = array(
		'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-7M12 13a3 3 0 016 0v4"/>',
		'github'   => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.9a3.4 3.4 0 00-.9-2.6c3-.3 6.1-1.5 6.1-6.7a5.2 5.2 0 00-1.4-3.6 4.8 4.8 0 00-.1-3.6s-1.2-.4-3.8 1.4a13 13 0 00-7 0C6.4 2.5 5.2 2.9 5.2 2.9a4.8 4.8 0 00-.1 3.6A5.2 5.2 0 003.7 10c0 5.2 3.1 6.4 6.1 6.7a3.4 3.4 0 00-.9 2.6V22"/>',
		'x'        => '<path d="M4 4l16 16M20 4L4 20"/>',
		'youtube'  => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9l5 3-5 3z"/>',
		'send'     => '<path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/>',
	);
	if ( ! isset( $icons[ $name ] ) ) { return ''; }
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[ $name ] . '</svg>';
}

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/importer.php';

// Friendly notice when Elementor is missing.
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || did_action( 'elementor/loaded' ) ) { return; }
	echo '<div class="notice notice-warning"><p><strong>Haroon Muneer Portfolio:</strong> install and activate the free <em>Elementor</em> plugin (version 3.18 or newer, with Containers enabled) so the pages can be edited visually. Then go to <a href="' . esc_url( admin_url( 'themes.php?page=hm-import' ) ) . '">Appearance &gt; Import Demo</a>.</p></div>';
} );
