<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Shop_Coupon' ) ) {
	class Smart_Manager_Shop_Coupon extends Smart_Manager_Base {

		public $dashboard_key = '',
			$default_store_model = array();

		function __construct($dashboard_key) {
			parent::__construct($dashboard_key);

			$this->dashboard_key = $dashboard_key;
			$this->post_type = $dashboard_key;
			$this->req_params  	= (!empty($_REQUEST)) ? $_REQUEST : array();
			
			add_filter( 'sm_dashboard_model',array( &$this,'coupons_dashboard_model' ), 10, 2 );
			// add_filter( 'sm_data_model', array( &$this, 'coupons_data_model' ), 10, 2 );

		}

		public function coupons_dashboard_model ($dashboard_model, $dashboard_model_saved) {
			global $wpdb, $current_user;

			$visible_columns = array('ID', 'post_title', 'discount_type', 'coupon_amount', 'post_excerpt', 'product_ids', 'product_categories', 'customer_email', 'usage_count', 'usage_limit', 'expiry_date', 'free_shipping', 'individual_use', 'exclude_sale_items', 'usage_limit_per_user');

			$numeric_columns = array('usage_limit', 'usage_limit_per_user', 'limit_usage_to_x_items', 'coupon_amount', 'usage_count', 'minimum_amount', 'maximum_amount');

			$checkbox_yes_no_columns = array('individual_use', 'free_shipping', 'exclude_sale_items', 'sc_restrict_to_new_user', 'auto_generate_coupon', 'sc_disable_email_restriction', 'is_pick_price_of_product', 'wc_email_message');

			$post_type_col_index = sm_multidimesional_array_search('postmeta_meta_key_discount_type_meta_value_discount_type', 'data', $dashboard_model['columns']);

			$coupon_statuses = ( function_exists('wc_get_coupon_types') ) ? wc_get_coupon_types() : array();

			$dashboard_model['columns'][$post_type_col_index]['type'] = 'dropdown';
			$dashboard_model['columns'][$post_type_col_index]['editor'] = 'select';
			$dashboard_model['columns'][$post_type_col_index]['renderer'] = 'selectValueRenderer';
			$dashboard_model['columns'][$post_type_col_index]['values'] = $coupon_statuses;
			$dashboard_model['columns'][$post_type_col_index]['selectOptions'] = $coupon_statuses;
			$dashboard_model['columns'][$post_type_col_index]['defaultValue'] = ( !empty( $coupon_statuses[0] ) ) ? $coupon_statuses[0] : '';
			$dashboard_model['columns'][$post_type_col_index]['save_state'] = true;

			$dashboard_model['columns'][$post_type_col_index]['search_values'] = array();
			foreach ($coupon_statuses as $key => $value) {
				$dashboard_model['columns'][$post_type_col_index]['search_values'][] = array('key' => $key, 'value' => $value);
			}

			$column_model = &$dashboard_model['columns'];

			foreach( $column_model as &$column ) {
				
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

				if ($src == 'post_title') {
					$column ['name_display'] = $column ['key'] = __('Coupon Code', 'smart-manager-for-wp-e-commerce');
				} else if( $src == 'post_excerpt' ) {
					$column ['name_display'] = $column ['key'] = __('Description', 'smart-manager-for-wp-e-commerce');
				} else if( $src == 'customer_email' ) {
					$column ['name_display'] = $column ['key'] = __('Allowed Emails', 'smart-manager-for-wp-e-commerce');
				} else if( $src == 'usage_limit' ) {
					$column ['name_display'] = $column ['key'] = __('Usage Limit Per Coupon', 'smart-manager-for-wp-e-commerce');
				} else if( $src == 'free_shipping' ) {
					$column ['name_display'] = $column ['key'] = __('Allow Free Shipping', 'smart-manager-for-wp-e-commerce');
				}

				if( !empty( $numeric_columns ) && in_array( $src, $numeric_columns ) ) {
					$column ['type'] = $column ['editor'] = 'numeric';
				} else if ( !empty( $checkbox_yes_no_columns ) && in_array( $src, $checkbox_yes_no_columns ) ) {
					$column ['type'] = 'checkbox';
					$column ['checkedTemplate'] = 'yes';
  					$column ['uncheckedTemplate'] = 'no';
				}
			}

			if (!empty($dashboard_model_saved)) {
				$col_model_diff = sm_array_recursive_diff($dashboard_model_saved,$dashboard_model);	
			}

			//clearing the transients before return
			if (!empty($col_model_diff)) {
				delete_transient( 'sm_beta_'.$this->dashboard_key );	
			}		

			return $dashboard_model;

		}

	}
}
