<?php
defined( 'ABSPATH' ) || exit;
//
//define( 'ACF_LITE', true );//Hide ACF Menu
include_once(ABSPATH . 'wp-content/plugins/advanced-custom-fields-pro/acf.php');

//Add ACF with generate code PHP

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_5d32f3163f9ed',
	'title' => 'Nội Dung Facebook Page',
	'fields' => array(
		array(
			'key' => 'field_5d32f72b9dded',
			'label' => 'Tiêu Đề',
			'name' => 'fb_page_tieu_de',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '[ Hàng like authentic cao cấp ]',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
		),
		array(
			'key' => 'field_5d32f341a062d',
			'label' => 'Nội Dung Sản Phẩm',
			'name' => 'fb_page_noi_dung_san_pham',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => '',
			'new_lines' => '',
		),
		array(
			'key' => 'field_5d32f7bf9ddef',
			'label' => 'Thông Tin Bảo Hành',
			'name' => 'fb_page_thong_tin_bao_hanh',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '✔️ Hỗ trợ bảo hành dài hạn (1 năm).
✔️ Chất liệu cao cấp.
✔️ Hàng săn sale mới 100%.',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 4,
			'new_lines' => '',
		),
		array(
			'key' => 'field_5d32f42a9a4b0',
			'label' => 'Thông Tin Liên Hệ',
			'name' => 'fb_page_thong_tin_lien_he',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '_____________________________
Koolwatch
🇻🇳Đồng hồ thời trang like authentic chất lượng, bảo hành dài hạn
📞Hotline: 0902660153
👉Website: https://koolwatch.me/
📩Email: support@koolwatch.me',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 2,
			'new_lines' => '',
		),
		array(
			'key' => 'field_5d32f7b99ddee',
			'label' => 'Thông Tin Liên Kết',
			'name' => 'fb_page_thong_tin_lien_ket',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '#DongHoNamNu #LikeAuthentic
#koolwatch.me',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 4,
			'new_lines' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'product',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));

endif;