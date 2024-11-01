<?php

use TTHQ\WPAPP\Lib\PayPal\PayPal_PPCP_Config;


function Paypal_payment_accept() {
    $output = '';

	//PayPal PPCP client ID
	$settings = PayPal_PPCP_Config::get_instance();
	$ppcp_live_client_id = $settings->get_value('paypal-live-client-id');
	//$ppcp_sandbox_client_id = $settings->get_value('paypal-sandbox-client-id');

    //Check if the new PayPal PPCP settings are configured.
    if( !empty($ppcp_live_client_id) ){
        //Using the new PPCP method.
        $output = wpapp_load_ppcp_button();
        return $output;
    } else {
        //PayPal API credentials haven't been configured. Show a notice.
        $output .= '<div class="wpapp-yellow-box">';
        $output .= "Error! Missing PayPal API credentials. Please configure the PayPal API credentials by going to the settings menu of this plugin.";
        $output .= '</div>';
        return $output;
    }

    /**************************************************
     * The old PayPal WPS METHOD has been phased out.
     * **********************************************/    
}

function wppp_render_paypal_button_form($args) {
    extract(shortcode_atts(array(
        'email' => '',
        'currency' => 'USD',
        'options' => 'Payment for Service 1:15.50|Payment for Service 2:30.00|Payment for Service 3:47.00',
        'return' => site_url(),
        'cbt' => '',
        'reference' => 'Your Email Address',
        'reference_placeholder' => '',
        'other_amount' => '',
        'other_amount_label' => 'Other Amount:',
        'other_amount_placeholder' => '',
        'country_code' => '',
        'payment_subject' => '',
        'button_image' => '',
        'button_text' => '',
        'cancel_url' => '',
        'new_window' => '',
        'tax' => '',
        'rm' => '0',
        'validate_ipn' => '',
    ), $args));

    //WPS paypal email address.
    $paypal_email = get_option('wp_pp_payment_email');
    //PPCP client ID
    $settings = PayPal_PPCP_Config::get_instance();
    $ppcp_live_client_id = $settings->get_value('paypal-live-client-id');
    //$ppcp_sandbox_client_id = $settings->get_value('paypal-sandbox-client-id');

    //Check if the new PPCP settings are configured.
    if( !empty($ppcp_live_client_id) ){
        //Using the new PPCP method.
        $output = wpapp_load_ppcp_button( $args );
        return $output;
    } else {
        //PayPal API credentials haven't been configured. Show a notice.
        $output .= '<div class="wpapp-yellow-box">';
        $output .= "Error! Missing PayPal API credentials. Please configure the PayPal API credentials by going to the settings menu of this plugin.";
        $output .= '</div>';
    }

    /**************************************************
     * The old PayPal WPS METHOD has been phased out.
     * **********************************************/
}

function wppp_render_paypal_button_with_other_amt($args) {
    $output = "";
    $output .= '<div class="wpapp-yellow-box">';
    $output .= 'This shortcode has been phased out. Please switch to our <a href="https://wordpress.org/plugins/wp-express-checkout/" target="_blank">WP Express Checkout plugin</a> for enhanced functionality.';
    $output .= '</div>';
    return $output;
}