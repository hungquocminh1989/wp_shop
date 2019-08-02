<?php
defined( 'ABSPATH' ) || exit;
//if (is_admin()) return;

function repoPostToFacebook($id, $target = NULL){
	
	if($target != NULL){
		
		//Get account token
		$args['post_type'] = 'token';
		$args['meta_key'] = 'fb_trang_thai';
		$args['meta_value'] = 0;
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
		    while ( $the_query->have_posts() ) {
		    	$the_query->the_post();
		        $page_ids = get_field('fb_danh_sach_page');
		        $group_ids = get_field('fb_danh_sach_group');
		        $arr_group_id = explode("\r\n", trim($group_ids));
		        $arr_page_id = explode("\r\n", trim($page_ids));
				$token = get_field('fb_access_token');
				$token_page = get_field('fb_access_token_truy_cap_page');
				
		        if($token != '' & $token_page != '' && count($arr_page_id) > 0){
					
					//Get data product
					$product = wc_get_product($id);
					if($product != NULL && count($product) > 0){
						
						$gia_san_pham = $product->price;
						$gia_san_pham_ctv = get_post_meta($product->id, 'ctv_price', true );
						
						//Get images
						$attachment_ids = $product->get_gallery_attachment_ids();
						foreach( $attachment_ids as $attachment_id ) {
						  	$attachments[] = wp_get_attachment_image_src( $attachment_id, 'full' )[0];
						}
						
						$api = new fbapi();
						
						switch ($target) {
						    case "to_profile":
						        $fb_tieu_de = get_field('fb_profile_tieu_de', $product->id);
								$fb_noi_dung_san_pham = get_field('fb_profile_noi_dung_san_pham', $product->id);
								$fb_thong_tin_bao_hanh = get_field('fb_profile_thong_tin_bao_hanh', $product->id);
								$fb_thong_tin_lien_he = get_field('fb_profile_thong_tin_lien_he', $product->id);
								$fb_thong_tin_lien_ket = get_field('fb_profile_thong_tin_lien_ket', $product->id);
								$main_profile_content = "
									$fb_tieu_de
									$fb_noi_dung_san_pham
									$fb_thong_tin_bao_hanh
									Giá bán : $gia_san_pham
									$fb_thong_tin_lien_he
									$fb_thong_tin_lien_ket
								";
						        break;
						    case "to_group":
						    	$fb_tieu_de = get_field('fb_group_tieu_de', $product->id);
								$fb_noi_dung_san_pham = get_field('fb_group_noi_dung_san_pham', $product->id);
								$fb_thong_tin_bao_hanh = get_field('fb_group_thong_tin_bao_hanh', $product->id);
								$fb_thong_tin_lien_he = get_field('fb_group_thong_tin_lien_he', $product->id);
								$fb_thong_tin_lien_ket = get_field('fb_group_thong_tin_lien_ket', $product->id);
						        $main_group_content = "
									$fb_tieu_de
									$fb_noi_dung_san_pham
									$fb_thong_tin_bao_hanh
									Giá bán : $gia_san_pham_ctv
									$fb_thong_tin_lien_he
									$fb_thong_tin_lien_ket
								";
								if(count($arr_group_id)>0){
									foreach($arr_group_id as $group_id){
										//
									}
								}
						        break;
						    case "to_page":
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
								if(count($arr_page_id)>0){
									foreach($arr_page_id as $page_id){
										$rs = $api->createPagePost($page_id, $main_page_content, $attachments, $token_page);
									}
								}
						        break;
						    default:
						        //Do somethings.
						}	
						
					}
					
				}
		    }
		}
		wp_reset_postdata();
		
	}
	
}

function repoExecuteAutoPost()
{
	echo "<pre>";
	echo 'Start crontab';
	echo '<br/>';
	
	$group_kinhdoanh = '155525341777249';
	$page_dongho = '1042724125855991';
	
	$api = new fbapi();
	
	//Get post from group
	$rs = $api->getGroupPost($group_kinhdoanh, LIMIT_POST, TOKEN_PROFILE);
	
	if($rs != NULL && isset($rs->data) == TRUE){
		$arr_check_duplicate = [];			
		foreach($rs->data as $item){
			$post_id = explode("_",$item->id)[1];
			if(isset($item->message) == FALSE){
				echo "$post_id -> Error not exist message object.\r\n";
				continue;
			}
			
			$message = $item->message;
			$permalink_url = $item->permalink_url;
			$media = $item->attachments->data[0]->subattachments->data;
			
			$id_duplicate = repoCheckDuplicateContent($message, $post_id, $arr_check_duplicate);
			if($id_duplicate !== FALSE){
				echo "$post_id -> Duplicate content with $id_duplicate .\r\n";
				continue;
			}
			
			$attachments = [];
			foreach($media as $item){
				if(isset($item->media->source)){
					//$attachments[] = $item->media->source;
				}
				else{
					$attachments[] = $item->media->image->src;
				}
			}
			
			//Thay đổi giá cộng tác viên
			$pattern = "/(CTV|ctv)(.*)/";
			preg_match ( $pattern , $message, $matches);
			$matches=array_map('trim',$matches);
			$price = "Giá : Liên Hệ";
			if($matches != NULL && count($matches) > 0){
				//Replace x or X -> 0
				$price = preg_replace("/[xX]/", "0", $matches[0]);
				//Remove all non numeric characters
				$price = preg_replace("/[^0-9]/", "", $price);
				$price = "GIÁ : " . number_format(round((int)$price + EXTRA_PRICE)) . "K";
			}
			else{
				echo "$post_id -> Error price invalid.\r\n";
				continue;
			}
			
			//Remove all link
			//$message = trim(preg_replace("/(.*\d+\.*X*x*\.*[kK])/", "", $message));
			
			//Remove all price
			$message = trim(preg_replace("/(.*\d+\.*X*x*\.*[kK])/", "", $message));
			
			//Remove all empty line
			$message = trim(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $message));
			
			//Append custome text
			$message = "[ Hàng Quốc Tế Cao Cấp Xách Tay ]\r\n" . $message;
			$message .= "\r\n" . $price;
			$message .= "\r\n" . "✔️ Bảo hành 1 năm.";
			$message .= "\r\n" . "✔️ 1 đổi 1 nếu không giống mẫu.";
			$message .= "\r\n" . "✔️ Hàng săn sale mới 100% bao giá thị trường.";
			$message .= "\r\n" . "✔️ Liên hệ 0902676026 hoặc inbox để xem hàng trực tiếp.";
			$message .= "\r\n" . "#DongHoNamNu #HangSanSale #DongHoChinhHang #HangXachTay #GiaSock";
			$message .= "\r\n" . "#ThiTruongGiaReVN #ShopDongHoNamNu #HopTacKinhDoanh #HangNuocNgoai #DongHoThoiTrang #$post_id";
			//var_dump($message);
			
			//Get page post
			$rs_page_post = $api->getPagePost($page_dongho, LIMIT_POST_CHECK, TOKEN_PAGE);
			$arr_page_post = [];
			if($rs_page_post != NULL && isset($rs_page_post->data) == TRUE){
				foreach($rs_page_post->data as $page_item){
					$arr_page_post[] = $page_item->message;
				}
			}
			//var_dump($arr_page_post);die();
			//var_dump($post_id);die();
			$arr_matchs = preg_grep("/$post_id/", $arr_page_post);
			//var_dump($arr_matchs);die();
			//echo count($arr_matchs);die();
			if(count($arr_matchs) === 0){					
				//Post to page
				if(POST_FLG === TRUE){
					$rs = $api->createPagePost($page_dongho, $message, $attachments, TOKEN_PAGE);
				}
				echo "$post_id -> Posted.\r\n";
			}
			else{
				echo "$post_id -> Skipped.\r\n";
			}
		}
	}
	
	echo 'End crontab';
	die();
	return FALSE;#Stop Route
}

function repoCheckDuplicateContent($msg, $post_id, &$arr){
	$isDuplicate = FALSE;
	foreach($arr as $key => $item){
		similar_text($msg,$item,$percent);
		if($percent >= 80){
			$isDuplicate = $key;
			/*var_dump($msg);
			var_dump($item);
			var_dump($percent);*/
			break;
		}
	}
	$arr[$post_id] = $msg;
	
	return $isDuplicate;
}