<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Controller' ) ) {
	class Smart_Manager_Controller {
		public $dashboard_key = '',
				$plugin_path = '';

		function __construct() {
			if (is_admin() ) {
				add_action ( 'wp_ajax_sm_beta_include_file', array(&$this,'request_handler') );
			}
			$this->plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) );

			add_action('admin_init',array(&$this,'call_custom_actions'),11);
			add_action('admin_footer',array(&$this,'sm_footer'));
			//Filter for setting the wp_editor default tab
			add_filter( 'wp_default_editor', array(&$this,'sm_wp_default_editor'),10, 1 );
		}

		public function sm_wp_default_editor( $tab ) { //TODO: change the name of the page befre release
			if ( !empty($_GET['page']) && 'smart-manager' === $_GET['page'] ) {
				$tab = "html";
			}
			return $tab;
		}

		public function sm_footer() {
			if( !empty($_GET['page']) && 'smart-manager' === $_GET['page'] && !( !empty( $_GET['sm_old'] ) && ( 'woo' === $_GET['sm_old'] || 'wpsc' === $_GET['sm_old'] ) ) ) {
				echo '<div id="sm_wp_editor" style="display:none;">';
				wp_editor( '', 'sm_inline_wp_editor', array('default_editor' => 'html') );
				echo '</div>';
			}
		}

		//Function to call custom actions on admin_init		
		public function call_custom_actions() {
			do_action('sm_admin_init');

			add_action( 'edited_term',array( &$this,'terms_added' ), 10, 3 );
			add_action( 'created_term',array( &$this,'terms_added' ), 10, 3 );
			add_action( 'delete_term',array( &$this,'terms_deleted' ), 10, 5 );
			add_action( 'woocommerce_attribute_added',array( &$this,'woocommerce_attributes_updated' ) );
			add_action( 'woocommerce_attribute_updated',array( &$this,'woocommerce_attributes_updated' ) );
			add_action( 'woocommerce_attribute_deleted',array( &$this,'woocommerce_attributes_updated' ) );
			add_action( 'added_post_meta', array( &$this, 'added_post_meta' ), 10, 4 );

			//for background updater
			if( defined('SMBETAPRO') && SMBETAPRO === true && file_exists(SM_BETA_PRO_URL . 'classes/class-smart-manager-pro-background-updater.php') ) {
				include_once SM_BETA_PRO_URL . 'classes/class-smart-manager-pro-background-updater.php';
				$sm_beta_pro_background_updater = Smart_Manager_Pro_Background_Updater::instance();
			}

		}

		public function woocommerce_attributes_updated() {
			$this->delete_transients( array( 'product' ) );
		}

		public function terms_added( $term, $tt_id, $taxonomy ) {
			global $wp_taxonomies;

			$post_types = ( !empty( $wp_taxonomies[$taxonomy] ) ) ? $wp_taxonomies[$taxonomy]->object_type : array();
			$this->delete_transients( $post_types );
		}

		public function terms_deleted( $term, $tt_id, $taxonomy, $deleted_term, $object_ids ) {
			global $wp_taxonomies;

			$post_types = ( !empty( $wp_taxonomies[$taxonomy] ) ) ? $wp_taxonomies[$taxonomy]->object_type : array();
			$this->delete_transients( $post_types );
		}

		public function added_post_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
			$post_type = get_post_type( $object_id );
			$post_types = ( !empty( $post_type ) ) ? array( $post_type ) : array();
			$this->delete_transients( $post_types );
		}

		public function delete_transients( $post_types = array() ) {
			if( !empty( $post_types ) ) {
				foreach( $post_types as $post_type ) {
					if( get_transient( 'sm_beta_'.$post_type ) ) {
						delete_transient( 'sm_beta_'.$post_type );
					}
				}
			}
		}

		//Function to handle the wp-admin ajax request
		public function request_handler() {

			if (empty($_REQUEST) || empty($_REQUEST['active_module']) || empty($_REQUEST['cmd'])) return;

			check_ajax_referer('smart-manager-security','security');

			if ( !is_user_logged_in() || !is_admin() ) {
				return;
			}

			$pro_flag_class_path = $pro_flag_class_nm = $sm_pro_class_nm = '';

			if( defined('SMBETAPRO') && SMBETAPRO === true ) {
				$plugin_path = SM_BETA_PRO_URL .'classes';
				$pro_flag_class_path = 'pro-';
				$pro_flag_class_nm = 'Pro_';
			} else {
				$plugin_path = $this->plugin_path;
			}

			//Including the common utility functions class
			include_once $plugin_path . '/class-smart-manager-'.$pro_flag_class_path.'utils.php';
			include_once $this->plugin_path . '/class-smart-manager-base.php';
			
			if( defined('SMBETAPRO') && SMBETAPRO === true ) {
				$sm_pro_class_nm = 'class-smart-manager-'.$pro_flag_class_path.'base.php';
				include_once $plugin_path . '/'. $sm_pro_class_nm;
			}

			$func_nm = $_REQUEST['cmd'];
			$this->dashboard_key = $_REQUEST['active_module'];
			
			//Code for initializing the specific dashboard class

			$file_nm = str_replace('_', '-', $this->dashboard_key);

			$class_name = '';

			if (file_exists($plugin_path . '/class-smart-manager-'.$pro_flag_class_path.''.$file_nm.'.php')) {

				$key_array = explode("_",$this->dashboard_key);
				$formatted_dashboard_key = array();
				foreach( $key_array as $value ) {
					$formatted_dashboard_key[] = ucwords($value);
				}

				$class_name = 'Smart_Manager_'.$pro_flag_class_nm.''.implode("_",$formatted_dashboard_key);

				if( file_exists( $this->plugin_path . '/class-smart-manager-'.$file_nm.'.php' ) ) {
					include_once $this->plugin_path . '/class-smart-manager-'.$file_nm.'.php';
				}

				if( defined('SMBETAPRO') && SMBETAPRO === true ) {
					$sm_pro_class_nm = 'class-smart-manager-'.$pro_flag_class_path.''.$file_nm.'.php';
					include_once $plugin_path .'/'. $sm_pro_class_nm;
				}
			} else {
				$class_name = (!empty($pro_flag_class_nm)) ? 'Smart_Manager_'.$pro_flag_class_nm.'Base' : 'Smart_Manager_Base';
			}

			$_REQUEST['class_nm'] = $class_name;
			$_REQUEST['class_path'] = $sm_pro_class_nm;

			$handler_obj = new $class_name($this->dashboard_key);
			$handler_obj->$func_nm();
		}		

	}
}
