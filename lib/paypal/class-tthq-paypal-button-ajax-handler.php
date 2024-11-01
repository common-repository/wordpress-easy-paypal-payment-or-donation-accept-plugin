<?php

namespace TTHQ\WPAPP\Lib\PayPal;

/**
 * This clcass handles the ajax requests from the PayPal button's createOrder, captureOrder functions.
 * On successful onApprove event, it creates the required $ipn_data array from the transaction so it can be fed into the existing IPN handler functions easily.
 */
class PayPal_Button_Ajax_Hander {

	public function __construct() {
		//Handle it at 'wp_loaded' hook since custom post types will also be available at that point.
		add_action( 'wp_loaded', array(&$this, 'setup_ajax_request_actions' ) );
	}

	/**
	 * Setup the ajax request actions.
	 */
	public function setup_ajax_request_actions() {
		//Handle the create-order ajax request for 'Buy Now' type buttons.
		add_action( PayPal_Utility_Functions::hook('pp_create_order', true), array(&$this, 'pp_create_order' ) );
		add_action( PayPal_Utility_Functions::hook('pp_create_order', true, true), array(&$this, 'pp_create_order' ) );
		
		//Handle the capture-order ajax request for 'Buy Now' type buttons.
		add_action( PayPal_Utility_Functions::hook('pp_capture_order', true), array(&$this, 'pp_capture_order' ) );
		add_action( PayPal_Utility_Functions::hook('pp_capture_order', true, true), array(&$this, 'pp_capture_order' ) );	
	}

	/**
	 * Handle the pp_create_order ajax request for 'Buy Now' type buttons.
	 */
	 public function pp_create_order(){
		//Get the data from the request
		$data = isset( $_POST['data'] ) ? stripslashes_deep( $_POST['data'] ) : array();
		if ( empty( $data ) ) {
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Empty data received.', 'accept-paypal-payment' ),
				)
			);
		}
		
		if( !is_array( $data ) ){
			//Convert the JSON string to an array (Vanilla JS AJAX data will be in JSON format).
			$data = json_decode( $data, true);		
		}

		$on_page_button_id = isset( $data['on_page_button_id'] ) ? sanitize_text_field( $data['on_page_button_id'] ) : '';
		PayPal_Utility_Functions::log( 'pp_create_order ajax request received for createOrder. On Page Button ID: ' . $on_page_button_id, true );

		//Received data
		PayPal_Utility_Functions::log_array( $data, true );//Debugging purpose.

		// Check nonce.
		if ( ! check_ajax_referer( $on_page_button_id, '_wpnonce', false ) ) {
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Nonce check failed. The page was most likely cached. Please reload the page and try again.', 'accept-paypal-payment' ),
				)
			);
			exit;
		}
		
		//Get the transient name and then retrieve the data
		$transient_name = $data['transient_name'];
		$transient_array = get_transient( $transient_name );
		if( empty($transient_array) ){
			PayPal_Utility_Functions::log( 'Error! Transient data not found. Transient name: ' . $transient_name, true );
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Transient data not found. Please refresh the page and try again.', 'accept-paypal-payment' ),
				)
			);
			exit;
		}

		/*****************************************************************************************
		 * The following step will ensure that the selected value is valid and exists in the transient array.
		 ****************************************************************************************/
		//Get the selected value and its corresponding name.
		$selected_value = isset($data['selected_val']) ? $data['selected_val'] : '';
		$available_options = isset($transient_array['payment_options']) ? $transient_array['payment_options'] : array();
		$selected_option_name = isset($available_options[$selected_value]) ? $available_options[$selected_value] : '';
		if (empty($selected_option_name)) {
			PayPal_Utility_Functions::log( 'Error! Selected option name not found in the transient data: ' . $selected_option_name, true );
			PayPal_Utility_Functions::log_array( $transient_array, true );
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Error! Could not find a match for the selected value. Please refresh the page and try again.', 'accept-paypal-payment' ),
				)
			);
			exit;
		}

		//Optional fields
		$other_amount_val = isset($data['other_amount_val']) ? (float)$data['other_amount_val'] : 0;
		$pp_reference_field = isset($data['pp_reference_field']) ? $data['pp_reference_field'] : '';
		
		if( is_numeric( $other_amount_val ) && $other_amount_val > 0 ){
			//Use the other amount value as the payment amount.
			PayPal_Utility_Functions::log( 'The other amount value has been entered. Going to use this amount for the donation: ' . $other_amount_val, true );
			$payment_amount = $other_amount_val;
			$item_name = 'Donation';
		} else {
			//Use the selected value as the payment amount.
			PayPal_Utility_Functions::log( 'The user has selected a value from the dropdown options. Going to use the payment amount for the transaction: ' . $selected_value, true );
			$payment_amount = $selected_value;
			$item_name = $selected_option_name;
		}

		//Transaction description - if the reference field is not empty, use it as the description of the transaction.
		if(!empty($pp_reference_field)){
			$description = $pp_reference_field;
		} else {
			$description = 'WP Accept PayPal Payment Transaction.';//Default description.
		}
		$description = substr($description, 0, 127);//Limit the item name to 127 characters (PayPal limit)

		//Get the currency
		//Currency needs to be set in the settings as it is loaded by the JS SDK (it cannot be passed as an argument to the shortcode)
		$currency = get_option('paypal_payment_currency');
		
		//Check if we need to collect the shipping address.
		$wpapp_collect_shipping_address = get_option('wpapp_collect_shipping_address');
		if ($wpapp_collect_shipping_address == '1') {
			$shipping_preference = 'GET_FROM_FILE';
		} else {
			$shipping_preference = 'NO_SHIPPING';
		}

		//Create the data array that we will pass to the create-order function.
		$data = array(
			'item_name' => $item_name,
			'description' => $description,			
			'quantity' => 1,
			'item_amount' => $payment_amount, //Individual item amount.
			'grand_total' => $payment_amount, //In our case grand total is same as the payment amount.
			'sub_total' => $payment_amount, //In our case sub total is same as the payment amount.
			'shipping_amt' => 0, //Currently we are not using shipping.
			'tax_amt' => 0, //Currently we are not using tax.
			'currency' => $currency,
			'shipping_preference' => $shipping_preference,
		);

		//Set the additional args for the API call.
		$additional_args = array();
		$additional_args['return_response_body'] = true;

		//Create the order using the PayPal API.
		$api_injector = new PayPal_Request_API_Injector();
		$response = $api_injector->create_paypal_order_by_url_and_args( $data, $additional_args );
            
		//We requested the response body to be returned, so we need to JSON decode it.
		if( $response !== false ){
			$order_data = json_decode( $response, true );
			$paypal_order_id = isset( $order_data['id'] ) ? $order_data['id'] : '';
		} else {
			//Failed to create the order.
			PayPal_Utility_Functions::log( 'Error! Failed to create the order using PayPal API.', false );
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Failed to create the order using PayPal API. Enable the debug logging feature to get more details.', 'accept-paypal-payment' ),
				)
			);
			exit;
		}

        PayPal_Utility_Functions::log( 'PayPal Order ID: ' . $paypal_order_id, true );

		//If everything is processed successfully, send the success response.
		wp_send_json( array( 'success' => true, 'order_id' => $paypal_order_id, 'order_data' => $order_data ) );
		exit;
    }


	/**
	 * Handles the order capture for standard 'Buy Now' type buttons.
	 */
	public function pp_capture_order(){

		//Get the data from the request
		$data = isset( $_POST['data'] ) ? stripslashes_deep( $_POST['data'] ) : array();
		if ( empty( $data ) ) {
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Empty data received.', 'accept-paypal-payment' ),
				)
			);
		}
		
		if( !is_array( $data ) ){
			//Convert the JSON string to an array (Vanilla JS AJAX data will be in JSON format).
			$data = json_decode( $data, true);		
		}

		//Get the order_id from data
		$order_id = isset( $data['order_id'] ) ? sanitize_text_field($data['order_id']) : '';
		if ( empty( $order_id ) ) {
			PayPal_Utility_Functions::log( 'pp_capture_order - empty order ID received.', false );
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Empty order ID received.', 'accept-paypal-payment' ),
				)
			);
		}

		
		$on_page_button_id = isset( $data['on_page_button_id'] ) ? sanitize_text_field( $data['on_page_button_id'] ) : '';
		$transient_name = isset( $data['transient_name'] ) ? sanitize_text_field( $data['transient_name'] ) : '';
		PayPal_Utility_Functions::log( 'Received request - pp_capture_order. PayPal Order ID: ' . $order_id . ', On Page Button ID: ' . $on_page_button_id . ', Transient name: ' . $transient_name, true );

		// Check nonce.
		if ( ! check_ajax_referer( $on_page_button_id, '_wpnonce', false ) ) {
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Nonce check failed. The page was most likely cached. Please reload the page and try again.', 'accept-paypal-payment' ),
				)
			);
			exit;
		}

		//Set the additional args for the API call.
		$additional_args = array();
		$additional_args['return_response_body'] = true;

		// Capture the order using the PayPal API.
		// https://developer.paypal.com/docs/api/orders/v2/#orders_capture
		$api_injector = new PayPal_Request_API_Injector();
		$response = $api_injector->capture_paypal_order( $order_id, $additional_args );

		//We requested the response body to be returned, so we need to JSON decode it.
		if($response !== false){
			$txn_data = json_decode( $response, true );//JSON decode the response body that we received.
		} else {
			//Failed to capture the order.
			wp_send_json(
				array(
					'success' => false,
					'err_msg'  => __( 'Failed to capture the order. Enable the debug logging feature to get more details.', 'accept-paypal-payment' ),
				)
			);
			exit;
		}

		//--
		// PayPal_Utility_Functions::log_array($data, true);//Debugging purpose.
		// PayPal_Utility_Functions::log_array($txn_data, true);//Debugging purpose.
		//--

		//Create the IPN data array from the transaction data.
		//Need to include the following values in the $data array.

		$ipn_data = PayPal_Utility_IPN_Related::create_ipn_data_array_from_capture_order_txn_data( $data, $txn_data );
		$paypal_capture_id = isset( $ipn_data['txn_id'] ) ? $ipn_data['txn_id'] : '';
		PayPal_Utility_Functions::log( 'PayPal Capture ID (Transaction ID): ' . $paypal_capture_id, true );
		//PayPal_Utility_Functions::log_array( $ipn_data, true );//Debugging purpose.
		
		/* Since this capture is done from server side, the validation is not required but we are doing it anyway. */
		//Validate the buy now txn data before using it.
		// $validation_response = PayPal_Utility_IPN_Related::validate_buy_now_checkout_txn_data( $data, $txn_data );
		// if( $validation_response !== true ){
		// 	//Debug logging will reveal more details.
		// 	wp_send_json(
		// 		array(
		// 			'success' => false,
		// 			'error_detail'  => $validation_response,/* it contains the error message */
		// 		)
		// 	);
		// 	exit;
		// }
		
		/**
		 * TODO: This is a plugin specific method.
		 */
		//PayPal_Utility_IPN_Related::complete_post_payment_processing( $data, $txn_data, $ipn_data );

		/**
		 * Trigger the IPN processed action hook (so other plugins can can listen for this event).
		 * Remember to use plugin shortname as prefix when searching for this hook.
		 */ 
		do_action( PayPal_Utility_Functions::hook('paypal_checkout_ipn_processed'), $ipn_data );
		do_action( PayPal_Utility_Functions::hook('payment_ipn_processed'), $ipn_data );

		//Everything is processed successfully, send the success response.
		wp_send_json( array( 'success' => true, 'order_id' => $order_id, 'capture_id' => $paypal_capture_id, 'txn_data' => $txn_data ) );
		exit;
	}	

}
