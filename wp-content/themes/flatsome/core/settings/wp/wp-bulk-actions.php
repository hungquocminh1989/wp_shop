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
	$bulk_actions['export_to_excel'] = 'Export to excel';
	return $bulk_actions;
}

/**
 * Handles the bulk action.
 */
 use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
function my_bulk_action_handler( $redirect_to, $action, $post_ids ) {
	if ( $action !== 'export_to_excel') {
		return $redirect_to;
	}
	
	$arr_products = [];
	$arr_products[] = ['No.', 'Product Name', 'Contents', 'Images'];
	$arr_pages = [];
	$arr_pages[] = ['No.', 'Token'];
	
	$args['post_type'] = 'token';
	$args['meta_key'] = 'fb_trang_thai';
	$args['meta_value'] = 0;
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) {
	    while ( $the_query->have_posts() ) {
	    	$the_query->the_post();
			
			$arr_token = explode("\r\n", get_field('fb_access_token_truy_cap_page'));
			
			foreach( $arr_token as $k => $token_page ) {
				
				$arr_pages[] = [$k + 1, $token_page];
				
			}
			
		}
		
	}
	wp_reset_postdata();
	
	foreach ( $post_ids as $key => $post_id ) {
		//Get data product
		$product = wc_get_product($post_id);
		
		if($product != NULL){
			
			$gia_san_pham = $product->price;
			$gia_san_pham_ctv = get_post_meta($product->id, 'ctv_price', true );
			
			//Get images
			$attachment_ids = $product->get_gallery_attachment_ids();
			
			foreach( $attachment_ids as $attachment_id ) {
				$attachments[] = wp_get_attachment_image_src( $attachment_id, 'full' )[0];
			}
			
			$images_str = implode("\r\n", $attachments);
			
			$fb_tieu_de = get_field('fb_page_tieu_de', $product->id);
			$fb_noi_dung_san_pham = get_field('fb_page_noi_dung_san_pham', $product->id);
			$fb_thong_tin_bao_hanh = get_field('fb_page_thong_tin_bao_hanh', $product->id);
			$fb_thong_tin_lien_he = get_field('fb_page_thong_tin_lien_he', $product->id);
			$fb_thong_tin_lien_ket = get_field('fb_page_thong_tin_lien_ket', $product->id);
			$main_page_content = "
				$fb_tieu_de
				$fb_noi_dung_san_pham
				$fb_thong_tin_bao_hanh
				Giá bán : $gia_san_pham
				$fb_thong_tin_lien_he
				$fb_thong_tin_lien_ket
			";

			$arr_products[]	= [$key + 1, '', $main_page_content, $images_str];			
			
		}
	}
	
	//Sheet Products
	$spreadsheet = new Spreadsheet();
	$sheet_product = $spreadsheet->getActiveSheet();
	$sheet_product->setTitle('Products');
	$sheet_product->fromArray(
		$arr_products,  // The data to set
		NULL,        // Array values with this value will not be set
		'A1'         // Top left coordinate of the worksheet range where
					 //    we want to set these values (default is A1)
	);
	
	//Sheet Pages
	$sheet_page = $spreadsheet->createSheet();
	$sheet_page->setTitle('Pages');
	$sheet_page->fromArray(
		$arr_pages,  // The data to set
		NULL,        // Array values with this value will not be set
		'A1'         // Top left coordinate of the worksheet range where
					 //    we want to set these values (default is A1)
	);
	
	//Format header excel
	$styleArray = [
		'font' => [
			'bold' => true,
		],
	];
	$sheet_product->getStyle('A1:D1')->applyFromArray($styleArray);
	$sheet_page->getStyle('A1:B1')->applyFromArray($styleArray);
	
	//Save file
	$writer = new Xlsx($spreadsheet);
	$writer->save(WP_CONTENT_DIR . '/download/export.xlsx');
	download_url(content_url() . '/download/export.xlsx');
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
				%s product(s) exported.
			</div>',
			$post_count
		);
	}
}
