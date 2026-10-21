<?php
/**
 * Shortcodes used inside Elementor's Shortcode widget:
 *   [hm_posts count="9" search="yes"]  blog grid with live search
 *   [hm_contact_form]                  contact form that emails you
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function hm_is_elementor_page( $post_id ) {
	return 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}

add_shortcode( 'hm_posts', function ( $atts ) {
	$a = shortcode_atts( array( 'count' => 9, 'search' => 'yes', 'category' => '' ), $atts, 'hm_posts' );
	$q = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => (int) $a['count'],
		'ignore_sticky_posts' => true,
		'category_name'       => sanitize_text_field( $a['category'] ),
	) );
	ob_start();
	if ( 'yes' === $a['search'] ) {
		echo '<div class="hm-posts-tools"><input id="hmSearch" class="hm-search" type="search" placeholder="' . esc_attr__( 'Search articles…', 'hm-portfolio' ) . '" aria-label="' . esc_attr__( 'Search articles', 'hm-portfolio' ) . '"></div>';
	}
	echo '<div class="hm-posts-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$cats = get_the_category();
		echo '<article class="hm-post-card">';
		if ( has_post_thumbnail() ) {
			echo '<a class="hm-post-thumb" href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( null, 'hm-card' ) . '</a>'; // phpcs:ignore
		}
		echo '<div class="hm-post-body"><small>' . esc_html( ( $cats ? $cats[0]->name . ' · ' : '' ) . get_the_date() ) . '</small>';
		echo '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
		echo '<p>' . esc_html( wp_trim_words( get_the_excerpt(), 26 ) ) . '</p>';
		echo '<span class="read">' . esc_html__( 'Read article', 'hm-portfolio' ) . ' &rarr;</span></div></article>';
	}
	wp_reset_postdata();
	echo '</div><p class="hm-empty">' . esc_html__( 'No articles match your search.', 'hm-portfolio' ) . '</p>';
	return ob_get_clean();
} );

add_shortcode( 'hm_contact_form', function () {
	$services = array( 'WordPress Development', 'Custom Theme / Plugin', 'WooCommerce', 'AI Automation', 'Digital Marketing', 'Other' );
	ob_start();
	if ( isset( $_GET['hm_sent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( '1' === $_GET['hm_sent'] ) { // phpcs:ignore
			echo '<div class="hm-notice">' . esc_html__( 'Thank you. Your message was sent and I will reply within one business day.', 'hm-portfolio' ) . '</div>';
		} else {
			echo '<div class="hm-notice is-error">' . esc_html__( 'Please fill in your name, a valid email and a message, then try again.', 'hm-portfolio' ) . '</div>';
		}
	}
	?>
	<form class="hm-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="hm_contact">
		<?php wp_nonce_field( 'hm_contact', 'hm_nonce' ); ?>
		<input class="hm-hp" type="text" name="hm_website" tabindex="-1" autocomplete="off" aria-hidden="true">
		<div class="row">
			<div><label for="hm_name"><?php esc_html_e( 'Name', 'hm-portfolio' ); ?></label><input class="hm-field" id="hm_name" name="hm_name" required></div>
			<div><label for="hm_email"><?php esc_html_e( 'Email', 'hm-portfolio' ); ?></label><input class="hm-field" id="hm_email" name="hm_email" type="email" required></div>
		</div>
		<div class="row">
			<div><label for="hm_service"><?php esc_html_e( 'Service', 'hm-portfolio' ); ?></label>
				<select class="hm-field" id="hm_service" name="hm_service"><?php foreach ( $services as $s ) { echo '<option>' . esc_html( $s ) . '</option>'; } ?></select></div>
			<div><label for="hm_subject"><?php esc_html_e( 'Subject', 'hm-portfolio' ); ?></label><input class="hm-field" id="hm_subject" name="hm_subject"></div>
		</div>
		<div><label for="hm_message"><?php esc_html_e( 'Message', 'hm-portfolio' ); ?></label><textarea class="hm-field" id="hm_message" name="hm_message" required></textarea></div>
		<button type="submit"><?php esc_html_e( 'Send message', 'hm-portfolio' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
} );

function hm_handle_contact() {
	$back = wp_get_referer() ? remove_query_arg( 'hm_sent', wp_get_referer() ) : home_url( '/' );
	if ( ! isset( $_POST['hm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hm_nonce'] ) ), 'hm_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'hm_sent', '0', $back ) );
		exit;
	}
	if ( ! empty( $_POST['hm_website'] ) ) { // honeypot
		wp_safe_redirect( add_query_arg( 'hm_sent', '1', $back ) );
		exit;
	}
	$name    = isset( $_POST['hm_name'] ) ? sanitize_text_field( wp_unslash( $_POST['hm_name'] ) ) : '';
	$email   = isset( $_POST['hm_email'] ) ? sanitize_email( wp_unslash( $_POST['hm_email'] ) ) : '';
	$service = isset( $_POST['hm_service'] ) ? sanitize_text_field( wp_unslash( $_POST['hm_service'] ) ) : '';
	$subject = isset( $_POST['hm_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['hm_subject'] ) ) : '';
	$message = isset( $_POST['hm_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hm_message'] ) ) : '';
	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		wp_safe_redirect( add_query_arg( 'hm_sent', '0', $back ) );
		exit;
	}
	$to      = get_theme_mod( 'hm_email', get_option( 'admin_email' ) );
	$subject = $subject ? $subject : 'Project enquiry';
	$body    = "Name: $name\nEmail: $email\nService: $service\n\n$message";
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$ok      = wp_mail( $to, '[Portfolio] ' . $subject, $body, $headers );
	wp_safe_redirect( add_query_arg( 'hm_sent', $ok ? '1' : '0', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_hm_contact', 'hm_handle_contact' );
add_action( 'admin_post_hm_contact', 'hm_handle_contact' );
