<?php
/*
|--------------------------------------------------------------------------
| TRANSLATE WOOCOMMERCE STRINGS
|-------------------------------------------------------------------------- 
*/
add_filter( 'gettext', 'translate_woocommerce_strings', 999, 3 );
function translate_woocommerce_strings( $translated, $text, $domain ) {
	$translated = str_ireplace( 'View cart', 'Xem Giỏ Hàng', $translated );
	$translated = str_ireplace( 'Checkout', 'Thanh Toán', $translated );
	
	return $translated;
}