<?php
/**
 * Shortcode registration, asset enqueuing, and frontend ticket form handling.
 *
 * @package WP_SupportDesk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores or retrieves the current request's form validation state.
 *
 * Keeping state in a static variable avoids polluting global scope and lets
 * template_redirect pass validation errors and sanitized inputs to the shortcode.
 *
 * @param array|null $new_state Optional state array to set.
 * @return array{errors: array<string, string>, values: array<string, string>}
 */
function wp_supportdesk_form_state( $new_state = null ) {
	static $state = array(
		'errors' => array(),
		'values' => array(
			'name'     => '',
			'email'    => '',
			'subject'  => '',
			'category' => '',
			'message'  => '',
		),
	);

	if ( is_array( $new_state ) ) {
		$state = array_merge( $state, $new_state );
	}

	return $state;
}

/**
 * Returns the clean current frontend URL without temporary SupportDesk query args.
 *
 * @return string
 */
function wp_supportdesk_get_current_url() {
	if ( is_singular() && get_queried_object_id() ) {
		$url = get_permalink( get_queried_object_id() );
	} else {
		$home_parts = wp_parse_url( home_url( '/' ) );
		$origin     = ( isset( $home_parts['scheme'] ) ? $home_parts['scheme'] : 'http' ) . '://' . ( isset( $home_parts['host'] ) ? $home_parts['host'] : 'localhost' );
		if ( ! empty( $home_parts['port'] ) ) {
			$origin .= ':' . (int) $home_parts['port'];
		}
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$url         = esc_url_raw( $origin . $request_uri );
	}

	return remove_query_arg( array( 'supportdesk_notice', 'category' ), $url );
}

/**
 * Registers frontend CSS and JavaScript, and enqueues them when the shortcode is present.
 */
function wp_supportdesk_enqueue_frontend_assets() {
	wp_register_style(
		'wp-supportdesk-style',
		WP_SUPPORTDESK_PLUGIN_URL . 'assets/css/supportdesk.css',
		array(),
		WP_SUPPORTDESK_VERSION
	);

	wp_register_script(
		'wp-supportdesk-script',
		WP_SUPPORTDESK_PLUGIN_URL . 'assets/js/supportdesk.js',
		array(),
		WP_SUPPORTDESK_VERSION,
		true
	);

	global $post;
	if ( is_singular() && $post instanceof WP_Post && has_shortcode( $post->post_content, 'supportdesk_form' ) ) {
		wp_enqueue_style( 'wp-supportdesk-style' );
		wp_enqueue_script( 'wp-supportdesk-script' );
	}
}
add_action( 'wp_enqueue_scripts', 'wp_supportdesk_enqueue_frontend_assets' );

/**
 * Builds a hashed transient key for per-IP submission throttling.
 *
 * @return string
 */
function wp_supportdesk_get_throttle_key() {
	$remote_addr = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: 'unknown';

	return 'wp_supportdesk_rl_' . substr( wp_hash( $remote_addr . '|supportdesk' ), 0, 24 );
}

/**
 * Checks whether the current visitor has exceeded the submission rate limit.
 *
 * @return bool
 */
function wp_supportdesk_is_rate_limited() {
	$max_attempts = (int) apply_filters( 'wp_supportdesk_rate_limit_max', 5 );
	if ( $max_attempts <= 0 ) {
		return false;
	}

	$count = (int) get_transient( wp_supportdesk_get_throttle_key() );
	return $count >= $max_attempts;
}

/**
 * Records a submission attempt in the transient throttle counter.
 */
function wp_supportdesk_record_submission_attempt() {
	$window = (int) apply_filters( 'wp_supportdesk_rate_limit_window', 300 );
	if ( $window <= 0 ) {
		return;
	}

	$key   = wp_supportdesk_get_throttle_key();
	$count = (int) get_transient( $key );
	set_transient( $key, $count + 1, $window );
}

/**
 * Processes frontend support ticket form submissions before headers are sent.
 *
 * Hooked to `template_redirect` so a safe Post/Redirect/Get (PRG) redirect can
 * run cleanly on success without duplicate submissions on browser refresh.
 */
function wp_supportdesk_handle_form_submission() {
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
	if ( 'POST' !== $method ) {
		return;
	}

	$action = isset( $_POST['wp_supportdesk_action'] ) ? sanitize_key( wp_unslash( $_POST['wp_supportdesk_action'] ) ) : '';
	if ( 'submit_ticket' !== $action ) {
		return;
	}

	// Verify WordPress nonce before processing any form input.
	$nonce = isset( $_POST['wp_supportdesk_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_nonce'] ) ) : '';
	if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_supportdesk_submit_ticket' ) ) {
		status_header( 403 );
		wp_supportdesk_form_state(
			array(
				'errors' => array(
					'general' => __( 'Security verification failed. Please reload the page and try again.', 'wp-supportdesk' ),
				),
			)
		);
		return;
	}

	// Honeypot check: automated bots often fill hidden fields left blank by humans.
	$honeypot = isset( $_POST['wp_supportdesk_website'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_website'] ) ) ) : '';
	if ( '' !== $honeypot ) {
		status_header( 400 );
		wp_supportdesk_form_state(
			array(
				'errors' => array(
					'general' => __( 'Your submission could not be verified. Please leave the hidden verification field empty and try again.', 'wp-supportdesk' ),
				),
			)
		);
		return;
	}

	// Basic IP-based throttling using WordPress Transients API.
	if ( wp_supportdesk_is_rate_limited() ) {
		status_header( 429 );
		wp_supportdesk_form_state(
			array(
				'errors' => array(
					'general' => __( 'Too many tickets were submitted recently. Please wait a few minutes and try again.', 'wp-supportdesk' ),
				),
			)
		);
		return;
	}

	// Sanitize incoming form inputs.
	$name       = isset( $_POST['wp_supportdesk_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_name'] ) ) ) : '';
	$raw_email  = isset( $_POST['wp_supportdesk_email'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_email'] ) ) ) : '';
	$email      = sanitize_email( $raw_email );
	$subject    = isset( $_POST['wp_supportdesk_subject'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_subject'] ) ) ) : '';
	$category   = isset( $_POST['wp_supportdesk_category'] ) ? sanitize_key( wp_unslash( $_POST['wp_supportdesk_category'] ) ) : '';
	$message    = isset( $_POST['wp_supportdesk_message'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['wp_supportdesk_message'] ) ) ) : '';
	$categories = wp_supportdesk_get_categories();

	$values = array(
		'name'     => $name,
		'email'    => $raw_email,
		'subject'  => $subject,
		'category' => $category,
		'message'  => $message,
	);

	$errors = array();

	if ( '' === $name ) {
		$errors['name'] = __( 'Please enter your full name.', 'wp-supportdesk' );
	} elseif ( mb_strlen( $name ) > 100 ) {
		$errors['name'] = __( 'Full name must be 100 characters or fewer.', 'wp-supportdesk' );
	}

	if ( '' === $raw_email ) {
		$errors['email'] = __( 'Please enter your email address.', 'wp-supportdesk' );
	} elseif ( $raw_email !== $email || ! is_email( $email ) ) {
		$errors['email'] = __( 'Please enter a valid email address.', 'wp-supportdesk' );
	}

	if ( '' === $subject ) {
		$errors['subject'] = __( 'Please enter a ticket subject.', 'wp-supportdesk' );
	} elseif ( mb_strlen( $subject ) > 200 ) {
		$errors['subject'] = __( 'Subject must be 200 characters or fewer.', 'wp-supportdesk' );
	}

	if ( '' === $category || ! array_key_exists( $category, $categories ) ) {
		$errors['category'] = __( 'Please select a valid category.', 'wp-supportdesk' );
	}

	if ( '' === $message ) {
		$errors['message'] = __( 'Please enter a message describing your issue.', 'wp-supportdesk' );
	} elseif ( mb_strlen( $message ) > 5000 ) {
		$errors['message'] = __( 'Message must be 5000 characters or fewer.', 'wp-supportdesk' );
	}

	if ( ! empty( $errors ) ) {
		wp_supportdesk_form_state(
			array(
				'errors' => $errors,
				'values' => $values,
			)
		);
		return;
	}

	// Create the custom post type entry in wp_posts.
	$ticket_id = wp_insert_post(
		array(
			'post_type'    => 'support_ticket',
			'post_title'   => wp_slash( $subject ),
			'post_content' => wp_slash( $message ),
			'post_status'  => 'publish',
			'post_author'  => is_user_logged_in() ? get_current_user_id() : 0,
		),
		true
	);

	if ( is_wp_error( $ticket_id ) || ! $ticket_id ) {
		wp_supportdesk_form_state(
			array(
				'errors' => array(
					'general' => __( 'Unable to submit your ticket at the moment. Please try again later.', 'wp-supportdesk' ),
				),
				'values' => $values,
			)
		);
		return;
	}

	// Save ticket metadata in wp_postmeta with default status 'open'.
	update_post_meta( $ticket_id, '_wp_supportdesk_customer_name', wp_slash( $name ) );
	update_post_meta( $ticket_id, '_wp_supportdesk_customer_email', wp_slash( $email ) );
	update_post_meta( $ticket_id, '_wp_supportdesk_category', $category );
	update_post_meta( $ticket_id, '_wp_supportdesk_status', 'open' );

	wp_supportdesk_record_submission_attempt();

	/**
	 * Fires after a support ticket is successfully created from the frontend form.
	 *
	 * @param int   $ticket_id   Created support_ticket post ID.
	 * @param array $ticket_data Sanitized ticket fields.
	 */
	do_action(
		'wp_supportdesk_ticket_created',
		$ticket_id,
		array(
			'name'     => $name,
			'email'    => $email,
			'subject'  => $subject,
			'category' => $category,
			'status'   => 'open',
		)
	);

	// Use a short-lived token so only the generated ticket ID is shown without exposing any ticket data.
	$notice_token = strtolower( wp_generate_password( 16, false, false ) );
	set_transient( 'wp_supportdesk_notice_' . $notice_token, (int) $ticket_id, 120 );

	$redirect_url = add_query_arg( 'supportdesk_notice', $notice_token, wp_supportdesk_get_current_url() );
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'template_redirect', 'wp_supportdesk_handle_form_submission' );

/**
 * Renders the `[supportdesk_form]` shortcode.
 *
 * @return string
 */
function wp_supportdesk_render_ticket_form( $atts = array() ) {
	// Ensure assets load even if the shortcode is rendered inside a block, template part, or widget.
	wp_enqueue_style( 'wp-supportdesk-style' );
	wp_enqueue_script( 'wp-supportdesk-script' );

	$state             = wp_supportdesk_form_state();
	$errors            = $state['errors'];
	$values            = $state['values'];
	$categories        = wp_supportdesk_get_categories();
	$form_action       = wp_supportdesk_get_current_url();
	$success_ticket_id = 0;

	if ( '' === $values['category'] && isset( $_GET['category'] ) ) {
		$requested_category = sanitize_key( wp_unslash( $_GET['category'] ) );
		if ( isset( $categories[ $requested_category ] ) ) {
			$values['category'] = $requested_category;
		}
	}

	if ( isset( $_GET['supportdesk_notice'] ) ) {
		$token = sanitize_key( wp_unslash( $_GET['supportdesk_notice'] ) );
		if ( '' !== $token ) {
			$stored_id = (int) get_transient( 'wp_supportdesk_notice_' . $token );
			if ( $stored_id > 0 && 'support_ticket' === get_post_type( $stored_id ) ) {
				$success_ticket_id = $stored_id;
			}
		}
	}

	ob_start();
	include WP_SUPPORTDESK_PLUGIN_DIR . 'templates/ticket-form.php';
	return ob_get_clean();
}
add_shortcode( 'supportdesk_form', 'wp_supportdesk_render_ticket_form' );
