<?php
/**
 * Plugin Name:       WP SupportDesk
 * Plugin URI:        https://example.com/wp-supportdesk
 * Description:       A beginner-friendly support ticket management plugin allowing visitors to submit tickets via a shortcode and administrators to manage them from the WordPress dashboard.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            WP SupportDesk
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-supportdesk
 */

// Prevent direct access to this file outside WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_SUPPORTDESK_VERSION', '1.0.0' );
define( 'WP_SUPPORTDESK_PLUGIN_FILE', __FILE__ );
define( 'WP_SUPPORTDESK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_SUPPORTDESK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load core plugin components.
require_once WP_SUPPORTDESK_PLUGIN_DIR . 'includes/post-types.php';
require_once WP_SUPPORTDESK_PLUGIN_DIR . 'includes/ticket-form.php';

// Load admin-only hooks only inside the WordPress admin dashboard.
if ( is_admin() ) {
	require_once WP_SUPPORTDESK_PLUGIN_DIR . 'includes/admin.php';
}

/**
 * Runs on plugin activation.
 *
 * Registers the custom post type, stores the plugin version, and refreshes rewrite rules.
 */
function wp_supportdesk_activate() {
	wp_supportdesk_register_post_type();
	update_option( 'wp_supportdesk_version', WP_SUPPORTDESK_VERSION );
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wp_supportdesk_activate' );

/**
 * Runs on plugin deactivation.
 *
 * Flushes rewrite rules while preserving all stored tickets and metadata.
 */
function wp_supportdesk_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wp_supportdesk_deactivate' );
