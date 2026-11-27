<?php
/**
 * Homepage template for the WP SupportDesk support portal.
 *
 * @package WP_SupportDesk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$support_url = wp_supportdesk_theme_get_page_url( 'support' );
$faq_url     = wp_supportdesk_theme_get_page_url( 'faq' );
?>

<section class="sd-hero">
	<div class="sd-container sd-hero-grid">
		<div class="sd-hero-content">
			<span class="sd-eyebrow"><?php esc_html_e( 'Customer Support Portal', 'wp-supportdesk-theme' ); ?></span>
			<h1 class="sd-hero-title">
				<?php esc_html_e( 'Fast, Organized Support Ticket Submission & Management', 'wp-supportdesk-theme' ); ?>
			</h1>
			<p class="sd-hero-subtitle">
				<?php esc_html_e( 'Submit technical, billing, or account inquiries in seconds. Every request receives a unique ticket ID and is reviewed privately by our administrative support team.', 'wp-supportdesk-theme' ); ?>
			</p>
			<div class="sd-hero-actions">
				<a class="sd-btn sd-btn--primary" href="<?php echo esc_url( $support_url ); ?>">
					<?php esc_html_e( 'Submit Ticket', 'wp-supportdesk-theme' ); ?>
				</a>
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( $faq_url ); ?>">
					<?php esc_html_e( 'Browse FAQ', 'wp-supportdesk-theme' ); ?>
				</a>
			</div>
		</div>

		<aside class="sd-hero-panel" aria-label="<?php esc_attr_e( 'Ticket Workflow Overview', 'wp-supportdesk-theme' ); ?>">
			<h2 class="sd-panel-title"><?php esc_html_e( 'Ticket Lifecycle', 'wp-supportdesk-theme' ); ?></h2>
			<ol class="sd-lifecycle-list">
				<li class="sd-lifecycle-item">
					<span class="sd-step-num">1</span>
					<div>
						<strong><?php esc_html_e( 'Submit Request', 'wp-supportdesk-theme' ); ?></strong>
						<p><?php esc_html_e( 'Fill out the guided form and receive your confirmation ticket ID.', 'wp-supportdesk-theme' ); ?></p>
					</div>
				</li>
				<li class="sd-lifecycle-item">
					<span class="sd-step-num">2</span>
					<div>
						<div class="sd-step-head">
							<strong><?php esc_html_e( 'Logged as Open', 'wp-supportdesk-theme' ); ?></strong>
							<span class="sd-status-pill sd-status-pill--open"><?php esc_html_e( 'Open', 'wp-supportdesk-theme' ); ?></span>
						</div>
						<p><?php esc_html_e( 'Stored privately in WordPress Admin with category and contact metadata.', 'wp-supportdesk-theme' ); ?></p>
					</div>
				</li>
				<li class="sd-lifecycle-item">
					<span class="sd-step-num">3</span>
					<div>
						<div class="sd-step-head">
							<strong><?php esc_html_e( 'Team Review & Resolution', 'wp-supportdesk-theme' ); ?></strong>
							<span class="sd-status-pill sd-status-pill--resolved"><?php esc_html_e( 'Resolved', 'wp-supportdesk-theme' ); ?></span>
						</div>
						<p><?php esc_html_e( 'Administrators triage, investigate in progress, and mark tickets resolved.', 'wp-supportdesk-theme' ); ?></p>
					</div>
				</li>
			</ol>
		</aside>
	</div>
</section>

<section class="sd-section">
	<div class="sd-container">
		<div class="sd-section-head">
			<h2 class="sd-section-title"><?php esc_html_e( 'How the Support Workflow Works', 'wp-supportdesk-theme' ); ?></h2>
			<p class="sd-section-desc">
				<?php esc_html_e( 'Built natively on WordPress APIs for clarity, data privacy, and dependable administration.', 'wp-supportdesk-theme' ); ?>
			</p>
		</div>

		<div class="sd-cards-grid sd-cards-grid--3">
			<article class="sd-card">
				<div class="sd-card-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
						<polyline points="14 2 14 8 20 8"></polyline>
						<line x1="16" y1="13" x2="8" y2="13"></line>
						<line x1="16" y1="17" x2="8" y2="17"></line>
					</svg>
				</div>
				<h3 class="sd-card-title"><?php esc_html_e( '1. Guided Ticket Intake', 'wp-supportdesk-theme' ); ?></h3>
				<p class="sd-card-text">
					<?php esc_html_e( 'Visitors submit their name, email, subject, category, and message through a clean responsive form with server-side validation and safe redirect protection.', 'wp-supportdesk-theme' ); ?>
				</p>
			</article>

			<article class="sd-card">
				<div class="sd-card-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
						<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
					</svg>
				</div>
				<h3 class="sd-card-title"><?php esc_html_e( '2. Private & Non-Public Storage', 'wp-supportdesk-theme' ); ?></h3>
				<p class="sd-card-text">
					<?php esc_html_e( 'Tickets use a non-public custom post type excluded from frontend URLs, public search, RSS feeds, XML sitemaps, and the WordPress REST API.', 'wp-supportdesk-theme' ); ?>
				</p>
			</article>

			<article class="sd-card">
				<div class="sd-card-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="9 11 12 14 22 4"></polyline>
						<path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
					</svg>
				</div>
				<h3 class="sd-card-title"><?php esc_html_e( '3. Structured Admin Triage', 'wp-supportdesk-theme' ); ?></h3>
				<p class="sd-card-text">
					<?php esc_html_e( 'Administrators view ticket details inside the SupportDesk dashboard menu, search subjects, filter by status or category, and update progress cleanly.', 'wp-supportdesk-theme' ); ?>
				</p>
			</article>
		</div>
	</div>
</section>

<section class="sd-section sd-section--tinted">
	<div class="sd-container">
		<div class="sd-section-head">
			<h2 class="sd-section-title"><?php esc_html_e( 'Choose a Support Category', 'wp-supportdesk-theme' ); ?></h2>
			<p class="sd-section-desc">
				<?php esc_html_e( 'Select the topic that best matches your issue to start a pre-categorized support ticket.', 'wp-supportdesk-theme' ); ?>
			</p>
		</div>

		<div class="sd-cards-grid sd-cards-grid--4">
			<?php
			$category_cards = array(
				'technical' => array(
					'title' => __( 'Technical Issue', 'wp-supportdesk-theme' ),
					'desc'  => __( 'Report bugs, error messages, broken pages, or system troubleshooting needs.', 'wp-supportdesk-theme' ),
				),
				'billing'   => array(
					'title' => __( 'Billing', 'wp-supportdesk-theme' ),
					'desc'  => __( 'Ask questions about invoices, subscription charges, or payment receipts.', 'wp-supportdesk-theme' ),
				),
				'account'   => array(
					'title' => __( 'Account Issue', 'wp-supportdesk-theme' ),
					'desc'  => __( 'Get assistance with login access, profile details, or account permissions.', 'wp-supportdesk-theme' ),
				),
				'other'     => array(
					'title' => __( 'Other', 'wp-supportdesk-theme' ),
					'desc'  => __( 'General inquiries, feedback, or questions that do not fit another topic.', 'wp-supportdesk-theme' ),
				),
			);

			foreach ( $category_cards as $slug => $card ) :
				$cat_url = add_query_arg( 'category', $slug, $support_url );
				?>
				<a class="sd-category-card" href="<?php echo esc_url( $cat_url ); ?>">
					<h3 class="sd-category-title"><?php echo esc_html( $card['title'] ); ?></h3>
					<p class="sd-category-desc"><?php echo esc_html( $card['desc'] ); ?></p>
					<span class="sd-category-link"><?php esc_html_e( 'Open ticket &rarr;', 'wp-supportdesk-theme' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="sd-cta-band">
	<div class="sd-container sd-cta-inner">
		<div>
			<h2 class="sd-cta-title"><?php esc_html_e( 'Need Help Right Away?', 'wp-supportdesk-theme' ); ?></h2>
			<p class="sd-cta-text">
				<?php esc_html_e( 'Open a ticket with our support team or review common questions in the FAQ.', 'wp-supportdesk-theme' ); ?>
			</p>
		</div>
		<div class="sd-cta-actions">
			<a class="sd-btn sd-btn--primary" href="<?php echo esc_url( $support_url ); ?>">
				<?php esc_html_e( 'Submit a Ticket', 'wp-supportdesk-theme' ); ?>
			</a>
			<a class="sd-btn sd-btn--outline" href="<?php echo esc_url( wp_supportdesk_theme_get_page_url( 'contact' ) ); ?>">
				<?php esc_html_e( 'Demo Contact Info', 'wp-supportdesk-theme' ); ?>
			</a>
		</div>
	</div>
</section>

<?php
get_footer();
