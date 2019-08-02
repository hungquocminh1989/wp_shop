<?php
defined( 'ABSPATH' ) || exit;
if (!is_admin()) return;

/*
|--------------------------------------------------------------------------
| TOKEN POSTTYPE
|-------------------------------------------------------------------------- 
*/
add_filter( 'bulk_actions-edit-product', 'register_my_bulk_actions' );
add_filter( 'handle_bulk_actions-edit-product', 'my_bulk_action_handler', 10, 3 );
add_action( 'admin_notices', 'my_bulk_action_admin_notice' );

/**
 * Adds a new item into the Bulk Actions dropdown.
 */
function register_my_bulk_actions( $bulk_actions ) {
	$bulk_actions['to_page'] = 'Up To Facebook Page';
	$bulk_actions['to_group'] = 'Up To Facebook Group';
	$bulk_actions['to_profile'] = 'Up To Facebook Profile';
	return $bulk_actions;
}

/**
 * Handles the bulk action.
 */
function my_bulk_action_handler( $redirect_to, $action, $post_ids ) {
	if ( $action !== 'to_page' && $action !== 'to_group' && $action !== 'to_profile' ) {
		return $redirect_to;
	}

	foreach ( $post_ids as $post_id ) {
		repoPostToFacebook($post_id, $action);
	}

	$redirect_to = add_query_arg( 'bulk_reposts', count( $post_ids ), $redirect_to );

	return $redirect_to;
}

/**
 * Shows a notice in the admin once the bulk action is completed.
 */
function my_bulk_action_admin_notice() {
	if ( ! empty( $_REQUEST['bulk_reposts'] ) ) {
		$post_count = intval( $_REQUEST['bulk_reposts'] );

		printf(
			'<div id="message" class="updated fade ctv_admin_notices">
				%s sản phẩm đã đưa lên Facebook.
			</div>',
			$post_count
		);
	}
}
