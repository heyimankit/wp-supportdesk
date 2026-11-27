<?php
/**
 * Frontend support ticket submission form template.
 *
 * Available variables from wp_supportdesk_render_ticket_form():
 * - array  $errors
 * - array  $values
 * - array  $categories
 * - string $form_action
 * - int    $success_ticket_id
 *
 * @package WP_SupportDesk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wp_supportdesk_field_ids = array(
	'name'     => 'wp-supportdesk-name',
	'email'    => 'wp-supportdesk-email',
	'subject'  => 'wp-supportdesk-subject',
	'category' => 'wp-supportdesk-category',
	'message'  => 'wp-supportdesk-message',
);

$wp_supportdesk_message_described_by = 'wp-supportdesk-message-hint';
if ( isset( $errors['message'] ) ) {
	$wp_supportdesk_message_described_by .= ' wp-supportdesk-message-error';
}
?>
<div class="wp-supportdesk-wrapper">
	<div class="wp-supportdesk-card">
		<div class="wp-supportdesk-header">
			<h2 class="wp-supportdesk-title"><?php esc_html_e( 'Submit a Support Ticket', 'wp-supportdesk' ); ?></h2>
			<p class="wp-supportdesk-intro">
				<?php esc_html_e( 'Need assistance? Fill out the form below and our support team will review your request as soon as possible.', 'wp-supportdesk' ); ?>
			</p>
		</div>

		<?php if ( $success_ticket_id > 0 ) : ?>
			<div class="wp-supportdesk-alert wp-supportdesk-alert--success" role="status" aria-live="polite" tabindex="-1" data-autofocus="true">
				<p>
					<?php
					printf(
						/* translators: %d: Support ticket ID. */
						esc_html__( 'Thank you! Your support ticket #%d has been submitted successfully. Its current status is Open.', 'wp-supportdesk' ),
						absint( $success_ticket_id )
					);
					?>
				</p>
				<p class="wp-supportdesk-alert-action">
					<a href="<?php echo esc_url( $form_action ); ?>">
						<?php esc_html_e( 'Submit another ticket', 'wp-supportdesk' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<div class="wp-supportdesk-alert wp-supportdesk-alert--error" role="alert" aria-live="assertive" tabindex="-1" data-autofocus="true">
				<?php if ( isset( $errors['general'] ) ) : ?>
					<p><?php echo esc_html( $errors['general'] ); ?></p>
				<?php else : ?>
					<p class="wp-supportdesk-alert-heading"><?php esc_html_e( 'Please fix the highlighted fields below and try submitting again:', 'wp-supportdesk' ); ?></p>
					<ul class="wp-supportdesk-error-list">
						<?php foreach ( $errors as $field_key => $error_message ) : ?>
							<li>
								<?php if ( isset( $wp_supportdesk_field_ids[ $field_key ] ) ) : ?>
									<a href="#<?php echo esc_attr( $wp_supportdesk_field_ids[ $field_key ] ); ?>">
										<?php echo esc_html( $error_message ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $error_message ); ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<form class="wp-supportdesk-form" method="post" action="<?php echo esc_url( $form_action ); ?>" novalidate>
			<input type="hidden" name="wp_supportdesk_action" value="submit_ticket" />
			<?php wp_nonce_field( 'wp_supportdesk_submit_ticket', 'wp_supportdesk_nonce' ); ?>

			<!-- Honeypot anti-spam field: must remain empty for legitimate submissions -->
			<div class="wp-supportdesk-hp" aria-hidden="true">
				<label for="wp-supportdesk-website"><?php esc_html_e( 'Leave this field empty', 'wp-supportdesk' ); ?></label>
				<input
					type="text"
					id="wp-supportdesk-website"
					name="wp_supportdesk_website"
					value=""
					tabindex="-1"
					autocomplete="off"
				/>
			</div>

			<div class="wp-supportdesk-row">
				<div class="wp-supportdesk-field<?php echo isset( $errors['name'] ) ? ' wp-supportdesk-field--error' : ''; ?>">
					<label class="wp-supportdesk-label" for="wp-supportdesk-name">
						<?php esc_html_e( 'Full Name', 'wp-supportdesk' ); ?>
						<span class="wp-supportdesk-required" aria-hidden="true">*</span>
					</label>
					<input
						type="text"
						id="wp-supportdesk-name"
						name="wp_supportdesk_name"
						class="wp-supportdesk-input"
						value="<?php echo esc_attr( $values['name'] ); ?>"
						maxlength="100"
						autocomplete="name"
						aria-required="true"
						<?php echo isset( $errors['name'] ) ? 'aria-invalid="true" aria-describedby="wp-supportdesk-name-error"' : ''; ?>
					/>
					<?php if ( isset( $errors['name'] ) ) : ?>
						<p class="wp-supportdesk-error-text" id="wp-supportdesk-name-error"><?php echo esc_html( $errors['name'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="wp-supportdesk-field<?php echo isset( $errors['email'] ) ? ' wp-supportdesk-field--error' : ''; ?>">
					<label class="wp-supportdesk-label" for="wp-supportdesk-email">
						<?php esc_html_e( 'Email Address', 'wp-supportdesk' ); ?>
						<span class="wp-supportdesk-required" aria-hidden="true">*</span>
					</label>
					<input
						type="email"
						id="wp-supportdesk-email"
						name="wp_supportdesk_email"
						class="wp-supportdesk-input"
						value="<?php echo esc_attr( $values['email'] ); ?>"
						autocomplete="email"
						aria-required="true"
						<?php echo isset( $errors['email'] ) ? 'aria-invalid="true" aria-describedby="wp-supportdesk-email-error"' : ''; ?>
					/>
					<?php if ( isset( $errors['email'] ) ) : ?>
						<p class="wp-supportdesk-error-text" id="wp-supportdesk-email-error"><?php echo esc_html( $errors['email'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="wp-supportdesk-row">
				<div class="wp-supportdesk-field<?php echo isset( $errors['subject'] ) ? ' wp-supportdesk-field--error' : ''; ?>">
					<label class="wp-supportdesk-label" for="wp-supportdesk-subject">
						<?php esc_html_e( 'Subject', 'wp-supportdesk' ); ?>
						<span class="wp-supportdesk-required" aria-hidden="true">*</span>
					</label>
					<input
						type="text"
						id="wp-supportdesk-subject"
						name="wp_supportdesk_subject"
						class="wp-supportdesk-input"
						value="<?php echo esc_attr( $values['subject'] ); ?>"
						maxlength="200"
						aria-required="true"
						<?php echo isset( $errors['subject'] ) ? 'aria-invalid="true" aria-describedby="wp-supportdesk-subject-error"' : ''; ?>
					/>
					<?php if ( isset( $errors['subject'] ) ) : ?>
						<p class="wp-supportdesk-error-text" id="wp-supportdesk-subject-error"><?php echo esc_html( $errors['subject'] ); ?></p>
					<?php endif; ?>
				</div>

				<div class="wp-supportdesk-field<?php echo isset( $errors['category'] ) ? ' wp-supportdesk-field--error' : ''; ?>">
					<label class="wp-supportdesk-label" for="wp-supportdesk-category">
						<?php esc_html_e( 'Category', 'wp-supportdesk' ); ?>
						<span class="wp-supportdesk-required" aria-hidden="true">*</span>
					</label>
					<select
						id="wp-supportdesk-category"
						name="wp_supportdesk_category"
						class="wp-supportdesk-select"
						aria-required="true"
						<?php echo isset( $errors['category'] ) ? 'aria-invalid="true" aria-describedby="wp-supportdesk-category-error"' : ''; ?>
					>
						<option value=""><?php esc_html_e( 'Select a category', 'wp-supportdesk' ); ?></option>
						<?php foreach ( $categories as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $values['category'], $slug ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php if ( isset( $errors['category'] ) ) : ?>
						<p class="wp-supportdesk-error-text" id="wp-supportdesk-category-error"><?php echo esc_html( $errors['category'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="wp-supportdesk-field<?php echo isset( $errors['message'] ) ? ' wp-supportdesk-field--error' : ''; ?>">
				<label class="wp-supportdesk-label" for="wp-supportdesk-message">
					<?php esc_html_e( 'Message', 'wp-supportdesk' ); ?>
					<span class="wp-supportdesk-required" aria-hidden="true">*</span>
				</label>
				<textarea
					id="wp-supportdesk-message"
					name="wp_supportdesk_message"
					class="wp-supportdesk-textarea"
					rows="6"
					maxlength="5000"
					aria-required="true"
					aria-describedby="<?php echo esc_attr( $wp_supportdesk_message_described_by ); ?>"
					<?php echo isset( $errors['message'] ) ? 'aria-invalid="true"' : ''; ?>
				><?php echo esc_textarea( $values['message'] ); ?></textarea>
				<p class="wp-supportdesk-hint" id="wp-supportdesk-message-hint">
					<?php esc_html_e( 'Include clear details so our team can assist faster. Please avoid entering passwords or sensitive card data.', 'wp-supportdesk' ); ?>
				</p>
				<?php if ( isset( $errors['message'] ) ) : ?>
					<p class="wp-supportdesk-error-text" id="wp-supportdesk-message-error"><?php echo esc_html( $errors['message'] ); ?></p>
				<?php endif; ?>
			</div>

			<div class="wp-supportdesk-actions">
				<button type="submit" class="wp-supportdesk-submit" data-submitting-text="<?php echo esc_attr__( 'Submitting...', 'wp-supportdesk' ); ?>">
					<?php esc_html_e( 'Submit Ticket', 'wp-supportdesk' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
