<?php
/**
 * One-click demo importer: Appearance > Import Demo.
 * Creates the 7 Elementor pages, uploads the images, adds blog posts and projects,
 * builds the menu and sets the home page. Safe to run again: nothing is duplicated
 * and existing pages are only replaced when the box is ticked.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
	add_theme_page( __( 'Import Demo', 'hm-portfolio' ), __( 'Import Demo', 'hm-portfolio' ), 'manage_options', 'hm-import', 'hm_import_screen' );
} );

function hm_import_checks() {
	$out = array();
	$out[] = array( did_action( 'elementor/loaded' ), 'Elementor is active', 'Install and activate the free Elementor plugin (3.18 or newer).' );
	$out[] = array( 'inactive' !== get_option( 'elementor_experiment-container', 'default' ), 'Elementor Containers are enabled', 'Elementor > Settings > Features > Container: set to Active.' );
	$out[] = array( version_compare( PHP_VERSION, '7.4', '>=' ), 'PHP 7.4 or newer', 'Ask your host to upgrade PHP.' );
	$out[] = array( is_writable( wp_get_upload_dir()['basedir'] ), 'Uploads folder is writable', 'Make wp-content/uploads writable so images can be imported.' );
	$out[] = array( file_exists( get_template_directory() . '/elementor-data/home.json' ), 'Theme data files present', 'Re-upload the theme zip; the elementor-data folder is missing.' );
	return $out;
}

function hm_import_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	echo '<div class="wrap"><h1>Import Demo Content</h1>';
	$report = get_transient( 'hm_import_report' );
	if ( isset( $_GET['hm_done'] ) && $report ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-' . ( empty( $report['errors'] ) ? 'success' : 'warning' ) . '"><p><strong>Import finished.</strong></p><ul style="list-style:disc;margin-left:20px">';
		foreach ( $report['lines'] as $l ) { echo '<li>' . esc_html( $l ) . '</li>'; }
		echo '</ul>';
		if ( ! empty( $report['errors'] ) ) {
			echo '<p><strong>Problems:</strong></p><ul style="list-style:disc;margin-left:20px">';
			foreach ( $report['errors'] as $e ) { echo '<li>' . esc_html( $e ) . '</li>'; }
			echo '</ul>';
		}
		echo '<p>Open <em>Pages</em> and choose <em>Edit with Elementor</em> on any page.</p></div>';
		delete_transient( 'hm_import_report' );
	}
	echo '<h2>Checks</h2><table class="widefat striped" style="max-width:720px"><tbody>';
	$all_ok = true;
	foreach ( hm_import_checks() as $c ) {
		$ok = (bool) $c[0];
		$all_ok = $all_ok && $ok;
		echo '<tr><td style="width:30px">' . ( $ok ? '&#9989;' : '&#10060;' ) . '</td><td>' . esc_html( $c[1] ) . ( $ok ? '' : '<br><small>' . esc_html( $c[2] ) . '</small>' ) . '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<p style="max-width:720px">This creates the pages Home, About, Services, Projects, Skills, Blog and Contact as Elementor pages, uploads the theme images to the Media Library, adds 4 sample blog posts and 6 sample projects, builds the menu and sets the home page.</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="hm_run_import">';
	wp_nonce_field( 'hm_run_import', 'hm_import_nonce' );
	echo '<p><label><input type="checkbox" name="overwrite" value="1"> Replace pages that already exist with the same slug (your edits on them will be lost)</label></p>';
	if ( ! $all_ok ) { echo '<p><em>Some checks failed. You can still import, but fix them first for the best result.</em></p>'; }
	echo '<p><button class="button button-primary button-hero" type="submit">Import now</button></p></form></div>';
}

function hm_import_media( &$errors ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$map   = array();
	$files = glob( get_template_directory() . '/assets/images/*.{png,jpg,jpeg}', GLOB_BRACE );
	foreach ( (array) $files as $file ) {
		$name     = basename( $file );
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_hm_asset', 'meta_value' => $name, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		if ( $existing ) {
			$map[ $name ] = array( 'id' => $existing[0], 'url' => wp_get_attachment_url( $existing[0] ), 'new' => false );
			continue;
		}
		$tmp = wp_tempnam( $name );
		if ( ! $tmp || ! copy( $file, $tmp ) ) { $errors[] = "Could not copy image $name."; continue; }
		$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0 );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore
			$errors[] = "Image $name failed: " . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, '_hm_asset', $name );
		$map[ $name ] = array( 'id' => $id, 'url' => wp_get_attachment_url( $id ), 'new' => true );
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

function hm_import_read_json( $file, &$errors ) {
	if ( ! file_exists( $file ) ) { $errors[] = 'Missing data file: ' . basename( $file ); return null; }
	$data = json_decode( file_get_contents( $file ), true ); // phpcs:ignore
	if ( null === $data ) { $errors[] = 'Could not read ' . basename( $file ) . ': ' . json_last_error_msg(); }
	return $data;
}

function hm_run_import() {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['hm_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hm_import_nonce'] ) ), 'hm_run_import' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'hm-portfolio' ), 403 );
	}
	@set_time_limit( 300 ); // phpcs:ignore
	@ini_set( 'memory_limit', '256M' ); // phpcs:ignore
	$overwrite = ! empty( $_POST['overwrite'] );
	$dir       = get_template_directory() . '/elementor-data/';
	$errors    = array();
	$lines     = array();
	$media     = hm_import_media( $errors );
	$new_imgs  = count( array_filter( wp_list_pluck( $media, 'new' ) ) );
	$lines[]   = sprintf( 'Images: %d added, %d already in the Media Library.', $new_imgs, count( $media ) - $new_imgs );
	$titles    = array( 'home' => 'Home', 'about' => 'About', 'services' => 'Services', 'projects' => 'Projects', 'skills' => 'Skills', 'blog' => 'Blog', 'contact' => 'Contact' );

	// Pass 1: create or find every page so links can point to them.
	$pages = array();
	$write = array();
	$made  = 0;
	foreach ( $titles as $slug => $title ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$pages[ $slug ] = $existing->ID;
			if ( $overwrite || ! hm_is_elementor_page( $existing->ID ) ) { $write[ $slug ] = true; }
		} else {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ), true );
			if ( is_wp_error( $id ) ) { $errors[] = "Page $title could not be created: " . $id->get_error_message(); continue; }
			$pages[ $slug ] = $id;
			$write[ $slug ] = true;
			$made++;
		}
	}

	// Pass 2: write the Elementor data.
	$filled = 0;
	foreach ( $pages as $slug => $id ) {
		if ( empty( $write[ $slug ] ) ) { continue; }
		$data = hm_import_read_json( $dir . $slug . '.json', $errors );
		if ( ! $data || empty( $data['content'] ) ) { continue; }
		$content = hm_import_walk( $data['content'], $media, $pages );
		$json    = wp_json_encode( $content );
		if ( ! $json ) { $errors[] = "Page $slug: could not encode the layout."; continue; }
		update_post_meta( $id, '_elementor_data', wp_slash( $json ) );
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.18.0' );
		update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
		$filled++;
	}
	$lines[] = sprintf( 'Pages: %d created, %d filled with Elementor layouts, %d left untouched.', $made, $filled, count( $pages ) - $filled );

	// Let Elementor edit the Projects post type.
	$cpts = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
	if ( is_array( $cpts ) && ! in_array( 'hm_project', $cpts, true ) ) {
		$cpts[] = 'hm_project';
		update_option( 'elementor_cpt_support', $cpts );
	}

	// Blog posts.
	$posts = hm_import_read_json( $dir . 'posts.json', $errors );
	$added = 0;
	foreach ( (array) $posts as $p ) {
		if ( get_page_by_path( $p['id'], OBJECT, 'post' ) ) { continue; }
		$pid = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $p['title'], 'post_name' => $p['id'], 'post_content' => $p['body'], 'post_excerpt' => $p['excerpt'] ), true );
		if ( is_wp_error( $pid ) ) { $errors[] = 'Post ' . $p['title'] . ' failed: ' . $pid->get_error_message(); continue; }
		$cat = wp_create_category( $p['cat'] );
		if ( $cat ) { wp_set_post_categories( $pid, array( (int) $cat ) ); }
		if ( isset( $media[ $p['image'] ] ) ) { set_post_thumbnail( $pid, $media[ $p['image'] ]['id'] ); }
		$added++;
	}
	$lines[] = sprintf( 'Blog posts: %d added.', $added );

	// Projects (custom post type).
	$projects = hm_import_read_json( $dir . 'projects-cpt.json', $errors );
	$added    = 0;
	foreach ( (array) $projects as $p ) {
		if ( get_page_by_path( $p['slug'], OBJECT, 'hm_project' ) ) { continue; }
		$pid = wp_insert_post( array( 'post_type' => 'hm_project', 'post_status' => 'publish', 'post_title' => $p['title'], 'post_name' => $p['slug'], 'post_content' => $p['desc'], 'post_excerpt' => $p['desc'] ), true );
		if ( is_wp_error( $pid ) ) { $errors[] = 'Project ' . $p['title'] . ' failed: ' . $pid->get_error_message(); continue; }
		update_post_meta( $pid, 'hm_kind', $p['kind'] );
		update_post_meta( $pid, 'hm_stack', $p['stack'] );
		update_post_meta( $pid, 'hm_tags', implode( ', ', $p['tags'] ) );
		$term_ids = array();
		foreach ( $p['cats'] as $c ) {
			$t = term_exists( $c, 'hm_project_cat' );
			if ( ! $t ) { $t = wp_insert_term( ucfirst( $c ), 'hm_project_cat', array( 'slug' => $c ) ); }
			if ( ! is_wp_error( $t ) ) { $term_ids[] = (int) ( is_array( $t ) ? $t['term_id'] : $t ); }
		}
		if ( $term_ids ) { wp_set_object_terms( $pid, $term_ids, 'hm_project_cat' ); }
		if ( isset( $media[ $p['image'] ] ) ) { set_post_thumbnail( $pid, $media[ $p['image'] ]['id'] ); }
		$added++;
	}
	$lines[] = sprintf( 'Projects: %d added (Projects menu in the admin).', $added );

	// Menu.
	$menu = wp_get_nav_menu_object( 'Main Menu' );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( 'Main Menu' );
		if ( is_wp_error( $menu_id ) ) {
			$errors[] = 'Menu could not be created: ' . $menu_id->get_error_message();
		} else {
			foreach ( $titles as $slug => $title ) {
				if ( empty( $pages[ $slug ] ) ) { continue; }
				wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $slug ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			}
			$loc            = get_theme_mod( 'nav_menu_locations', array() );
			$loc['primary'] = $menu_id;
			$loc['footer']  = $menu_id;
			set_theme_mod( 'nav_menu_locations', $loc );
			$lines[] = 'Menu: Main Menu created and assigned to the header and footer.';
		}
	} else {
		$lines[] = 'Menu: Main Menu already exists, left as it is.';
	}

	// Site settings.
	update_option( 'blogname', 'Haroon Muneer' );
	update_option( 'blogdescription', 'Full Stack WordPress Developer' );
	if ( ! empty( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
		$lines[] = 'Home page set to the Home page.';
	}
	flush_rewrite_rules();
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	set_transient( 'hm_import_report', array( 'lines' => $lines, 'errors' => $errors ), 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'themes.php?page=hm-import&hm_done=1' ) );
	exit;
}
add_action( 'admin_post_hm_run_import', 'hm_run_import' );
