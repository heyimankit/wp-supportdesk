<?php
/**
 * Theme header template.
 *
 * @package WP_SupportDesk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sd-skip-link" href="#main-content">
	<?php esc_html_e( 'Skip to content', 'wp-supportdesk-theme' ); ?>
</a>

<header class="sd-header" role="banner">
	<div class="sd-container sd-header-inner">
		<a class="sd-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="sd-brand-icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
					<path d="m9 12 2 2 4-4"></path>
				</svg>
			</span>
			<span class="sd-brand-text">
				<span class="sd-brand-name"><?php bloginfo( 'name' ); ?></span>
				<span class="sd-brand-tag"><?php esc_html_e( 'Support Portal', 'wp-supportdesk-theme' ); ?></span>
			</span>
		</a>

		<button
			type="button"
			class="sd-nav-toggle"
			aria-controls="site-navigation"
			aria-expanded="false"
			aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'wp-supportdesk-theme' ); ?>"
		>
			<span class="sd-nav-toggle-bar"></span>
			<span class="sd-nav-toggle-bar"></span>
			<span class="sd-nav-toggle-bar"></span>
		</button>

		<nav id="site-navigation" class="sd-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'wp-supportdesk-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'sd-nav-list',
					'container'      => false,
					'fallback_cb'    => 'wp_supportdesk_theme_fallback_menu',
				)
			);
			?>
		</nav>
	</div>
</header>

<main id="main-content" class="sd-main" tabindex="-1">
