<?php
/**
 * Standard WordPress page template.
 *
 * Used for Submit Ticket, FAQ, Contact, and standard informational pages.
 *
 * @package WP_SupportDesk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="sd-page-hero">
		<div class="sd-container">
			<h1 class="sd-page-title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="sd-page-subtitle"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="sd-page-content-wrap">
		<div class="sd-container sd-page-container">
			<div class="sd-page-body">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
