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
	$bulk_actions['download_shoppe_list'] = 'Tải bảng mẫu Shoppe.';
	$bulk_actions['add_to_facebook_pages'] = 'Chuyển đến trang Facebook.';
	return $bulk_actions;
}

/**
* Remove empty new line
*/
function _remove_empty_new_line($str){
	$arr = explode("\r\n", $str);
	$arr_filter = array_filter($arr);
	$output = implode("\r\n", $arr_filter);
	
	return $output;
}

/**
 * Handles the bulk action.
 */
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
function my_bulk_action_handler( $redirect_to, $action, $post_ids ) {
	if ( $action === 'add_to_facebook_pages' ) {
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
				$arr_token = explode("\r\n", _remove_empty_new_line(trim(get_field('fb_access_token_truy_cap_page'))));
				foreach( $arr_token as $k => $token_page ) {
					$arr_pages[] = [$k + 1, $token_page];
				}
			}
		}
		wp_reset_postdata();
		
		foreach ( $post_ids as $key => $post_id ) {
			//Get data product
			$product = wc_get_product($post_id);
			$product_url = get_permalink( $post_id ) ;
			$response = wp_remote_post(
				'https://api-ssl.bitly.com/v4/bitlinks', 
				[
					'body' => 	json_encode([
									'long_url'=>$product_url . '?uuid=' . wp_generate_uuid4(),
								]),
					'headers' => [
									'Content-Type' => 'application/json',
									'Authorization' => "Bearer 1a2e53f2db298e7a0dc0c71d870aa5783bd7dc74"
								],
				]
			);
			
			$bitly_url = '';
			if(is_wp_error( $response ) == FALSE){
				// Response body.
				//$responceData = json_decode(wp_remote_retrieve_body( $response ) );
				//$bitly_url = $responceData->link;
			}	
			$bitly_url = 'koolwatch.me';	
			
			if($product != NULL){
				$product_name = trim($product->get_title());
				$gia_san_pham = number_format(($product->price != NULL) ? $product->price : 0, 0);
				//$gia_san_pham_ctv = number_format(get_post_meta($product->id, 'ctv_price', true ), 0);
				
				//$variations = $product->get_available_variations();//get_variation_attributes()
				
				//echo '<pre>';
				//print_r($variations);die();
				
				//Get images
				$attachment_ids = $product->get_gallery_attachment_ids();
				$attachments = [];
				foreach( $attachment_ids as $attachment_id ) {
					$attachments[] = wp_get_attachment_image_src( $attachment_id, 'full' )[0];
				}
				
				$images_str = implode("\r\n", $attachments);
				
				$fb_tieu_de = _remove_empty_new_line(trim(get_field('fb_page_tieu_de', $product->id)));
				$fb_noi_dung_san_pham = _remove_empty_new_line(trim(get_field('fb_page_noi_dung_san_pham', $product->id)));
				$fb_thong_tin_bao_hanh = _remove_empty_new_line(trim(get_field('fb_page_thong_tin_bao_hanh', $product->id)));
				$fb_thong_tin_lien_he = _remove_empty_new_line(trim(get_field('fb_page_thong_tin_lien_he', $product->id)));
				$fb_thong_tin_lien_ket = _remove_empty_new_line(trim(get_field('fb_page_thong_tin_lien_ket', $product->id)));
				$main_page_content = "
					$fb_tieu_de
					$product_name
					$fb_noi_dung_san_pham
					$fb_thong_tin_bao_hanh
					Giá bán : $gia_san_pham
					$fb_thong_tin_lien_he
					$fb_thong_tin_lien_ket
					
					Truy cập tại website : $bitly_url
				";

				$arr_products[]	= [$key + 1, $product_name, $main_page_content, $images_str];			
				
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
		$ymdhis = Date('YmdHis');
		$export_filename = 'Export_' . $ymdhis . '.xlsx';
		$log_filename = 'Export_' . $ymdhis . '.log';
		$excel_path = WP_CONTENT_DIR . "/download/$export_filename";
		$log_path = WP_CONTENT_DIR . "/download/$log_filename";
		$writer->save($excel_path);
		
		// redirect output to client browser
		/*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $export_filename . '"');
		header('Cache-Control: max-age=0');
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');*/
		
		//$excel_path = WP_CONTENT_DIR . "/download/Export_20200229053355.xlsx";
		
		$path_shell = RUN_PYTHON_SHELL_SCRITP;
		$path_python = get_template_directory() . "/linux_shell_script/CreatePostToPages.py";
		
		//Execute shell script upload to facebook
		$command = "sh $path_shell $path_python $log_path $excel_path";
		$output = exec($command);
		
		//return;//Return download file
		
		$redirect_to = add_query_arg( 
			[
				'bulk_reposts' => count( $post_ids ),
				'link_download' => content_url() . "/download/$export_filename",
				'link_log_download' => content_url() . "/download/$log_filename",
			]
			, $redirect_to 
		);
		
		return $redirect_to;
	}
	else if( $action === 'download_shoppe_list' ){
		return $redirect_to;
	}
	else{
		return $redirect_to;
	}
}

/**
 * Shows a notice in the admin once the bulk action is completed.
 */
function my_bulk_action_admin_notice() {
	if ( ! empty( $_REQUEST['bulk_reposts'] ) ) {
		$post_count = intval( $_REQUEST['bulk_reposts'] );
		$link_download = $_REQUEST['link_download'];
		$link_log_download = $_REQUEST['link_log_download'];
		printf(
			'<div id="message" class="updated fade ctv_admin_notices">
				%s product(s) exported. <a href="%s">Download excel</a> - <a href="%s">Download log</a>
			</div>',
			$post_count, $link_download, $link_log_download
		);
	}
}
