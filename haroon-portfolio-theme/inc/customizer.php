<?php
/**
 * Customizer: contact details, social links, CV link, colours and footer options.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function hm_default_colors() {
	return array( 'hm_gold' => '#e3bd8a', 'hm_gold_light' => '#f0d3a6', 'hm_bg' => '#0a0807' );
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'hm_options', array(
		'title'       => __( 'Portfolio Details', 'hm-portfolio' ),
		'description' => __( 'Contact details, social links and footer text used across the theme.', 'hm-portfolio' ),
		'priority'    => 30,
	) );
	$fields = array(
		'hm_logo_text' => array( 'Logo text (signature)', 'Haroon Muneer', 'sanitize_text_field', 'text' ),
		'hm_tagline'   => array( 'Footer tagline', 'Full Stack WordPress Developer | AI Automation Engineer', 'sanitize_text_field', 'text' ),
		'hm_copyright' => array( 'Copyright text (leave empty for the default)', '', 'sanitize_text_field', 'text' ),
		'hm_email'     => array( 'Email (contact form delivers here)', 'haroonmuneer@gmail.com', 'sanitize_email', 'email' ),
		'hm_linkedin'  => array( 'LinkedIn URL', 'https://linkedin.com/in/haroonmuneer', 'esc_url_raw', 'url' ),
		'hm_github'    => array( 'GitHub URL', 'https://github.com/HaroonMuneer', 'esc_url_raw', 'url' ),
		'hm_x'         => array( 'X (Twitter) URL', '', 'esc_url_raw', 'url' ),
		'hm_youtube'   => array( 'YouTube URL', '', 'esc_url_raw', 'url' ),
		'hm_cv_url'    => array( 'CV file URL (PDF from Media Library)', '', 'esc_url_raw', 'url' ),
	);
	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[1], 'sanitize_callback' => $f[2] ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'hm_options', 'type' => $f[3] ) );
	}
	$wp_customize->add_setting( 'hm_show_top', array( 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$wp_customize->add_control( 'hm_show_top', array( 'label' => __( 'Show the back-to-top button', 'hm-portfolio' ), 'section' => 'hm_options', 'type' => 'checkbox' ) );

	$wp_customize->add_section( 'hm_colors', array( 'title' => __( 'Portfolio Colours', 'hm-portfolio' ), 'priority' => 31 ) );
	$labels = array( 'hm_gold' => 'Gold (accent)', 'hm_gold_light' => 'Light gold (headings)', 'hm_bg' => 'Page background' );
	foreach ( hm_default_colors() as $id => $default ) {
		$wp_customize->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $labels[ $id ], 'section' => 'hm_colors' ) ) );
	}
} );

// Output colour overrides only when they differ from the defaults.
add_action( 'wp_head', function () {
	$vars = array( 'hm_gold' => '--hm-gold', 'hm_gold_light' => '--hm-gold-light', 'hm_bg' => '--hm-bg' );
	$css  = '';
	foreach ( hm_default_colors() as $id => $default ) {
		$v = sanitize_hex_color( get_theme_mod( $id, $default ) );
		if ( $v && strtolower( $v ) !== $default ) { $css .= $vars[ $id ] . ':' . $v . ';'; }
	}
	if ( $css ) { echo '<style id="hm-colors">:root{' . esc_html( $css ) . '}</style>' . "\n"; } // phpcs:ignore
}, 30 );
