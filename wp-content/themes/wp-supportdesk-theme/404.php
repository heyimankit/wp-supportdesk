<?php
/**
 * 404 Not Found template.
 *
 * @package WP_SupportDesk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="sd-page-hero">
	<div class="sd-container">
		<h1 class="sd-page-title"><?php esc_html_e( 'Page Not Found', 'wp-supportdesk-theme' ); ?></h1>
	</div>
</section>

<section class="sd-page-content-wrap">
	<div class="sd-container sd-page-container">
		<div class="sd-empty-state">
			<h2><?php esc_html_e( 'We could not find that page.', 'wp-supportdesk-theme' ); ?></h2>
			<p><?php esc_html_e( 'The page you requested may have moved or does not exist. Private support tickets are never accessible from public URLs.', 'wp-supportdesk-theme' ); ?></p>
			<div class="sd-hero-actions">
				<a class="sd-btn sd-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Back to Home', 'wp-supportdesk-theme' ); ?>
				</a>
				<a class="sd-btn sd-btn--outline" href="<?php echo esc_url( wp_supportdesk_theme_get_page_url( 'support' ) ); ?>">
					<?php esc_html_e( 'Submit Ticket', 'wp-supportdesk-theme' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
