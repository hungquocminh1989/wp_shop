<?php
defined( 'ABSPATH' ) || exit;
if (!is_admin()) return;

add_action( 'woocommerce_product_options_pricing', 'wc_cost_product_field' );
function wc_cost_product_field() {
    woocommerce_wp_text_input( array( 'id' => 'org_price', 'class' => 'wc_input_price short', 'label' => __( 'Giá Vốn', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
    woocommerce_wp_text_input( array( 'id' => 'ctv_price', 'class' => 'wc_input_price short', 'label' => __( 'Giá Cộng Tác Viên', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
}

add_action( 'save_post', 'wc_cost_save_product' );
function wc_cost_save_product( $product_id ) {

     // stop the quick edit interferring as this will stop it saving properly, when a user uses quick edit feature
     /*if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce'))
        return;*/

    // If this is a auto save do nothing, we only save when update button is clicked
	/*if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;*/
	if ( isset( $_POST['ctv_price'] ) ) {
		if ( is_numeric( $_POST['ctv_price'] ) )
			update_post_meta( $product_id, 'ctv_price', $_POST['ctv_price'] );
	} else delete_post_meta( $product_id, 'ctv_price' );
	
	if ( isset( $_POST['org_price'] ) ) {
		if ( is_numeric( $_POST['org_price'] ) )
			update_post_meta( $product_id, 'org_price', $_POST['org_price'] );
	} else delete_post_meta( $product_id, 'org_price' );
}