<?php

use TTHQ\WPAPP\Lib\PayPal\PayPal_PPCP_Config;

// Handles the PayPal Payment Accept plugin's settings menu
function paypal_payment_add_option_pages() {
	if ( function_exists( 'add_options_page' ) ) {
		add_options_page( 'WP Paypal Payment Accept', 'WP PayPal Payment', 'manage_options', __FILE__, 'paypal_payment_options_page' );
	}
}
// Insert the paypal_payment_add_option_pages in the 'admin_menu'
add_action( 'admin_menu', 'paypal_payment_add_option_pages' );

function paypal_payment_options_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to access this settings page.' );
	}

	echo '<div class=wrap>';
	echo '<h2>Easy Accept Payments via PayPal Settings v' . WPAPP_PLUGIN_VERSION . '</h2>';

    $wpapp_plugin_tabs = array(
        'wordpress-easy-paypal-payment-or-donation-accept-plugin/admin/wpapp_admin_menu.php' => __('General Settings', 'wp-easy-paypal-payment-accept'),
        'wordpress-easy-paypal-payment-or-donation-accept-plugin/admin/wpapp_admin_menu.php&action=ppcp-settings' => __('PayPal PPCP (New API)', 'wp-easy-paypal-payment-accept'),
    );

    $current = "";
    if (isset($_GET['page'])) {
        $current = sanitize_text_field($_GET['page']);
        if (isset($_GET['action'])) {
            $current .= "&action=" . sanitize_text_field($_GET['action']);
        }
    }
    $content = '';
    $content .= '<h2 class="nav-tab-wrapper">';
    foreach ($wpapp_plugin_tabs as $location => $tabname) {

        if ($current == $location) {
            $class = ' nav-tab-active';
        } else {
            $class = '';
        }
        $content .= '<a class="nav-tab' . esc_attr($class) . '" href="?page=' . esc_attr($location) . '">' . esc_attr($tabname) . '</a>';
    }
    $content .= '</h2>';
    echo $content;

	echo '<div id="poststuff"><div id="post-body">';

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'ppcp-settings':
                include_once( WPAPP_PLUGIN_PATH . 'admin/wpapp_admin_menu_ppcp_settings.php');
                new WPAPP_PPCP_settings_page();
                break;
            default:
                wpapp_show_general_settings_menu();
                break;
        }
    } else {
        wpapp_show_general_settings_menu();
    }

	echo '</div></div>';// <!-- end of .poststuff and post-body -->
	echo '</div>'; //<!-- end of .wrap -->

}

function wpapp_show_general_settings_menu() {
	?>
	<div class="wpapp-grey-box">
		For usage documentation and updates, please visit the plugin page at the following URL:<br />
		<a href="https://www.tipsandtricks-hq.com/wordpress-easy-paypal-payment-or-donation-accept-plugin-120"
			target="_blank">https://www.tipsandtricks-hq.com/wordpress-easy-paypal-payment-or-donation-accept-plugin-120</a>
	</div>

	<div class="postbox">
			<h3 class="hndle"><label for="title">Plugin Usage</label></h3>
			<div class="inside">
				<p>To begin utilizing the plugin, please follow these steps:</p>
				<ol>
					<li>Navigate to the 'PayPal PPCP' tab in the settings to set up your PayPal API credentials.</li>
					<li>Adjust the following settings as needed, then insert the shortcode [wp_paypal_payment] into a post, page, or sidebar widget where you wish to display the payment button.</li>
					<li>For more versatility, you can use the following mentioned shortcode along with custom parameter options to incorporate various payment widgets, each with its unique configuration.
						<a href="https://www.tipsandtricks-hq.com/wordpress-easy-paypal-payment-or-donation-accept-plugin-120#shortcode_with_custom_parameters"
							target="_blank">View shortcode documentation</a>
					</li>
				</ol>
			</div>
	</div><!-- end of postbox -->
	<?php
	
	// Reset the debug log file (if requested)
    if(isset($_GET['wpapp-action']) && $_GET['wpapp-action'] == 'wpapp_clear_log') {
        // Reset the debug log file
        if(wpapp_reset_logfile()){
            echo '<div id="message" class="updated fade"><p><strong>Debug log file has been reset!</strong></p></div>';
        }
        else{
            echo '<div id="message" class="updated fade"><p><strong>Debug log file could not be reset!</strong></p></div>';
        }
    }	

	// Update the plugin settings
	if ( isset ( $_POST['info_update'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, 'wp_accept_pp_payment_settings_update' ) ) {
			wp_die( 'Error! Nonce Security Check Failed! Go back to settings menu and save the settings again.' );
		}
		$value1 = filter_input( INPUT_POST, 'wp_pp_payment_value1', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$value2 = filter_input( INPUT_POST, 'wp_pp_payment_value2', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$value3 = filter_input( INPUT_POST, 'wp_pp_payment_value3', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$value4 = filter_input( INPUT_POST, 'wp_pp_payment_value4', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$value5 = filter_input( INPUT_POST, 'wp_pp_payment_value5', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$value6 = filter_input( INPUT_POST, 'wp_pp_payment_value6', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		update_option( 'wp_paypal_widget_title_name', sanitize_text_field( stripslashes( $_POST["wp_paypal_widget_title_name"] ) ) );

		if(isset( $_POST["wp_pp_payment_email"] )){
			//Don't update the email address on the new version of the plugin. So it preserves the old paypal email setting.
			$paypal_email = isset( $_POST["wp_pp_payment_email"] ) ? sanitize_email( $_POST["wp_pp_payment_email"] ) : '';
			update_option( 'wp_pp_payment_email', $paypal_email );
		}

		update_option( 'paypal_payment_currency', sanitize_text_field( $_POST["paypal_payment_currency"] ) );
		update_option( 'wp_pp_payment_subject', sanitize_text_field( stripslashes( $_POST["wp_pp_payment_subject"] ) ) );
		update_option( 'wp_pp_payment_item1', sanitize_text_field( stripslashes( $_POST["wp_pp_payment_item1"] ) ) );
		update_option( 'wp_pp_payment_value1', $value1 );
		update_option( 'wp_pp_payment_item2', sanitize_text_field( stripslashes( $_POST["wp_pp_payment_item2"] ) ) );
		update_option( 'wp_pp_payment_value2', $value2 );
		update_option( 'wp_pp_payment_item3', sanitize_text_field( stripslashes( $_POST["wp_pp_payment_item3"] ) ) );
		update_option( 'wp_pp_payment_value3', $value3 );
		update_option( 'wp_pp_payment_item4', sanitize_text_field( stripslashes( $_POST["wp_pp_payment_item4"] ) ) );
		update_option( 'wp_pp_payment_value4', $value4 );
		update_option( 'wp_pp_payment_item5', sanitize_text_field( stripslashes( $_POST["wp_pp_payment_item5"] ) ) );
		update_option( 'wp_pp_payment_value5', $value5 );
		update_option( 'wp_pp_payment_item6', sanitize_text_field( stripslashes( $_POST["wp_pp_payment_item6"] ) ) );
		update_option( 'wp_pp_payment_value6', $value6 );
		$payment_button_type = isset( $_POST["payment_button_type"] ) ? sanitize_text_field( $_POST["payment_button_type"] ) : '';
		update_option( 'payment_button_type', $payment_button_type );
		update_option( 'wp_pp_show_other_amount', isset ( $_POST['wp_pp_show_other_amount'] ) ? '1' : '-1' );
		update_option( 'wp_pp_show_ref_box', isset ( $_POST['wp_pp_show_ref_box'] ) ? '1' : '-1' );
		update_option( 'wp_pp_ref_title', sanitize_text_field( stripslashes( $_POST["wp_pp_ref_title"] ) ) );
		update_option( 'wp_pp_return_url', esc_url_raw( sanitize_text_field( $_POST["wp_pp_return_url"] ) ) );
		update_option( 'wpapp_collect_shipping_address', isset ( $_POST['wpapp_collect_shipping_address'] ) ? '1' : '-1' );
		update_option( 'wpapp_enable_debug_logging', isset ( $_POST['wpapp_enable_debug_logging'] ) ? '1' : '-1' );

		$wp_pp_cancel_url = isset( $_POST["wp_pp_cancel_url"] ) ? esc_url_raw( sanitize_text_field( $_POST["wp_pp_cancel_url"] ) ) : '';
		$cancel_url = $wp_pp_cancel_url;
		if ( empty ( $cancel_url ) ) {
			$cancel_url = home_url();
		}
		update_option( 'wp_pp_cancel_url', $cancel_url );

		echo '<div id="message" class="updated fade"><p><strong>';
		echo 'Options Updated!';
		echo '</strong></p></div>';
	}

	$paypal_payment_currency = stripslashes( get_option( 'paypal_payment_currency' ) );
	$payment_button_type = stripslashes( get_option( 'payment_button_type' ) );
	?>

	<form method="post" action="">
		<?php wp_nonce_field( 'wp_accept_pp_payment_settings_update' ); ?>
		<input type="hidden" name="info_update" id="info_update" value="true" />

		<div class="postbox">
			<h3 class="hndle"><label for="title">WP Paypal Payment or Donation Accept Plugin Options</label></h3>
			<div class="inside">

				<table class="form-table">

					<tr valign="top">
						<td width="25%" align="left">
							<strong>WP Paypal Payment Widget Title:</strong>
						</td>
						<td align="left">
							<input name="wp_paypal_widget_title_name" type="text" size="30"
								value="<?php echo esc_attr( get_option( 'wp_paypal_widget_title_name' ) ); ?>" />
							<br /><i>This will be the title of the Widget on the Sidebar if you use it.</i><br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Choose Payment Currency: </strong>
						</td>
						<td align="left">
							<select id="paypal_payment_currency" name="paypal_payment_currency">					
								<?php
								_e( '<option value="USD" '.( $paypal_payment_currency == "USD" ? " selected ": "" ).'>US Dollar</option>' );
								_e( '<option value="EUR" '.( $paypal_payment_currency == "EUR" ? " selected ": "" ).'>Euro</option>' );

								_e( '<option value="AUD" '.( $paypal_payment_currency == "AUD" ? " selected ": "" ).'>Australian Dollar</option>' );
								_e( '<option value="BRL" '.( $paypal_payment_currency == "BRL" ? " selected ": "" ).'>Brazilian Real</option>' );
								_e( '<option value="CAD" '.( $paypal_payment_currency == "CAD" ? " selected ": "" ).'>Canadian Dollar</option>' );
								_e( '<option value="CNY" '.( $paypal_payment_currency == "CNY" ? " selected ": "" ).'>Chinese Yuan</option>' );
								_e( '<option value="CZK" '.( $paypal_payment_currency == "CZK" ? " selected ": "" ).'>Czech Koruna</option>' );
								_e( '<option value="HKD" '.( $paypal_payment_currency == "HKD" ? " selected ": "" ).'>Hong Kong Dollar</option>' );

								_e( '<option value="INR" '.( $paypal_payment_currency == "INR" ? " selected ": "" ).'>Indian Rupee</option>' );
								_e( '<option value="IDR" '.( $paypal_payment_currency == "IDR" ? " selected ": "" ).'>Indonesia Rupiah</option>' );
								_e( '<option value="ILS" '.( $paypal_payment_currency == "ILS" ? " selected ": "" ).'>Israeli Shekel</option>' );
								_e( '<option value="JPY" '.( $paypal_payment_currency == "JPY" ? " selected ": "" ).'>Japanese Yen</option>' );

								_e( '<option value="NZD" '.( $paypal_payment_currency == "NZD" ? " selected ": "" ).'>New Zealand Dollar</option>' );
								_e( '<option value="NOK" '.( $paypal_payment_currency == "NOK" ? " selected ": "" ).'>Norwegian krone</option>' );
								_e( '<option value="GBP" '.( $paypal_payment_currency == "GBP" ? " selected ": "" ).'>Pound Sterling</option>' );

								_e( '<option value="SGD" '.( $paypal_payment_currency == "SGD" ? " selected ": "" ).'>Singapore Dollar</option>' );								
								_e( '<option value="ZAR" '.( $paypal_payment_currency == "ZAR" ? " selected ": "" ).'>South African Rand</option>' );
								_e( '<option value="SEK" '.( $paypal_payment_currency == "SEK" ? " selected ": "" ).'>Swedish Krona</option>' );
								_e( '<option value="CHF" '.( $paypal_payment_currency == "CHF" ? " selected ": "" ).'>Swiss Franc</option>' );
								_e( '<option value="TWD" '.( $paypal_payment_currency == "TWD" ? " selected ": "" ).'>Taiwan New Dollars</option>' );
								_e( '<option value="TRY" '.( $paypal_payment_currency == "TRY" ? " selected ": "" ).'>Turkish Lira</option>' );
								?>							
							</select>
							<br /><i>This is the currency for your visitors to make Payments or Donations in.</i><br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Payment Subject:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_payment_subject" type="text" size="35"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_subject' ) ); ?>" />
							<br /><i>Enter the Product or service name or the reason for the payment here. The visitors will
								see this text</i><br />
						</td>
					</tr>

					<tr valign="top">
						<td colspan="2" width="100%" align="left">
							<h3>Payment Options</h3>
							<p>Use the following section to input the name of the service or product along with its price. For instance, type 'Basic service - $10' in the Payment Option text box and '10.00' in the Price text box to process a $10 payment for 'Basic service'. If you do not wish to utilize an option, simply leave the Payment Option and Price fields blank. For example, if you offer three pricing options, fill in the first three and leave any remaining fields empty</p>
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Payment Option 1:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_payment_item1" type="text" size="25"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_item1' ) ); ?>" />
							<strong>Price :</strong>
							<input name="wp_pp_payment_value1" type="text" size="10"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_value1' ) ); ?>" />
							<br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Payment Option 2:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_payment_item2" type="text" size="25"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_item2' ) ); ?>" />
							<strong>Price :</strong>
							<input name="wp_pp_payment_value2" type="text" size="10"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_value2' ) ); ?>" />
							<br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Payment Option 3:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_payment_item3" type="text" size="25"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_item3' ) ); ?>" />
							<strong>Price :</strong>
							<input name="wp_pp_payment_value3" type="text" size="10"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_value3' ) ); ?>" />
							<br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Payment Option 4:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_payment_item4" type="text" size="25"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_item4' ) ); ?>" />
							<strong>Price :</strong>
							<input name="wp_pp_payment_value4" type="text" size="10"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_value4' ) ); ?>" />
							<br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Payment Option 5:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_payment_item5" type="text" size="25"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_item5' ) ); ?>" />
							<strong>Price :</strong>
							<input name="wp_pp_payment_value5" type="text" size="10"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_value5' ) ); ?>" />
							<br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Payment Option 6:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_payment_item6" type="text" size="25"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_item6' ) ); ?>" />
							<strong>Price :</strong>
							<input name="wp_pp_payment_value6" type="text" size="10"
								value="<?php echo esc_attr( get_option( 'wp_pp_payment_value6' ) ); ?>" />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Show Other Amount:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_show_other_amount" type="checkbox" <?php if ( get_option( 'wp_pp_show_other_amount' ) != '-1' )
								echo ' checked="checked"'; ?> value="1" />
							<i> Tick this checkbox if you want to show other amount text box to your visitors so they can
								enter custom amount.</i>
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Show Reference Text Box:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_show_ref_box" type="checkbox" <?php if ( get_option( 'wp_pp_show_ref_box' ) != '-1' )
								echo ' checked="checked"'; ?> value="1" />
							<i> Tick this checkbox if you want your visitors to be able to enter a reference text like email
								or web address. This will be shown in the 'Memo' field of the PayPal transaction.</i>
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Reference Text Box Title:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_ref_title" type="text" size="35"
								value="<?php echo esc_attr( get_option( 'wp_pp_ref_title' ) ); ?>" />
							<br /><i>Enter a title for the Reference text box (ie. Your Web Address). The visitors will see this text.</i><br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Return URL:</strong>
						</td>
						<td align="left">
							<input name="wp_pp_return_url" type="text" size="60"
								value="<?php echo esc_url( get_option( 'wp_pp_return_url' ) ); ?>" />
							<br /><i>Enter a return URL (could be a Thank You page). The visitors will be redirected to this page after a successful payment.</i><br />
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Collect Shipping Address During Checkout:</strong>
						</td>
						<td align="left">
							<input name="wpapp_collect_shipping_address" type="checkbox" <?php if ( get_option( 'wpapp_collect_shipping_address' ) == '1' ) echo ' checked="checked"'; ?> value="1" />
							<i> If this option is checked, customers will be required to enter their shipping address during the PayPal checkout process.</i>
						</td>
					</tr>

					<tr valign="top">
						<td width="25%" align="left">
							<strong>Enable Debug Logging:</strong>
						</td>
						<td align="left">
							<input name="wpapp_enable_debug_logging" type="checkbox" <?php if ( get_option( 'wpapp_enable_debug_logging' ) == '1' ) echo ' checked="checked"'; ?> value="1" />
							<i> If checked, debug output will be written to a log file (keep it disabled unless you are troubleshooting). This is useful for troubleshooting payment related failures.</i>
							<?php
							$clean_log_url = add_query_arg('wpapp-action', 'wpapp_clear_log', WPAPP_PLUGIN_ADMIN_URL);
							echo '<p>';
							echo '<a href="'. esc_url( wp_nonce_url( get_admin_url() . '?wpapp-action=view_log', 'wpapp_view_log_nonce' ) ) . '" target="_blank">View Log</a>';
							echo ' | ';
							echo '<a href="'. esc_url( wp_nonce_url( $clean_log_url, 'wpapp_clear_log_nonce' ) ) . '" target="_blank">Clear Log</a>';
							echo '</p>';
							?>
						</td>
					</tr>

				</table>

			</div>
		</div><!-- end of postbox -->

		<div class="submit">
			<input type="submit" class="button-primary" name="info_update"
				value="<?php _e( 'Update options' ); ?> &raquo;" />
		</div>
	</form>

    <div class="wpapp-yellow-box">
    <p>
        Try our free <a href="https://wordpress.org/plugins/wordpress-simple-paypal-shopping-cart/" target="_blank">Simple Shopping Cart</a> or <a href="https://wordpress.org/plugins/wp-express-checkout/" target="_blank">WP Express Checkout</a> or <a href="https://wordpress.org/plugins/stripe-payments/" target="_blank">Accept Stripe Payments</a> plugins to sell your products.
    </p>
    </div>

	<?php
}

//Admin notice to prompt users to switch to the new PayPal settings.
function wpapp_admin_notices(){
	//WPS paypal email address.
	$paypal_email = get_option('wp_pp_payment_email');
	//PPCP client ID
	$settings = PayPal_PPCP_Config::get_instance();
	$ppcp_live_client_id = $settings->get_value('paypal-live-client-id');
	//$ppcp_sandbox_client_id = $settings->get_value('paypal-sandbox-client-id');

	if( empty($ppcp_live_client_id) && !empty($paypal_email) ){
		//The site has the old PayPal settings.
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php _e('You are using the old PayPal settings in the "WP Accept PayPal Payments" plugin. Please switch to the new PayPal settings for better security.', 'wp-easy-paypal-payment-accept'); ?></p>
			<p><a href="<?php echo admin_url('options-general.php?page=wordpress-easy-paypal-payment-or-donation-accept-plugin/admin/wpapp_admin_menu.php&action=ppcp-settings'); ?>"><?php _e('Switch to new PayPal API by configuring the API credentials', 'wp-easy-paypal-payment-accept'); ?></a></p>
		</div>
		<?php
	}

}

if( is_admin() ) {
	// Add the admin notices hook
	add_filter('admin_notices', 'wpapp_admin_notices');
}