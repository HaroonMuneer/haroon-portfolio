<?php
/**
 * WooCommerce support: wrappers, gallery features, cart count and dark/gold styling.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( function_exists( 'is_woocommerce' ) ) {
		wp_enqueue_style( 'hm-woocommerce', get_template_directory_uri() . '/assets/css/woocommerce.css', array( 'hm-theme' ), HM_VERSION );
	}
}, 30 );

// Replace WooCommerce's default wrappers with the theme's.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', function () { echo '<section class="hm-archive hm-shop"><div class="hm-container">'; } );
add_action( 'woocommerce_after_main_content', function () { echo '</div></section>'; } );

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['.hm-cart-count'] = '<span class="hm-cart-count">' . (int) WC()->cart->get_cart_contents_count() . '</span>';
	return $fragments;
} );

add_filter( 'loop_shop_columns', function () { return 3; } );
add_filter( 'woocommerce_output_related_products_args', function ( $args ) { $args['posts_per_page'] = 3; $args['columns'] = 3; return $args; } );
