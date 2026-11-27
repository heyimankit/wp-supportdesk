<?php
/**
 * WordPress admin dashboard integration for WP SupportDesk.
 *
 * Adds custom list table columns, status/category filters, a Ticket Details & Status
 * meta box, bulk status actions, and capability-protected save handlers.
 *
 * @package WP_SupportDesk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues admin-only CSS on SupportDesk screens.
 */
function wp_supportdesk_enqueue_admin_assets() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'support_ticket' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style(
		'wp-supportdesk-admin',
		WP_SUPPORTDESK_PLUGIN_URL . 'assets/css/admin.css',
		array(),
		WP_SUPPORTDESK_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'wp_supportdesk_enqueue_admin_assets' );

/**
 * Defines custom columns for the SupportDesk tickets list table.
 *
 * @param array<string, string> $columns Default WordPress columns.
 * @return array<string, string>
 */
function wp_supportdesk_set_admin_columns( $columns ) {
	$checkbox = isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />';

	return array(
		'cb'              => $checkbox,
		'ticket_id'       => __( 'Ticket ID', 'wp-supportdesk' ),
		'title'           => __( 'Subject', 'wp-supportdesk' ),
		'customer_name'   => __( 'Customer Name', 'wp-supportdesk' ),
		'customer_email'  => __( 'Email', 'wp-supportdesk' ),
		'ticket_category' => __( 'Category', 'wp-supportdesk' ),
		'ticket_status'   => __( 'Status', 'wp-supportdesk' ),
		'date'            => __( 'Submitted', 'wp-supportdesk' ),
	);
}
add_filter( 'manage_support_ticket_posts_columns', 'wp_supportdesk_set_admin_columns' );

/**
 * Renders content for each custom column on the SupportDesk list table.
 *
 * @param string $column  Column key.
 * @param int    $post_id Support ticket post ID.
 */
function wp_supportdesk_render_admin_column( $column, $post_id ) {
	switch ( $column ) {
		case 'ticket_id':
			$edit_link = get_edit_post_link( $post_id );
			if ( $edit_link ) {
				printf(
					'<a href="%1$s"><strong>#%2$d</strong></a>',
					esc_url( $edit_link ),
					absint( $post_id )
				);
			} else {
				printf( '<strong>#%d</strong>', absint( $post_id ) );
			}
			break;

		case 'customer_name':
			$name = get_post_meta( $post_id, '_wp_supportdesk_customer_name', true );
			echo '' !== (string) $name ? esc_html( $name ) : '&mdash;';
			break;

		case 'customer_email':
			$email = get_post_meta( $post_id, '_wp_supportdesk_customer_email', true );
			if ( is_email( $email ) ) {
				printf(
					'<a href="mailto:%1$s">%2$s</a>',
					esc_attr( $email ),
					esc_html( $email )
				);
			} else {
				echo '&mdash;';
			}
			break;

		case 'ticket_category':
			$category = get_post_meta( $post_id, '_wp_supportdesk_category', true );
			echo esc_html( wp_supportdesk_get_category_label( $category ) );
			break;

		case 'ticket_status':
			$status   = sanitize_key( (string) get_post_meta( $post_id, '_wp_supportdesk_status', true ) );
			$statuses = wp_supportdesk_get_statuses();
			if ( ! isset( $statuses[ $status ] ) ) {
				$status = 'open';
			}
			printf(
				'<span class="wp-supportdesk-badge wp-supportdesk-badge--%1$s">%2$s</span>',
				esc_attr( $status ),
				esc_html( wp_supportdesk_get_status_label( $status ) )
			);
			break;
	}
}
add_action( 'manage_support_ticket_posts_custom_column', 'wp_supportdesk_render_admin_column', 10, 2 );

/**
 * Registers sortable columns in the SupportDesk list table.
 *
 * @param array<string, string> $columns Existing sortable columns.
 * @return array<string, string>
 */
function wp_supportdesk_sortable_columns( $columns ) {
	$columns['ticket_id']       = 'ID';
	$columns['ticket_category'] = 'ticket_category';
	$columns['ticket_status']   = 'ticket_status';
	return $columns;
}
add_filter( 'manage_edit-support_ticket_sortable_columns', 'wp_supportdesk_sortable_columns' );

/**
 * Outputs status and category filter dropdowns above the SupportDesk list table.
 *
 * @param string $post_type Current screen post type.
 */
function wp_supportdesk_render_admin_filters( $post_type ) {
	if ( 'support_ticket' !== $post_type ) {
		return;
	}

	$selected_status   = isset( $_GET['supportdesk_status'] ) ? sanitize_key( wp_unslash( $_GET['supportdesk_status'] ) ) : '';
	$selected_category = isset( $_GET['supportdesk_category'] ) ? sanitize_key( wp_unslash( $_GET['supportdesk_category'] ) ) : '';
	$statuses          = wp_supportdesk_get_statuses();
	$categories        = wp_supportdesk_get_categories();
	?>
	<label class="screen-reader-text" for="wp-supportdesk-filter-status"><?php esc_html_e( 'Filter by ticket status', 'wp-supportdesk' ); ?></label>
	<select name="supportdesk_status" id="wp-supportdesk-filter-status">
		<option value=""><?php esc_html_e( 'All Statuses', 'wp-supportdesk' ); ?></option>
		<?php foreach ( $statuses as $slug => $label ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected_status, $slug ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<label class="screen-reader-text" for="wp-supportdesk-filter-category"><?php esc_html_e( 'Filter by ticket category', 'wp-supportdesk' ); ?></label>
	<select name="supportdesk_category" id="wp-supportdesk-filter-category">
		<option value=""><?php esc_html_e( 'All Categories', 'wp-supportdesk' ); ?></option>
		<?php foreach ( $categories as $slug => $label ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected_category, $slug ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'wp_supportdesk_render_admin_filters' );

/**
 * Modifies the admin list query to apply status/category filters and custom sorting.
 *
 * @param WP_Query $query Current query instance.
 */
function wp_supportdesk_filter_admin_tickets( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	global $pagenow;
	if ( 'edit.php' !== $pagenow || 'support_ticket' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$meta_query = $query->get( 'meta_query' );
	if ( ! is_array( $meta_query ) ) {
		$meta_query = array();
	}

	$status   = isset( $_GET['supportdesk_status'] ) ? sanitize_key( wp_unslash( $_GET['supportdesk_status'] ) ) : '';
	$statuses = wp_supportdesk_get_statuses();
	if ( '' !== $status && isset( $statuses[ $status ] ) ) {
		$meta_query[] = array(
			'key'   => '_wp_supportdesk_status',
			'value' => $status,
		);
	}

	$category   = isset( $_GET['supportdesk_category'] ) ? sanitize_key( wp_unslash( $_GET['supportdesk_category'] ) ) : '';
	$categories = wp_supportdesk_get_categories();
	if ( '' !== $category && isset( $categories[ $category ] ) ) {
		$meta_query[] = array(
			'key'   => '_wp_supportdesk_category',
			'value' => $category,
		);
	}

	if ( ! empty( $meta_query ) ) {
		if ( count( $meta_query ) > 1 && ! isset( $meta_query['relation'] ) ) {
			$meta_query['relation'] = 'AND';
		}
		$query->set( 'meta_query', $meta_query );
	}

	$orderby = $query->get( 'orderby' );
	if ( 'ticket_status' === $orderby ) {
		$query->set( 'meta_key', '_wp_supportdesk_status' );
		$query->set( 'orderby', 'meta_value' );
	} elseif ( 'ticket_category' === $orderby ) {
		$query->set( 'meta_key', '_wp_supportdesk_category' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'wp_supportdesk_filter_admin_tickets' );

/**
 * Registers the Ticket Details & Status meta box on the individual ticket edit screen.
 */
function wp_supportdesk_register_meta_boxes() {
	add_meta_box(
		'wp_supportdesk_ticket_details',
		__( 'Ticket Details & Status', 'wp-supportdesk' ),
		'wp_supportdesk_render_ticket_meta_box',
		'support_ticket',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'wp_supportdesk_register_meta_boxes' );

/**
 * Renders the Ticket Details & Status meta box fields.
 *
 * @param WP_Post $post Current support ticket post object.
 */
function wp_supportdesk_render_ticket_meta_box( $post ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_nonce_field( 'wp_supportdesk_save_ticket_meta', 'wp_supportdesk_meta_nonce' );

	$status         = sanitize_key( (string) get_post_meta( $post->ID, '_wp_supportdesk_status', true ) );
	$category       = sanitize_key( (string) get_post_meta( $post->ID, '_wp_supportdesk_category', true ) );
	$customer_name  = (string) get_post_meta( $post->ID, '_wp_supportdesk_customer_name', true );
	$customer_email = (string) get_post_meta( $post->ID, '_wp_supportdesk_customer_email', true );

	$statuses   = wp_supportdesk_get_statuses();
	$categories = wp_supportdesk_get_categories();

	if ( ! isset( $statuses[ $status ] ) ) {
		$status = 'open';
	}
	if ( ! isset( $categories[ $category ] ) ) {
		$category = 'other';
	}
	?>
	<div class="wp-supportdesk-metabox">
		<?php if ( 'auto-draft' !== $post->post_status ) : ?>
			<div class="wp-supportdesk-metabox-summary">
				<p>
					<strong><?php esc_html_e( 'Ticket ID:', 'wp-supportdesk' ); ?></strong>
					#<?php echo absint( $post->ID ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Current Status:', 'wp-supportdesk' ); ?></strong>
					<span class="wp-supportdesk-badge wp-supportdesk-badge--<?php echo esc_attr( $status ); ?>">
						<?php echo esc_html( wp_supportdesk_get_status_label( $status ) ); ?>
					</span>
				</p>
				<p>
					<strong><?php esc_html_e( 'Submitted:', 'wp-supportdesk' ); ?></strong>
					<?php echo esc_html( get_the_date( '', $post ) . ' ' . get_the_time( '', $post ) ); ?>
				</p>
			</div>
		<?php endif; ?>

		<p class="wp-supportdesk-metabox-field">
			<label for="wp-supportdesk-admin-status"><strong><?php esc_html_e( 'Ticket Status', 'wp-supportdesk' ); ?></strong></label>
			<select id="wp-supportdesk-admin-status" name="wp_supportdesk_status" class="widefat">
				<?php foreach ( $statuses as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $status, $slug ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p class="wp-supportdesk-metabox-field">
			<label for="wp-supportdesk-admin-category"><strong><?php esc_html_e( 'Category', 'wp-supportdesk' ); ?></strong></label>
			<select id="wp-supportdesk-admin-category" name="wp_supportdesk_category" class="widefat">
				<?php foreach ( $categories as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $category, $slug ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p class="wp-supportdesk-metabox-field">
			<label for="wp-supportdesk-admin-name"><strong><?php esc_html_e( 'Customer Name', 'wp-supportdesk' ); ?></strong></label>
			<input
				type="text"
				id="wp-supportdesk-admin-name"
				name="wp_supportdesk_customer_name"
				value="<?php echo esc_attr( $customer_name ); ?>"
				class="widefat"
			/>
		</p>

		<p class="wp-supportdesk-metabox-field">
			<label for="wp-supportdesk-admin-email"><strong><?php esc_html_e( 'Customer Email', 'wp-supportdesk' ); ?></strong></label>
			<input
				type="email"
				id="wp-supportdesk-admin-email"
				name="wp_supportdesk_customer_email"
				value="<?php echo esc_attr( $customer_email ); ?>"
				class="widefat"
			/>
		</p>
	</div>
	<?php
}

/**
 * Saves Ticket Details & Status metadata when an administrator updates a ticket.
 *
 * @param int $post_id Support ticket post ID.
 */
function wp_supportdesk_save_ticket_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$nonce = isset( $_POST['wp_supportdesk_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_meta_nonce'] ) ) : '';
	if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_supportdesk_save_ticket_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$statuses = wp_supportdesk_get_statuses();
	if ( isset( $_POST['wp_supportdesk_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_POST['wp_supportdesk_status'] ) );
		if ( isset( $statuses[ $status ] ) ) {
			update_post_meta( $post_id, '_wp_supportdesk_status', $status );
		}
	}

	$categories = wp_supportdesk_get_categories();
	if ( isset( $_POST['wp_supportdesk_category'] ) ) {
		$category = sanitize_key( wp_unslash( $_POST['wp_supportdesk_category'] ) );
		if ( isset( $categories[ $category ] ) ) {
			update_post_meta( $post_id, '_wp_supportdesk_category', $category );
		}
	}

	if ( isset( $_POST['wp_supportdesk_customer_name'] ) ) {
		$name = trim( sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_customer_name'] ) ) );
		update_post_meta( $post_id, '_wp_supportdesk_customer_name', wp_slash( $name ) );
	}

	if ( isset( $_POST['wp_supportdesk_customer_email'] ) ) {
		$raw_email = trim( sanitize_text_field( wp_unslash( $_POST['wp_supportdesk_customer_email'] ) ) );
		$email     = sanitize_email( $raw_email );
		if ( '' === $raw_email ) {
			delete_post_meta( $post_id, '_wp_supportdesk_customer_email' );
		} elseif ( $raw_email === $email && is_email( $email ) ) {
			update_post_meta( $post_id, '_wp_supportdesk_customer_email', wp_slash( $email ) );
		} else {
			set_transient( 'wp_supportdesk_admin_email_err_' . get_current_user_id(), 1, 30 );
		}
	}
}
add_action( 'save_post_support_ticket', 'wp_supportdesk_save_ticket_meta' );

/**
 * Adds bulk status update actions to the SupportDesk list table.
 *
 * @param array<string, string> $actions Existing bulk actions.
 * @return array<string, string>
 */
function wp_supportdesk_register_bulk_actions( $actions ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $actions;
	}

	$actions['wp_supportdesk_mark_open']        = __( 'Mark as Open', 'wp-supportdesk' );
	$actions['wp_supportdesk_mark_in_progress'] = __( 'Mark as In Progress', 'wp-supportdesk' );
	$actions['wp_supportdesk_mark_resolved']    = __( 'Mark as Resolved', 'wp-supportdesk' );

	return $actions;
}
add_filter( 'bulk_actions-edit-support_ticket', 'wp_supportdesk_register_bulk_actions' );

/**
 * Handles bulk status update actions on the SupportDesk list table.
 *
 * @param string $redirect_to Redirect URL.
 * @param string $action      Bulk action name.
 * @param array  $post_ids    Selected ticket IDs.
 * @return string
 */
function wp_supportdesk_handle_bulk_actions( $redirect_to, $action, $post_ids ) {
	$status_map = array(
		'wp_supportdesk_mark_open'        => 'open',
		'wp_supportdesk_mark_in_progress' => 'in_progress',
		'wp_supportdesk_mark_resolved'    => 'resolved',
	);

	if ( ! isset( $status_map[ $action ] ) ) {
		return $redirect_to;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return $redirect_to;
	}

	$new_status = $status_map[ $action ];
	$updated    = 0;

	foreach ( (array) $post_ids as $post_id ) {
		$post_id = absint( $post_id );
		if ( $post_id > 0 && 'support_ticket' === get_post_type( $post_id ) && current_user_can( 'edit_post', $post_id ) ) {
			update_post_meta( $post_id, '_wp_supportdesk_status', $new_status );
			++$updated;
		}
	}

	return add_query_arg(
		array(
			'supportdesk_bulk_updated' => $updated,
			'supportdesk_bulk_status'  => $new_status,
		),
		$redirect_to
	);
}
add_filter( 'handle_bulk_actions-edit-support_ticket', 'wp_supportdesk_handle_bulk_actions', 10, 3 );

/**
 * Displays admin feedback notices for bulk status updates and invalid admin email input.
 */
function wp_supportdesk_admin_notices() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'support_ticket' !== $screen->post_type ) {
		return;
	}

	if ( isset( $_GET['supportdesk_bulk_updated'], $_GET['supportdesk_bulk_status'] ) ) {
		$count  = absint( $_GET['supportdesk_bulk_updated'] );
		$status = sanitize_key( wp_unslash( $_GET['supportdesk_bulk_status'] ) );
		if ( $count > 0 ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: number of updated tickets, 2: status label. */
						_n(
							'%1$d ticket marked as %2$s.',
							'%1$d tickets marked as %2$s.',
							$count,
							'wp-supportdesk'
						),
						$count,
						wp_supportdesk_get_status_label( $status )
					)
				)
			);
		}
	}

	$user_id = get_current_user_id();
	if ( $user_id > 0 && get_transient( 'wp_supportdesk_admin_email_err_' . $user_id ) ) {
		delete_transient( 'wp_supportdesk_admin_email_err_' . $user_id );
		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html__( 'Customer email was not updated because the submitted address was invalid.', 'wp-supportdesk' )
		);
	}
}
add_action( 'admin_notices', 'wp_supportdesk_admin_notices' );

/**
 * Customizes WordPress update messages for the `support_ticket` post type.
 *
 * @param array $messages Post updated messages keyed by post type.
 * @return array
 */
function wp_supportdesk_updated_messages( $messages ) {
	$messages['support_ticket'] = array(
		0  => '',
		1  => __( 'Support ticket updated.', 'wp-supportdesk' ),
		2  => __( 'Custom field updated.', 'wp-supportdesk' ),
		3  => __( 'Custom field deleted.', 'wp-supportdesk' ),
		4  => __( 'Support ticket updated.', 'wp-supportdesk' ),
		6  => __( 'Support ticket saved.', 'wp-supportdesk' ),
		7  => __( 'Support ticket saved.', 'wp-supportdesk' ),
		8  => __( 'Support ticket submitted.', 'wp-supportdesk' ),
		10 => __( 'Support ticket draft updated.', 'wp-supportdesk' ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'wp_supportdesk_updated_messages' );

/**
 * Adds clickable Open / In Progress / Resolved status summary links above the ticket list table.
 *
 * @param array<string, string> $views Default WordPress post status views.
 * @return array<string, string>
 */
function wp_supportdesk_admin_status_views( $views ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $views;
	}

	$base_url        = admin_url( 'edit.php?post_type=support_ticket' );
	$current_status  = isset( $_GET['supportdesk_status'] ) ? sanitize_key( wp_unslash( $_GET['supportdesk_status'] ) ) : '';
	$current_wp_stat = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : '';
	$counts          = wp_count_posts( 'support_ticket' );
	$total_active    = isset( $counts->publish ) ? (int) $counts->publish : 0;

	$custom_views        = array();
	$all_class           = ( '' === $current_status && 'trash' !== $current_wp_stat ) ? ' class="current" aria-current="page"' : '';
	$custom_views['all'] = sprintf(
		'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
		esc_url( $base_url ),
		$all_class,
		esc_html__( 'All Tickets', 'wp-supportdesk' ),
		$total_active
	);

	foreach ( wp_supportdesk_get_statuses() as $slug => $label ) {
		$count = wp_supportdesk_count_tickets_by_status( $slug );
		$url   = add_query_arg( 'supportdesk_status', $slug, $base_url );
		$class = ( $current_status === $slug && 'trash' !== $current_wp_stat ) ? ' class="current" aria-current="page"' : '';

		$custom_views[ 'status_' . $slug ] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
			esc_url( $url ),
			$class,
			esc_html( $label ),
			$count
		);
	}

	if ( isset( $views['trash'] ) ) {
		$custom_views['trash'] = $views['trash'];
	}

	return $custom_views;
}
add_filter( 'views_edit-support_ticket', 'wp_supportdesk_admin_status_views' );

/**
 * Simplifies row actions on the SupportDesk list table by removing Quick Edit.
 *
 * @param array<string, string> $actions Existing row actions.
 * @param WP_Post               $post    Current post object.
 * @return array<string, string>
 */
function wp_supportdesk_row_actions( $actions, $post ) {
	if ( ! $post instanceof WP_Post || 'support_ticket' !== $post->post_type ) {
		return $actions;
	}

	unset( $actions['inline hide-if-no-js'] );

	if ( isset( $actions['edit'] ) && current_user_can( 'edit_post', $post->ID ) ) {
		$edit_url        = get_edit_post_link( $post->ID );
		$actions['edit'] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $edit_url ),
			/* translators: %s: Ticket subject. */
			esc_attr( sprintf( __( 'View or update "%s"', 'wp-supportdesk' ), get_the_title( $post ) ) ),
			esc_html__( 'View / Update', 'wp-supportdesk' )
		);
	}

	return $actions;
}
add_filter( 'post_row_actions', 'wp_supportdesk_row_actions', 10, 2 );

