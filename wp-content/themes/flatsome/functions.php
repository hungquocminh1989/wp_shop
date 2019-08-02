<?php
/*
|--------------------------------------------------------------------------
| LOAD CORE
|-------------------------------------------------------------------------- 
*/
locate_template('/core/init.php', TRUE);

/*
|--------------------------------------------------------------------------
| START HERE
|-------------------------------------------------------------------------- 
*/

//Disable classic editor
//add_filter( 'use_block_editor_for_post', '__return_false' );

/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';

/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */
