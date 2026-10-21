<?php
/**
 * Projects post type (Projects > Add New), project categories,
 * a details box and the [hm_projects] shortcode.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function () {
	register_post_type( 'hm_project', array(
		'labels'       => array(
			'name'               => __( 'Projects', 'hm-portfolio' ),
			'singular_name'      => __( 'Project', 'hm-portfolio' ),
			'add_new_item'       => __( 'Add New Project', 'hm-portfolio' ),
			'edit_item'          => __( 'Edit Project', 'hm-portfolio' ),
			'all_items'          => __( 'All Projects', 'hm-portfolio' ),
			'menu_name'          => __( 'Projects', 'hm-portfolio' ),
			'not_found'          => __( 'No projects found.', 'hm-portfolio' ),
		),
		'public'       => true,
		'has_archive'  => 'work',
		'rewrite'      => array( 'slug' => 'work' ),
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position' => 20,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'show_in_rest' => true,
	) );
	register_taxonomy( 'hm_project_cat', 'hm_project', array(
		'labels'       => array( 'name' => __( 'Project Categories', 'hm-portfolio' ), 'singular_name' => __( 'Project Category', 'hm-portfolio' ) ),
		'hierarchical' => true,
		'public'       => true,
		'rewrite'      => array( 'slug' => 'work-category' ),
		'show_in_rest' => true,
		'show_admin_column' => true,
	) );
} );

add_action( 'after_switch_theme', function () {
	update_option( 'hm_needs_flush', 1 );
	set_transient( 'hm_activated_redirect', 1, 60 );
} );
// Flush permalinks once, after the post types above are registered.
add_action( 'init', function () {
	if ( get_option( 'hm_needs_flush' ) ) {
		delete_option( 'hm_needs_flush' );
		flush_rewrite_rules();
	}
}, 99 );

// Send the admin to the importer once after activation.
add_action( 'admin_init', function () {
	if ( get_transient( 'hm_activated_redirect' ) && current_user_can( 'manage_options' ) && ! wp_doing_ajax() && ! isset( $_GET['activate-multi'] ) ) { // phpcs:ignore
		delete_transient( 'hm_activated_redirect' );
		wp_safe_redirect( admin_url( 'themes.php?page=hm-import' ) );
		exit;
	}
} );

/* ---------- details box ---------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'hm_project_details', __( 'Project details', 'hm-portfolio' ), function ( $post ) {
		wp_nonce_field( 'hm_project_save', 'hm_project_nonce' );
		$f = array(
			'hm_kind'  => array( __( 'Type (e.g. E-commerce / WordPress)', 'hm-portfolio' ), 'text' ),
			'hm_stack' => array( __( 'Tech stack', 'hm-portfolio' ), 'text' ),
			'hm_tags'  => array( __( 'Tags (comma separated)', 'hm-portfolio' ), 'text' ),
			'hm_url'   => array( __( 'Live site URL', 'hm-portfolio' ), 'url' ),
		);
		echo '<table class="form-table">';
		foreach ( $f as $key => $d ) {
			printf( '<tr><th><label for="%1$s">%2$s</label></th><td><input class="widefat" type="%3$s" id="%1$s" name="%1$s" value="%4$s"></td></tr>',
				esc_attr( $key ), esc_html( $d[0] ), esc_attr( $d[1] ), esc_attr( get_post_meta( $post->ID, $key, true ) ) );
		}
		echo '</table>';
	}, 'hm_project', 'normal', 'high' );
} );
add_action( 'save_post_hm_project', function ( $post_id ) {
	if ( ! isset( $_POST['hm_project_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hm_project_nonce'] ) ), 'hm_project_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	foreach ( array( 'hm_kind', 'hm_stack', 'hm_tags' ) as $k ) {
		if ( isset( $_POST[ $k ] ) ) { update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ); }
	}
	if ( isset( $_POST['hm_url'] ) ) { update_post_meta( $post_id, 'hm_url', esc_url_raw( wp_unslash( $_POST['hm_url'] ) ) ); }
} );

/* ---------- card markup shared by shortcode and archives ---------- */
function hm_project_card_html( $post_id = null ) {
	$id    = $post_id ? $post_id : get_the_ID();
	$terms = get_the_terms( $id, 'hm_project_cat' );
	$cls   = 'hm-project-card hm-sc-card';
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) { $cls .= ' hm-cat-' . sanitize_html_class( $t->slug ); }
	}
	$tags = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $id, 'hm_tags', true ) ) ) );
	ob_start();
	?>
	<article class="<?php echo esc_attr( $cls ); ?>">
		<a class="hm-sc-link" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
			<div class="hm-sc-head">
				<h3><?php echo esc_html( get_the_title( $id ) ); ?></h3>
				<span><?php echo esc_html( get_post_meta( $id, 'hm_kind', true ) ); ?></span>
			</div>
			<?php if ( has_post_thumbnail( $id ) ) : ?>
				<div class="hm-sc-img"><?php echo get_the_post_thumbnail( $id, 'hm-card' ); // phpcs:ignore ?></div>
			<?php endif; ?>
		</a>
		<?php if ( $tags ) : ?>
			<div class="hm-sc-tags"><?php foreach ( $tags as $t ) { echo '<span>' . esc_html( $t ) . '</span>'; } ?></div>
		<?php endif; ?>
	</article>
	<?php
	return ob_get_clean();
}

add_shortcode( 'hm_projects', function ( $atts ) {
	$a    = shortcode_atts( array( 'count' => 6, 'filter' => 'yes', 'category' => '' ), $atts, 'hm_projects' );
	$args = array( 'post_type' => 'hm_project', 'posts_per_page' => (int) $a['count'], 'ignore_sticky_posts' => true );
	if ( $a['category'] ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'hm_project_cat', 'field' => 'slug', 'terms' => array_map( 'sanitize_title', explode( ',', $a['category'] ) ) ) );
	}
	$q = new WP_Query( $args );
	ob_start();
	if ( 'yes' === $a['filter'] ) {
		$terms = get_terms( array( 'taxonomy' => 'hm_project_cat', 'hide_empty' => true ) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			echo '<div class="hm-sc-filters"><button type="button" class="hm-filter hm-filter-all is-active">' . esc_html__( 'All', 'hm-portfolio' ) . '</button>';
			foreach ( $terms as $t ) {
				echo '<button type="button" class="hm-filter hm-filter-' . esc_attr( sanitize_html_class( $t->slug ) ) . '">' . esc_html( $t->name ) . '</button>';
			}
			echo '</div>';
		}
	}
	echo '<div class="hm-sc-grid">';
	while ( $q->have_posts() ) { $q->the_post(); echo hm_project_card_html( get_the_ID() ); } // phpcs:ignore
	wp_reset_postdata();
	echo '</div>';
	if ( ! $q->have_posts() ) { echo '<p>' . esc_html__( 'No projects yet. Add some under Projects > Add New.', 'hm-portfolio' ) . '</p>'; }
	return ob_get_clean();
} );
