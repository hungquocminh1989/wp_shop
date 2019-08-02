<?php
defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| wp_ajax_thongbao
|-------------------------------------------------------------------------- 
*/
add_action( 'wp_ajax_thongbao', 'thongbao_init' );//Khai báo khi sử dụng bên Admin
add_action( 'wp_ajax_nopriv_thongbao', 'thongbao_init' );//Khai báo khi sử dụng bên Public
function thongbao_init() {
	
    //do bên js để dạng json nên giá trị trả về dùng phải encode
    $website = (isset($_POST['website']))?esc_attr($_POST['website']) : '';
    wp_send_json_success('Chào mừng bạn đến với '.$website);
    
}

/*
|--------------------------------------------------------------------------
| AJAX GET FACEBOOK TOKEN
|-------------------------------------------------------------------------- 
*/
add_action( 'wp_ajax_get_token', 'repo_action_get_token' );//Khai báo khi sử dụng bên Admin
//add_action( 'wp_ajax_nopriv_get_token', 'repo_action_get_token' );//Khai báo khi sử dụng bên Public
function repo_action_get_token() {
	
	if(isset($_POST['fb_user']) == TRUE && isset($_POST['fb_pass']) == TRUE
		&& $_POST['fb_user'] != '' && $_POST['fb_pass'] != ''
	){
		$user = $_POST['fb_user'];
		$pass = $_POST['fb_pass'];
		$api = new fbapi();
		$token = $api->get_token($user, $pass);
		$status = $api->checkToken($token);
		if($status == TRUE && $token != ''){
			wp_send_json_success($token);
		}
		else{
			wp_send_json_error("Token error.");
		}
	}
}

/*
|--------------------------------------------------------------------------
| AJAX CHECK TOKEN
|-------------------------------------------------------------------------- 
*/
add_action( 'wp_ajax_check_token', 'repo_action_check_token' );//Khai báo khi sử dụng bên Admin
//add_action( 'wp_ajax_nopriv_check_token', 'repo_action_check_token' );//Khai báo khi sử dụng bên Public
function repo_action_check_token() {
	if(isset($_POST['fb_token']) == TRUE
		&& $_POST['fb_token'] != ''
	){
		$token = $_POST['fb_token'];
		$api = new fbapi();
		$status = $api->checkToken($token);
		if($status == TRUE){
			wp_send_json_success($token);
		}
		else{
			wp_send_json_error();
		}
	}
    else{
		wp_send_json_error();
	}
}
