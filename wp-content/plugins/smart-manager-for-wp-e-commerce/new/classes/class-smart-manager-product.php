<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Product' ) ) {
	class Smart_Manager_Product extends Smart_Manager_Base {
		public $dashboard_key = '',
			$default_store_model = array(),
			$prod_sort = false,
			$terms_att_search_flag = 0, //flag for handling attrbute search
			$product_visibility_visible_flag = 0, //flag for handling visibility search
			$product_old_title = array(); // array for storing the old product titles

		function __construct($dashboard_key) {
			parent::__construct($dashboard_key);

			$this->dashboard_key = $dashboard_key;
			$this->post_type = array('product', 'product_variation');
			$this->req_params  	= (!empty($_REQUEST)) ? $_REQUEST : array();

			add_filter('sm_dashboard_model',array(&$this,'products_dashboard_model'),10,2);
			add_filter('sm_data_model',array(&$this,'products_data_model'),10,2);

			add_filter('sm_required_cols',array(&$this,'sm_beta_required_cols'),10,1);

			add_filter('sm_inline_update_pre',array(&$this,'products_inline_update_pre'),10,1);
			add_action('sm_inline_update_post',array(&$this,'products_inline_update'),10,2);

			// add_filter('posts_orderby',array(&$this,'sm_product_query_order_by'),10,2);

			add_filter('posts_fields',array(&$this,'sm_product_query_post_fields'),100,2);
			add_filter('posts_where',array(&$this,'sm_product_query_post_where_cond'),100,2);
			add_filter('posts_orderby',array(&$this,'sm_product_query_order_by'),100,2);
			
			//filters for handling search
			add_filter('sm_search_postmeta_cond',array(&$this,'sm_search_postmeta_cond'),10,2);
			add_filter('sm_search_terms_cond',array(&$this,'sm_search_terms_cond'),10,2);

			//filter for modifying each of the search cond
			add_filter('sm_search_format_query_terms_col_name',array(&$this,'sm_search_format_query_terms_col_name'),10,2);

			add_filter('sm_search_query_formatted',array(&$this,'sm_search_query_formatted'),10,2);

			add_filter('sm_search_query_terms_select',array(&$this,'sm_search_query_terms_select'),10,2);
			add_filter('sm_search_query_terms_from',array(&$this,'sm_search_query_terms_from'),10,2);
			add_filter('sm_search_query_terms_where',array(&$this,'sm_search_query_terms_where'),10,2);

			add_filter('sm_search_query_posts_where',array(&$this,'sm_search_query_posts_where'),10,2);

			add_action('sm_search_terms_condition_complete',array(&$this,'search_terms_condition_complete'),10,2);
			add_action('sm_search_terms_conditions_array_complete',array(&$this,'search_terms_conditions_array_complete'),10,1);

			add_filter('sm_search_query_postmeta_where',array(&$this,'sm_search_query_postmeta_where'),10,2);

			add_filter('sm_batch_update_copy_from_ids_select',array(&$this,'sm_batch_update_copy_from_ids_select'),10,1);
			// add_action('admin_footer',array(&$this,'attribute_handling'));
		}

		//Fucntion for overriding the select clause for fetching the ids for batch update 'copy from' functionality
		public function sm_batch_update_copy_from_ids_select( $select ) {

			$select = " SELECT ID AS id, 
							( CASE 
			            		WHEN (post_excerpt != '' AND post_type = 'product_variation') THEN CONCAT(post_title, ' - ( ', post_excerpt, ' ) ')
								ELSE post_title
			            	END ) as title ";

			return $select;
		}

		public function sm_beta_required_cols( $cols ) {

			$required_cols = array('posts_post_title', 'posts_post_parent', 'postmeta_meta_key__product_attributes_meta_value__product_attributes');
			return array_merge($cols, $required_cols);
		}

		//function to modify the terms search column name while forming the formatted search query		
		public function sm_search_format_query_terms_col_name($search_col='', $search_params=array()) {

			if( !empty($search_col) && substr($search_col, 0, 10) == 'attribute_' ) {
				$search_col = substr($search_col, 10);
			}

			return $search_col;
		}

		//function to handle child ids for terms search
		public function search_terms_condition_complete($result_terms_search = array(), $search_params = array()) {

			global $wpdb;

			if( empty($search_params) ) {
				return;
			}

			//Code to handle child ids in case of category search
            if (!empty($result_terms_search) && !empty($search_params) && substr($search_params['cond_terms_col_name'], 0, 10) != 'attribute_' ) {

            	$flag = ( !empty($search_params['terms_search_result_flag']) ) ? $search_params['terms_search_result_flag'] : ', 0';

                //query when attr cond has been applied
                if ( $this->terms_att_search_flag == 1 ){
                    $query = "REPLACE INTO {$wpdb->base_prefix}sm_advanced_search_temp
                            ( SELECT {$wpdb->prefix}posts.id ". $flag ." ,1
                                FROM {$wpdb->prefix}posts
                                JOIN {$wpdb->base_prefix}sm_advanced_search_temp AS temp1
                                    ON (temp1.product_id = {$wpdb->prefix}posts.id)
                                JOIN {$wpdb->base_prefix}sm_advanced_search_temp AS temp2
                                    ON (temp2.product_id = {$wpdb->prefix}posts.post_parent)
                                WHERE temp2.cat_flag = 1 )";    
                } else {
                    //query when no attr cond has been applied
                    $query = "REPLACE INTO {$wpdb->base_prefix}sm_advanced_search_temp
                            ( SELECT {$wpdb->prefix}posts.id ". $flag ." ,1
                                FROM {$wpdb->prefix}posts 
                                JOIN {$wpdb->base_prefix}sm_advanced_search_temp
                                    ON ({$wpdb->base_prefix}sm_advanced_search_temp.product_id = {$wpdb->prefix}posts.post_parent)
                                WHERE {$wpdb->base_prefix}sm_advanced_search_temp.cat_flag = 1 )";
                }

                $result = $wpdb->query ( $query );
            }

            if( !empty($search_params) && trim($search_params['cond_terms_col_name']) == 'product_visibility' && trim($search_params['cond_terms_operator']) == 'LIKE' && trim($search_params['cond_terms_col_value']) == 'visible' ) {
                $this->product_visibility_visible_flag = 1;
            }
		}


		//function to handle visibility search
		public function search_terms_conditions_array_complete($search_params = array()) {

			if( empty($search_params) ) {
				return;
			}

			global $wpdb;

			if( !empty($this->product_visibility_visible_flag) && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {

                $query_advanced_search_taxonomy_id = "SELECT {$wpdb->prefix}term_taxonomy.term_taxonomy_id
                                                      FROM {$wpdb->prefix}term_taxonomy
                                                        JOIN {$wpdb->prefix}terms
                                                            ON ( {$wpdb->prefix}terms.term_id = {$wpdb->prefix}term_taxonomy.term_id)
                                                      WHERE {$wpdb->prefix}term_taxonomy.taxonomy LIKE 'product_visibility' 
                                                            AND {$wpdb->prefix}terms.slug IN ('exclude-from-search', 'exclude-from-catalog')";
                $result_advanced_search_taxonomy_id = $wpdb->get_col ( $query_advanced_search_taxonomy_id );

                if( count($result_advanced_search_taxonomy_id) > 0 ) {
                    $result_taxonomy_ids = implode(",",$result_advanced_search_taxonomy_id);

                    $query_terms_visibility = " DELETE FROM {$wpdb->base_prefix}sm_advanced_search_temp
                                                WHERE product_id IN (SELECT DISTINCT {$wpdb->prefix}posts.id
                                                                    FROM {$wpdb->prefix}posts
                                                                        JOIN {$wpdb->prefix}term_relationships
                                                                        ON ({$wpdb->prefix}term_relationships.object_id = {$wpdb->prefix}posts.id) 
                                                                    WHERE {$wpdb->prefix}term_relationships.term_taxonomy_id IN (". $result_taxonomy_ids ."))"; 
                    $result_terms_visibility = $wpdb->query( $query_terms_visibility );
                }                                
                
            }
		}

		//function to handle custom postmeta conditions for advanced search
		public function sm_search_postmeta_cond($postmeta_cond = '', $search_params = array()) {
			if ( !empty($search_params) && !empty($search_params['search_col']) && $search_params['search_col'] == '_product_attributes' ) {
				if ($search_params['search_operator'] == 'is') {
					$postmeta_cond = " ( ". $search_params['search_string']['table_name'].".meta_key LIKE '". $search_params['search_col'] . "' AND ". $search_params['search_string']['table_name'] .".meta_value LIKE '%" . $search_params['search_value'] . "%'" . " )";
				} else if ($search_params['search_operator'] == 'is not') {
					$postmeta_cond = " ( ". $search_params['search_string']['table_name'].".meta_key LIKE '". $search_params['search_col'] . "' AND ". $search_params['search_string']['table_name'] .".meta_value NOT LIKE '%" . $search_params['search_value'] . "%'" . " )";
				}
			}

			return $postmeta_cond;
		}


		//function to handle custom terms conditions for advanced search
		public function sm_search_terms_cond($terms_cond = '', $search_params = array()) {

			global $wpdb;

			if( !empty($search_params) ) {
				if ($search_params['search_operator'] == 'is') {
					if( $search_params['search_string']['value'] == "''" ) { //for handling empty search strings
						$empty_cond = ''; //variable for handling conditions for empty string

	                    if( substr($search_params['search_col'],0,3) == 'pa_' ) { //for attributes column TODO in products
	                        $empty_cond = " AND ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '%pa_%' ";
	                    }

	                    $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy NOT LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."term_taxonomy.taxonomy NOT LIKE 'product_type' ". $empty_cond ." )";
					} else {

							if( $search_params['search_col'] == 'product_visibility' && ( ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) ) { //TODO in products

                            if( $search_params['search_value'] == 'visible' ) {
                                $terms_cond = " ( ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-search' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-catalog' ) OR ( ". $wpdb->prefix ."term_taxonomy.taxonomy NOT LIKE '". $search_params['search_col'] . "' ) )";
                                $advanced_search_query[$i]['cond_terms_operator'] .= 'LIKE';    
                            } else if( $search_params['search_value'] == 'hidden' ) {
                                $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug = 'exclude-from-search' ) AND  ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug = 'exclude-from-catalog' ) ";
                                $advanced_search_query[$i]['cond_terms_operator'] .= 'LIKE'; 
                            } else if( $search_params['search_value'] == 'catalog' ) { //TODO: Needs Improvement
                                $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug = 'exclude-from-search' ) AND  ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-catalog' ) ";
                                $advanced_search_query[$i]['cond_terms_operator'] .= 'LIKE'; 

                                $advanced_search_query[$i]['cond_terms_col_name'] .= " AND ". $search_params['search_col']; //added only for this specific search condition
                            } else if( $search_params['search_value'] == 'search' ) { //TODO: Needs Improvement
                                $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug = 'exclude-from-catalog' ) AND  ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-search' ) ";
                                $advanced_search_query[$i]['cond_terms_operator'] .= 'LIKE'; 
                            }

                        } else if( $search_params['search_col'] == 'product_visibility_featured' && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {
                            $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE 'product_visibility' AND ". $wpdb->prefix ."terms.slug = 'featured' ) ";
                        }
					}
				} else if ($search_params['search_operator'] == 'is not') {
					if( $search_params['search_string']['value'] != "''" ) {
						$attr_cond = '';

                        if( substr($search_params['search_col'],0,3) == 'pa_' ) { //for attributes column
                            $attr_cond = " AND ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '%pa_%' ";
                        }

                        if( $search_params['search_col'] == 'product_visibility' && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {

                            if( $search_params['search_value'] == 'visible' ) {
                                $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug = 'exclude-from-search' OR ". $wpdb->prefix ."terms.slug = 'exclude-from-catalog' )";
                            } else if( $search_params['search_value'] == 'hidden' ) {
                                $terms_cond = " ( ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-search' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-catalog' ) OR ( ". $wpdb->prefix ."term_taxonomy.taxonomy NOT LIKE '". $search_params['search_col'] . "' ) ) ";
                            } else if( $search_params['search_value'] == 'catalog' ) { //TODO: Needs Improvement
                                $terms_cond = " ( ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-search' ) OR ( ". $wpdb->prefix ."term_taxonomy.taxonomy NOT LIKE '". $search_params['search_col'] . "' ) )";
                            } else if( $search_params['search_value'] == 'search' ) { //TODO: Needs Improvement
                                $terms_cond = " ( ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE '". $search_params['search_col'] . "' AND ". $wpdb->prefix ."terms.slug != 'exclude-from-catalog' ) OR ( ". $wpdb->prefix ."term_taxonomy.taxonomy NOT LIKE '". $search_params['search_col'] . "' ) )";
                            }

                        } else if( $search_params['search_col'] == 'product_visibility_featured' && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {
                            $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy LIKE 'product_visibility' AND ". $wpdb->prefix ."terms.slug != 'featured' ) ";
                        } else {
                            $terms_cond = " ( ". $wpdb->prefix ."term_taxonomy.taxonomy NOT LIKE '". $search_params['search_col'] . "' ". $attr_cond ." AND ". $wpdb->prefix ."terms.slug NOT LIKE '" . $search_params['search_value'] . "'" . " )";
                        }
					}
				}	
			}

			return $terms_cond;

		}

		//function to modify the advanced search query formatted array
		public function sm_search_query_formatted($advanced_search_query = array(), $search_params = array()) {

			if( !empty($search_params) ) {
				if ($search_params['search_operator'] == 'is') {
					if( $search_params['search_string']['value'] != "''" ) {
						if( $search_params['search_col'] == 'product_visibility' && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {
							if( $search_params['search_value'] != 'visible' ) {
								$advanced_search_query['cond_terms_col_name'] .= $search_params['search_col'] ." AND "; //added only for this specific search condition
							}
						}
					}
				}
			}

			return $advanced_search_query;
		}

		//function to handle terms custom select clause
		public function sm_search_query_terms_select($sm_search_query_terms_select = '', $search_params = array()) {

			if ( !empty($search_params['cond_terms_col_name']) && substr($search_params['cond_terms_col_name'], 0, 10) == 'attribute_' ) {
		        $sm_search_query_terms_select .= " ,0";
		        $this->terms_att_search_flag = 1; //Flag to handle the child ids for cat advanced search
		    } else if ( !empty($search_params['cond_terms_col_name']) && substr($search_params['cond_terms_col_name'], 0, 10) != 'attribute_' ) {
		        $sm_search_query_terms_select .= " ,1  ";
		    }

			return $sm_search_query_terms_select;
		}

		//function to handle terms custom from clause
		public function sm_search_query_terms_from($sm_search_query_terms_from = '', $search_params = array()) {

			global $wpdb;

			if ( !empty($search_params['cond_terms_col_name']) && substr($search_params['cond_terms_col_name'], 0, 10) == 'attribute_' ) {
		        $sm_search_query_terms_from = " FROM {$wpdb->prefix}posts
	                                            LEFT JOIN {$wpdb->prefix}term_relationships
	                                                ON ({$wpdb->prefix}term_relationships.object_id = {$wpdb->prefix}posts.id)
	                                            JOIN {$wpdb->prefix}postmeta
	                                                ON ( {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.id
	                                                	AND {$wpdb->prefix}posts.post_type IN ('". implode( "','", $search_params['post_type'] ) ."') )";
	            $this->terms_att_search_flag = 1; //Flag to handle the child ids for cat advanced search
		    } else if ( !empty($search_params['cond_terms_col_name']) && substr($search_params['cond_terms_col_name'], 0, 10) != 'attribute_' ) {
		        $sm_search_query_terms_from = "FROM {$wpdb->prefix}posts
                                                JOIN {$wpdb->prefix}term_relationships
                                                    ON ({$wpdb->prefix}term_relationships.object_id = {$wpdb->prefix}posts.id
                                                		AND {$wpdb->prefix}posts.post_type IN ('". implode( "','", $search_params['post_type'] ) ."') )";
		    }

			return $sm_search_query_terms_from;
		}

		//function to handle terms custom where clause
		public function sm_search_query_terms_where($sm_search_query_terms_where = '', $search_params = array()) {

			global $wpdb;

			if( !empty($search_params['result_taxonomy_ids']) ) {
				$taxonomy_cond = " ({$wpdb->prefix}term_relationships.term_taxonomy_id IN (". $search_params['result_taxonomy_ids'] .")) ";
			}

			if ( !empty($search_params['cond_terms_col_name']) && substr($search_params['cond_terms_col_name'], 0, 10) == 'attribute_' ) {

				$taxonomy_cond = (!empty($taxonomy_cond)) ? ' ( '. $taxonomy_cond : '';	

		        $sm_search_query_terms_where = " WHERE ". $taxonomy_cond ."
                                                        OR ({$wpdb->prefix}postmeta.meta_key ". (($search_params['cond_terms_col_value'] == "''") ? 'LIKE' : $search_params['cond_terms_operator']) ." '".trim($search_params['cond_terms_col_name']) . 
                                                        "' AND {$wpdb->prefix}postmeta.meta_value ". $search_params['cond_terms_operator'] ." '". trim($search_params['cond_terms_col_value'])."'))";
                $this->terms_att_search_flag = 1; //Flag to handle the child ids for cat advanced search
		    } else if ( !empty($search_params['cond_terms_col_name']) && substr($search_params['cond_terms_col_name'], 0, 10) != 'attribute_' ) {
		    	$sm_search_query_terms_where = (!empty($taxonomy_cond)) ? ' WHERE '. $taxonomy_cond : '';
		    }

			return $sm_search_query_terms_where;
		}



		//function to handle postmeta custom where clause
		public function sm_search_query_postmeta_where($sm_search_query_postmeta_where = '', $search_params = array()) {

			global $wpdb;

			if(!empty( $search_params ) && !empty( $search_params['cond_postmeta_col_name'] ) ) {
				if( $search_params['cond_postmeta_col_name'] == '_regular_price' || $search_params['cond_postmeta_col_name'] == '_sale_price' ) {
	               $sm_search_query_postmeta_where .= " AND {$wpdb->prefix}postmeta.post_id NOT IN (SELECT post_parent 
	                                                                  FROM {$wpdb->prefix}posts
	                                                                  WHERE post_type IN ('product', 'product_variation')
	                                                                    AND post_parent > 0) ";
	            }

	            if( $search_params['cond_postmeta_col_name'] == '_product_attributes' ) {
	            	$index = strpos($sm_search_query_postmeta_where, 'WHERE');
		            if( $index !== false ){
		            	$sm_search_query_postmeta_where = substr($sm_search_query_postmeta_where, ($index + 5) );
		            }
		        	$sm_search_query_postmeta_where = " WHERE ( (". $sm_search_query_postmeta_where .") OR ({$wpdb->prefix}postmeta.meta_key LIKE 'attribute%' AND {$wpdb->prefix}postmeta.meta_value ". $search_params['cond_postmeta_operator'] ." '%". $search_params['cond_postmeta_col_value'] ."%') ) ";
	            }
			}

			return $sm_search_query_postmeta_where;
			
		}

		//function to handle posts custom where clause
		public function sm_search_query_posts_where($posts_advanced_search_where = '', $search_params = array()) {

			global $wpdb;

			if( strpos( $search_params['cond_posts'],'post_status' ) !== FALSE ) {
	            $posts_advanced_search_where .= " AND ".$wpdb->prefix."posts.post_parent = 0 ";
	        }

			return $posts_advanced_search_where;
		}

		public function sm_product_query_post_fields ($fields, $wp_query_obj) {
			
			global $wpdb;

			$fields .= ',if('.$wpdb->prefix.'posts.post_parent = 0,'.$wpdb->prefix.'posts.id,'.$wpdb->prefix.'posts.post_parent - 1 + ('.$wpdb->prefix.'posts.id)/pow(10,char_length(cast('.$wpdb->prefix.'posts.id as char)))) as parent_sort_id';

			return $fields;
		}

		public function sm_product_query_post_where_cond ($where, $wp_query_obj) {
			
			global $wpdb;

			//Code to get the ids of all the products whose post_status is thrash
	        $query_trash = "SELECT ID FROM {$wpdb->prefix}posts 
	                        WHERE post_status = 'trash'
	                            AND post_type IN ('product')";
	        $results_trash = $wpdb->get_col( $query_trash );
	        $rows_trash = $wpdb->num_rows;
	        
	        // Code to get all the variable parent ids whose type is set to 'simple'

	        //Code to get the taxonomy id for 'simple' product_type
	        $query_taxonomy_ids = "SELECT taxonomy.term_taxonomy_id as term_taxonomy_id
	                                    FROM {$wpdb->prefix}terms as terms
	                                        JOIN {$wpdb->prefix}term_taxonomy as taxonomy ON (taxonomy.term_id = terms.term_id)
	                                    WHERE taxonomy.taxonomy = 'product_type'
	                                    	AND terms.slug IN ('variable', 'variable-subscription')";
	        $variable_taxonomy_ids = $wpdb->get_col( $query_taxonomy_ids );

	        if ( !empty($variable_taxonomy_ids) ) {
	        	$query_post_parent_not_variable = "SELECT distinct products.post_parent 
				                            FROM {$wpdb->prefix}posts as products 
				                            WHERE NOT EXISTS (SELECT * 
				                            					FROM {$wpdb->prefix}term_relationships 
				                            					WHERE object_id = products.post_parent
				                            						AND term_taxonomy_id IN (". implode(",",$variable_taxonomy_ids) ."))
				                              AND products.post_parent > 0 
				                              AND products.post_type = 'product_variation'";
		        $results_post_parent_not_variable = $wpdb->get_col( $query_post_parent_not_variable );
		        $rows_post_parent_not_variable = $wpdb->num_rows;	

		        for ($i=sizeof($results_trash),$j=0;$j<sizeof($results_post_parent_not_variable);$i++,$j++ ) {
		            $results_trash[$i] = $results_post_parent_not_variable[$j];
		        }
	        }

	        if ($rows_trash > 0 || $rows_post_parent_not_variable > 0) {
	            $where .= " AND {$wpdb->prefix}posts.post_parent NOT IN (" .implode(",",$results_trash). ")";
	        }

			return $where;
		}

		public function sm_product_query_order_by ($order_by, $wp_query_obj) {
	
			global $wpdb;


			if ( !empty( $this->req_params['sort_params'] ) && empty( $this->req_params['sort_params']['default'] ) && ( ( !empty( $this->req_params['sort_params']['column_nm'] ) && ( ( $this->req_params['sort_params']['column_nm'] != 'ID' ) || ( $this->req_params['sort_params']['column_nm'] == 'ID' && $this->req_params['sort_params']['sortOrder'] == 'ASC' ) ) ) || empty( $this->req_params['sort_params']['coumn_nm'] ) ) ) {

				if( empty( $this->req_params['sort_params']['column_nm'] ) ) {
					$col_exploded = explode( "/", $this->req_params['sort_params']['column'] );

					$this->req_params['sort_params']['table'] = $col_exploded[0];

					if ( sizeof($col_exploded) == 2) {
						$this->req_params['sort_params']['column_nm'] = $col_exploded[1];
					}

					$this->req_params['sort_params']['sortOrder'] = strtoupper( $this->req_params['sort_params']['sortOrder'] );
				}

				$sort_order = ( !empty( $this->req_params['sort_params']['sortOrder'] ) ) ? $this->req_params['sort_params']['sortOrder'] : 'ASC';

				if ( $this->req_params['sort_params']['table'] == 'posts' ) {				
					$order_by = $this->req_params['sort_params']['column_nm'] .' '. $sort_order;
				} else if ( $this->req_params['sort_params']['table'] == 'terms' ) {
					$order_by = $wpdb->prefix. 'term_relationships.term_taxonomy_id '.$sort_order ;
				}

				$this->prod_sort = true;

			} else {
				$order_by = 'parent_sort_id DESC';
				$this->prod_sort = false;
			}
			return $order_by;
		}

		public function products_dashboard_model ($dashboard_model, $dashboard_model_saved) {

			global $wpdb, $current_user;

			$visible_columns = array('ID', '_thumbnail_id', 'post_title', '_sku', '_regular_price', '_sale_price', 
									'_stock','post_status', 'post_content','product_cat','product_attributes', '_length', '_width', '_height', 
									'_visibility', '_tax_status', 'product_type', 'link');

			$numeric_columns = array('_regular_price', '_sale_price', '_price', '_stock','_length', '_width', '_height');
			$datetime_columns = array('_sale_price_dates_from', '_sale_price_dates_to');

			$column_model = &$dashboard_model['columns'];

			$dashboard_model['tables']['posts']['where']['post_type'] = array('product');

			if( !isset($dashboard_model_saved['treegrid']) ) {
				$dashboard_model['treegrid'] = 'true'; //for setting the treegrid
			} else {
				$dashboard_model['treegrid'] = $dashboard_model_saved['treegrid'];
			}

			if( $dashboard_model['treegrid'] == 'true' ) {
				$dashboard_model['tables']['posts']['where']['post_type'] = array('product', 'product_variation');
			}

			$product_visibility_index = sm_multidimesional_array_search('terms/product_visibility', 'src', $column_model);
			$product_shop_url_index = sm_multidimesional_array_search('custom/product_shop_url', 'src', $column_model);

			if( !empty($product_visibility_index) ) {
				$visibility_index = sm_multidimesional_array_search ('postmeta/meta_key=_visibility/meta_value=_visibility', 'src', $column_model);
				$featured_index = sm_multidimesional_array_search ('postmeta/meta_key=_featured/meta_value=_featured', 'src', $column_model);
					
				if( !empty($visibility_index) && isset($column_model[$visibility_index]) ) {
					unset($column_model[$visibility_index]);
					$column_model = array_values($column_model);
				}

				if( !empty($featured_index) && isset($column_model[$featured_index]) ) {
					unset($column_model[$featured_index]);
					$column_model = array_values($column_model);
				}
			}			

			$attr_col_index = sm_multidimesional_array_search ('custom/product_attributes', 'src', $column_model);

			$attributes_val = array();
			$attributes_label = array();
			$attributes_search_val = array();
			
			// Load from cache

			if (empty($attr_col_index) || ( !empty($attr_col_index) && empty($column_model [$attr_col_index]['values']) ) ) {
				//Query to get the attribute name
				$query_attribute_label = "SELECT attribute_name, attribute_label, attribute_type
		                                FROM {$wpdb->prefix}woocommerce_attribute_taxonomies";
		        $results_attribute_label = $wpdb->get_results( $query_attribute_label, 'ARRAY_A' );
		        $attribute_label_count = $wpdb->num_rows;

		        if($attribute_label_count > 0) {
			        foreach ($results_attribute_label as $results_attribute_label1) {
			            $attributes_label['pa_' . $results_attribute_label1['attribute_name']]['lbl'] = $results_attribute_label1['attribute_label'];
			            $attributes_label['pa_' . $results_attribute_label1['attribute_name']]['type'] = $results_attribute_label1['attribute_type'];
			        }	
		        }
			} else {
				$column_model [$attr_col_index]['batch_editable']= true;
			}

			//Get Product Visibility options
			$product_visibility_options = array();
			if( function_exists('wc_get_product_visibility_options') ){
				$product_visibility_options = wc_get_product_visibility_options();	
			} else { //default values for product_visibility
				$product_visibility_options = array('visible' => __('Shop and search results', 'smart-manager-for-wp-e-commerce'),
												   'catalog' => __('Shop only', 'smart-manager-for-wp-e-commerce'),
												   'search' => __('Search results only', 'smart-manager-for-wp-e-commerce'),
												   'hidden' => __('Hidden', 'smart-manager-for-wp-e-commerce'));
			}

			foreach ($column_model as $key => &$column) {
				if (empty($column['src'])) continue;

				$src_exploded = explode("/",$column['src']);

				if (empty($src_exploded)) {
					$src = $column['src'];
				}

				if ( sizeof($src_exploded) > 2) {
					$col_table = $src_exploded[0];
					$cond = explode("=",$src_exploded[1]);

					if (sizeof($cond) == 2) {
						$src = $cond[1];
					}
				} else {
					$src = $src_exploded[1];
					$col_table = $src_exploded[0];
				}

				if( empty($dashboard_model_saved) ) {

					//Code for unsetting the position for hidden columns
					if (!empty($column['position'])) {
						unset($column['position']);
					}

					$position = array_search($src, $visible_columns);

					if ($position !== false) {
						$column['position'] = $position + 1;
						$column['hidden'] = false;
					} else {
						$column['hidden'] = true;
					}
				}

				if (!empty($src)) {
					// if (substr($src,0,13)=='attribute_pa_' || (substr($src,0,3)=='pa_' && $col_table == 'terms') ) {
					if ((substr($src,0,3)=='pa_' && $col_table == 'terms') ) {

						$attr_name = substr($src,3);
						// $attr_name_src = 'attribute_pa_'.$attr_name;
						$attr_name_src = 'pa_'.$attr_name;

						if( substr($src,0,3)=='pa_' && $col_table == 'terms' && !empty( $attributes_val[$attr_name_src] ) ) {
							$attributes_val [$attr_name_src]['val'] = $column['values'];
						} else {
							$attributes_val [$src] = array();
							$attributes_val [$src]['lbl'] = (!empty($attributes_label[$src]['lbl'])) ? $attributes_label[$src]['lbl'] : $src;
							$attributes_val [$src]['val'] = $column['values'];
							$attributes_val [$src]['type'] = (!empty($attributes_label[$src]['type'])) ? $attributes_label[$src]['type'] : $src;
							unset($column_model[$key]);
							$column_model = array_values($column_model);
						}

						//code for search columns
						$attributes_search_val[$attr_name_src] = $column['search_values'];

						$column['type'] = 'sm.multilist';

					} else if( empty($dashboard_model_saved) ) {
						if ($src == 'product_cat') {
							$column['type'] = 'sm.multilist';
							$column['editable']	= false;
							$column['name']	= 'Category';
						} else if( $src == 'product_type' ) {
							$column['type'] = 'dropdown';
						} else if ( in_array($src, $numeric_columns) ) {
							$column['type'] = 'numeric';
							$column['editor'] = $column['type'];
						} else if ( in_array($src, $datetime_columns) ) {
							$column['type'] = 'sm.datetime';
							$column['editor'] = $column['type'];
						} else if ($src == '_visibility') {
							$column['type'] = 'dropdown';

							//get the custom product_visibility using woo function
							$column ['values'] = $product_visibility_options;
							

							$column ['search_values'] = array();

							if( !empty( $column ['values'] ) ) {
								foreach( $column ['values'] as $key => $value ) {
									$column['search_values'][] = array( 'key' => $key, 'value' => $value );
								}
							}

						} else if ($src == '_tax_status') {

							$column['type'] = 'dropdown';

							$column ['values'] = array('taxable' => __('Taxable', 'smart-manager-for-wp-e-commerce'),
													   'shipping' => __('Shipping only', 'smart-manager-for-wp-e-commerce'),
													   'none' => __('None', 'smart-manager-for-wp-e-commerce'));

							$column ['search_values'] = array();

							$column['search_values'][0] = array('key' => 'taxable', 'value' =>  __('Taxable','smart-manager-for-wp-e-commerce'));
							$column['search_values'][1] = array('key' => 'shipping', 'value' =>  __('Shipping only','smart-manager-for-wp-e-commerce'));
							$column['search_values'][2] = array('key' => 'none', 'value' =>  __('None','smart-manager-for-wp-e-commerce'));

						} else if ($src == '_stock_status') {

							$column['type'] = 'dropdown';

							//get the custom _stock_status using woo function
							if( function_exists('wc_get_product_stock_status_options') ){
								$column ['values'] = wc_get_product_stock_status_options();	
							} else { //default values for _stock_status
								$column ['values'] = array('instock' => __('In stock', 'smart-manager-for-wp-e-commerce'),
													   'outofstock' => __('Out of stock', 'smart-manager-for-wp-e-commerce'),
													   'onbackorder' => __('On backorder', 'smart-manager-for-wp-e-commerce'));
							}

							$column ['search_values'] = array();

							if( !empty( $column ['values'] ) ) {
								foreach( $column ['values'] as $key => $value ) {
									$column['search_values'][] = array( 'key' => $key, 'value' => $value );
								}
							}

							$color_codes = array( 'green' => array( 'instock' ),
													'red' => array( 'outofstock' ),
													'blue' => array( 'onbackorder' ) );

							$column['colorCodes'] = apply_filters( 'sm_'.$this->dashboard_key.''.$src.'_color_codes', $color_codes );

						} else if ($src == '_tax_class') {

							$column['type'] = 'dropdown';

							//get the custom tax status using woo function
							if( function_exists('wc_get_product_tax_class_options') ){
								$column ['values'] = wc_get_product_tax_class_options();	
							} else { //default values for tax_status
								$column ['values'] = array('' => __('Standard', 'smart-manager-for-wp-e-commerce'),
													   'reduced-rate' => __('Reduced Rate', 'smart-manager-for-wp-e-commerce'),
													   'zero-rate' => __('Zero Rate', 'smart-manager-for-wp-e-commerce'));	
							}

							$column ['search_values'] = array();

							if( !empty( $column ['values'] ) ) {
								foreach( $column ['values'] as $key => $value ) {
									$column['search_values'][] = array( 'key' => $key, 'value' => $value );
								}
							}

						} else if ($src == '_backorders') {

							$column['type'] = 'dropdown';

							//get the custom _backorders using woo function
							if( function_exists('wc_get_product_backorder_options') ){
								$column ['values'] = wc_get_product_backorder_options();	
							} else { //default values for _backorders
								$column ['values'] = array('no' => __('Do Not Allow', 'smart-manager-for-wp-e-commerce'),
													   'notify' => __('Allow, but notify customer', 'smart-manager-for-wp-e-commerce'),
													   'yes' => __('Allow', 'smart-manager-for-wp-e-commerce'));
							}

							$column ['search_values'] = array();

							if( !empty( $column ['values'] ) ) {
								foreach( $column ['values'] as $key => $value ) {
									$column['search_values'][] = array( 'key' => $key, 'value' => $value );
								}
							}

							$color_codes = array( 'green' => array( 'yes', 'notify' ),
													'red' => array( 'no' ),
													'blue' => array() );

							$column['colorCodes'] = apply_filters( 'sm_'.$this->dashboard_key.''.$src.'_color_codes', $color_codes );

						} else if ($src == 'product_shipping_class') {

							if( empty($column ['values']) ) {
								$column ['values'] = array();
							}

							if( empty($column ['search_values']) ) {
								$column ['search_values'] = array();
							}

							$column ['values'] = array_replace( array('' => __('No shipping class', 'smart-manager-for-wp-e-commerce') ), $column ['values'] );

							$column['search_values'] = array_unshift($column ['values'], array('key' => '', 'value' =>  __('No shipping class','smart-manager-for-wp-e-commerce')));
						}  else if ($src == '_sku') {
							$column ['name'] = $column ['key'] = __('SKU', 'smart-manager-for-wp-e-commerce');
						} else if ($src == 'post_title') {
							$column ['name'] = $column ['key'] = __('Name', 'smart-manager-for-wp-e-commerce');
						} else if ($src == 'post_name') {
							$column ['name'] = $column ['key'] = __('Slug', 'smart-manager-for-wp-e-commerce');
						} else if ($src == 'post_content') {
							$column ['name'] = $column ['key'] = __('Description', 'smart-manager-for-wp-e-commerce');
						} else if ($src == 'post_excerpt') {
							$column ['name'] = $column ['key'] = __('Additional Description', 'smart-manager-for-wp-e-commerce');
						} else if ( substr($src, 0, 12) == 'attribute_pa' || substr($src, 0, 10) == 'attribute_' ) {
							$column ['searchable']= false;
							$column ['batch_editable']= false;
						} else if ($src == '_default_attributes') {
							$column ['searchable']= false;
							$column ['batch_editable']= false;
						} else if ($src == '_product_attributes') {
							$column ['searchable']= false;
							$column ['batch_editable']= false;
							$column ['hidden']= true;
							$column ['allow_showhide']= false;
							$column ['exportable']= false;
						} else if ($src == '_product_url') {
							$column ['name'] = $column ['key'] = __('External Url', 'smart-manager-for-wp-e-commerce');
						} else if ($src == '_thumbnail_id') {
							unset($column ['min']);
							unset($column ['edittype']);
							$column ['key']= __('Image', 'smart-manager-for-wp-e-commerce');
							$column ['width']= 25;
							$column ['align']= 'center';
							$column ['type']= 'sm.image';
							$column ['searchable']= false;
							$column ['editable']= false;
							$column ['batch_editable']= true;
							$column ['sortable']= false;
							$column ['resizable']= true;
							$column ['name_display']= __('Featured Image', 'smart-manager-for-wp-e-commerce');
						}

						if( $column['type'] == 'dropdown' ) {
							$column ['strict'] = true;
							$column ['allowInvalid'] = false;	
							$column ['selectOptions'] = $column['values'];
							$column ['editor'] = 'select';
							$column ['renderer'] = 'selectValueRenderer';
						}

						
					}
				}
			}

			if (empty($attr_col_index)) {
				$index = sizeof($column_model);

				//Code for including custom columns for product dashboard
				$column_model [$index] = array();
				$column_model [$index]['src'] = 'custom/product_attributes';
				$column_model [$index]['data'] = sanitize_title(str_replace('/', '_', $column_model [$index]['src'])); // generate slug using the wordpress function if not given 
				$column_model [$index]['name'] = __(ucwords(str_replace('_', ' ', 'attributes')), 'smart-manager-for-wp-e-commerce');
				$column_model [$index]['key'] = $column_model [$index]['name'];
				// $column_model [$index]['type'] = 'serialized';
				$column_model [$index]['type'] = 'sm.longstring';
				
				// $column_model [$index]['hidden']	= true;
				$column_model [$index]['editable']	= false;
				$column_model [$index]['searchable']= false;

				$column_model [$index]['batch_editable']= true;

				$column_model [$index]['width'] = 100;
				$column_model [$index]['save_state'] = true;

				$column_model [$index]['wordWrap'] = false; //For disabling word-wrap

				if( empty($dashboard_model_saved) ) {
					$position = array_search('product_attributes', $visible_columns);

					if ($position !== false ) {
						$column_model [$index]['position'] = $position + 1;
						$column_model [$index]['hidden'] = false;
					} else {
						$column_model [$index]['hidden'] = true;
					}
				}

				$column_model [$index]['allow_showhide'] = true;
				$column_model [$index]['exportable']	 = true;

				//Code for assigning attr. values
				$column_model [$index]['values'] = $attributes_val;
			} else if ( !empty($attr_col_index) && empty($column_model [$attr_col_index]['values']) ) {
				$column_model [$attr_col_index]['values'] = $attributes_val; //Code for assigning attr. values
			}

			//code for creating search columns for attributes
			if(!empty($attributes_search_val)) {

				foreach ($attributes_search_val as $key => $value) {

					++$index;

					//Code for including custom columns for product dashboard
					$column_model [$index] = array();

					$column_model [$index]['src'] = 'terms/attribute_'.$key;
					$column_model [$index]['data'] = sanitize_title(str_replace('/', '_', $column_model [$index]['src'])); // generate slug using the wordpress function if not given 
					$column_model [$index]['name'] = __('Attributes', 'smart-manager-for-wp-e-commerce') .': '. substr($key,3);
					$column_model [$index]['key'] = $column_model [$index]['name'];
					$column_model [$index]['type'] = 'text';
					$column_model [$index]['hidden']	= true;
					$column_model [$index]['editable']	= false;
					$column_model [$index]['batch_editable']	= false;
					$column_model [$index]['sortable']	= false;
					$column_model [$index]['resizable']	= false;
					$column_model [$index]['allow_showhide'] = false;
					$column_model [$index]['exportable']	= false;
					$column_model [$index]['searchable']	= true;

					$column_model [$index]['wordWrap'] = false; //For disabling word-wrap

					$column_model [$index]['table_name'] = $wpdb->prefix.'terms';
					$column_model [$index]['col_name'] = 'attribute_'.$key;

					$column_model [$index]['width'] = 0;
					$column_model [$index]['save_state'] = true;

					//Code for assigning attr. values
					$column_model [$index]['values'] = array();

					$column_model [$index]['search_values'] = $value;
				}

				++$index;

				//Code for including custom attribute column for product dashboard
				$column_model [$index] = array();

				$column_model [$index]['src'] = 'postmeta/meta_key=_product_attributes/meta_value=_product_attributes';
				$column_model [$index]['data'] = sanitize_title(str_replace('/', '_', $column_model [$index]['src'])); // generate slug using the wordpress function if not given 
				$column_model [$index]['name'] = __('Attributes: custom', 'smart-manager-for-wp-e-commerce');
				$column_model [$index]['key'] = $column_model [$index]['name'];
				$column_model [$index]['type'] = 'text';
				$column_model [$index]['hidden']	= true;
				$column_model [$index]['editable']	= false;
				$column_model [$index]['batch_editable']	= false;
				$column_model [$index]['sortable']	= false;
				$column_model [$index]['resizable']	= false;
				$column_model [$index]['allow_showhide'] = false;
				$column_model [$index]['exportable']	= false;
				$column_model [$index]['searchable']	= true;

				$column_model [$index]['wordWrap'] = false; //For disabling word-wrap

				$column_model [$index]['table_name'] = $wpdb->prefix.'postmeta';
				$column_model [$index]['col_name'] = '_product_attributes';

				$column_model [$index]['width'] = 0;
				$column_model [$index]['save_state'] = true;

				//Code for assigning attr. values
				$column_model [$index]['values'] = array();
				$column_model [$index]['search_values'] = array();

			}

			if( !empty($product_visibility_index) && empty($dashboard_model_saved) ) {

				$product_visibility_index = sm_multidimesional_array_search('terms/product_visibility', 'src', $column_model);
				if( isset( $column_model[$product_visibility_index] ) ) {
					unset( $column_model[$product_visibility_index] );
					$column_model = array_values($column_model); //added for recalculating the indexes of the array
					$product_visibility_index = sm_multidimesional_array_search ('terms/product_visibility', 'src', $column_model);
				}

				$index = sizeof($column_model);

				$index++;

				if( empty( $product_visibility_index ) ) {

					//Code for including custom columns for product dashboard
					$column_model [$index] = array();

					$column_model [$index]['src'] = 'terms/product_visibility';
					$column_model [$index]['data'] = sanitize_title(str_replace('/', '_', $column_model [$index]['src'])); // generate slug using the wordpress function if not given 
					$column_model [$index]['name'] = __('Catalog Visibility', 'smart-manager-for-wp-e-commerce');
					$column_model [$index]['key'] = $column_model [$index]['name'];
					$column_model [$index]['type'] = 'dropdown';
					$column_model [$index]['hidden']	= true;
					$column_model [$index]['editable']	= true;
					$column_model [$index]['batch_editable']	= true;
					$column_model [$index]['sortable']	= true;
					$column_model [$index]['resizable']	= true;
					$column_model [$index]['allow_showhide']	= true;
					$column_model [$index]['exportable']	= true;
					$column_model [$index]['searchable']	= true;

					$column_model [$index]['wordWrap'] = false; //For disabling word-wrap

					$column_model [$index]['table_name'] = $wpdb->prefix.'terms';
					$column_model [$index]['col_name'] = 'product_visibility';

					$column_model [$index]['width'] = 100;
					$column_model [$index]['save_state'] = true;

					//Code for assigning attr. values
					$column_model [$index]['values'] = $product_visibility_options;

					$column_model [$index]['search_values'] = array();

					if( !empty( $column_model [$index]['values'] ) ) {
						foreach( $column_model [$index]['values'] as $key => $value ) {
							$column_model [$index]['search_values'][] = array( 'key' => $key, 'value' => $value );
						}
					}

					$column_model [$index] ['strict'] = true;
					$column_model [$index] ['allowInvalid'] = false;
					$column_model [$index] ['selectOptions'] = $column_model [$index]['values'];
					$column_model [$index] ['editor'] = 'select';
					$column_model [$index] ['renderer'] = 'selectValueRenderer';
				}

				$featured_index = sm_multidimesional_array_search ('terms/product_visibility_featured', 'src', $column_model);

				if( empty($featured_index) ) {

					++$index;

					$column_model [$index] = array();
					$column_model [$index]['src'] = 'terms/product_visibility_featured';
					$column_model [$index]['data'] = sanitize_title(str_replace('/', '_', $column_model [$index]['src'])); // generate slug using the wordpress function if not given 
					$column_model [$index]['name'] = __('Featured', 'smart-manager-for-wp-e-commerce');
					$column_model [$index]['key'] = $column_model [$index]['name'];
					$column_model [$index]['type'] = 'checkbox';
					$column_model [$index]['hidden']	= true;
					$column_model [$index]['editable']	= true;

					$column_model [$index]['wordWrap'] = false; //For disabling word-wrap

					$column_model [$index]['width'] = 100;
					$column_model [$index]['save_state'] = true;

					$column_model [$index]['batch_editable']	= true;
					$column_model [$index]['sortable']	= true;
					$column_model [$index]['resizable']	= true;
					$column_model [$index]['allow_showhide']	= true;
					$column_model [$index]['exportable']	= true;
					$column_model [$index]['searchable']	= true;

					$column_model [$index]['table_name'] = $wpdb->prefix.'terms';
					$column_model [$index]['col_name'] = 'product_visibility_featured';

					//Code for assigning attr. values
					$column_model [$index]['values'] = array();
					$column_model [$index]['search_values'] = array();
				}
				
			}

			// if( empty($product_shop_url_index) && empty($dashboard_model_saved) ) { // for product shop url
			// 	$index = sizeof($column_model);

			// 	//Code for including custom columns for product dashboard
			// 	$column_model [$index] = array();
			// 	$column_model [$index]['src'] = 'custom/product_shop_url';
			// 	$column_model [$index]['data'] = sanitize_title(str_replace('/', '_', $column_model [$index]['src'])); // generate slug using the wordpress function if not given 
			// 	$column_model [$index]['name'] = __(ucwords(str_replace('_', ' ', 'shop_url')), 'smart-manager-for-wp-e-commerce');
			// 	$column_model [$index]['key'] = $column_model [$index]['name'];
			// 	$column_model [$index]['hidden']	= true;
			// 	$column_model [$index]['editable']	= false;
			// 	$column_model [$index]['batch_editable']	= false;
			// 	$column_model [$index]['sortable']	= false;
			// 	$column_model [$index]['resizable']	= false;
			// 	$column_model [$index]['allow_showhide'] = true;
			// 	$column_model [$index]['exportable']	= true;
			// 	$column_model [$index]['searchable']	= false;

			// 	$column_model [$index]['wordWrap'] = false; //For disabling word-wrap

			// 	$column_model [$index]['table_name'] = 'custom';
			// 	$column_model [$index]['col_name'] = 'product_shop_url';

			// 	$column_model [$index]['width'] = 100;
			// 	$column_model [$index]['save_state'] = true;

			// 	//Code for assigning attr. values
			// 	$column_model [$index]['values'] = array();
			// 	$column_model [$index]['search_values'] = array();
			// }

			if (!empty($dashboard_model_saved)) {
				$col_model_diff = sm_array_recursive_diff($dashboard_model_saved,$dashboard_model);	
			}

			//clearing the transients before return
			if (!empty($col_model_diff)) {
				delete_transient( 'sm_beta_'.$this->dashboard_key );	
			}

			return $dashboard_model;
		}

		public function products_data_model ($data_model, $data_col_params) {

			global $wpdb, $current_user;

			//Code for loading the data for the attributes column

			if(empty($data_model) || empty($data_model['items'])) return;

			$current_store_model = get_transient( 'sm_beta_'.$this->dashboard_key );
			$col_model = (!empty($current_store_model['columns'])) ? $current_store_model['columns'] : array();

			if (!empty($col_model)) {

				//Code to get attr values by slug name
				$attr_val_by_slug = array();
				$attr_taxonomy_nm = get_object_taxonomies($this->post_type);

				if ( !empty($attr_taxonomy_nm) ) {
					foreach ( $attr_taxonomy_nm as $key => $attr_taxonomy ) {
						if ( substr($attr_taxonomy,0,13) != 'attribute_pa_' ) {
							unset( $attr_taxonomy_nm[$key] );
						}
					}

					$attr_terms = get_terms($attr_taxonomy_nm, array('hide_empty'=> 0,'orderby'=> 'id'));

					if ( !empty($attr_terms) ){
						foreach ( $attr_terms as $attr_term ) {
							if (empty($attr_val_by_slug[$attr_term->taxonomy])) {
								$attr_val_by_slug[$attr_term->taxonomy] = array();
							}
							$attr_val_by_slug[$attr_term->taxonomy][$attr_term->slug] = $attr_term->name;
						}
					}	
				}

				$taxonomy_nm = array();
				$term_taxonomy_ids = array();
				$post_ids = array();
				$parent_ids = array();
				$product_attributes_postmeta = array();
				$post_parent_hidden = 0;

				foreach ($col_model as $column) {
					if (empty($column['src'])) continue;

					$src_exploded = explode("/",$column['src']);

					if (!empty($src_exploded) && $src_exploded[1] == 'product_attributes') {
						$attr_values = $column['values'];

						if (!empty($attr_values)) {
							foreach ($attr_values as $key => $attr_value) {
								$taxonomy_nm[] = $key;
								$term_taxonomy_ids = $term_taxonomy_ids + $attr_value;
							}
						}
					} if( !empty($src_exploded) && $src_exploded[1] == 'post_parent' && !empty( $column['hidden'] ) ) {
						$post_parent_hidden = 1;
					}
				}

				// Code for fetching the parent ids incase the post_parent is hidden
				if( $post_parent_hidden == 1 ) {

					$ids = array();
					$post_parents = array();

					foreach( $data_model['items'] as $key => $data ) {
						if (empty($data['posts_id'])) continue;
						$ids[] = $data['posts_id'];
					}

					if( !empty($ids) ) {
						$results = $wpdb->get_results($wpdb->prepare("SELECT ID, post_parent FROM {$wpdb->prefix}posts WHERE 1=%d AND post_type IN ('product', 'product_variation') AND id IN (". implode(",",$ids) .")", 1), 'ARRAY_A');

						if( !empty( $results ) > 0 ) {
							foreach( $results as $result ) {
								$post_parents[ $result['ID'] ] = $result['post_parent'];
							}
						}
					}
				}

				$product_visibility_index = sm_multidimesional_array_search('terms/product_visibility', 'src', $col_model);
				$product_featured_index = sm_multidimesional_array_search('terms/product_visibility_featured', 'src', $col_model);
				$product_shop_url_index = sm_multidimesional_array_search('custom/product_shop_url', 'src', $col_model);

				$variation_ids = array();
				$key_post_ids = array();

				foreach ($data_model['items'] as $key => $data) {

					if (empty($data['posts_id'])) continue;
					$post_ids[] = $data['posts_id'];

					if ( empty( $data['posts_post_parent'] ) ) {
						continue;
					}
					$variation_ids[] = $data['posts_id'];
					$key_post_ids[$data['posts_id']] = $key;
				}

				if( !empty( $variation_ids ) ) { //Code for fetching variation attributes for variation title
					$variation_attribute_results = $wpdb->get_results( $wpdb->prepare("SELECT post_id,
																			meta_key,
																			meta_value
																	FROM {$wpdb->prefix}postmeta
																	WHERE post_id IN (". implode(",", $variation_ids) .")
																		AND meta_key LIKE 'attribute_%'
																		AND 1=%d
																	GROUP BY post_id, meta_key", 1), 'ARRAY_A' );

					if( !empty( $variation_attribute_results ) ) {
						foreach( $variation_attribute_results as $result ) {

							$key = ( isset( $key_post_ids[$result['post_id']] ) ) ? $key_post_ids[$result['post_id']] : '';

							if( empty( $key ) && $key != 0 ) {
								continue;
							}

							$meta_key = 'postmeta_meta_key_'.$result['meta_key'].'_meta_value_'.$result['meta_key'];
							$data_model['items'][$key][$meta_key] = $result['meta_value'];
						}
					}
				}


				foreach ($data_model['items'] as $key => $data) {

					if (empty($data['posts_id'])) continue;
					$post_ids[] = $data['posts_id'];

					$data_model['items'][$key]['loaded'] = true;
					$data_model['items'][$key]['expanded'] = true;

					if( empty($data['posts_post_parent']) && !empty($post_parents[$data['posts_id']]) ) {
						$data['posts_post_parent'] = $post_parents[$data['posts_id']];
					}

					if ( !empty($data['posts_post_parent']) ) {

						$parent_key = sm_multidimesional_array_search($data['posts_post_parent'], 'posts_id', $data_model['items']);
						$parent_title  = '';

						// Code for the variation title on sorting
						// if ( $this->prod_sort === true ) {
							$parent_title = (!empty($data_model['items'][$parent_key]['posts_post_title'])) ? $data_model['items'][$parent_key]['posts_post_title'] : get_the_title($data['posts_post_parent']);
							$parent_title .= ( !empty($parent_title) ) ? ' - ' : '';
						// }
						
						$data_model['items'][$key]['parent'] = $data['posts_post_parent'];
						$data_model['items'][$key]['isLeaf'] = true;
						$data_model['items'][$key]['level'] = 1;
						$data_model['items'][$key]['terms_product_type'] = 'Variation';

						//Code for modifying the variation name
						$variation_title = '';

						foreach ($data as $key1 => $value) {
							$start_pos = strrpos($key1, '_meta_value_attribute_');

							if ( $start_pos !== false ){
								
								$attr_nm = substr($key1, $start_pos+22);

								$data_model['items'][$key][$key1] = (empty($data_model['items'][$key][$key1])) ? 'any' : $data_model['items'][$key][$key1];

								if ( !empty($attr_values[$attr_nm]) ) {

									$attr_lbl = (!empty($attr_values[$attr_nm]['lbl'])) ? $attr_values[$attr_nm]['lbl'] : $attr_nm;
									$attr_val = ( !empty($attr_val_by_slug[$attr_nm][$data_model['items'][$key][$key1]]) ) ? $attr_val_by_slug[$attr_nm][$data_model['items'][$key][$key1]] : $data_model['items'][$key][$key1];
									$variation_title .= $attr_lbl . ': ' . $attr_val;

								} else {
									$variation_title .= $attr_nm . ': ' . $data_model['items'][$key][$key1];
								}
								$variation_title .= ' | ';
							}	
						}

						$variation_title = ( !empty( $data['posts_post_title'] ) && empty( $variation_title ) ) ? $data['posts_post_title'] : ( $parent_title .''. substr( $variation_title, 0, strlen( $variation_title )-2 ) );

						if( !empty($variation_title) && $this->prod_sort === false ){
							$data_model['items'][$key]['posts_post_title'] = '<div style="margin-left: 2px;color: #469BDD;float: left;" class="dashicons dashicons-minus"></div>'.' <div>'.$variation_title.'</div>';	
						}
						

					} else if ( !empty($data['terms_product_type']) ) {
						if ( $data['terms_product_type'] == 'simple' ) {
							$data_model['items'][$key]['icon_show'] = false;
						} 
						$data_model['items'][$key]['parent'] = 'null';
						$data_model['items'][$key]['isLeaf'] = false;
						$data_model['items'][$key]['level'] = 0;							
					}

					if ( $this->prod_sort === true ) {
						$data_model['items'][$key]['icon_show'] = false;
						$data_model['items'][$key]['parent'] = 'null';
						$data_model['items'][$key]['isLeaf'] = false;
						$data_model['items'][$key]['level'] = 0;	
					}

					if ( empty($data['posts_post_parent']) ) {
						$parent_ids[] = $data['posts_id'];
					}

					if ( ! empty( $data['postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id'] ) ) {
						$thumbnail_id = $data['postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id'];
						$attachment = wp_get_attachment_image_src($thumbnail_id, 'full');
						if ( is_array( $attachment ) && ! empty( $attachment[0] ) ) {
							$thumbnail = $attachment[0];
						} else {
							$thumbnail = '';
						}
						$data_model['items'][$key]['postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id'] = $thumbnail;
					} else {
						$data_model['items'][$key]['postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id'] = '';

						// $data_model['items'][$key]['postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id'] = '<div title="' . __( 'Set', 'smart-manager-for-wp-e-commerce' ) . '" width="20" height="20">&nbsp;</div>';
					}

					if( !empty($product_shop_url_index) ) { //for product url
						$data_model['items'][$key]['custom_product_shop_url'] = get_permalink($data['posts_id']);
					}

					if ( empty( $data['postmeta_meta_key__product_addons_exclude_global_meta_value__product_addons_exclude_global'] ) ) {
						$data_model['items'][$key]['postmeta_meta_key__product_addons_exclude_global_meta_value__product_addons_exclude_global'] = 0;
					}

					if ( empty( $data['postmeta_meta_key__wc_mmax_prd_opt_enable_meta_value__wc_mmax_prd_opt_enable'] ) ) {
						$data_model['items'][$key]['postmeta_meta_key__wc_mmax_prd_opt_enable_meta_value__wc_mmax_prd_opt_enable'] = 0;
					}

					if (empty($data['postmeta_meta_key__product_attributes_meta_value__product_attributes'])) continue;
					$product_attributes_postmeta[$data['posts_id']] = json_decode( $data['postmeta_meta_key__product_attributes_meta_value__product_attributes'], true );

				}

				$data_model['items'] = array_values($data_model['items']);

				if( !empty($parent_ids) && ( $product_visibility_index != '' || $product_featured_index != '' ) ) {
					$terms_objects = wp_get_object_terms( $parent_ids, 'product_visibility', 'orderby=none&fields=all_with_object_id' );

					$product_visibility = array();

					if (!empty($terms_objects)) {
						foreach ($terms_objects as $terms_object) {

							$post_id = $terms_object->object_id;
							$slug = $terms_object->slug;

							if (!isset($product_visibility[$post_id])){
								$product_visibility[$post_id] = array();
							}

							if (!isset($product_visibility[$post_id][$slug])){
								$product_visibility[$post_id][$slug] = '';
							}

						}
					}

					foreach ($data_model['items'] as $key => $data) {
						if ( empty($data['posts_id']) || !empty($data['posts_post_parent']) ) continue;

						$visibility = 'visible';
						$featured = 'no';

						if( isset($product_visibility[$data['posts_id']]['exclude-from-search']) && isset($product_visibility[$data['posts_id']]['exclude-from-catalog']) ) {
							$visibility = 'hidden';
						} else if( isset($product_visibility[$data['posts_id']]['exclude-from-search']) ) {
							$visibility = 'catalog';
						} else if( isset($product_visibility[$data['posts_id']]['exclude-from-catalog']) ) {
							$visibility = 'search';
						}

						if( isset($product_visibility[$data['posts_id']]['featured']) ) {
							$featured = 'yes';	
						}

						$data_model['items'][$key]['terms_product_visibility'] = $visibility;
						$data_model['items'][$key]['terms_product_visibility_featured'] = $featured;
					}

				}

				$terms_objects = wp_get_object_terms( $post_ids, $taxonomy_nm, 'orderby=none&fields=all_with_object_id' );
				$attributes_val = array();
				$temp_attribute_nm = "";

				if (!empty($terms_objects)) {
					foreach ($terms_objects as $terms_object) {

						$post_id = $terms_object->object_id;
						$taxonomy = $terms_object->taxonomy;
						$term_id = $terms_object->term_id;

						if (!isset($attributes_val[$post_id])){
							$attributes_val[$post_id] = array();
						}

						if (!isset($attributes_val[$post_id][$taxonomy])){
							$attributes_val[$post_id][$taxonomy] = array();
						}

			            $attributes_val[$post_id][$taxonomy][$term_id] = $terms_object->name;
					}
				}
				
				//Query to get the attribute name
				$query_attribute_label = "SELECT attribute_name, attribute_label
		                                FROM {$wpdb->prefix}woocommerce_attribute_taxonomies";
		        $results_attribute_label = $wpdb->get_results( $query_attribute_label, 'ARRAY_A' );
		        $attribute_label_count = $wpdb->num_rows;

		        $attributes_label = array();

		        if($attribute_label_count > 0) {
			        foreach ($results_attribute_label as $results_attribute_label1) {
			            $attributes_label['pa_' . $results_attribute_label1['attribute_name']] = array();
			            $attributes_label['pa_' . $results_attribute_label1['attribute_name']] = $results_attribute_label1['attribute_label'];
			        }	
		        }
		        
				// $query_attributes = $wpdb->prepare("SELECT post_id as id,
				// 											meta_value as product_attributes
				// 										FROM {$wpdb->prefix}postmeta
				// 										WHERE meta_key = '%s'
				// 											AND meta_value <> '%s'
				// 											AND post_id IN (".implode(',', array_filter($post_ids,'is_int')).")
				// 										GROUP BY id",'_product_attributes','a:0:{}');

				// $product_attributes = $wpdb->get_results($query_attributes, 'ARRAY_A');
				// $product_attributes_count = $wpdb->num_rows;

				if (!empty($product_attributes_postmeta)) {


					foreach ($product_attributes_postmeta as $post_id => $prod_attr) {

						if (empty($prod_attr)) continue;

                    	// $prod_attr = json_decode($product_attribute,true);
                    	$update_index = sm_multidimesional_array_search ($post_id, 'posts_id', $data_model['items']);
                    	$attributes_list = "";

	                    //cond added for handling blank data
	                    if (is_array($prod_attr) && !empty($prod_attr)) {

	                    	$attributes_list = "";

	                    	foreach ($prod_attr as &$prod_attr1) {

	                    		if( !empty($attributes_list) ) {
	                    			$attributes_list .= ", <br>";
	                    		}

	                    		if ($prod_attr1['is_taxonomy'] == 0) {
	                    			$attributes_list .= $prod_attr1['name'] . ": [" . trim($prod_attr1['value']) ."]";
		                    	} else {
		                    		$attributes_val_current = (!empty($attributes_val[$post_id][$prod_attr1['name']])) ? $attributes_val[$post_id][$prod_attr1['name']] : array();
		                    		$attributes_list .= $attributes_label[$prod_attr1['name']] . ": [" . implode(" | ",$attributes_val_current) . "]";
                                    $prod_attr1['value'] = $attributes_val_current;
		                    	}
	                    	}

	                    	$data_model['items'][$update_index]['custom_product_attributes'] = $attributes_list;
	                    	$data_model['items'][$update_index]['postmeta_meta_key__product_attributes_meta_value__product_attributes'] = json_encode($prod_attr);
	                    }
					}
				}
			}
			return $data_model;
		}

		//function for modifying edited data before updating
		public function products_inline_update_pre($edited_data) {
			if (empty($edited_data)) return $edited_data;

			global $wpdb;

			$prod_title_ids = array();

			foreach ($edited_data as $key => $edited_row) {

				if( empty( $key ) ) {
					continue;
				}

				if( !empty( $edited_row['posts/post_title'] ) && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {
					if( strpos($key, 'sm_temp_') === false ) {
						$prod_title_ids[] = $key;
					}
				}

				if ( empty($edited_row['postmeta/meta_key=_product_attributes/meta_value=_product_attributes']) ) {
 					continue;
				}

				$product_attributes = json_decode($edited_row['postmeta/meta_key=_product_attributes/meta_value=_product_attributes'],true); 

				if (empty($product_attributes)) continue;

				foreach ($product_attributes as $attr => $attr_value) {
					if ($attr_value['is_taxonomy'] == 0) continue;
					$product_attributes[$attr]['value'] = '';
				}

				$product_attributes = sm_multidimensional_array_sort($product_attributes, 'position', SORT_ASC);
				
				$edited_data[$key]['postmeta/meta_key=_product_attributes/meta_value=_product_attributes'] = json_encode($product_attributes);
			}

			if( !empty( $prod_title_ids ) && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {

		        $results = sm_get_current_variation_title( $prod_title_ids );

                if( count( $results ) > 0 ) {
                    foreach( $results as $result ) {
                        $this->product_old_title[ $result['id'] ] = $result['post_title'];
                    }
                }
			}

			return $edited_data;
		}

		//function for updating product visibility
		public function set_product_visibility( $id, $visibility ) {

			if( empty( $id ) || empty( $visibility ) ) {
				return;
			}

			$visibility = strtoupper($visibility);

			if( $visibility == strtoupper('visible') ) {
                wp_remove_object_terms( $id, array('exclude-from-search', 'exclude-from-catalog'), 'product_visibility' );
            } else {

                $terms = '';

                if( $visibility == strtoupper('catalog') ) {
                    $terms = 'exclude-from-search';
                } else if( $visibility == strtoupper('search') ) {
                    $terms = 'exclude-from-catalog';
                } else if( $visibility == strtoupper('hidden') ) {
                    $terms = array('exclude-from-search', 'exclude-from-catalog');
                }

                if( !empty($terms) ) {
                    wp_remove_object_terms( $id, array('exclude-from-search', 'exclude-from-catalog'), 'product_visibility' );
                    wp_set_object_terms($id, $terms, 'product_visibility', true);
                }
            }
		}

		//function for inline update of custom fields
		public function products_inline_update($edited_data, $params) {

			global $current_user, $wpdb;

			if(empty($edited_data)) return;

			$attr_values = array();
			$current_store_model = get_transient( 'sm_beta_'.$this->dashboard_key );
			$col_model = (!empty($current_store_model['columns'])) ? $current_store_model['columns'] : array();

			$product_visibility_index = sm_multidimesional_array_search('terms_product_visibility', 'data', $col_model);
			$product_featured_index = sm_multidimesional_array_search('terms_product_visibility_featured', 'data', $col_model);

			if (!empty($col_model)) {

				foreach ($col_model as $column) {
					if (empty($column['src'])) continue;

					$src_exploded = explode("/",$column['src']);

					if (!empty($src_exploded) && $src_exploded[1] == 'product_attributes') {
						$col_values = $column['values'];

						if (!empty($col_values)) {
							foreach ($col_values as $key => $col_value) {
								$attr_values [$col_value['lbl']] = array();
								$attr_values [$col_value['lbl']] ['taxonomy_nm'] = $key;
								$attr_values [$col_value['lbl']] ['val'] = $col_value['val'];
								$attr_values [$col_value['lbl']] ['type'] = $col_value['type'];
							}
						}
					}
				}
			}

			if( empty($attr_values) && empty($product_visibility_index) && empty($product_featured_index) ) {
				return;
			}

			$price_update_ids = array();
			$post_title_update_ids = array();
			$new_title_update_case = array();
			$sm_update_lookup_table_ids = array();

			foreach( $edited_data as $pid => $edited_row ) {

				if( !empty( $edited_row['posts/post_title'] ) && ( !empty( Smart_Manager::$sm_is_woo30 ) && Smart_Manager::$sm_is_woo30 == 'true' ) ) {
					if( !empty( $this->product_old_title[ $pid ] ) && $this->product_old_title[ $pid ] != $edited_row['posts/post_title'] ) {
						$post_title_update_ids[] = $pid;
                        $new_title_update_case[] = 'WHEN post_parent='. $pid .' THEN REPLACE(post_title, \''. $this->product_old_title[ $pid ] .'\', \''. $edited_row['posts/post_title'] .'\')';
                    }
				}

				$id = (!empty($edited_row['posts/ID'])) ? $edited_row['posts/ID'] : $pid;

				if (empty($id)) continue;

				//Code to update the '_price' for the products
				if ( isset($edited_row['postmeta/meta_key=_regular_price/meta_value=_regular_price']) || isset($edited_row['postmeta/meta_key=_sale_price/meta_value=_sale_price']) ) {
					$price_update_ids[] = $id;
				}

				// WC 3.6+ compat
				if ( !empty( Smart_Manager::$sm_is_woo36 ) && Smart_Manager::$sm_is_woo36 == 'true' ) {
					if ( isset( $edited_row['postmeta/meta_key=_sku/meta_value=_sku'] ) || 
						 isset( $edited_row['postmeta/meta_key=_regular_price/meta_value=_regular_price'] ) || 
						 isset( $edited_row['postmeta/meta_key=_price/meta_value=_price'] ) || 
						 isset( $edited_row['postmeta/meta_key=_sale_price/meta_value=_sale_price'] ) || 
						 isset( $edited_row['postmeta/meta_key=_virtual/meta_value=_virtual'] ) || 
						 isset( $edited_row['postmeta/meta_key=_downloadable/meta_value=_downloadable'] ) || 
						 isset( $edited_row['postmeta/meta_key=_stock/meta_value=_stock'] ) || 
						 isset( $edited_row['postmeta/meta_key=_manage_stock/meta_value=_manage_stock'] ) || 
						 isset( $edited_row['postmeta/meta_key=_stock_status/meta_value=_stock_status'] ) || 
						 isset( $edited_row['postmeta/meta_key=_wc_rating_count/meta_value=_wc_rating_count'] ) || 
						 isset( $edited_row['postmeta/meta_key=_wc_average_rating/meta_value=_wc_average_rating'] ) || 
						 isset( $edited_row['postmeta/meta_key=total_sales/meta_value=total_sales'] ) 
						) {
						$sm_update_lookup_table_ids[] = $id;
					}
				}

				if ( isset( $edited_row['postmeta/meta_key=_stock/meta_value=_stock'] ) ) { //For handling product inventory updates
					sm_update_stock_status( $id, $edited_row['postmeta/meta_key=_stock/meta_value=_stock'] );
				}

				if( !empty($product_visibility_index) || !empty($product_featured_index) ) {
					//set the visibility taxonomy
					$visibility = (!empty($edited_row['terms/product_visibility'])) ? $edited_row['terms/product_visibility'] : '';

					if( !empty( $visibility ) ) {
						$this->set_product_visibility( $id, $visibility );
                    }

					//set the featured taxonomy
					$featured = (!empty($edited_row['terms/product_visibility_featured'])) ? $edited_row['terms/product_visibility_featured'] : '';
					
					if( !empty($featured) ) {
                        if( !empty($featured) ) {
                            $result = ( $featured == "Yes" ) ? wp_set_object_terms($id, 'featured', 'product_visibility', true) : wp_remove_object_terms( $id, 'featured', 'product_visibility' );
                        }
					}
				}

				$attr_edited = (!empty($edited_row['custom/product_attributes'])) ? $edited_row['custom/product_attributes'] : '';
				$attr_edited = array_filter(explode(', <br>',$attr_edited));


				if (empty($attr_edited)) continue;

				foreach ($attr_edited as $attr) {
					$attr_data = explode(': ',$attr);

					if (empty($attr_data)) continue;

					$taxonomy_nm = $attr_data[0];
					$attr_editd_val = (substr($attr_data[1], 0, 1) == '[') ? substr($attr_data[1], 1) : $attr_data[1];
					$attr_editd_val = (substr($attr_editd_val, -1) == ']') ? substr($attr_editd_val, 0, -1) : $attr_editd_val;

					if (!empty($attr_values[$taxonomy_nm])) {
						//Code for type=select attributes

						$attr_val = $attr_values[$taxonomy_nm]['val'];
						$attr_type = $attr_values[$taxonomy_nm]['type'];

						$taxonomy_nm = $attr_values[$taxonomy_nm]['taxonomy_nm'];
						$attr_editd_val = array_filter(explode(" | ",$attr_editd_val));
						
						if (empty($attr_editd_val)) continue;

						$term_ids = array();

						foreach ($attr_editd_val as $attr_editd) {

							$term_id = array_search($attr_editd, $attr_val);

							if ($term_id === false && $attr_type == 'text') {
								$new_term = wp_insert_term($attr_editd, $taxonomy_nm);

								if ( !is_wp_error( $new_term ) ) {
									$term_id = (!empty($new_term['term_id'])) ? $new_term['term_id'] : '';
								}
							}
							$term_ids [] = $term_id;
						}
						wp_set_object_terms($id, $term_ids, $taxonomy_nm);
					} 
				}
			}

			if( !empty( $price_update_ids ) ) {
				sm_update_price_meta($price_update_ids);
				//Code For updating the parent price of the product
				sm_variable_parent_sync_price($price_update_ids);
			}

			// Update the post title for variations if parent is updated
			if( !empty( $new_title_update_case ) && !empty( $post_title_update_ids ) ) {
				sm_sync_variation_title( $new_title_update_case, $post_title_update_ids );
            }

            /**
             * To update wc_product_meta_lookup for WC 3.6+
             * Since SM 4.2.3
             */
            if ( !empty( $sm_update_lookup_table_ids ) ) {
            	sm_update_product_lookup_table( $sm_update_lookup_table_ids );
            }

            // Delete the product transients
            if( function_exists('wc_delete_product_transients') ) {
            	$pids = array_keys( $edited_data );
            	if( !empty( $pids ) ) {
            		foreach( $pids as $id ) {
            			wc_delete_product_transients( $id );
            		}
            	}
            }
		}

		public function inline_update_product_featured_image() {

		    if ( ! empty( $_POST['update_field'] ) && 'postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id' === $_POST['update_field'] ) {
		    	$product_id = ( ! empty( $_POST['product_id'] ) && is_numeric( $_POST['product_id'] ) ) ? $_POST['product_id'] : 0;
		    	$attachment_id = ( ! empty( $_POST['selected_attachment_id'] ) && is_numeric( $_POST['selected_attachment_id'] ) ) ? $_POST['selected_attachment_id'] : 0;
		    	if ( ! empty( $product_id ) && ! empty( $attachment_id ) ) {

		    		update_post_meta( $product_id, '_thumbnail_id', $attachment_id );

		    		if( isset( $this->req_params['pro'] ) && empty( $this->req_params['pro'] ) ) {
						$sm_inline_update_count = get_option( 'sm_inline_update_count', 0 );
						$sm_inline_update_count += 1;
						update_option( 'sm_inline_update_count', $sm_inline_update_count );
						$resp = array( 'sm_inline_update_count' => $sm_inline_update_count,
										'msg' => esc_html__( 'Featured Image updated successfully', 'smart-manager-for-wp-e-commerce' ) );
						$msg = json_encode($resp);
					} else {
						$msg = esc_html__( 'Featured Image updated successfully', 'smart-manager-for-wp-e-commerce' );
					}

					echo $msg;
		    	} else {
		    		echo esc_html( 'failed' );
		    	}
		    }

		    exit;

		}
	} //End of Class
}
