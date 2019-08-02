<?php
defined( 'ABSPATH' ) || exit;

add_action( 'acf/render_field/name=fb_access_token', 'repo_button_get_token', 10, 1 );
add_action('acf/input/admin_head', 'repo_acf_admin_head');
add_action('acf/input/admin_footer', 'repo_acf_admin_footer');
add_action('acf/input/admin_enqueue_scripts', 'repo_acf_admin_enqueue_scripts');
add_action('acf/input/form_data', 'repo_acf_form_data');
add_action('acf/validate_save_post', 'repo_acf_validate_save_post');
add_action('acf/save_post', 'repo_acf_save_post');