<?php
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div id="wrapper"style="background-color: #f5f5f5; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
        <tr>
            <td align="center" valign="top">
                <div id="template_header_image">
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container"
                       style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
                    <tr>
                        <td align="center" valign="top">
                            <!-- Header -->
                            <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header"
                                   style='background-color: #557da1; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;'>
                                <tr>
                                    <td>
                                        <h1 style='color: #ffffff; display: block; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; padding: 36px 48px; text-align: left; text-shadow: 0 1px 0 #7797b4; -webkit-font-smoothing: antialiased;'>
                                           <?php
                                           _e('New info request', 'yith-woocommerce-tab-manager');
                                           ?>
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                            <!-- End Header -->
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top">
                            <!-- Body -->
                            <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                <tr>
                                    <td valign="top" id="body_content" style="background-color: #fdfdfd;">
                                        <!-- Content -->
                                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                            <tr>
                                                <td valign="top" style="padding: 48px;">
                                                    <div id="body_content_inner" style='color: #737373; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;'>

                                                        <p style="margin: 0 0 16px;">
                                                            <?php
                                                            _e('Hi administrator, you have just received an email about the following product:','yith-woocommerce-tab-manager');
                                                            ?>
                                                        </p>

                                                        <h2 style='color: #557da1; display: block; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 16px 0 8px; text-align: left;'>
                                                            <a href="<?php echo $product_url;?>" style="color: #557da1; font-weight: normal; text-decoration: underline;">
                                                                <?php
                                                                echo $product->get_title();
                                                                ?>
                                                            </a>
                                                        </h2>

                                                        <h2 style='color: #557da1; display: block; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 16px 0 8px; text-align: left;'>
                                                            <?php _e('Details','yith-woocommerce-tab-manager');?>
                                                        </h2>
                                                        <p style="margin: 0 0 16px;">
                                                            <strong><?php _e( 'Name' , 'yith-woocommerce-tab-manager');?></strong>
                                                            <?php echo $name;?>
                                                        </p>

                                                        <p style="margin: 0 0 16px;">
                                                            <strong><?php _e( 'Email' ,'yith-woocommerce-tab-manager' );?></strong>
                                                            <?php
                                                            echo $from;
                                                            ?>
                                                        </p>
                                                        <p style="margin: 0 0 16px;">
                                                            <strong><?php _e( 'Website' , 'yith-woocommerce-tab-manager');?></strong>
                                                            <?php
                                                            echo $webaddr;
                                                            ?>
                                                        </p>
                                                        <p style="margin: 0 0 16px;">
                                                            <strong><?php _e( 'Description' , 'yith-woocommerce-tab-manager');?></strong>
                                                            <?php
                                                            echo $text;
                                                            ?>
                                                        </p>



                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- End Content -->
                                    </td>
                                </tr>
                            </table>
                            <!-- End Body -->
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

