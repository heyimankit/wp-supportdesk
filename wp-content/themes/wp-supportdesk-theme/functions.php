<?php
/**
 * WP SupportDesk Theme functions and definitions.
 *
 * @package WP_SupportDesk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_SUPPORTDESK_THEME_VERSION', '1.0.0' );

/**
 * Registers theme supports and navigation menus.
 */
function wp_supportdesk_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'wp-supportdesk-theme' ),
			'footer'  => __( 'Footer Navigation', 'wp-supportdesk-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'wp_supportdesk_theme_setup' );

/**
 * Enqueues theme stylesheet and minimal navigation script.
 */
function wp_supportdesk_theme_enqueue_assets() {
	wp_enqueue_style(
		'wp-supportdesk-theme-style',
		get_template_directory_uri() . '/assets/css/theme.css',
		array(),
		WP_SUPPORTDESK_THEME_VERSION
	);

	wp_enqueue_script(
		'wp-supportdesk-theme-script',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		WP_SUPPORTDESK_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'wp_supportdesk_theme_enqueue_assets' );

/**
 * Resolves a page URL cleanly by slug, falling back to a relative path under home_url().
 *
 * @param string $slug Page post_name slug.
 * @return string
 */
function wp_supportdesk_theme_get_page_url( $slug ) {
	$page = get_page_by_path( sanitize_title( $slug ) );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}

	return home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Fallback navigation menu when no WordPress menu is assigned to the primary location.
 */
function wp_supportdesk_theme_fallback_menu() {
	$items = array(
		array(
			'label' => __( 'Home', 'wp-supportdesk-theme' ),
			'url'   => home_url( '/' ),
		),
		array(
			'label' => __( 'Submit Ticket', 'wp-supportdesk-theme' ),
			'url'   => wp_supportdesk_theme_get_page_url( 'support' ),
		),
		array(
			'label' => __( 'FAQ', 'wp-supportdesk-theme' ),
			'url'   => wp_supportdesk_theme_get_page_url( 'faq' ),
		),
		array(
			'label' => __( 'Contact', 'wp-supportdesk-theme' ),
			'url'   => wp_supportdesk_theme_get_page_url( 'contact' ),
		),
	);

	echo '<ul id="primary-menu" class="sd-nav-list">';
	foreach ( $items as $item ) {
		printf(
			'<li class="menu-item"><a href="%1$s">%2$s</a></li>',
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}
