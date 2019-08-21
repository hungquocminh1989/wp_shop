<?php

add_action('wp_ajax_ywtm_sendermail', 'ywtm_sendermail');
add_action('wp_ajax_nopriv_ywtm_sendermail', 'ywtm_sendermail');


if( !function_exists( 'ywtm_sendermail' ) ){
    /**
     * Send email
     * @author YITHEMES
     * @since 1.0.0
     * @use wp_ajax_ywtm_sendermail,wp_ajax_nopriv_ywtm_sendermail
     */
    function ywtm_sendermail(){

        if( isset( $_POST['ywtm_bot'] ) && !empty( $_POST['ywtm_bot'] ) )
            return;

        if( isset( $_POST['ywtm_action'] ) && $_POST['ywtm_action']=='ywtm_sendermail' && wp_verify_nonce( $_REQUEST['_wpnonce'], 'ywtm-sendmail' ) ) {

            $to         =   get_option('admin_email');
            $from       =  isset( $_POST['ywtm_email_contact_field'] )  ?   stripslashes( $_POST['ywtm_email_contact_field'] )      :   '';
            $name       =  isset( $_POST['ywtm_name_contact_field'] )   ?   stripslashes( $_POST['ywtm_name_contact_field'] )       :   '';
            $webaddr    =  isset( $_POST['ywtm_webaddr_contact_field'] )?   stripslashes( $_POST['ywtm_webaddr_contact_field'] )    :   '';
            $subj       =  isset( $_POST['ywtm_subj_contact_field'] )   ?   stripslashes( $_POST['ywtm_subj_contact_field'] )       :   '';
            $text       =  isset( $_POST['ywtm_info_contact_field'] )  ?   stripslashes( $_POST['ywtm_info_contact_field'] )       :   '';

            /*OTHER INFORMATION*/

            $product_id =   stripslashes( $_POST['ywtm_product_id'] );

            $product    = new WC_Product( $product_id );

            $errors='';

            /*Check error*/

            if ( isset( $_POST['ywtm_req_name'] ) )
                $errors.=check_error('name', $name);

            if( isset( $_POST['ywtm_req_email'] ) )
                $errors.=check_error('email', $from);

            if( isset( $_POST['ywtm_req_subj'] ) )
                $errors.=check_error('subj', $subj);

            if( isset( $_POST['ywtm_req_webaddr'] ) )
                $errors.=check_error('website', $webaddr);

            if( isset( $_POST['ywtm_req_info'] ) )
                $errors.=check_error('mess', $text);


            if( !empty( $errors ) )
            {
                echo $errors;
                exit();
            }
            else
            {
                $subject = !empty ( $subj ) ? $subj : __('Request from ', 'yith-woocommerce-tab-manager').$name;
                $headers = __('From: ','yith-woocommerce-tab-manager'). $to . "\r\n" .
                    'Reply-To: ' . $from . "\r\n";

                $product_url = get_permalink( $product_id );



                ob_start();
                  include("email/yith-tab-manager-email-template.php");
                 $html = ob_get_clean();

                add_filter( 'wp_mail_content_type', create_function( '$content_type', "return 'text/html';" ) );
                add_filter( 'wp_mail_from',         create_function( '$from', "return '$from';" ) );
                add_filter( 'wp_mail_from_name',    create_function( '$from', "return '$name';" ) );

              $send =  wp_mail( $to, $subject, $html, $headers  );

                if ($send )
                    echo "<span class='message_send'>".__( 'Message sent!' , 'yith-woocommerce-tab-manager' )."</span>";
                else
                    echo "<span class='error_message'>".__( 'Error, Please Try Again!' , 'yith-woocommerce-tab-manager' )."<br /></span>";
                exit();


            }

        }
    }

}

/**Check contact form and print the errors
 * @param $field
 * @param $value
 * @return string
 */
function check_error( $field, $value ) //email,name,subj,mess,website
{
    $error='';
    switch ( $field ){

        case 'email' :

            if( !isset( $value ) || empty( $value ) )
                $error  =   '<div class="error_message">'.__('Email is required!', 'yith-woocommerce-tab-manager').'</div>';

            else if(! filter_var ( $value, FILTER_VALIDATE_EMAIL ) )
                $error  =   '<div class="error_message">'.__('Email not valid!', 'yith-woocommerce-tab-manager').'</div>';

            break;

        case 'name' :

            if( !isset( $value ) || empty( $value ) )
                $error='<div class="error_message">'.__('Name is required!', 'yith-woocommerce-tab-manager').'</div>';

            break;

        case 'subj' :
            if( !isset( $value ) || empty( $value ) )
                $error='<div class="error_message">'.__('Subject is required!', 'yith-woocommerce-tab-manager').'</div>';
            break;

        case 'website' :
            if( !isset( $value ) || empty( $value ) )
                $error='<div class="error_message">'.__('Website is required!', 'yith-woocommerce-tab-manager').'</div>';
            break;
        case 'mess' :
            if( !isset( $value ) || empty( $value ) )
                $error='<div class="error_message">'.__('Message is required!', 'yith-woocommerce-tab-manager').'</div>';
            break;
    }

    return $error;
}