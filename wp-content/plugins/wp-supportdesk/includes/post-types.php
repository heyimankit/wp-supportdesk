<?php
/**
 * Custom post type registration and shared ticket taxonomy/status helpers.
 *
 * @package WP_SupportDesk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the allowed support ticket categories.
 *
 * @return array<string, string> Associative array of category_slug => label.
 */
function wp_supportdesk_get_categories() {
	$categories = array(
		'technical' => __( 'Technical Issue', 'wp-supportdesk' ),
		'billing'   => __( 'Billing', 'wp-supportdesk' ),
		'account'   => __( 'Account Issue', 'wp-supportdesk' ),
		'other'     => __( 'Other', 'wp-supportdesk' ),
	);

	return apply_filters( 'wp_supportdesk_categories', $categories );
}

/**
 * Returns the allowed support ticket statuses.
 *
 * @return array<string, string> Associative array of status_slug => label.
 */
function wp_supportdesk_get_statuses() {
	$statuses = array(
		'open'        => __( 'Open', 'wp-supportdesk' ),
		'in_progress' => __( 'In Progress', 'wp-supportdesk' ),
		'resolved'    => __( 'Resolved', 'wp-supportdesk' ),
	);

	return apply_filters( 'wp_supportdesk_statuses', $statuses );
}

/**
 * Returns a human-readable category label for a stored category key.
 *
 * @param string $category Stored category key.
 * @return string
 */
function wp_supportdesk_get_category_label( $category ) {
	$categories = wp_supportdesk_get_categories();
	$key        = sanitize_key( (string) $category );

	if ( isset( $categories[ $key ] ) ) {
		return $categories[ $key ];
	}

	return __( 'Other', 'wp-supportdesk' );
}

/**
 * Returns a human-readable status label for a stored status key.
 *
 * @param string $status Stored status key.
 * @return string
 */
function wp_supportdesk_get_status_label( $status ) {
	$statuses = wp_supportdesk_get_statuses();
	$key      = sanitize_key( (string) $status );

	if ( isset( $statuses[ $key ] ) ) {
		return $statuses[ $key ];
	}

	return __( 'Open', 'wp-supportdesk' );
}

/**
 * Registers the non-public `support_ticket` custom post type.
 *
 * Tickets are only accessible in the WordPress admin dashboard by administrators
 * and are excluded from public URLs, search results, feeds, and the REST API.
 */
function wp_supportdesk_register_post_type() {
	$labels = array(
		'name'                  => __( 'Support Tickets', 'wp-supportdesk' ),
		'singular_name'         => __( 'Support Ticket', 'wp-supportdesk' ),
		'menu_name'             => __( 'SupportDesk', 'wp-supportdesk' ),
		'name_admin_bar'        => __( 'Support Ticket', 'wp-supportdesk' ),
		'add_new'               => __( 'Add Ticket', 'wp-supportdesk' ),
		'add_new_item'          => __( 'Add New Ticket', 'wp-supportdesk' ),
		'edit_item'             => __( 'Ticket Details', 'wp-supportdesk' ),
		'new_item'              => __( 'New Ticket', 'wp-supportdesk' ),
		'view_item'             => __( 'View Ticket', 'wp-supportdesk' ),
		'search_items'          => __( 'Search Tickets', 'wp-supportdesk' ),
		'not_found'             => __( 'No support tickets found.', 'wp-supportdesk' ),
		'not_found_in_trash'    => __( 'No support tickets found in Trash.', 'wp-supportdesk' ),
		'all_items'             => __( 'All Tickets', 'wp-supportdesk' ),
		'filter_items_list'     => __( 'Filter tickets list', 'wp-supportdesk' ),
		'items_list_navigation' => __( 'Tickets list navigation', 'wp-supportdesk' ),
		'items_list'            => __( 'Tickets list', 'wp-supportdesk' ),
	);

	// Restrict all admin ticket management capabilities to administrators (manage_options).
	$capabilities = array(
		'edit_post'              => 'edit_post',
		'read_post'              => 'read_post',
		'delete_post'            => 'delete_post',
		'edit_posts'             => 'manage_options',
		'edit_others_posts'      => 'manage_options',
		'publish_posts'          => 'manage_options',
		'read_private_posts'     => 'manage_options',
		'delete_posts'           => 'manage_options',
		'delete_private_posts'   => 'manage_options',
		'delete_published_posts' => 'manage_options',
		'delete_others_posts'    => 'manage_options',
		'edit_private_posts'     => 'manage_options',
		'edit_published_posts'   => 'manage_options',
		'create_posts'           => 'manage_options',
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'menu_position'       => 26,
		'menu_icon'           => 'dashicons-tickets-alt',
		'supports'            => array( 'title', 'editor' ),
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'capabilities'        => $capabilities,
		'delete_with_user'    => false,
	);

	register_post_type( 'support_ticket', $args );
}
add_action( 'init', 'wp_supportdesk_register_post_type' );

/**
 * Explicitly excludes `support_ticket` from WordPress XML sitemaps.
 *
 * @param array<string, WP_Post_Type> $post_types Public post types included in sitemaps.
 * @return array<string, WP_Post_Type>
 */
function wp_supportdesk_exclude_from_sitemaps( $post_types ) {
	unset( $post_types['support_ticket'] );
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'wp_supportdesk_exclude_from_sitemaps' );

/**
 * Counts published support tickets matching a given status key.
 *
 * @param string $status Status key (`open`, `in_progress`, `resolved`).
 * @return int
 */
function wp_supportdesk_count_tickets_by_status( $status ) {
	$statuses = wp_supportdesk_get_statuses();
	$key      = sanitize_key( (string) $status );

	if ( ! isset( $statuses[ $key ] ) ) {
		return 0;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'support_ticket',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_wp_supportdesk_status',
					'value' => $key,
				),
			),
		)
	);

	return (int) $query->found_posts;
}

