<?php
defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| LOAD CONFIG
|-------------------------------------------------------------------------- 
*/
locate_template('/core/config.php', TRUE);

/*
|--------------------------------------------------------------------------
| LOAD SHARE FUNCTIONS
|-------------------------------------------------------------------------- 
*/
locate_template('/core/functions.php', TRUE);

/*
|--------------------------------------------------------------------------
| LOAD CLASSES
|-------------------------------------------------------------------------- 
*/
locate_template('/core/classes/class-tgm-plugin-activation.php', TRUE);
locate_template('/core/classes/class-fbtool.php', TRUE);

/*
|--------------------------------------------------------------------------
| LOAD CLIENT SIDE SCRIPT
|-------------------------------------------------------------------------- 
*/
locate_template('/core/settings/wp/wp-admin-script.php', TRUE);

/*
|--------------------------------------------------------------------------
| LOAD WORDPRESS SETTINGS
|-------------------------------------------------------------------------- 
*/
locate_template('/core/settings/wp/wp-ajax.php', TRUE);
locate_template('/core/settings/wp/wp-bulk-actions.php', TRUE);
locate_template('/core/settings/wp/wp-plugins.php', TRUE);
locate_template('/core/settings/wp/wp-ajax.php', TRUE);
locate_template('/core/settings/wp/wp-post-type.php', TRUE);

/*
|--------------------------------------------------------------------------
| LOAD WOOCOMMERCE SETTINGS
|-------------------------------------------------------------------------- 
*/
locate_template('/core/settings/wc/wc-admin-product-option.php', TRUE);
locate_template('/core/settings/wc/wc-translates.php', TRUE);

/*
|--------------------------------------------------------------------------
| LOAD ADVANCED CUSTOM FIELDS SETTINGS
|-------------------------------------------------------------------------- 
*/
locate_template('/core/settings/acf/acf-product-generate.php', TRUE);
locate_template('/core/settings/acf/acf-token-generate.php', TRUE);
locate_template('/core/settings/acf/acf-customer-generate.php', TRUE);
locate_template('/core/settings/acf/acf-function.php', TRUE);
locate_template('/core/settings/acf/acf-hook.php', TRUE);

/*
|--------------------------------------------------------------------------
| LOAD FACEBOOK SETTINGS
|-------------------------------------------------------------------------- 
*/
locate_template('/core/settings/fb/fb-tool.php', TRUE);