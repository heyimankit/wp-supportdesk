<?php
/**
 * Main fallback template for blog archives or generic queries.
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
		<h1 class="sd-page-title"><?php esc_html_e( 'Support Updates', 'wp-supportdesk-theme' ); ?></h1>
	</div>
</section>

<section class="sd-page-content-wrap">
	<div class="sd-container sd-page-container">
		<?php if ( have_posts() ) : ?>
			<div class="sd-posts-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'sd-card sd-post-item' ); ?>>
						<h2 class="sd-card-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<p class="sd-post-meta"><?php echo esc_html( get_the_date() ); ?></p>
						<div class="sd-card-text"><?php the_excerpt(); ?></div>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="sd-empty-state">
				<h2><?php esc_html_e( 'No content found', 'wp-supportdesk-theme' ); ?></h2>
				<p><?php esc_html_e( 'There are no announcements or matching items right now. If you need assistance, please submit a support ticket.', 'wp-supportdesk-theme' ); ?></p>
				<a class="sd-btn sd-btn--primary" href="<?php echo esc_url( wp_supportdesk_theme_get_page_url( 'support' ) ); ?>">
					<?php esc_html_e( 'Submit Ticket', 'wp-supportdesk-theme' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
