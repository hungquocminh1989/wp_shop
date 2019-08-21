<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

class Smart_Manager_Pricing {

	public static function sm_show_pricing() {
		?>
		<style type="text/css">
			.update-nag {
				display: none;
			}
			.wrap.about-wrap.sm {
				margin: 25px 70px 0 70px;
				max-width: 100%;
			}
			.sm_main_heading {
				font-size: 2em;
				background-color: #D2E8FE;
				color: #7F7F8E;
				text-align: center;
				font-weight: 500;
				margin: auto;
				padding-top: 1em;
				padding-bottom: 1em;
				max-width: 1375px;
			}
			.sm_discount_code {
				color: #2D9FE3;
				font-weight: 600;
				font-size: 1.1em;
			}
			.sm_sub_headline {
				font-size: 1.6em;
				font-weight: 400;
				color: #008cddc7;
				text-align: center;
				line-height: 1.5em;
				margin: 0 auto 1em;
			}
			.sm_row {
				padding: 1em !important;
				margin: 1.5em !important;
				clear: both;
				position: relative;
			}
			.sm_price_column_container {
				display: -webkit-box;
				display: -webkit-flex;
				display: -ms-flexbox;
				display: flex;
				max-width: 1190px;
				margin-right: auto;
				margin-left: auto;
				margin-top: 3em;
				padding: 2em;
			}
			.sm_column {
				padding: 2em;
				margin: 0 1em;
				background-color: #fff;
				border: 1px solid rgba(0, 0, 0, 0.1);
				text-align: center;
				color: rgba(0, 0, 0, 0.75);
			}
			.column_one_fourth {
				width: 40%;
				border-radius: 3px;
				margin: unset !important;
			}
			.sm_last {
				margin-right: 0;
			}
			.sm_price {
				margin: 1.5em 0;
				color: #1e73be;
			}
			.sm_button {
				color: #FFFFFF !important;
				padding: 15px 32px;
				text-align: center;
				text-decoration: none;
				display: inline-block;
				font-size: 16px;
				font-weight: 500;
				margin: 2em 2px 1em 2px;
				cursor: pointer;
			}
			.sm_button.green {
				background: #4fad43;
				border-color: #4fad43;
			}
			.sm_button.green:hover {
				background: #00870c;
				border-color: #00870c;
			}
			.sm_discount_amount {
				font-size: 1.3em !important;
			}
			.dashicons.dashicons-yes {
				color: green;
				font-size: 2em;
			}
			.dashicons.dashicons-no-alt {
				color: #ed4337;
				font-size: 2em;
			}
			.dashicons.dashicons-yes.yellow {
				color: #BDB76B;
				line-height: unset;
			}
			.dashicons.dashicons-awards,
			.dashicons.dashicons-testimonial {
				line-height: 1.6 !important;
				color: darkgoldenrod;
			}
			.sm_license_name {
				font-size: 1.1em !important;
				color: #1a72bf !important;
				font-weight: 500 !important;
			}
			.sm_old_price {
				font-size: 1.3em;
				color: #ed4337;
				vertical-align: top;
			}
			.sm_new_price {
				font-size: 1.6em;
				padding-left: 0.2em;
				font-weight: 400;
			}
			.sm_most_popular {
				position: absolute;
				right: 0px;
				top: -39px;
				background-color: #41495b;
				background-color: #596174;
				text-align: center;
				color: white;
				padding: 10px;
				font-size: 18px;
				border-top-right-radius: 4px;
				border-top-left-radius: 4px;
				font-weight: 500;
				width: 275px;
			}
			#sm-testimonial {
				text-align: center;
			}
			#sm-jeff-testimonial {
				width: 50%;
				margin: 0 auto;
				background-color: #FCFEE9;
			}
			#sm-jeff-testimonial img {
				width: 12% !important;
			}
			.sm_testimonial_headline {
				margin: 0.6em 0 !important;
				font-weight: 500 !important;
				font-size: 1.5em !important;
			}
			.sm_testimonial_text {
				text-align: left;
				font-size: 1.2em;
				line-height: 1.6;
				padding: 1em;
			}
			table.sm_feature_table {
				width: 70%;
				margin-left: 15%;
				margin-right: 15%;
			}
			table.sm_feature_table th,
			table.sm_feature_table tr,
			table.sm_feature_table td,
			table.sm_feature_table td span {
				padding: 0.5em !important;
				text-align: center !important;
				background-color: transparent !important;
				vertical-align: middle !important;
			}
			table.sm_feature_table,
			table.sm_feature_table th,
			table.sm_feature_table tr,
			table.sm_feature_table td {
				border: 1px solid #eaeaea;
			}
			table.sm_feature_table.widefat th,
			table.sm_feature_table.widefat td {
				color: #515151;
			}
			table.sm_feature_table th {
				font-weight: bolder !important;
				font-size: 1.3em;
			}
			table.sm_feature_table tr td {
				font-size: 15px;
			}
			table.sm_feature_table th.sm_features {
				background-color: #F4F4F4 !important;
				color: #A1A1A1 !important;
			}
			table.sm_feature_table th.sm_free_features {
				background-color: #F7E9C8 !important;
				color: #D39E22 !important;
			}
			table.sm_feature_table th.sm_pro_features {
				background-color: #DCDDFC !important;
				color: #4D51F4 !important;
			}
			table.sm_feature_table td.sm_feature_name {
				text-transform: capitalize;
			}
			table.sm_feature_table td.sm_free_feature_name {
				background-color: #FCF7EC !important;
				padding: 2em !important;
			}
			table.sm_feature_table td.sm_pro_feature_name {
				background-color: #F4F5FD !important;
				padding: 2em !important;
			}
			#sm_product_page_link {
				text-align: center;
				font-size: 1.2em;
				margin-top: 2em;
			}
			.update-nag , .error, .updated{ 
				display:none; 
			}
		</style>

		<div class="wrap about-wrap sm">
			<div class="sm_row" id="sm-pricing">
				<div class="sm_main_heading"><?php echo sprintf( __( '🎉 Congratulations! You just unlocked %s on Smart Manager Pro 🎉 ', 'smart-manager-for-wp-e-commerce' ), '<span class="sm_discount_code">' . __( '50% off', 'smart-manager-for-wp-e-commerce' ) . '</span>' ); ?></div>
				<div class="sm_price_column_container">
					<div class="sm_column column_one_fourth">
						<span class="sm_plan"><h4 class="sm_license_name"><?php echo __( '1 site (Annual)', 'smart-manager-for-wp-e-commerce' ); ?></h4></span>
						<span class="sm_price">
							<strike class="sm_old_price"><?php echo __( '$149/year', 'smart-manager-for-wp-e-commerce' ); ?></strike>
							<b class="sm_new_price"><?php echo __( '$75/year', 'smart-manager-for-wp-e-commerce' ); ?></b>
						</span>
						<a href="https://www.storeapps.org/?buy-now=18694&qty=1&coupon=sm-50off&page=722&with-cart=1&utm_source=sm&utm_medium=in_app_pricing&utm_campaign=single_annual" target="_blank" rel="noopener" class="sm_button green"><?php echo sprintf( __( 'Get %s off', 'smart-manager-for-wp-e-commerce' ), '<span class="sm_discount_amount">' . __( '50%', 'smart-manager-for-wp-e-commerce' ) . '</span>' ); ?></a>
					</div>
					<div class="sm_column column_one_fourth sm_lifetime_price" style="position: relative; display: inline-block;">
						<span class="sm_plan"><h4 class="sm_license_name"><?php echo __( '1 site (Lifetime)', 'smart-manager-for-wp-e-commerce' ); ?></h4></span>
						<span class="sm_price">
							<strike class="sm_old_price"><?php echo __( '$449', 'smart-manager-for-wp-e-commerce' ); ?></strike>
							<b class="sm_new_price"><?php echo __( '$225', 'smart-manager-for-wp-e-commerce' ); ?></b>
						</span>
						<div>
							<span class="sm_most_popular"><?php echo __( 'MOST POPULAR', 'smart-manager-for-wp-e-commerce' ); ?></span>
							<a href="https://www.storeapps.org/?buy-now=86835&qty=1&coupon=sm-50off-l&page=722&with-cart=1&utm_source=sm&utm_medium=in_app_pricing&utm_campaign=single_lifetime" target="_blank" rel="noopener" class="sm_button green"><?php echo sprintf( __( 'Get %s off', 'smart-manager-for-wp-e-commerce' ), '<span class="sm_discount_amount">' . __( '50%', 'smart-manager-for-wp-e-commerce' ) . '</span>' ); ?></a>
						</div>
					</div>
					<div class="sm_column column_one_fourth">
						<span class="sm_plan"><h4 class="sm_license_name"><?php echo __( '5 sites (Annual)', 'smart-manager-for-wp-e-commerce' ); ?></h4></span>
						<span class="sm_price">
							<strike class="sm_old_price"><?php echo __( '$179/year', 'smart-manager-for-wp-e-commerce' ); ?></strike>
							<b class="sm_new_price"><?php echo __( '$90/year', 'smart-manager-for-wp-e-commerce' ); ?></b>
						</span>
						<a href="https://www.storeapps.org/?buy-now=18693&qty=1&coupon=sm-50off&page=722&with-cart=1&utm_source=sm&utm_medium=in_app_pricing&utm_campaign=multi_annual" target="_blank" rel="noopener" class="sm_button green"><?php echo sprintf( __( 'Get %s off', 'smart-manager-for-wp-e-commerce' ), '<span class="sm_discount_amount">' . __( '50%', 'smart-manager-for-wp-e-commerce' ) . '</span>' ); ?></a>
					</div>
					<div class="sm_column column_one_fourth sm_last sm_lifetime_price">
						<span class="sm_plan"><h4 class="sm_license_name"><?php echo __( '5 sites (Lifetime)', 'smart-manager-for-wp-e-commerce' ); ?></h4></span>
						<span class="sm_price">
							<strike class="sm_old_price"><?php echo __( '$549', 'smart-manager-for-wp-e-commerce' ); ?></strike>
							<b class="sm_new_price"><?php echo __( '$275', 'smart-manager-for-wp-e-commerce' ); ?></b>
						</span>
						<a href="https://www.storeapps.org/?buy-now=86836&qty=1&coupon=sm-50off-l&page=722&with-cart=1&utm_source=sm&utm_medium=in_app_pricing&utm_campaign=multi_lifetime" target="_blank" rel="noopener" class="sm_button green"><?php echo sprintf( __( 'Get %s off', 'smart-manager-for-wp-e-commerce' ), '<span class="sm_discount_amount">' . __( '50%', 'smart-manager-for-wp-e-commerce' ) . '</span>' ); ?></a>
					</div>
				</div>
			</div>
			<div class="sm_row" id="sm-testimonial">
				<div class="sm_sub_headline"><span class="dashicons dashicons-testimonial"></span><?php echo __( ' Read what Jeff has to say about Smart Manager Pro:', 'smart-manager-for-wp-e-commerce' ); ?></div>
				<div class="sm_column" id="sm-jeff-testimonial">
					<img src="<?php echo SM_BETA_IMG_URL ?>jeff-smith.png" alt="Jeff" />
					<h3 class="sm_testimonial_headline"><?php echo __( 'I would happily pay five times for this product!', 'smart-manager-for-wp-e-commerce' ); ?></h3>
					<div class="sm_testimonial_text">
						<?php echo __( 'What really sold me on Smart Manager Pro was Batch Update. My assistant does not have to do any complex math now (earlier, I always feared she would make mistakes)! With Smart Manager, she has more free time at hand, so I asked her to set up auto responder emails. The response was phenomenal. Repeat sales were up by 19.5%.', 'smart-manager-for-wp-e-commerce' ); ?>
					</div>
				</div>
			</div>
			<div class="sm_row" id="sm_comparison_table">
				<div class="sm_sub_headline"><span class="dashicons dashicons-awards"></span><?php echo __( ' Get tons of more features with Smart Manager Pro!', 'smart-manager-for-wp-e-commerce' ); ?></div>
				<table class="sm_feature_table wp-list-table widefat fixed">
					<thead>
						<tr>
							<th class="sm_features">
								<?php echo esc_html__( 'Features', 'smart-manager-for-wp-e-commerce' ); ?>
							</th>
							<th class="sm_free_features">
								<?php echo esc_html__( 'Free', 'smart-manager-for-wp-e-commerce' ); ?>
							</th>
							<th class="sm_pro_features">
								<?php echo esc_html__( 'Pro', 'smart-manager-for-wp-e-commerce' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Supported Post Types', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-yes yellow'></span><br>
								<?php echo __( '5 POST TYPES', 'smart-manager-for-wp-e-commerce' ); ?><br>
								<?php echo __( 'WordPress: Posts', 'smart-manager-for-wp-e-commerce' ); ?><br>
								<?php echo __( 'WooCommerce: Products, Variations, Orders, Coupons', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span><br>
								<strong>
									<?php echo __( 'ALL POST TYPES', 'smart-manager-for-wp-e-commerce' ); ?><br>
									<?php echo __( 'Everything in Lite +', 'smart-manager-for-wp-e-commerce' ); ?>
								</strong><br>
								<?php echo __( 'WordPress: Pages, Media, Users', 'smart-manager-for-wp-e-commerce' ); ?><br>
								<?php echo __( 'WooCommerce Post Types: Customers, Subscriptions, Smart Offers', 'smart-manager-for-wp-e-commerce' ); ?>
								<?php echo __( 'and all your WordPress custom post types and their custom fields', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Inline editing', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-yes yellow'></span><br>
								<?php echo __( 'Only 3 records at a time', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span><br>
								<?php echo __( 'Unlimited records', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Add and delete records', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Customizable Columns', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Simple Search', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Advanced Search', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-yes yellow'></span><br>
								<?php echo __( 'Only using AND operator', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span><br>
								<?php echo __( 'Using AND + OR operator', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<strong><?php echo __( 'Bulk / Batch Update', 'smart-manager-for-wp-e-commerce' ); ?></strong>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-no-alt'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span><br>
								<?php echo __( 'Set to, Append, Prepend, Increase / Decrease by %, Increase / Decrease by number, Set datetime to, Set date to, Set time to, Upload images and many more...', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Export all / filtered records as CSV', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-no-alt'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Duplicate single / multiple / all records for a particular post type  in a single click', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-no-alt'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Manage WordPress User roles', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-no-alt'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Print packing slips for WooCommerce orders in bulk', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-no-alt'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'View Customer Lifetime Value (LTV)', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<span class='dashicons dashicons-no-alt'></span>
							</td>
							<td class="sm_pro_feature_name">
								<span class='dashicons dashicons-yes'></span>
							</td>
						</tr>
						<tr>
							<td class="sm_feature_name">
								<?php echo __( 'Help', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_free_feature_name">
								<?php echo __( 'WP forum', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
							<td class="sm_pro_feature_name">
								<?php echo __( 'Priority support via Email', 'smart-manager-for-wp-e-commerce' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="sm_row" id="sm_product_page_link">
				<?php echo sprintf( __( 'Want to know more about Smart Manager Pro? %s.', 'smart-manager-for-wp-e-commerce' ), '<a style="color: #008cddc7;" target="_blank" href="https://www.storeapps.org/product/smart-manager/?utm_source=sm&utm_medium=in_app_pricing&utm_campaign=sm_know">' . __( 'Click here', 'smart-manager-for-wp-e-commerce' ) . '</a>' ); ?>
			</div>
		</div>
		<?php
	}
}

new Smart_Manager_Pricing();
