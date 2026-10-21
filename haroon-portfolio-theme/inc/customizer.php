<?php
/**
 * Customizer: contact details, social links, CV link.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'hm_options', array(
		'title'    => __( 'Portfolio Details', 'hm-portfolio' ),
		'priority' => 30,
	) );
	$fields = array(
		'hm_logo_text' => array( 'Logo text (signature)', 'Haroon Muneer', 'sanitize_text_field', 'text' ),
		'hm_tagline'   => array( 'Footer tagline', 'Full Stack WordPress Developer | AI Automation Engineer', 'sanitize_text_field', 'text' ),
		'hm_email'     => array( 'Email (contact form delivers here)', 'haroonmuneer@gmail.com', 'sanitize_email', 'email' ),
		'hm_linkedin'  => array( 'LinkedIn URL', 'https://linkedin.com/in/haroonmuneer', 'esc_url_raw', 'url' ),
		'hm_github'    => array( 'GitHub URL', 'https://github.com/haroonmuneer', 'esc_url_raw', 'url' ),
		'hm_x'         => array( 'X (Twitter) URL', '', 'esc_url_raw', 'url' ),
		'hm_youtube'   => array( 'YouTube URL', '', 'esc_url_raw', 'url' ),
		'hm_cv_url'    => array( 'CV file URL (PDF from Media Library)', '', 'esc_url_raw', 'url' ),
	);
	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[1], 'sanitize_callback' => $f[2] ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'hm_options', 'type' => $f[3] ) );
	}
} );
