<?php
/**
 * Theme footer template.
 *
 * @package WP_SupportDesk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer class="sd-footer" role="contentinfo">
	<div class="sd-container sd-footer-grid">
		<div class="sd-footer-col">
			<h2 class="sd-footer-heading"><?php bloginfo( 'name' ); ?></h2>
			<p class="sd-footer-text">
				<?php esc_html_e( 'A WordPress-powered customer support portal for structured ticket submission and private administrative ticket management.', 'wp-supportdesk-theme' ); ?>
			</p>
		</div>

		<div class="sd-footer-col">
			<h2 class="sd-footer-heading"><?php esc_html_e( 'Navigation', 'wp-supportdesk-theme' ); ?></h2>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'menu_class'     => 'sd-footer-links',
						'container'      => false,
						'depth'          => 1,
					)
				);
			} else {
				?>
				<ul class="sd-footer-links">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'wp-supportdesk-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( wp_supportdesk_theme_get_page_url( 'support' ) ); ?>"><?php esc_html_e( 'Submit Ticket', 'wp-supportdesk-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( wp_supportdesk_theme_get_page_url( 'faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'wp-supportdesk-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( wp_supportdesk_theme_get_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'wp-supportdesk-theme' ); ?></a></li>
				</ul>
				<?php
			}
			?>
		</div>

		<div class="sd-footer-col">
			<h2 class="sd-footer-heading"><?php esc_html_e( 'Demo Support Hours', 'wp-supportdesk-theme' ); ?></h2>
			<p class="sd-footer-text">
				<?php esc_html_e( 'Monday – Friday: 9:00 AM – 6:00 PM (Demo Schedule)', 'wp-supportdesk-theme' ); ?><br />
				<?php esc_html_e( 'Demo Email: support-demo@example.com', 'wp-supportdesk-theme' ); ?>
			</p>
		</div>
	</div>

	<div class="sd-footer-bottom">
		<div class="sd-container sd-footer-bottom-inner">
			<p class="sd-copyright">
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'wp-supportdesk-theme' ); ?>
			</p>
			<p class="sd-demo-note">
				<?php esc_html_e( 'Portfolio demo portal — submitted ticket data stays private in WordPress Admin.', 'wp-supportdesk-theme' ); ?>
			</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
