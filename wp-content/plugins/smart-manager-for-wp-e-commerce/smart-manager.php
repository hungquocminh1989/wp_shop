<?php
/*
* Plugin Name: Smart Manager
* Plugin URI: https://www.storeapps.org/product/smart-manager/
* Description: <strong>Lite Version Installed</strong>. The most popular store admin plugin for WooCommerce. 10x faster, inline updates. Price, inventory, variations management. 200+ features.
* Version: 4.2.18
* Author: StoreApps
* Author URI: https://www.storeapps.org/
* Text Domain: smart-manager-for-wp-e-commerce
* Domain Path: /languages/

* Requires at least: 4.8.0
* Tested up to: 5.2.2
* WC requires at least: 2.0.0
* WC tested up to: 3.6.4

* Copyright (c) 2010 - 2019 StoreApps. All rights reserved.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

//Hooks

register_activation_hook( __FILE__, 'smart_activate' );
register_deactivation_hook( __FILE__, 'smart_deactivate' );

/**
 * Registers a plugin function to be run when the plugin is activated.
 */
function smart_activate() {
	global $wpdb;

	$index_queries = generate_db_index_queries();
	process_db_indexes( $index_queries ['add'] );

	$collate = '';

	if ( $wpdb->has_cap( 'collation' ) ) {
		$collate = $wpdb->get_charset_collate();
	}

	$sm_advanced_search_temp = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sm_advanced_search_temp` (
					  `product_id` bigint(20) unsigned NOT NULL default '0',
					  `flag` bigint(20) unsigned NOT NULL default '0',
					  `cat_flag` bigint(20) unsigned NOT NULL default '0') $collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sm_advanced_search_temp );


	// Redirect to welcome screen
	if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
		set_transient( '_sm_activation_redirect', 1, 30 );
	}
}

/**
 * Registers a plugin function to be run when the plugin is deactivated.
 */
function smart_deactivate() {
	global $wpdb;

	$index_queries = generate_db_index_queries();
	process_db_indexes( $index_queries ['remove'] );

	$wpdb->query( "DROP TABLE {$wpdb->prefix}sm_advanced_search_temp" );
	$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_sm_beta_%' OR option_name LIKE '_transient_timeout_sm_beta_%'"); //for deleting sm beta transients
}

function smart_get_latest_version() {
	$sm_plugin_info = get_site_transient( 'update_plugins' );
	$latest_version = isset( $sm_plugin_info->response [SM_PLUGIN_BASE_NM]->new_version ) ? $sm_plugin_info->response [SM_PLUGIN_BASE_NM]->new_version : '';
	return $latest_version;
}

function smart_get_user_sm_version() {
	$sm_plugin_info = get_plugins();
	$user_version = $sm_plugin_info [SM_PLUGIN_BASE_NM] ['Version'];
	return $user_version;
}

function smart_is_pro_updated() {
	$user_version = smart_get_user_sm_version();
	$latest_version = smart_get_latest_version();
	return version_compare( $user_version, $latest_version, '>=' );
}

$plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) );

/**
 * Throw an error on admin page when WP e-Commerece plugin is not activated.
 */
//if (is_admin ()) {

// require_once (ABSPATH . 'wp-includes/pluggable.php'); // Sometimes conflict with SB-Welcome Email Editor

require_once (ABSPATH . WPINC . '/default-constants.php');
$plugin = plugin_basename( __FILE__ );
$msg = str_word_count("Upgrade In Progress");
$upmsg = "Upgrade to";

define( 'SM_PLUGIN_FILE', __FILE__ );
define( 'SM_PLUGIN_DIR', dirname( $plugin ) );
define( 'SM_PLUGIN_BASE_NM', $plugin );
define( 'SM_TEXT_DOMAIN', 'smart-manager-for-wp-e-commerce' );
define( 'SM_PREFIX', 'sa_smart_manager' );
define( 'SM_SKU', 'sm' );
define( 'SM_PLUGIN_NAME', 'Smart Manager' );
define( 'SM_UPGRADE', $msg );
define( 'SM_DUPGRADE', ( ($msg*8)+1 ) );
define( 'SM_UPDATE', $upmsg );
define( 'SM_ADMIN_URL', get_admin_url() ); //defining the admin url

define( 'SM_PLUGIN_DIR_PATH', dirname( __FILE__ ) );
define( 'SM_PLUGINS_FILE_PATH', dirname( dirname( __FILE__ ) ) );
define( 'SM_PLUGIN_DIRNAME', plugins_url( '', __FILE__ ) );
define( 'SM_IMG_URL', SM_PLUGIN_DIRNAME . '/images/' );

if ( ! defined( 'SM_BETA_IMG_URL' ) ) {
	define( 'SM_BETA_IMG_URL', SM_PLUGIN_DIRNAME . '/new/assets/images/' );
}

if (!defined('STORE_APPS_URL')) {
	define( 'STORE_APPS_URL', 'https://www.storeapps.org/' );
}

if (!defined('SMPRO')) {
	if (file_exists ( (dirname ( __FILE__ )) . '/pro/sm.js' )) {
		define( 'SMPRO', true );
	} else {
		define( 'SMPRO', false );
	}
}

if ( ! defined( 'SMBETAPRO' ) ) { // for SM BETA
	if (file_exists ( (dirname ( __FILE__ )) . '/new/pro/assets/js/smart-manager.js' )) { 
		define ( 'SMBETAPRO', true );
	} else {
		define ( 'SMBETAPRO', false );
	}
}

if ( ! defined( 'SM_BETA_PRO_URL' ) ) {
	define( 'SM_BETA_PRO_URL', (dirname ( __FILE__ )) . '/new/pro/' );
}

if ( ! defined( 'SM_BETA_URL' ) ) {
	define( 'SM_BETA_URL', (dirname ( __FILE__ )) . '/new/' );
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';
include_once (ABSPATH . WPINC . '/functions.php');

// if ( !empty($_GET['page']) && ( 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] || 'smart-manager' === $_GET['page'] ) ) {
	include_once $plugin_path . '/new/classes/class-smart-manager-admin-welcome.php';
// }

add_filter( 'site_transient_update_plugins', 'sm_overwrite_site_transient',11,1);
add_filter( 'pre_set_site_transient_update_plugins', 'sm_overwrite_site_transient',11,1);

function get_sa_smart_manager_pro_download_url() {
	$sm_old_download_url = '';
	$sm_new_download_url = '';
	if ( defined('SM_PREFIX') ) {
		$sm_old_download_url = get_site_option( SM_PREFIX.'_download_url' );
	}
	$data = get_option( '_storeapps_connector_data', array() );
	if ( defined('SM_SKU') && ! empty( $data[SM_SKU] ) ) {
		$sm_new_download_url = $data[SM_SKU]['download_url'];
	}
	$sm_download_url = ( ! empty( $sm_new_download_url ) ) ? $sm_new_download_url : $sm_old_download_url;
	return $sm_download_url;
}

function is_sa_smart_manager_pro_available() {
	$sm_download_url = get_sa_smart_manager_pro_download_url();
	if ( ! file_exists( ( dirname( __FILE__ ) ) . '/pro/sm.js' ) && ! empty( $sm_download_url ) ) {
		return true;
	}
	return false;
}

function sm_overwrite_site_transient( $plugin_info ) {

	if ( ! defined('SM_SKU') ) {
		return $plugin_info;
	}

	$data = get_option( '_storeapps_connector_data', array() );
	$sm_license_key = !empty($data[SM_SKU]) ? $data[SM_SKU]['license_key'] : '';

	$sm_download_url = get_sa_smart_manager_pro_download_url();

	if ( file_exists((dirname( __FILE__ )) . '/pro/sm.js') && (empty($sm_license_key) || empty($sm_download_url)) ) {
		$plugin_base_file = plugin_basename( __FILE__ );

		$live_version = !empty($data[SM_SKU]['live_version']) ? $data[SM_SKU]['live_version'] : '';
		$installed_version = !empty($data[SM_SKU]['installed_version']) ? $data[SM_SKU]['installed_version'] : '';

		if ( version_compare( $live_version, $installed_version, '>' ) ) {
			$plugin_info->response[$plugin_base_file]->package = '';
		}		
	}

	return $plugin_info;
}

add_action( 'plugins_loaded', 'sm_plugins_loaded' );

// Find latest StoreApps Upgrade file
function sm_get_latest_upgrade_class() {

	$available_classes = get_declared_classes();
	$available_upgrade_classes = array_filter( $available_classes, function ( $class_name ) {
																							return strpos( $class_name, 'StoreApps_Upgrade_' ) === 0;
																						} );
	$latest_class = 'StoreApps_Upgrade_3_3';
	$latest_version = 0;
	foreach ( $available_upgrade_classes as $class ) {
		$exploded = explode( '_', $class );
		$get_numbers = array_filter( $exploded, function ( $value ) {
													return is_numeric( $value );
												} );
		$version = implode( '.', $get_numbers );
		if ( version_compare( $version, $latest_version, '>' ) ) {
			$latest_version = $version;
			$latest_class = $class;
		}
	}

	return $latest_class;
}

//function to handle inclusion of the SA upgrade file
function sm_plugins_loaded() {

	if ( defined('SMPRO') && SMPRO === true ) {

		if( !class_exists( 'Smart_Manager_Pro_Access_Privilege' ) && file_exists( (dirname( __FILE__ )) . '/new/pro/classes/class-smart-manager-pro-access-privilege.php' ) ) {
			include_once 'new/pro/classes/class-smart-manager-pro-access-privilege.php';
		}

		if ( !class_exists( 'StoreApps_Upgrade_3_3' ) ) {
			require_once 'pro/sa-includes/class-storeapps-upgrade-3-3.php';
		}

		$latest_upgrade_class = sm_get_latest_upgrade_class();

		$sku = SM_SKU;
		$prefix = SM_PREFIX;
		$plugin_name = SM_PLUGIN_NAME;
		$documentation_link = 'https://www.storeapps.org/knowledgebase_category/smart-manager/';
		$GLOBALS['smart_manager_upgrade'] = new $latest_upgrade_class( __FILE__, $sku, $prefix, $plugin_name, SM_TEXT_DOMAIN, $documentation_link );

		//filters for handling quick_help_widget
		add_filter( 'sa_active_plugins_for_quick_help', 'sm_quick_help_widget', 10, 2 );
		add_filter( 'sa_is_page_for_notifications', 'sm_sa_is_page_for_notifications', 10, 2 );


		//Code for handling the in app offer
		if ( ! class_exists( 'SA_In_App_Offer' ) ) {
			include_once 'pro/sa-includes/class-sa-in-app-offer.php';
			$args = array(
				'file'           => __FILE__,
				'prefix'         => 'sm',				// prefix/slug of your plugin
				'option_name'    => 'sa_offer_halloween_2018',
				'campaign'       => 'sa_halloween_2018',
				'start'          => '2018-10-30',
				'end'            => '2018-11-02',
				'is_plugin_page' => ( !empty($_GET['page']) && ( 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] || 'smart-manager' === $_GET['page'] ) ) ? true : false,	// page where you want to show offer, do not send this if no plugin page is there and want to show offer on Products page
			);

			SA_In_App_Offer::get_instance( $args );
		}
	}
}

// add_action ( 'admin_notices', 'smart_admin_notices' );
//	admin_init is triggered before any other hook when a user access the admin area. 
// This hook doesn't provide any parameters, so it can only be used to callback a specified function.
add_action ( 'admin_init', 'smart_admin_init' );
add_action ( 'admin_init', 'sm_dismiss_admin_notices' );

//For handling media links on plugins page
add_action( 'admin_footer', 'sm_add_plugin_style_script' );

// Function to dequeue unwanted WPML scripts on Smart Manager page. It was conflicting with batch update of images [Ratnakar]
function sa_sm_dequeue_wpml_scripts() {
	if ( is_admin() && !empty( $_GET['page'] ) && ( 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] || ( !empty( $_GET['sm_old'] ) && ( 'woo' === $_GET['sm_old'] || 'wpsc' === $_GET['sm_old'] ) && 'smart-manager' === $_GET['page'] ) || 'smart-manager' === $_GET['page'] || 'smart-manager-settings' === $_GET['page'] ) ) {
		if ( wp_script_is( 'wpml-tm-progressbar' ) ) {
			wp_dequeue_script( 'wpml-tm-progressbar' );
		}
		if ( wp_script_is( 'wpml-tm-scripts' ) ) {
			wp_dequeue_script( 'wpml-tm-scripts' );
		}
		if ( wp_script_is( 'toolset-utils' ) ) {
			wp_dequeue_script( 'toolset-utils' );
			wp_deregister_script( 'toolset-utils' );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'sa_sm_dequeue_wpml_scripts', 999 );

if ( defined('SMPRO') && SMPRO === false ) {
	add_action( 'admin_init', 'sm_show_upgrade_to_pro'); //for handling Pro to Lite
} else if ( defined('SMPRO') && SMPRO === true ) {
	add_action( 'admin_init', 'sa_sm_activated' );
}

function sa_sm_activated() {
	$is_check = get_option( SM_PREFIX . '_check_update', 'no' );
	if ( $is_check === 'no' ) {
	  $response = wp_remote_get( 'https://www.storeapps.org/wp-admin/admin-ajax.php?action=check_update&plugin='.SM_SKU );
	  update_option( SM_PREFIX . '_check_update', 'yes' );
	}
}

//Language loader

add_action ( 'admin_init', 'localize_smart_manager' );
	 
function localize_smart_manager() {

	$text_domain = SM_TEXT_DOMAIN;

	$plugin_dirname = dirname( plugin_basename(__FILE__) );

	$locale = apply_filters( 'plugin_locale', get_locale(), $text_domain );

	$loaded = load_textdomain( $text_domain, WP_LANG_DIR . '/plugins/' . $text_domain . '-' . $locale . '.mo' );    

	if ( ! $loaded ) {
		$loaded = load_plugin_textdomain( $text_domain, false, $plugin_dirname . '/languages/' );
	}

}

function smart_manager_get_data() {
	return get_plugin_data( __FILE__ );
}

// function to handle the display of quick help widget
function sm_quick_help_widget( $active_plugins, $upgrader ) {
	
	if ( is_admin() && !empty( $_GET['page'] ) && ( 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] || ( !empty( $_GET['sm_old'] ) && ( 'woo' === $_GET['sm_old'] || 'wpsc' === $_GET['sm_old'] ) && 'smart-manager' === $_GET['page'] ) || 'smart-manager-settings' === $_GET['page'] ) ) {
		$active_plugins[SM_SKU] = 'smart-manager';
	} elseif ( array_key_exists( SM_SKU, $active_plugins ) ) {
		unset( $active_plugins[SM_SKU] );
	}
		
	return $active_plugins;
}

function sm_sa_is_page_for_notifications( $is_page, $upgrader ) {
	
	if ( is_admin() && ! empty( $_GET['page'] ) && ( 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] || ( !empty( $_GET['sm_old'] ) && ( 'woo' === $_GET['sm_old'] || 'wpsc' === $_GET['sm_old'] ) && 'smart-manager' === $_GET['page'] ) || 'smart-manager' === $_GET['page'] || 'smart-manager-settings' === $_GET['page'] ) ) {
		return true;
	}
		
	return $is_page;
}

/*
* Function to to handle media links on plugin page
*/ 
function sm_add_plugin_style_script() {

	$is_pro_available = is_sa_smart_manager_pro_available();
	if( $is_pro_available === true ) { //request ftp credentials form
		wp_print_request_filesystem_credentials_modal();
	}

?>
<script type="text/javascript">
	jQuery(function() {
		jQuery(document).ready(function() {
			jQuery('tr[id="smart-manager"]').find( 'div.plugin-version-author-uri' ).addClass( 'sa_smart_manager_social_links' );
		});
	});
</script>
<style type="text/css">
	@keyframes beat {
		to { transform: scale(1.1); }
	}
	.sm_pricing_icon {
		animation: beat .25s infinite alternate;
		transform-origin: center;
		color: #ea7b00;
		display: inline-block;
		font-size: 1.5em;
	}
</style>

<?php
}

function smart_admin_init() {
	global $wp_version,$wpdb;

	if( defined('WOOCOMMERCE_VERSION') ) {
		// checking the version for WooCommerce plugin
		define ( 'IS_WOO13', version_compare ( WOOCOMMERCE_VERSION, '1.4', '<' ) );

		if ( version_compare( WOOCOMMERCE_VERSION , '3.6.0', '>=' ) ) {
			define( 'SM_IS_WOO36', 'true' );
			define( 'SM_IS_WOO30', 'true' );
			define( 'SM_IS_WOO22', 'false' );
			define( 'SM_IS_WOO21', 'false' );
			define( 'SM_IS_WOO16', 'false' );
		} else {
			define( 'SM_IS_WOO36', 'false' );
			if (version_compare ( WOOCOMMERCE_VERSION, '3.0.0', '<' )) {
				
				if (version_compare ( WOOCOMMERCE_VERSION, '2.2.0', '<' )) {

					if (version_compare ( WOOCOMMERCE_VERSION, '2.1.0', '<' )) {

						if (version_compare ( WOOCOMMERCE_VERSION, '2.0', '<' )) {
							define ( 'SM_IS_WOO16', "true" );
						} else {
							define ( 'SM_IS_WOO16', "false" );	
						}
						define ( 'SM_IS_WOO21', "false" );
					} else {
						define ( 'SM_IS_WOO16', "false" );
						define ( 'SM_IS_WOO21', "true" );
					}
					define ( 'SM_IS_WOO22', "false" );
				} else {
					define ( 'SM_IS_WOO16', "false" );
					define ( 'SM_IS_WOO21', "false" );
					define ( 'SM_IS_WOO22', "true" );
				}
				define ( 'SM_IS_WOO30', "false" );
			} else {
				define ( 'SM_IS_WOO16', "false" );
				define ( 'SM_IS_WOO21', "false" );
				define ( 'SM_IS_WOO22', "false" );
				define ( 'SM_IS_WOO30', "true" );
			}
		}
	}

	include_once ('new/smart-manager.php');

	$plugin = plugin_basename( __FILE__ );
	$old_plugin = 'smart-manager/smart-manager.php';
	if (is_plugin_active( $old_plugin )) {
		deactivate_plugins( $old_plugin );
		$action_url = "plugins.php?action=activate&plugin=$plugin&plugin_status=all&paged=1";
		$url = wp_nonce_url( $action_url, 'activate-plugin_' . $plugin );
		update_option( 'recently_activated', array ($plugin => time() ) + ( array ) get_option( 'recently_activated' ) );
		
		if (headers_sent())
			echo "<meta http-equiv='refresh' content='" . esc_attr( "0;url=plugins.php?deactivate=true&plugin_status=$status&paged=$page" ) . "' />";
		else {
			wp_redirect( str_replace( '&amp;', '&', $url ) );
			exit();
		}
	}

	add_action( 'admin_head', 'remove_help_tab' ); // For removing the help tab

	$plugin_info = get_plugins ();
	$sm_plugin_info = $plugin_info [SM_PLUGIN_BASE_NM];
	$ext_version = '3.3.1';
	$sm_plugin_data = get_plugin_data(__FILE__);
	$sm_version = $sm_plugin_data['Version'];
	define ( 'SM_VERSION', $sm_version );
	load_plugin_textdomain( SM_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/languages' );
	if (is_plugin_active ( 'woocommerce/woocommerce.php' ) && is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' )) {
		define('WPSC_WOO_ACTIVATED',true);
	} elseif (is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' )) {
		define('WPSC_ACTIVATED',true);
	} elseif (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
		define('WOO_ACTIVATED', true);
	}

	// Including Scripts for using the wordpress new media manager
	if (version_compare ( $wp_version, '3.5', '>=' )) {
		define ( 'IS_WP35', true);

		if ( !empty( $_GET['page'] ) && ( 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] || 'smart-manager' === $_GET['page'] || 'smart-manager-settings' === $_GET['page'] ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'custom-header' );
			// wp_enqueue_script( 'media-upload' );
		}
		
	}

	//Flag for handling changes since WP 4.0+
	if (version_compare ( $wp_version, '4.0', '>=' )) {
		define ( 'IS_WP40', true);
	}

	if ( !wp_script_is( 'jquery' ) ) {
		wp_enqueue_script( 'jquery' );
	}

	if ( !wp_script_is( 'underscore' ) ) {
		wp_enqueue_script( 'underscore' );
	}

	$deps = array ('jquery', 'jquery-ui-menu', 'jquery-ui-autocomplete',  'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-position', 'wp-util', 'underscore'); //dependencies for visual search

	wp_register_script ( 'sm_visualsearch_dependencies', plugins_url ( '/visualsearch/backbone.js', __FILE__ ), $deps, '0.0.1' );
	wp_register_script ( 'sm_search', plugins_url ( '/visualsearch/search.js', __FILE__ ), array ('sm_visualsearch_dependencies'), '0.0.1' );
	wp_register_script ( 'sm_ext_base', plugins_url ( '/ext/ext-base.js', __FILE__ ), array ('sm_search'), $ext_version );

	wp_register_script ( 'sm_ext_all', plugins_url ( '/ext/ext-all.js', __FILE__ ), array ('sm_ext_base' ), $ext_version );

	if( !empty( $_GET['page'] ) && 'smart-manager' === $_GET['page'] && !empty( $_GET['sm_old'] ) && 'wpsc' === $_GET['sm_old'] ) {
		wp_register_script ( 'sm_main', plugins_url ( '/sm/smart-manager.js', __FILE__ ), array ('sm_ext_all'), $sm_plugin_info ['Version'] );
		define('WPSC_RUNNING', true);
		define('WOO_RUNNING', false);

		// checking the version for WPSC plugin
		define ( 'IS_WPSC37', version_compare ( WPSC_VERSION, '3.8', '<' ) );
		define ( 'IS_WPSC38', version_compare ( WPSC_VERSION, '3.8', '>=' ) );
		if ( IS_WPSC38 ) {		// WPEC 3.8.7 OR 3.8.8 OR 3.8.14
			define('IS_WPSC387', version_compare ( WPSC_VERSION, '3.8.8', '<' ));
			define('IS_WPSC388', version_compare ( WPSC_VERSION, '3.8.8', '>=' ));
			define('IS_WPSC3814', version_compare ( WPSC_VERSION, '3.8.14', '>=' )); // Added as Database upgrade since 3.8.14
		}

	} elseif( !empty( $_GET['page'] ) && 'smart-manager' === $_GET['page'] && !empty( $_GET['sm_old'] ) && 'woo' === $_GET['sm_old'] ) {
		wp_register_script ( 'sm_main', plugins_url ( '/sm/smart-manager-woo.js', __FILE__ ), array ('sm_ext_all' ), $sm_plugin_info ['Version'] );
		define('WPSC_RUNNING', false);
		define('WOO_RUNNING', true);
	}

	wp_register_style ( 'sm_search_reset', plugins_url ( '/visualsearch/reset.css', __FILE__ ), array (), '0.0.1' );
	wp_register_style ( 'sm_search_icons', plugins_url ( '/visualsearch/icons.css', __FILE__ ), array ('sm_search_reset'), '0.0.1' );
	wp_register_style ( 'sm_search_workspace', plugins_url ( '/visualsearch/workspace.css', __FILE__ ), array ('sm_search_icons'), '0.0.1' );
	wp_register_style ( 'sm_ext_all', plugins_url ( '/ext/ext-all.css', __FILE__ ), array ('sm_search_workspace'), $ext_version );
	wp_register_style ( 'sm_ext_theme_grey', plugins_url ( '/ext/ext-theme-grey.css', __FILE__ ), array ('sm_ext_all'), $ext_version );
	wp_register_style ( 'sm_main', plugins_url ( '/sm/smart-manager.css', __FILE__ ), array ('sm_ext_theme_grey' ), $sm_plugin_info ['Version'] );
	
	if ( defined('SMPRO') && SMPRO === true ) {
		wp_register_script ( 'sm_functions', plugins_url ( '/pro/sm.js', __FILE__ ), array ('sm_main' ), $sm_plugin_info ['Version'] );
	}


	add_action( 'admin_notices', 'sm_add_admin_notices' );

	if (SMPRO === true) {
		include ('pro/sm-settings.php');
	} else {
		// Code to hide SM Promo
		if ( is_admin() ) {
			if(isset($_GET['sm_dismiss_admin_notice']) && $_GET['sm_dismiss_admin_notice'] == '1'){
				update_option('sm_dismiss_admin_notice', true);
				wp_safe_redirect($_SERVER['HTTP_REFERER']);
			}

			if(isset($_GET['sm_dismiss_subscribe_admin_notice']) && $_GET['sm_dismiss_subscribe_admin_notice'] == '1'){
				update_option('sm_dismiss_subscribe_admin_notice', true);
				wp_safe_redirect($_SERVER['HTTP_REFERER']);
			}


			if(isset($_GET['sm_dismiss_anniversary_promo']) && $_GET['sm_dismiss_anniversary_promo'] == '1'){
				update_option('sm_dismiss_anniversary_promo', true);
				wp_safe_redirect($_SERVER['HTTP_REFERER']);
			} 
		}
	}
	//wp-ajax action
	if (is_admin() ) {
		add_action ( 'wp_ajax_sm_include_file', 'sm_include_file' ); 
		add_action ( 'wp_ajax_sm_klawoo_subscribe', 'sm_klawoo_subscribe' );
		add_action ( 'wp_ajax_sm_update_to_pro', 'sm_update_to_pro' );
	}

}

// Function for klawoo subscribe
function sm_klawoo_subscribe() {
	$url = 'http://app.klawoo.com/subscribe';

	if( !empty( $_POST ) ) {
		$params = $_POST;
	} else {
		exit();
	}

	if( empty($params['name']) ) {
		$params['name'] = '';
	}

	$method = 'POST';
	$qs = http_build_query( $params );

	$options = array(
		'timeout' => 15,
		'method' => $method
	);

	if ( $method == 'POST' ) {
		$options['body'] = $qs;
	} else {
		if ( strpos( $url, '?' ) !== false ) {
			$url .= '&'.$qs;
		} else {
			$url .= '?'.$qs;
		}
	}

	$response = wp_remote_request( $url, $options );
	if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
		$data = $response['body'];
		if ( $data != 'error' ) {
			$message_start = substr( $data, strpos( $data,'<body>' ) + 6 );
			$remove = substr( $message_start, strpos( $message_start,'</body>' ) );
			$message = trim( str_replace( $remove, '', $message_start ) );

			if( !empty( $_POST['valid_offer'] ) ) {
				update_option('sm_valid_offer', $_POST['valid_offer']);
			} else {
				update_option('sm_dismiss_admin_notice', true); // for hiding the promo message
			}

			echo ( $message );
			exit();                
		}
	}
	exit();
}

//Function to re-update to Pro in case of Pro to Lite
function sm_update_to_pro() {
	
	$sm_download_url = get_sa_smart_manager_pro_download_url();

	if ( ! empty( $sm_download_url ) ) {

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );

		$result = $upgrader->run( array(
			'package'           => $sm_download_url,
			'destination'       => WP_PLUGIN_DIR,
			'clear_destination' => true,
			'clear_working'     => true,
			'hook_extra'        => array(
										'plugin' => 'smart-manager-for-wp-e-commerce/smart-manager.php',
										'type'   => 'plugin',
										'action' => 'update',
									),
		) );

		if( !empty($result) ) {
			die('Success');	
		} else {
			die('Failed');
		}
		
	}
}

//function to show the upgrade to Pro link only for Pro to Lite
function sm_show_upgrade_to_pro() {

	if( !( !empty( $_GET['page'] ) && ( 'smart-manager' === $_GET['page'] || 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] ) ) ) {
		return;
	}

	$sm_license_key = get_site_option( SM_PREFIX.'_license_key' );

	if ( !empty($sm_license_key) ) {
		$storeapps_validation_url = 'https://www.storeapps.org/?wc-api=validate_serial_key&serial=' . urlencode( $sm_license_key ) . '&is_download=true&sku=' . SM_SKU . '&uuid=' . admin_url();
		$resp_type = array ('headers' => array ('content-type' => 'application/text' ) );
		$response_info = wp_remote_post( $storeapps_validation_url, $resp_type ); //return WP_Error on response failure

		if (is_array( $response_info )) {
			$response_code = wp_remote_retrieve_response_code( $response_info );
			$response_msg = wp_remote_retrieve_response_message( $response_info );

			if ($response_code == 200) {
				$storeapps_response = wp_remote_retrieve_body( $response_info );
				$decoded_response = json_decode( $storeapps_response );
				if ($decoded_response->is_valid == 1) {               
					update_site_option( SM_PREFIX.'_download_url', $decoded_response->download_url );
					define('SMPROTOLITE', true);
				} else {
					define('SMPROTOLITE', false);
				}
			} else {
				define('SMPROTOLITE', false);
			}
		}
	}
}

//function to validate the license key for the Pro to Lite issue
function sm_validate_license_key($sm_license_key) {
	
	if( empty($sm_license_key) ) {
		return;
	}

	if (is_array( $response_info )) {
		$response_code = wp_remote_retrieve_response_code( $response_info );
		$response_msg = wp_remote_retrieve_response_message( $response_info );

		if ($response_code == 200) {
			$storeapps_response = wp_remote_retrieve_body( $response_info );
			$decoded_response = json_decode( $storeapps_response );
			if ($decoded_response->is_valid == 1) {
				update_site_option( SM_PREFIX.'_license_key', $license_key );                
				update_site_option( SM_PREFIX.'_download_url', $decoded_response->download_url );
			}
			return json_encode($storeapps_response);
		}
	}
}

// Function to handle SM admin notices
function sm_add_admin_notices() {

	if( !( !empty( $_GET['page'] ) && ( 'smart-manager' === $_GET['page'] || 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] ) ) ) {
		return;
	}

	if (SMPRO === false) {
		// sm_add_promo_notices();
		sm_add_subscribe_notices();
	}
}

//Function to hide SM offer admin notices
function sm_dismiss_admin_notices() {
	if(isset($_GET['sm_dismiss_offer_christmas_admin_notice']) && $_GET['sm_dismiss_offer_christmas_admin_notice'] == '1'){
		update_option('sm_dismiss_offer_christmas_admin_notice', true);
		if( !empty($_GET['sm_redirect']) && $_GET['sm_redirect'] == 1 ) {
			header("Location: https://www.storeapps.org/shop/?utm_source=in_app&utm_medium=sm_banner&utm_campaign=xmas2017");
			exit();
		}
	}
}

function sm_add_subscribe_notices() {

	$sm_dismiss_admin_notice = get_option('sm_dismiss_subscribe_admin_notice', false);
	if ( !empty($_GET['page']) && ( 'smart-manager' === $_GET['page'] || 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] ) && empty( $sm_dismiss_admin_notice ) ) {

			$sm_promo_msg = '<b>'. __('Get latest hacks & tips to better manage your store using Smart Manager', 'smart-manager-for-wp-e-commerce');
			$sm_klawoo_list_id = '892PeLE57RO1PUqkLq892HhHOg';
			$sm_resp_msg = "<div style='padding-top: 0em;float: left;font-size:1.3em;'><b>". __('Thank you for Subscribing!!!') ."</b></div>";
			$sm_promo_cond = '';
			$sm_promo_hide_msg = __('No, I don\'t need help...', 'smart-manager');
			$valid_offer = 0;

			$current_url = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];

			echo '<div id="sm_promo_msg" class="updated fade" style="display:block !important;background-color:#584079;color:#fff;border-left-color:#A3B745;"> 
					<table style="width:100%;padding-bottom: 0.25em;"> 
						<tbody> 
							<tr>
								<td> 
									<span class="dashicons dashicons-awards" style="font-size:5em;color:#A3B745;margin-left: -0.2em;margin-right: 0.5rem;margin-bottom: 0.65em;"></span> 
								</td> 
								<td id="sm_promo_msg_content" style="padding:0.5em;"> ';

									echo '<div style="padding-top: 0.3em;float: left;font-size:1.1em;">'. $sm_promo_msg .'</div>
												<div id="sm_promo_valid_msg">'. $sm_promo_cond .'</div>
												<form name="sm_klawoo_subscribe" action="#" method="POST" accept-charset="utf-8" style="padding-top:0.65em;"><br/>
													<input class="regular-text ltr" type="email" name="email" id="email" placeholder="Email" required="required" style="width:14em;height:1.75em;"/>
													<input type="checkbox" name="sm-gdpr-agree" id="sm-gdpr-agree" value="1" required="required" style="margin-left:0.5em;">
													<label for="es-gdpr-agree" style="margin-right:0.5em;">I have read and agreed to your <a href="https://www.storeapps.org/privacy-policy/" target="_blank">Privacy Policy</a>.</label>
													<input type="hidden" name="list" value="'. $sm_klawoo_list_id .'"/>
													<input type="hidden" name="valid_offer" value="'. $valid_offer .'"/>
													<input type="submit" name="submit" id="submit" class="button button-primary" value="Subscribe" style="font-size:1.1em;line-height:0em;margin-top:0;font-weight:bold;">
												</form>';							
							echo ' </td>
								</tr>
						</tbody> 
					</table> 
				</div>
				<script type="text/javascript">
					jQuery(function () {
						jQuery(document).on("click", "#sm_promo_msg_content .app_span", function() {
							var temp = jQuery("<input>");
							  jQuery("body").append(temp);
							  temp.val(jQuery(this).find("#sm_code").html()).select();
							  document.execCommand("copy");
							  temp.remove();
						});

						jQuery("form[name=sm_klawoo_subscribe]").submit(function (e) {
							e.preventDefault();
							
							params = jQuery("form[name=sm_klawoo_subscribe]").serializeArray();
							params.push( {name: "action", value: "sm_klawoo_subscribe" });
							
							jQuery.ajax({
								method: "POST",
								type: "text",
								url: "'.admin_url( 'admin-ajax.php' ).'",
								data: params,
								success: function(response) {
									var resp = jQuery(response);
									if (resp.find("h2").text() == "You\'re subscribed!") {
										jQuery("td[id=sm_promo_msg_content]").html("'.$sm_resp_msg.'");
										'. ( ( empty( $sm_valid_offer ) && empty( $valid_offer ) ) ? 'jQuery(".dashicons-awards").css("margin-right","0em");' : '' ) .'
									}
								}
							});
						});
					});
				</script>';
			}
}

// Function to handle SM In App Promo
function sm_add_promo_notices() {

	if ( !empty($_GET['page']) && ( 'smart-manager' === $_GET['page'] || 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] ) ) {
		
		$sm_dismiss_admin_notice = '';
		$sm_promo_msg = '';

		$sm_lite_activation_date = get_option( 'sm_lite_activation_date', false );
		$timezone_format = _x('Y-m-d H:i:s', 'timezone date format');
		$current_wp_date = date_i18n($timezone_format);

		if ( $sm_lite_activation_date === false ) {
			$sm_lite_activation_date = $current_wp_date;
			add_option('sm_lite_activation_date',$sm_lite_activation_date);
			add_option('_sm_update_418_date',$sm_lite_activation_date);
		} else {
			$sm_lite_activation_date = get_option( '_sm_update_418_date', false );
			if( false === $sm_lite_activation_date ) {
				$sm_lite_activation_date = $current_wp_date;
				add_option('_sm_update_418_date',$sm_lite_activation_date);
			}
		}

		$date_diff = floor(( strtotime($current_wp_date) - strtotime( $sm_lite_activation_date ) ) / (3600 * 24) );

		if ( 'smart-manager' === $_GET['page'] && empty( $_GET['sm_old'] ) ) {

			$sm_inline_update_count = get_option( 'sm_inline_update_count', 0 );

			$current_user = wp_get_current_user();
			if ( ! $current_user->exists() ) {
				return;
			}
			$sm_current_user_display_name = $current_user->display_name;
			if ( empty( $sm_current_user_display_name ) ) {
				$sm_current_user_display_name = 'there';
			}

			if( false !== get_option( 'sm_dismiss_admin_notice', false ) ) {
				delete_option( 'sm_dismiss_admin_notice' );
			}

			echo '<style type="text/css">
					.sm_design_notice {
						width: 45%;
						background-color: #FFF !important;
						margin-top: 1em !important;
						padding: 1em;
						box-shadow: 0 0 7px 0 rgba(0, 0, 0, .2);
						font-size: 1.1em;
						border: 0.25em solid #753d81;
						margin: 0 auto;
						text-align: center;
					}
					.sm_main_headline {
						font-size: 1.7em;
						font-weight: bold;
						padding-bottom: 1em;
						color: #753d81;
					}
					.sm_main_headline .dashicons.dashicons-awards {
						font-size: 1.2em;
						color: #b8860b;
						width: unset;
						line-height: 0.75em;
					}
					.sm_sub_headline {
						padding-bottom: 1em;
						font-size: 1.2em;
						color: #2d3e50;
						line-height: 1.3em;
					}
				</style>

				<div class="sm_design_notice">
					<div class="sm_container">
						<div class="sm_main_headline"><span class="dashicons dashicons-awards"></span>'. sprintf( __( 'Hey %1s, you just unlocked %2sOFF on Smart Manager Pro!', 'smart-manager-for-wp-e-commerce' ), $sm_current_user_display_name, __( "50%", "smart-manager-for-wp-e-commerce" ) ) .'</div>
						<div class="sm_sub_headline">' . sprintf( __( '%s to check Smart Manager Pro features and claim your discount.', 'smart-manager-for-wp-e-commerce' ), '<a href="'. admin_url( 'admin.php?page=smart-manager-pricing' ) .'">' . __( 'Click here', 'smart-manager-for-wp-e-commerce' ) . '</a>' ) .'</div>
					</div>
				</div>';

		} else if ( $date_diff > 14 || ( 'smart-manager' === $_GET['page'] && !empty( $_GET['sm_old'] ) ) ) {
			$sm_promo_msg = '<b>'. __('Sign up to get updates, insights & tips...', 'smart-manager');
			$sm_klawoo_list_id = 'eGXNNhOHHcRHSDOv3hmSsA';
			$sm_resp_msg = "<div style='padding-top: 0em;float: left;font-size:1.3em;'><b>". __('Thank you for Subscribing!!!') ."</b></div>";
			$sm_promo_cond = '';
			$sm_promo_hide_msg = __('No, I don\'t need help...', 'smart-manager');
			$valid_offer = 0;
			$sm_dismiss_admin_notice = get_option('sm_dismiss_admin_notice', false);
		}

		if ( ( !empty( $sm_promo_msg ) || ( !empty( $sm_valid_offer ) && !empty( $sm_resp_msg ) ) ) && empty( $sm_dismiss_admin_notice ) ) {

			$current_url = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];

			echo '<div id="sm_promo_msg" class="updated fade" style="display:block !important;background-color:#584079;color:#fff;border-left-color:#A3B745;"> 
					<table style="width:100%;"> 
						<tbody> 
							<tr>
								<td> 
									<span class="dashicons dashicons-awards" style="font-size:5em;color:#A3B745;margin-left: -0.2em;margin-right: 0.5rem;margin-bottom: 0.65em;"></span> 
								</td> 
								<td id="sm_promo_msg_content" style="padding:0.5em;"> ';

								if( !empty( $sm_valid_offer ) ) {
									echo $sm_resp_msg;
								} else {
									echo '<div style="padding-top: 0.3em;float: left;font-size:1.1em;">'. $sm_promo_msg .'</div>
												<div id="sm_promo_valid_msg">'. $sm_promo_cond .'</div>
												<form name="sm_klawoo_subscribe" action="#" method="POST" accept-charset="utf-8" style="padding-top:0.65em;"><br/>
													<input class="regular-text ltr" type="email" name="email" id="email" placeholder="Email" required="required" style="width:14em;height:1.75em;"/>
													<input type="checkbox" name="sm-gdpr-agree" id="sm-gdpr-agree" value="1" required="required" style="margin-left:0.5em;">
													<label for="es-gdpr-agree" style="margin-right:0.5em;">I have read and agreed to your <a href="https://www.storeapps.org/privacy-policy/" target="_blank">Privacy Policy</a>.</label>
													<input type="hidden" name="list" value="'. $sm_klawoo_list_id .'"/>
													<input type="hidden" name="valid_offer" value="'. $valid_offer .'"/>
													<input type="submit" name="submit" id="submit" class="button button-primary" value="Subscribe" style="font-size:1.1em;line-height:0em;margin-top:0;font-weight:bold;">
												</form>
												<div style="padding-top: 0.5em;font-size:0.8em;width:100%;float:left;">
													<div style="float:left;"><a href="https://www.storeapps.org/product/smart-manager" target=_storeapps> '. __( 'Learn more about Pro version', 'smart-manager' ) . '</a> ' . __( 'or take a', 'smart-manager' ) . ' <a href="https://Demo.storeapps.org/?demo=sm-woo" target=_livedemo> ' . __( 'Live Demo', 'smart-manager' ) . ' </a>	</div>
													<div style="float:right;"><a href="'.$current_url.'&sm_dismiss_admin_notice=1">'. $sm_promo_hide_msg .'</a></div>
											</div>';
								}
							
							echo ' </td>
								</tr>
						</tbody> 
					</table> 
				</div>
				<script type="text/javascript">
					jQuery(function () {
						jQuery(document).on("click", "#sm_promo_msg_content .app_span", function() {
							var temp = jQuery("<input>");
							  jQuery("body").append(temp);
							  temp.val(jQuery(this).find("#sm_code").html()).select();
							  document.execCommand("copy");
							  temp.remove();
						});

						jQuery("form[name=sm_klawoo_subscribe]").submit(function (e) {
							e.preventDefault();
							
							params = jQuery("form[name=sm_klawoo_subscribe]").serializeArray();
							params.push( {name: "action", value: "sm_klawoo_subscribe" });
							
							jQuery.ajax({
								method: "POST",
								type: "text",
								url: "'.admin_url( 'admin-ajax.php' ).'",
								data: params,
								success: function(response) {
									var resp = jQuery(response);
									if (resp.find("h2").text() == "You\'re subscribed!") {
										jQuery("td[id=sm_promo_msg_content]").html("'.$sm_resp_msg.'");
										'. ( ( empty( $sm_valid_offer ) && empty( $valid_offer ) ) ? 'jQuery(".dashicons-awards").css("margin-right","0em");' : '' ) .'
									}
								}
							});
						});
					});
				</script>';
		}
	}
}

function sm_include_file() {

	check_ajax_referer('smart-manager-security','security');

	$json_filename = $_REQUEST['file'];
	$base_path = WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) . 'sm/' . $json_filename . '.php';
	include_once ( $base_path );
}

function generate_db_index_queries() {
	global $wpdb;
	
	$index_queries = array ('add' => array (), 'remove' => array () );
	
	$index_queries ['add'] [] = "ALTER TABLE {$wpdb->prefix}posts
												ADD KEY `sm_idx_post_parent` ( `post_parent` ),
												ADD KEY `sm_idx_post_date` ( `post_date` )";
	$index_queries ['remove'] [] = "ALTER TABLE {$wpdb->prefix}posts
												DROP KEY `sm_idx_post_parent`,
												DROP KEY `sm_idx_post_date`";
	
	if (is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' )) {
		
		$index_queries ['add'] [] = "ALTER TABLE {$wpdb->prefix}wpsc_cart_contents 
													ADD KEY `sm_idx_cart_contents_purchaseid`( `purchaseid` ),
													ADD KEY `sm_idx_cart_contents_prodid`( `prodid` ),
													ADD KEY `sm_idx_cart_contents_name`( `name` )";
		$index_queries ['remove'] [] = "ALTER TABLE {$wpdb->prefix}wpsc_cart_contents 
													DROP KEY `sm_idx_cart_contents_purchaseid`,
													DROP KEY `sm_idx_cart_contents_prodid`,
													DROP KEY `sm_idx_cart_contents_name`";
		
		$index_queries ['add'] [] = "ALTER TABLE {$wpdb->prefix}wpsc_purchase_logs 
													ADD KEY `sm_idx_purchase_logs_userid` ( `user_ID` ),
													ADD KEY `sm_idx_purchase_logs_date` ( `date` )";
		$index_queries ['remove'] [] = "ALTER TABLE {$wpdb->prefix}wpsc_purchase_logs 
													DROP KEY `sm_idx_purchase_logs_userid`,
													DROP KEY `sm_idx_purchase_logs_date`";
		
		// ADD KEY `sm_idx_submited_form_data_value` ( `value` )

		$index_queries ['add'] [] = "ALTER TABLE {$wpdb->prefix}wpsc_submited_form_data 
													ADD KEY `sm_idx_submited_form_data_log_id` ( `log_id` )";
		$index_queries ['remove'] [] = "ALTER TABLE {$wpdb->prefix}wpsc_submited_form_data 
													DROP KEY `sm_idx_submited_form_data_log_id`";
	
	}
	
	return $index_queries;
}

function process_db_indexes($queries) {
	global $wpdb;
	
	foreach ( $queries as $query ) {
		$wpdb->query( $query );
	}
}

function smart_admin_notices() {
	if (! is_plugin_active( 'woocommerce/woocommerce.php' ) && ! is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' )) {
		echo '<div id="notice" class="error"><p>';
		echo '<b>' . __( 'Smart Manager', SM_TEXT_DOMAIN ) . '</b> ' . __( 'add-on requires', SM_TEXT_DOMAIN ) . ' <a href="https://www.storeapps.org/wpec/">' . __( 'WP e-Commerce', SM_TEXT_DOMAIN ) . '</a> ' . __( 'plugin or', SM_TEXT_DOMAIN ) . ' <a href="https://www.storeapps.org/woocommerce/">' . __( 'WooCommerce', SM_TEXT_DOMAIN ) . '</a> ' . __( 'plugin. Please install and activate it.', SM_TEXT_DOMAIN );
		echo '</p></div>', "\n";
	}
}

function smart_admin_scripts() {

	if( !empty( $_GET['landing-page'] ) || !( !empty( $_GET['page'] ) && 'smart-manager' === $_GET['page'] ) ) {
		return;
	}

	if ( !empty( $_GET['sm_old'] ) && ( 'woo' === $_GET['sm_old'] || 'wpsc' === $_GET['sm_old'] ) ) {
		if (file_exists( (dirname( __FILE__ )) . '/pro/sm.js' )) {
			wp_enqueue_script( 'sm_functions' );
		}
		wp_enqueue_script( 'sm_main' );
	} else {
		$GLOBALS['smart_manager_beta']->enqueue_admin_scripts();
	}
}

function smart_admin_styles() {

	if( !empty( $_GET['landing-page'] ) || !( !empty( $_GET['page'] ) && 'smart-manager' === $_GET['page'] ) ) {
		return;
	}

	if ( !empty( $_GET['sm_old'] ) && ( 'woo' === $_GET['sm_old'] || 'wpsc' === $_GET['sm_old'] ) ) {
		wp_enqueue_style( 'sm_main' );
	} else {
		$GLOBALS['smart_manager_beta']->enqueue_admin_styles();
	}

}

function sm_get_free_menu_position($start, $increment = 0.0001) {
	foreach ($GLOBALS['menu'] as $key => $menu) {
		$menus_positions[] = $key;
	}

	if (!in_array($start, $menus_positions)) return $start;

	/* the position is already reserved find the closet one */
	while (in_array($start, $menus_positions)) {
		$start += $increment;
	}
	return $start;
}

function smart_woo_add_modules_admin_pages() {
	global $wpdb, $current_user;

	if (!function_exists('wp_get_current_user')) {
		require_once (ABSPATH . 'wp-includes/pluggable.php'); // Sometimes conflict with SB-Welcome Email Editor
	}

	$current_user = wp_get_current_user(); // Sometimes conflict with SB-Welcome Email Editor

	if ( !isset( $current_user->roles[0] ) ) {
		$roles = array_values( $current_user->roles );
	} else {
		$roles = $current_user->roles;
	}

	//Fix for the client
	if ( !empty( $current_user->caps ) ) {
		$caps = array_keys($current_user->caps);
		$current_user_caps = $roles[0] = (!empty($caps)) ? $caps[0] : '';
	}

	$position = (string) sm_get_free_menu_position(56.00001);

	if( ( defined( 'SMBETAPRO' ) && SMBETAPRO === true ) || ( ( !empty( $current_user->roles[0] ) && $current_user->roles[0] == 'administrator' ) || ( !empty( $current_user_caps ) && $current_user_caps == 'administrator') ) ) {
		$page = add_menu_page( 'Smart Manager', 'Smart Manager','read', 'smart-manager', 'sm_admin_page', 'dashicons-performance', $position );

		if( defined( 'SMBETAPRO' ) && SMBETAPRO !== true ) {
			add_submenu_page( 'smart-manager', __( '<span class="sm_pricing_icon"> 🔥 </span> Go Pro', 'smart-manager-for-wp-e-commerce' ), __( '<span class="sm_pricing_icon"> 🔥 </span> Go Pro', 'smart-manager-for-wp-e-commerce' ), 'manage_options', 'smart-manager-pricing', 'sm_admin_page' );
		}

		if( ( defined( 'SMBETAPRO' ) && SMBETAPRO === true ) && ( ( !empty( $current_user->roles[0] ) && $current_user->roles[0] == 'administrator' ) || ( !empty( $current_user_caps ) && $current_user_caps == 'administrator') ) ) {
			add_submenu_page( 'smart-manager', __( 'Settings', 'smart-manager-for-wp-e-commerce' ),  __( 'Settings', 'smart-manager-for-wp-e-commerce' ), 'manage_options', 'smart-manager&sm-settings', 'sm_admin_page' );
		}

		add_submenu_page( 'smart-manager', __( 'Docs & Support', 'smart-manager-for-wp-e-commerce' ),  __( 'Docs & Support', 'smart-manager-for-wp-e-commerce' ), 'manage_options', 'smart-manager&landing-page=sm-about', 'sm_admin_page' );
	}	

	add_action( 'admin_print_scripts-' . $page, 'smart_admin_scripts' );
	add_action( 'admin_print_styles-' . $page, 'smart_admin_styles' );
}

function smart_wpsc_add_modules_admin_pages($page_hooks, $base_page) {
	global $wpdb, $current_user;

	if (!function_exists('wp_get_current_user')) {
		require_once (ABSPATH . 'wp-includes/pluggable.php'); // Sometimes conflict with SB-Welcome Email Editor
	}

	$current_user = wp_get_current_user(); // Sometimes conflict with SB-Welcome Email Editor
	
	$page = add_submenu_page( $base_page, 'Smart Manager', 'Smart Manager', 'edit_posts', 'smart-manager-wpsc', 'sm_admin_page' );
		
	$sm_action = (isset($_GET['action']) ? $_GET['action'] : '');

	if ($sm_action != 'sm-settings') { // not be include for settings page
		add_action( 'admin_print_scripts-' . $page, 'smart_admin_scripts' );
	}
		
	add_action( 'admin_print_styles-' . $page, 'smart_admin_styles' );
	$page_hooks [] = $page;
	return $page_hooks;
}

function smart_add_menu_access() {
	global $wpdb, $current_user;

	if (!function_exists('wp_get_current_user')) {
		require_once (ABSPATH . 'wp-includes/pluggable.php'); // Sometimes conflict with SB-Welcome Email Editor
	}

	$current_user = wp_get_current_user(); // Sometimes conflict with SB-Welcome Email Editor
		
		if ( !isset( $current_user->roles[0] ) ) {
			$roles = array_values( $current_user->roles );
		} else {
			$roles = $current_user->roles;
		}

		//Fix for the client
		if ( !empty( $current_user->caps ) ) {
			$caps = array_keys($current_user->caps);
			$current_user_caps = $roles[0] = (!empty($caps)) ? $caps[0] : '';
		}

	$query = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'sm_" . $roles [0] . "_dashboard'";
	$result_old = $wpdb->get_results( $query );

	$beta_dashboard_privileges = array();

	if( class_exists('Smart_Manager_Pro_Access_Privilege') ) {
		$option_nm = Smart_Manager_Pro_Access_Privilege::$access_privilege_option_start."".$roles [0]."".Smart_Manager_Pro_Access_Privilege::$access_privilege_option_end;
		$beta_dashboard_privileges = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->prefix}options WHERE option_name = %s", $option_nm ), 'ARRAY_A' );
	}

	if ( ( !empty( $result_old [0] ) && ! empty( $result_old [0]->option_value ) ) || !empty( $beta_dashboard_privileges ) || $current_user->roles [0] == 'administrator' 
		|| (!empty($current_user_caps) && $current_user_caps == 'administrator' ) ) { //modified cond for client fix

		add_filter( 'wpsc_additional_pages', 'smart_wpsc_add_modules_admin_pages', 10, 2 );
		add_action( 'admin_menu', 'smart_woo_add_modules_admin_pages' );
	}
}

add_action( 'admin_menu', 'smart_add_menu_access', 9 );

// if (is_admin()) {
	
// 	function smart_show_privilege_page() {
// 		$plugin_base = WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) . 'pro/';
// 		if (file_exists( $plugin_base . 'sm-privilege.php' )) {
// 			include_once ($plugin_base . 'sm-privilege.php');
// 			return;
// 		} else {
// 			$error_message = __( "A required Smart Manager file is missing. Can't continue. ", SM_TEXT_DOMAIN );
// 		}
// 	}
	
// 	function smart_add_privilege_page() {

// 		$page = add_submenu_page( 'options-general.php', 'Smart Manager', 'Smart Manager', 'activate_plugins', 'smart-manager-settings', 'smart_show_privilege_page' );
		
// 				$sm_action = (isset($_GET['action']) ? $_GET['action'] : '');
// 				if ($sm_action != 'sm-settings') { // not be include for settings page
// 						add_action( 'admin_print_scripts-' . $page, 'smart_admin_scripts' );
// 				}
// 		add_action( 'admin_print_styles-' . $page, 'smart_admin_styles' );
// 	}
// 	if (file_exists( (dirname( __FILE__ )) . '/pro/sm.js' ))
// 		add_action( 'admin_menu', 'smart_add_privilege_page', 11 );

// }

// function for removing the Help Tab
function remove_help_tab(){

	//condition to remove the help tab only from SM pages
	if( !empty( $_GET['page'] ) && ( 'smart-manager' === $_GET['page'] || 'smart-manager-woo' === $_GET['page'] || 'smart-manager-wpsc' === $_GET['page'] || 'smart-manager-settings' === $_GET['page'] ) ) {
		$screen = get_current_screen();
		$screen->remove_help_tabs();
	}
}

function smart_manager_upgrade_notifications() {

	?>
		<script type="text/javascript">

				jQuery(document).ready(function(){
					var current_url = "<?php echo admin_url('admin.php?&page=smart-manager'); ?>";
					jQuery('.request-filesystem-credentials-dialog-content').find('form').attr('action',current_url+'&action=sm_update_to_pro');

					jQuery('.request-filesystem-credentials-dialog-content').find('form').on('submit', function(e){
						e.preventDefault();

						jQuery( '#request-filesystem-credentials-dialog' ).hide();
						jQuery( 'body' ).removeClass( 'modal-open' );

						var params = jQuery(this).serializeArray();

						setTimeout(function(){ jQuery.ajax({
													type : 'POST',
													url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sm_include_file' : ajaxurl + '?action=sm_update_to_pro',
													dataType:"text",
													async: false,
													data: params,
													success: function(response) {
														jQuery('#sm_pro_to_lite_msg').removeClass('notice-error').addClass('notice-success').html('<div style="margin:.5em 0;"><?php echo __( 'Upgraded Successfully!!!', SM_TEXT_DOMAIN ); ?></div>');

														// Remove navigation prompt
														window.onbeforeunload = null;

														setTimeout(function(){ window.location.replace(current_url); }, 3000);
													}
												});
						 }, 1000);
						
					});
				});

				jQuery(document).on('click','#sm_update_to_pro_link',function(e){
					e.preventDefault();

					var current_url = "<?php echo admin_url('admin.php?&page=smart-manager'); ?>";
					var $modal = jQuery( '#request-filesystem-credentials-dialog' );
					jQuery('#sm_pro_to_lite_msg_hidden').html(jQuery('#sm_pro_to_lite_msg').html());
					jQuery('#sm_pro_to_lite_msg').html('<div style="margin:.5em 0;"><span style="margin-right:6px;color:#f56e28;animation:rotation 2s infinite linear;" class="dashicons dashicons-update"></span><?php echo __( 'Upgrading to Smart Manager Pro...', SM_TEXT_DOMAIN ); ?></div>');

					// Enable navigation prompt
					window.onbeforeunload = function() {
						return true;
					};

					setTimeout(function(){ jQuery.ajax({
								type : 'POST',
								url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sm_include_file' : ajaxurl + '?action=sm_update_to_pro',
								dataType:"text",
								async: false,
								success: function(response) {

									if( response == 'Success' ) {
										jQuery('#sm_pro_to_lite_msg').removeClass('notice-error').addClass('notice-success').html('<div style="margin:.5em 0;"><?php echo __( 'Upgraded Successfully!!!', SM_TEXT_DOMAIN ); ?></div>');
										
										// Remove navigation prompt
										window.onbeforeunload = null;
										
										setTimeout(function(){ window.location.replace(current_url); }, 3000);
									} else {
										jQuery( 'body' ).addClass( 'modal-open' );
										$modal.show();
										$modal.find( 'input:enabled:first' ).focus();
									}
								}
							});
					}, 1000);
					 
				});

				jQuery(document).on('click', '[data-js-action="close"], .notification-dialog-background',function(e){
					e.preventDefault();

					// Remove navigation prompt
					window.onbeforeunload = null;

					jQuery('#sm_pro_to_lite_msg').html(jQuery('#sm_pro_to_lite_msg_hidden').html());

					jQuery( '#request-filesystem-credentials-dialog' ).hide();
					jQuery( 'body' ).removeClass( 'modal-open' );

				});

	</script>

	<?php
		$is_pro_available = is_sa_smart_manager_pro_available();
		if( $is_pro_available === true ) { ?>

			<div id="sm_pro_to_lite_msg" class="update-message notice inline notice-error notice-alt" style="display:block !important;">
				<p>
					<?php
						printf( ('<b>' . __( 'Oops!', SM_TEXT_DOMAIN ) . '</b> ' . __( 'Seems like your Smart Manager plugin has downgraded to the Lite version. ', SM_TEXT_DOMAIN ) . " " . '<a id="sm_update_to_pro_link" href="">' . " " .__( 'Click here', SM_TEXT_DOMAIN ) . '</a> ')." ".__( 'to', SM_TEXT_DOMAIN )." <b>".__( 'convert it back to the Pro version.', SM_TEXT_DOMAIN )."</b>" );
					?>
				</p>
			</div>
			<div id="sm_pro_to_lite_msg_hidden" style="display:none;"></div>

			<?php

		} else if ( SMPRO === false && get_option('sm_dismiss_admin_notice') == '1') { ?>
				<div id="message" class="updated fade" style="display:block !important;">
					<p> <?php
							printf( ('<b>' . __( 'Important:', SM_TEXT_DOMAIN ) . '</b> ' . __( 'Upgrade to Pro to get features like \'<i>Manage any Custom Post Type</i>\' , \'<i>Batch Update</i>\' , \'<i>Export CSV </i>\' , \'<i>Duplicate Products</i>\' &amp; many more...', SM_TEXT_DOMAIN ) . " " . '<br /><a href="%1s" target=_storeapps>' . " " .__( 'Learn more about Pro version', SM_TEXT_DOMAIN ) . '</a> ' . __( 'or take a', SM_TEXT_DOMAIN ) . " " . '<a href="%2s" target=_livedemo>' . " " . __( 'Live Demo', SM_TEXT_DOMAIN ) . '</a>'), 'https://www.storeapps.org/product/smart-manager', 'http://demo.storeapps.org/?demo=sm-woo' );							
						?>
					</p>
				</div>
			<?php
		} 
}

function sm_admin_page() {

	if( !empty($_GET['landing-page']) ) {
		$GLOBALS['smart_manager_admin_welcome']->show_welcome_page();
	} else if( isset( $_GET['sm-settings'] ) && ( class_exists( 'Smart_Manager_Pro_Access_Privilege' ) && is_callable( array('Smart_Manager_Pro_Access_Privilege', 'render_access_privilege_settings') ) ) ) {
			if( !empty( $_GET['sm-old'] ) ) {
				$plugin_base = WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) . 'pro/';
				if (file_exists( $plugin_base . 'sm-privilege.php' )) {
					include_once ($plugin_base . 'sm-privilege.php');
					return;
				}
			} else {
				Smart_Manager_Pro_Access_Privilege::render_access_privilege_settings();
			}
	} else if( !empty( $_GET['page'] ) && 'smart-manager' === $_GET['page'] ) {

		if ( !empty( $_GET['sm_old'] ) && ( 'woo' === $_GET['sm_old'] || 'wpsc' === $_GET['sm_old'] ) ) {
			smart_show_console();
		} else {
			$GLOBALS['smart_manager_beta']->show_console_beta();
		}
	} else if( ( !empty( $_GET['page'] ) && 'smart-manager-pricing' === $_GET['page'] ) && ( class_exists( 'Smart_Manager_Pricing' ) && is_callable( array('Smart_Manager_Pricing', 'sm_show_pricing') ) ) ) {
		Smart_Manager_Pricing::sm_show_pricing();
	} else {
		wp_redirect( admin_url( 'admin.php?page=smart-manager' ) );
	}
}


function smart_show_console() {
	
	$current_user;

	if (!function_exists('wp_get_current_user')) {
		require_once (ABSPATH . 'wp-includes/pluggable.php'); // Sometimes conflict with SB-Welcome Email Editor
	}

	$current_user = wp_get_current_user(); // Sometimes conflict with SB-Welcome Email Editor

	if ( !isset( $current_user->roles[0] ) ) {
		$roles = array_values( $current_user->roles );
	} else {
		$roles = $current_user->roles;
	}

	//Fix for the client
	if ( !empty( $current_user->caps ) ) {
		$caps = array_keys($current_user->caps);
		$current_user_caps = $roles[0] = (!empty($caps)) ? $caps[0] : '';
	}

	if (WPSC_RUNNING === true) {
		$json_filename = (IS_WPSC37) ? 'json37' : 'json38';
	} else if (WOO_RUNNING === true) {
		$json_filename = 'woo-json';
	}
	// define( 'JSON_URL', SM_PLUGIN_DIRNAME . "/sm/$json_filename.php" );
	define( 'SM_JSON_URL', $json_filename );
	define( 'SM_ABS_WPSC_URL', WP_PLUGIN_DIR . '/wp-e-commerce' );
	define( 'SM_WPSC_NAME', 'wp-e-commerce' );
	
	$latest_version = smart_get_latest_version();
	$is_pro_updated = smart_is_pro_updated();
	

//	if (isset( $_GET ['action'] ) && $_GET ['action'] == 'sm-settings') {
//		smart_settings_page();
//	} else {
		$base_path = WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) . 'sm/';
		?>
<div class="wrap">
<style>
	div#TB_window {
		background: lightgrey;
	}
</style>    
<?php if ( SMPRO === true && function_exists( 'smart_manager_support_ticket_content' ) ) smart_manager_support_ticket_content();  ?>    

<div style = "margin-left: 0.25em;margin-top: 1.5em;width: 100%;">
	<span class = "sm-h2">
	<?php
		echo 'Smart Manager Old';
		echo '<sup style="vertical-align: super;color:red;">'.((SMPRO === true) ? 'Pro' : 'Lite').'</sup>';
		$before_plug_page = '';
		$after_plug_page = '';
		$plug_page = '';
	?>
	</span>
	<span style="float: right; line-height: 17px;">
	<?php
		if ( SMPRO === true && ! is_multisite() ) {
			$before_plug_page .= '<a href="admin.php?page=smart-manager-';
			$after_plug_page = '&action=sm-settings">Settings</a>';
			if (WPSC_RUNNING == true) {
				$plug_page = 'wpsc';
			} elseif (WOO_RUNNING == true) {
				$plug_page = 'woo';
			}
		} else {
			$before_plug_page = '';
			$after_plug_page = '';
			$plug_page = '';
		}

		$sm_beta = '';

		if( $current_user->roles [0] == 'administrator' || (!empty($current_user_caps) && $current_user_caps == 'administrator') || ( defined('SM_BETA_ACCESS') && SM_BETA_ACCESS === true ) ) {
			$sm_beta = '<a href="'. admin_url('admin.php?page='.$_GET['page']) .'" title="'. __( 'Switch back to Smart Manager', SM_TEXT_DOMAIN ) .'"> ' . __( 'Switch back to Smart Manager', SM_TEXT_DOMAIN ) .'</a> <sup style="vertical-align: super;color:red;">New</sup>';
			if ( defined('SMBETAPRO') && true === SMBETAPRO ) {
				$sm_beta .= ' | ';
			}
		}

		if ( SMPRO === true ) {
			if ( !wp_script_is( 'thickbox' ) ) {
				if ( !function_exists( 'add_thickbox' ) ) {
					require_once ABSPATH . 'wp-includes/general-template.php';
				}
				add_thickbox();
			}
			if (is_super_admin()) {
				$sm_beta .= '<a href="options-general.php?page=smart-manager&sm-settings&sm-old=1">Settings</a> ';
			}

		}

		printf ( __ ( '%1s' , SM_TEXT_DOMAIN) ,$sm_beta);
	?>
	</span>
</div>
<h6 align="right">
	<?php
		if (! $is_pro_updated) {
			$admin_url = SM_ADMIN_URL . "plugins.php";
			$update_link = __( 'An upgrade for Smart Manager Pro', SM_TEXT_DOMAIN ) . " " . $latest_version . " " . __( 'is available.', SM_TEXT_DOMAIN ) . " " . "<a align='right' href=$admin_url>" . __( 'Click to upgrade.', SM_TEXT_DOMAIN ) . "</a>";
			smart_display_notice( $update_link );
		}
	?>
</h6>
<!--<h6 align="right"> 
	<?php
// 		if (SMPRO === true) {
// 			$sm_license_key = smart_get_license_key();
// 			if ($sm_license_key == '') {
// 				if (! is_multisite()) {
// 					if (WPSC_RUNNING == true) {
// 						$plug_page = 'wpsc';
// 					} elseif (WOO_RUNNING == true) {
// 						$plug_page = 'woo';
// 					}
// 					smart_display_notice( __( 'Please enter your license key for automatic upgrades and support to get activated.', SM_TEXT_DOMAIN ) . '<a href=admin.php?page=smart-manager-' . $plug_page . '&action=sm-settings>' . __( 'Enter License Key', SM_TEXT_DOMAIN ) . '</a>' );
// 				}
// 			}
// 		}
		?>
<!-- </h6> -->
</div>

		<?php 

			if( function_exists('smart_manager_upgrade_notifications') ) {
				smart_manager_upgrade_notifications();
			}

		$error_message = '';
		if ((file_exists( WP_PLUGIN_DIR . '/wp-e-commerce/wp-shopping-cart.php' )) && (file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ))) {
			if (is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' )) {

							require_once (WPSC_FILE_PATH . '/wp-shopping-cart.php');
							// if (IS_WPSC37 || IS_WPSC38) {
							if  ( version_compare ( WPSC_VERSION, '3.8', '>=' )) {
								if (file_exists( $base_path . 'manager-console.php' )) {
										include_once ($base_path . 'manager-console.php');
										return;
								} else {
										$error_message = __( "A required Smart Manager file is missing. Can't continue.", SM_TEXT_DOMAIN );
								}
							} else {
								$error_message = __( 'Smart Manager currently works only with WP e-Commerce 3.7 or above.', SM_TEXT_DOMAIN );
							}
			} else if (is_plugin_active( 'woocommerce/woocommerce.php' )) {
							if (IS_WOO13) {
									$error_message = __( 'Smart Manager currently works only with WooCommerce 1.4 or above.', SM_TEXT_DOMAIN );
							} else {
								if (file_exists( $base_path . 'manager-console.php' )) {
										include_once ($base_path . 'manager-console.php');
										return;
								} else {
										$error_message = __( "A required Smart Manager file is missing. Can't continue.", SM_TEXT_DOMAIN );
								}
							}
			}
						else {
							$error_message = "<b>" . __( 'Smart Manager', SM_TEXT_DOMAIN ) . "</b> " . __( 'add-on requires', SM_TEXT_DOMAIN ) . " " .'<a href="https://www.storeapps.org/wpec/">' . __( 'WP e-Commerce', SM_TEXT_DOMAIN ) . "</a>" . " " . __( 'plugin or', SM_TEXT_DOMAIN ) . " " . '<a href="https://www.storeapps.org/woocommerce/">' . __( 'WooCommerce', SM_TEXT_DOMAIN ) . "</a>" . " " . __( 'plugin. Please install and activate it.', SM_TEXT_DOMAIN );
						}
					} else if (file_exists( WP_PLUGIN_DIR . '/wp-e-commerce/wp-shopping-cart.php' )) {
						if (is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' )) {
							require_once (WPSC_FILE_PATH . '/wp-shopping-cart.php');
							if (IS_WPSC37 || IS_WPSC38) {
								if (file_exists( $base_path . 'manager-console.php' )) {
										include_once ($base_path . 'manager-console.php');
										return;
								} else {
										$error_message = __( "A required Smart Manager file is missing. Can't continue.", SM_TEXT_DOMAIN );
								}
							} else {
								$error_message = __( 'Smart Manager currently works only with WP e-Commerce 3.7 or above.', SM_TEXT_DOMAIN );
							}
						} else {
								$error_message = __( 'WP e-Commerce plugin is not activated.', SM_TEXT_DOMAIN ) . "<br/><b>" . _e( 'Smart Manager', SM_TEXT_DOMAIN ) . "</b> " . _e( 'add-on requires WP e-Commerce plugin, please activate it.', SM_TEXT_DOMAIN );
						}
					} else if (file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' )) {
						if (is_plugin_active( 'woocommerce/woocommerce.php' )) {
							if (IS_WOO13) {
									$error_message = __( 'Smart Manager currently works only with WooCommerce 1.4 or above.', SM_TEXT_DOMAIN );
							} else {
								if (file_exists( $base_path . 'manager-console.php' )) {
									include_once ($base_path . 'manager-console.php');
									return;
								} else {
									$error_message = __( "A required Smart Manager file is missing. Can't continue.", SM_TEXT_DOMAIN );
								}
							}
						} else {
							$error_message = __( 'WooCommerce plugin is not activated.', SM_TEXT_DOMAIN ) . "<br/><b>" . __( 'Smart Manager', SM_TEXT_DOMAIN ) . "</b> " . __( 'add-on requires WooCommerce plugin, please activate it.', SM_TEXT_DOMAIN );
						}
					}
					else {
						$error_message = "<b>" . __( 'Smart Manager', SM_TEXT_DOMAIN ) . "</b> " . __( 'add-on requires', SM_TEXT_DOMAIN ) . " " .'<a href="https://www.storeapps.org/wpec/">' . __( 'WP e-Commerce', SM_TEXT_DOMAIN ) . "</a>" . " " . __( 'plugin or', SM_TEXT_DOMAIN ) . " " . '<a href="https://www.storeapps.org/woocommerce/">' . __( 'WooCommerce', SM_TEXT_DOMAIN ) . "</a>" . " " . __( 'plugin. Please install and activate it.', SM_TEXT_DOMAIN );
					}
		
		if ($error_message != '') {
			smart_display_err( $error_message );
			?>
</p>
</div>
<?php
		}
}

function smart_update_notice() {
	if (! function_exists( 'sm_get_download_url_from_db' ))
		return;
	$download_details = sm_get_download_url_from_db();
	$link = $download_details ['results'] [0]->option_value; //$plugins->response [SM_PLUGIN_BASE_NM]->package;
	

	if (! empty( $link )) {
		$current = get_site_transient( 'update_plugins' );
//		$r1 = smart_plugin_reset_upgrade_link( $current, $link );
//		set_site_transient( 'update_plugins', $r1 );
		echo $man_download_link = ' ' . __( 'Or', SM_TEXT_DOMAIN ) . ' ' . "<a href='$link'>" . __( 'click here to download the latest version.', SM_TEXT_DOMAIN ) . "</a>";
	}

}

function smart_display_err($error_message) {
	echo "<div id='notice' class='error'>";
	echo "<b>" . __( 'Error:', SM_TEXT_DOMAIN ) . "</b>" . $error_message;
	echo "</div>";
}

function smart_display_notice($notice) {
	echo "<div id='message' class='updated fade'>
			 <p>";
	echo _e( $notice, SM_TEXT_DOMAIN );
	echo "</p></div>";
}
