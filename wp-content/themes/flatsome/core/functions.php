<?php
defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| LOAD WORDPRESS FUNCTIONS
|-------------------------------------------------------------------------- 
*/
if(is_admin() === TRUE){
	if (function_exists('unzip_file') === FALSE){ 
	    require_once ABSPATH . '/wp-admin/includes/file.php' ;
		WP_Filesystem();
	}
	if (function_exists('is_plugin_active') === FALSE){ 
	    require_once ABSPATH . '/wp-admin/includes/plugin.php' ;
	}
}

/*
|--------------------------------------------------------------------------
| LOAD DEFINE FUNCTIONS
|-------------------------------------------------------------------------- 
*/
if(is_admin() === TRUE){
	function repoUpdateCustomeField( $post_id, $field_name, $value = '' )
	{
	    if ( empty( $value ) OR ! $value )
	    {
	        delete_post_meta( $post_id, $field_name );
	    }
	    elseif ( ! get_post_meta( $post_id, $field_name ) )
	    {
	        add_post_meta( $post_id, $field_name, $value );
	    }
	    else
	    {
	        update_post_meta( $post_id, $field_name, $value );
	    }
	}
}

function repoDebugVar($object){
	echo "<pre>";
	var_dump($object);
}