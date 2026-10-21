<?php
/**
 * One-click demo importer: Appearance > Import Demo.
 * Creates the 7 Elementor pages, uploads the images, adds the blog posts,
 * builds the menu and sets the home page.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
	add_theme_page( __( 'Import Demo', 'hm-portfolio' ), __( 'Import Demo', 'hm-portfolio' ), 'manage_options', 'hm-import', 'hm_import_screen' );
} );

function hm_import_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	echo '<div class="wrap"><h1>Import Demo Content</h1>';
	if ( isset( $_GET['hm_done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success"><p><strong>Done.</strong> Pages, images, blog posts and the menu were created. Open <em>Pages</em> and click <em>Edit with Elementor</em> on any page.</p></div>';
	}
	if ( ! did_action( 'elementor/loaded' ) ) {
		echo '<div class="notice notice-warning"><p>Elementor is not active. Install and activate it first (version 3.18 or newer).</p></div>';
	}
	echo '<p>This creates the pages Home, About, Services, Projects, Skills, Blog and Contact as native Elementor pages, uploads the theme images to your Media Library, adds 4 sample blog posts and sets up the menu and home page.</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="hm_run_import">';
	wp_nonce_field( 'hm_run_import', 'hm_import_nonce' );
	echo '<p><label><input type="checkbox" name="overwrite" value="1"> Replace pages that already exist with the same slug (your edits on them will be lost)</label></p>';
	echo '<p><button class="button button-primary button-hero" type="submit">Import now</button></p></form></div>';
}

function hm_import_media() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$map = array();
	foreach ( (array) glob( get_template_directory() . '/assets/images/*.{png,jpg,jpeg}', GLOB_BRACE ) as $file ) {
		$name     = basename( $file );
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_hm_asset', 'meta_value' => $name, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		if ( $existing ) {
			$map[ $name ] = array( 'id' => $existing[0], 'url' => wp_get_attachment_url( $existing[0] ) );
			continue;
		}
		$tmp = wp_tempnam( $name );
		if ( ! $tmp || ! copy( $file, $tmp ) ) { continue; }
		$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0 );
		if ( is_wp_error( $id ) ) { @unlink( $tmp ); continue; } // phpcs:ignore
		update_post_meta( $id, '_hm_asset', $name );
		$map[ $name ] = array( 'id' => $id, 'url' => wp_get_attachment_url( $id ) );
	}
	return $map;
}

function hm_import_walk( $v, $media, $pages ) {
	if ( is_array( $v ) ) {
		if ( isset( $v['widgetType'] ) && 'button' === $v['widgetType'] && isset( $v['settings']['text'] ) && false !== strpos( $v['settings']['text'], 'Download CV' ) ) {
			$cv = get_theme_mod( 'hm_cv_url', '' );
			if ( $cv ) { $v['settings']['link']['url'] = $cv; }
		}
		foreach ( $v as $k => $x ) { $v[ $k ] = hm_import_walk( $x, $media, $pages ); }
		return $v;
	}
	if ( ! is_string( $v ) ) { return $v; }
	if ( preg_match( '/^\{\{id:(.+?)\}\}$/', $v, $m ) ) {
		return isset( $media[ $m[1] ] ) ? (int) $media[ $m[1] ]['id'] : '';
	}
	return preg_replace_callback( '/\{\{(img|page|home):?([^}]*)\}\}/', function ( $x ) use ( $media, $pages ) {
		if ( 'img' === $x[1] ) { return isset( $media[ $x[2] ] ) ? $media[ $x[2] ]['url'] : ''; }
		if ( 'page' === $x[1] ) { return isset( $pages[ $x[2] ] ) ? get_permalink( $pages[ $x[2] ] ) : home_url( '/' ); }
		return home_url( '/' );
	}, $v );
}

add_action( 'admin_post_hm_run_import', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['hm_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hm_import_nonce'] ) ), 'hm_run_import' ) ) {
		wp_die( 'Not allowed.' );
	}
	@set_time_limit( 300 ); // phpcs:ignore
	$overwrite = ! empty( $_POST['overwrite'] );
	$dir       = get_template_directory() . '/elementor-data/';
	$media     = hm_import_media();
	$titles    = array( 'home' => 'Home', 'about' => 'About', 'services' => 'Services', 'projects' => 'Projects', 'skills' => 'Skills', 'blog' => 'Blog', 'contact' => 'Contact' );

	// Pass 1: create (or find) every page so links can point to them.
	$pages = array();
	$fresh = array();
	foreach ( $titles as $slug => $title ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$pages[ $slug ] = $existing->ID;
			if ( $overwrite || ! hm_is_elementor_page( $existing->ID ) ) { $fresh[ $slug ] = true; }
		} else {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ) );
			if ( $id && ! is_wp_error( $id ) ) { $pages[ $slug ] = $id; $fresh[ $slug ] = true; }
		}
	}

	// Pass 2: write the Elementor data.
	foreach ( $pages as $slug => $id ) {
		if ( empty( $fresh[ $slug ] ) ) { continue; }
		$file = $dir . $slug . '.json';
		if ( ! file_exists( $file ) ) { continue; }
		$data = json_decode( file_get_contents( $file ), true ); // phpcs:ignore
		if ( ! $data || empty( $data['content'] ) ) { continue; }
		$content = hm_import_walk( $data['content'], $media, $pages );
		update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.18.0' );
		update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
	}

	// Blog posts.
	$posts_file = $dir . 'posts.json';
	if ( file_exists( $posts_file ) ) {
		$posts = json_decode( file_get_contents( $posts_file ), true ); // phpcs:ignore
		foreach ( (array) $posts as $p ) {
			if ( get_page_by_path( $p['id'], OBJECT, 'post' ) ) { continue; }
			$pid = wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $p['id'],
				'post_content' => $p['body'],
				'post_excerpt' => $p['excerpt'],
			) );
			if ( ! $pid || is_wp_error( $pid ) ) { continue; }
			$cat = wp_create_category( $p['cat'] );
			if ( $cat ) { wp_set_post_categories( $pid, array( (int) $cat ) ); }
			if ( isset( $media[ $p['image'] ] ) ) { set_post_thumbnail( $pid, $media[ $p['image'] ]['id'] ); }
		}
	}

	// Menu.
	$menu = wp_get_nav_menu_object( 'Main Menu' );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( 'Main Menu' );
		if ( ! is_wp_error( $menu_id ) ) {
			foreach ( $titles as $slug => $title ) {
				if ( empty( $pages[ $slug ] ) ) { continue; }
				wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $slug ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			}
			$loc            = get_theme_mod( 'nav_menu_locations', array() );
			$loc['primary'] = $menu_id;
			$loc['footer']  = $menu_id;
			set_theme_mod( 'nav_menu_locations', $loc );
		}
	}

	// Site settings.
	update_option( 'blogname', 'Haroon Muneer' );
	update_option( 'blogdescription', 'Full Stack WordPress Developer' );
	if ( ! empty( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
	}
	flush_rewrite_rules();
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	wp_safe_redirect( admin_url( 'themes.php?page=hm-import&hm_done=1' ) );
	exit;
} );
