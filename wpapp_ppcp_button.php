<?php 

use TTHQ\WPAPP\Lib\PayPal\PayPal_PPCP_Config;
use TTHQ\WPAPP\Lib\PayPal\PayPal_JS_Button_Embed;

function wpapp_load_ppcp_button( $args = array() ) {

    if (isset($args) && !empty($args)) {
        //Custom arguments are passed to the shortcode. Use this data for the button.
        //Get the arguments passed to the shortcode.
        $wp_pp_show_other_amount = isset($args['other_amount']) ? '1' : '';
        $wp_pp_other_amount_label = isset($args['other_amount_label']) ? $args['other_amount_label'] : '';
        $wp_pp_other_amount_placeholder = isset($args['other_amount_placeholder']) ? $args['other_amount_placeholder'] : '';

        $wp_pp_show_ref_box = isset($args['reference']) ? '1' : '';
        $wp_pp_ref_title = isset($args['reference']) ? $args['reference'] : '';
        $wp_pp_ref_placeholder = isset($args['reference_placeholder']) ? $args['reference_placeholder'] : '';
        $return_url = isset($args['return']) ? $args['return'] : '';
        $paypal_subject = isset($args['payment_subject']) ? $args['payment_subject'] : '';
        //Currency needs to be set in the settings as it is loaded by the JS SDK (it cannot be passed as an argument to the shortcode)
        $payment_currency =  get_option('paypal_payment_currency');
        
        //Get the options for the select box
        $payment_options = array();//We will use it to have the options for the select box.
        $options_str = isset($args['options']) ? $args['options'] : '';
        $options = explode('|', $options_str);
        foreach ($options as $option) {
            $option = explode(':', $option);
            $name = esc_attr($option[0]);
            $price = esc_attr($option[1]);
            // Remove $, £, and € symbols from the beginning of the string (if present).
            $price_for_array_key = ltrim($price, '$£€');
            //Construct our array with price as key and name (with price) as value.
            $payment_options[$price_for_array_key] = $name .' - ' .$price;
        }
    } else {
        //Default arguments
        //Get the default payment options from the settings
        $wp_pp_show_other_amount = get_option('wp_pp_show_other_amount');
        $wp_pp_other_amount_label = 'Other Amount';
        $wp_pp_other_amount_placeholder = '';

        $wp_pp_show_ref_box = get_option('wp_pp_show_ref_box');
        $wp_pp_ref_title = get_option('wp_pp_ref_title');
        $wp_pp_ref_placeholder = '';
        $return_url = get_option('wp_pp_return_url');
        $paypal_subject = get_option('wp_pp_payment_subject');
        $payment_currency = get_option('paypal_payment_currency');

        //Get the options for the select box
        $itemName1 = get_option('wp_pp_payment_item1');
        $value1 = get_option('wp_pp_payment_value1');
        $itemName2 = get_option('wp_pp_payment_item2');
        $value2 = get_option('wp_pp_payment_value2');
        $itemName3 = get_option('wp_pp_payment_item3');
        $value3 = get_option('wp_pp_payment_value3');
        $itemName4 = get_option('wp_pp_payment_item4');
        $value4 = get_option('wp_pp_payment_value4');
        $itemName5 = get_option('wp_pp_payment_item5');
        $value5 = get_option('wp_pp_payment_value5');
        $itemName6 = get_option('wp_pp_payment_item6');
        $value6 = get_option('wp_pp_payment_value6');
        //Create our payment options array
        $payment_options = array(
            $value1 => $itemName1,
        );
        if( !empty($itemName2) ){
            $payment_options[$value2] = $itemName2;
        }
        if( !empty($itemName3) ){
            $payment_options[$value3] = $itemName3;
        }
        if( !empty($itemName4) ){
            $payment_options[$value4] = $itemName4;
        }
        if( !empty($itemName5) ){
            $payment_options[$value5] = $itemName5;
        }
        if( !empty($itemName6) ){
            $payment_options[$value6] = $itemName6;
        }        
    }

    //print_r($payment_options);
    //Example of the payment_options array: 
    // Array ( 
    //     [15.50] => T-Shirt Payment - 15.50 
    //     [30.00] => Ticket Payment - 30.00 
    //     [47.95] => Membership Payment - 47.95 
    //     )
    
    /***********************************************
     * Settings and checkout button specific variables
     ***********************************************/
    $ppcp_configs = PayPal_PPCP_Config::get_instance();
    $live_client_id = $ppcp_configs->get_value('paypal-live-client-id');
    $sandbox_client_id = $ppcp_configs->get_value('paypal-sandbox-client-id');
    
    $sandbox_enabled = $ppcp_configs->get_value('enable-sandbox-testing');
    $is_live_mode = $sandbox_enabled ? 0 : 1;

	$disable_funding_card = $ppcp_configs->get_value('ppcp_disable_funding_card');
    $disable_funding_credit = $ppcp_configs->get_value('ppcp_disable_funding_credit');
    $disable_funding_venmo = $ppcp_configs->get_value('ppcp_disable_funding_venmo');
    $disable_funding = array();
    if( !empty($disable_funding_card)){
        $disable_funding[] = 'card';
    }
    if( !empty($disable_funding_credit)){
        $disable_funding[] = 'credit';
    }
    if( !empty($disable_funding_venmo)){
        $disable_funding[] = 'venmo';
    }

	$btn_type = !empty($ppcp_configs->get_value('ppcp_btn_type')) ? $ppcp_configs->get_value('ppcp_btn_type') : 'checkout';
    $btn_shape = !empty($ppcp_configs->get_value('ppcp_btn_shape')) ? $ppcp_configs->get_value('ppcp_btn_shape') : 'rect';
    $btn_layout = !empty($ppcp_configs->get_value('ppcp_btn_layout')) ? $ppcp_configs->get_value('ppcp_btn_layout') : 'vertical';
    $btn_color = !empty($ppcp_configs->get_value('ppcp_btn_color')) ? $ppcp_configs->get_value('ppcp_btn_color') : 'blue';

    $btn_width = !empty($ppcp_configs->get_value('ppcp_btn_width')) ? $ppcp_configs->get_value('ppcp_btn_width') : 250;
    $btn_height = $ppcp_configs->get_value('ppcp_btn_height');
    $btn_sizes = array( 'small' => 25, 'medium' => 35, 'large' => 45, 'xlarge' => 55 );
    $btn_height = isset( $btn_sizes[ $btn_height ] ) ? $btn_sizes[ $btn_height ] : 35;

    $currency = get_option('paypal_payment_currency');
    $txn_success_message = __('Transaction completed successfully!', 'wordpress-accept-paypal-payment');
    $txn_success_extra_msg = __('Feel free to browse our site for another checkout.', 'wordpress-accept-paypal-payment');


    /****************************
     * PayPal SDK related settings
     ****************************/
    //Configure the paypal SDK settings
    $settings_args = array(
        'is_live_mode' => $is_live_mode,
        'live_client_id' => $live_client_id,
        'sandbox_client_id' => $sandbox_client_id,
        'currency' => $currency,
        'disable-funding' => $disable_funding, /*array('card', 'credit', 'venmo'),*/
        'intent' => 'capture', /* It is used to set the "intent" parameter in the JS SDK */
        'is_subscription' => 0, /* It is used to set the "vault" parameter in the JS SDK */
    );

    //Initialize and set the settings args that will be used to load the JS SDK.
    $pp_js_button = PayPal_JS_Button_Embed::get_instance();
    $pp_js_button->set_settings_args( $settings_args );

    //Load the JS SDK on footer (so it only loads once per page)
    add_action( 'wp_footer', array($pp_js_button, 'load_paypal_sdk') );

    /************************************************
     * Button's HTML and JS code related data
     ************************************************/

    //The on page embed button id is used to identify the button on the page. Useful when there are multiple buttons (of the same item/product) on the same page.
    $on_page_embed_button_id = $pp_js_button->get_next_button_id();

    //Create nonce for this button.
    $wp_nonce = wp_create_nonce($on_page_embed_button_id);
    
    //Save all the button's data in a transient. This will be used to create the order and capture the payment.
    //Generate the output for the select box and other form elements for user to select the payment option.

    //At 'init' time, we call the 'wpapp_set_visitor_id_to_cookie()' function to set a unique visitor ID to the cookie.
    //We will use the global variable when the cookie is not present on the first page load.
    global $wpapp_global_visitor_id;
    $visitor_id = isset($_COOKIE['wpapp_visitor_id']) ? $_COOKIE['wpapp_visitor_id'] : $wpapp_global_visitor_id;
    //echo '<br />Visitor ID: ' . $visitor_id;
    //Alternative option: use the post/page slug or ID to make the transient name unique. <post_id>_<on_page_embed_button_id>
    $transient_name = $visitor_id . '_'. $on_page_embed_button_id; //Example: 4b3403665fea6_wpapp_paypal_button_0
    $elements_wrapper_id = 'elements_wrapper_' . $on_page_embed_button_id; //Example: elements_wrapper_wpapp_paypal_button_0
    $select_id = 'select_' . $on_page_embed_button_id; //Example: select_wpapp_paypal_button_0

    //Save the transient data for this button.
    $transient_array = array(
        'on_page_embed_button_id' => $on_page_embed_button_id,
        'elements_wrapper_id' => $elements_wrapper_id,
        'select_id' => $select_id,
        'wp_nonce' => $wp_nonce,
        'transient_name' => $transient_name,
        'paypal_subject' => $paypal_subject,
        'payment_currency' => $payment_currency,
        'wp_pp_show_other_amount' => $wp_pp_show_other_amount,
        'wp_pp_show_ref_box' => $wp_pp_show_ref_box,
        'wp_pp_ref_title' => $wp_pp_ref_title,
        'return_url' => $return_url,
        'txn_success_message' => $txn_success_message,
        'txn_success_extra_msg' => $txn_success_extra_msg,
        'payment_options' => $payment_options,
    );
    $transient_timeout = ((60 * 60) * 48); //48 hours (make it longer than the cookie timeout so the transient is always available when the cookie is present.)
    $transient_timeout = apply_filters('wpapp_pp_button_transient_timeout', $transient_timeout);
    set_transient($transient_name, $transient_array, $transient_timeout);
    //echo '<br />Transient Name: '.$transient_name.'<br />';
    //print_r($transient_array);

    //Payment widget output (select box, other amount input, reference input, etc.)
    $widget_output = '';
    $widget_output .= '<div id="'.$elements_wrapper_id.'">';
    $widget_output .= '<div class="wpapp_payment_subject"><span class="payment_subject"><strong>'.esc_attr($paypal_subject).'</strong></span></div>';
    $widget_output .= '<select id="'.$select_id.'" name="'.$select_id.'" class="">';
    //Add the options (from the payment_options array) to the select box
    foreach ($payment_options as $key => $value) {
        $widget_output .= '<option value="'.esc_attr($key).'">'.esc_attr($value).'</option>';
    }
    $widget_output .= '</select>';

    // Show other amount text box
    if ($wp_pp_show_other_amount == '1') {
        $widget_output .= '<div class="wpapp_other_amount_label"><strong>'.esc_attr($wp_pp_other_amount_label).'</strong></div>';
        $widget_output .= '<div class="wpapp_other_amount_input"><input type="number" min="1" step="any" name="wpapp_other_amt" title="Other Amount" value="" placeholder="'.esc_attr($wp_pp_other_amount_placeholder).'" class="wpapp_other_amt_input" style="max-width:80px;" /></div>';
    }

    // Show the reference text box
    if ($wp_pp_show_ref_box == '1') {
        $widget_output .= '<div class="wpapp_ref_title_label"><strong>'.esc_attr($wp_pp_ref_title).':</strong></div>';
        $widget_output .= '<div class="wpapp_ref_value"><input type="text" name="wpapp_button_reference" maxlength="60" value="'.apply_filters('wp_pp_button_reference_value','').'" placeholder="'.esc_attr($wp_pp_ref_placeholder).'" class="wp_pp_button_reference" /></div>';
    }
    
    $widget_output .= '<div style="margin-bottom:10px;"></div>';
    $widget_output .= '</div>';//end of <elements_wrapper_id>

    //Start the ppcp button's HTML output
    $ppcp_output = '';
    ob_start();
    ?>
    <div class="wpapp-ppcp-button-wrapper">

    <!-- PayPal button container where the button will be rendered -->
    <div id="<?php echo esc_attr($on_page_embed_button_id); ?>" style="width: <?php echo esc_attr($btn_width); ?>px;"></div>

    <script type="text/javascript">
        document.addEventListener( "wpapp_paypal_sdk_loaded", function() { 
            //Anything that goes here will only be executed after the PayPal SDK is loaded.
            console.log('PayPal JS SDK is loaded for WPAPP.');

            /**
             * See documentation: https://developer.paypal.com/sdk/js/reference/
             */
            paypal.Buttons({
                /**
                 * Optional styling for buttons.
                 * 
                 * See documentation: https://developer.paypal.com/sdk/js/reference/#link-style
                 */
                style: {
                    color: '<?php echo esc_js($btn_color); ?>',
                    shape: '<?php echo esc_js($btn_shape); ?>',
                    height: <?php echo esc_js($btn_height); ?>,
                    label: '<?php echo esc_js($btn_type); ?>',
                    layout: '<?php echo esc_js($btn_layout); ?>',
                },

                // Triggers when the button first renders.
                onInit: onInitHandler,

                // Triggers when the button is clicked.
                onClick: onClickHandler,

                // Setup the transaction.
                createOrder: createOrderHandler,

                // Handle the onApprove event.
                onApprove: onApproveHandler,

                // Handle unrecoverable errors.
                onError: onErrorHandler,

                // Handles onCancel event.
                onCancel: onCancelHandler,

            })
            .render('#<?php echo esc_js($on_page_embed_button_id); ?>')
            .catch((err) => {
                console.error('PayPal Buttons failed to render');
            });

            function onInitHandler(data, actions) {
                //This function is called when the button first renders.
            }

            function onClickHandler(data, actions) {
                //This function is called when the button is clicked.
            }

            
            /**
             * See documentation: https://developer.paypal.com/sdk/js/reference/#link-createorder
             */
            async function createOrderHandler() {
                // Create the order in PayPal using the PayPal API.
                // https://developer.paypal.com/docs/checkout/standard/integrate/
                // The server-side Create Order API is used to generate the Order. Then the Order-ID is returned.                    
                console.log('Setting up the AJAX request for create-order call.');

                // First, select the interacted button's wrapper div by its ID (so  we can target various elements within it)
                const wrapper_div_interacted_button = document.getElementById('<?php echo esc_js($elements_wrapper_id); ?>');
                //console.log(wrapper_div_interacted_button);

                // Read the select element and get the selected option's value and text.
                js_on_page_embed_button_id = '<?php echo esc_js($on_page_embed_button_id); ?>';
                var selectId = 'select_' + js_on_page_embed_button_id; // Construct the select ID
                var selectElement = document.getElementById(selectId);
                //console.log("Select Element: ", selectElement);

                //Get the other amount field value.
                var other_amount_input = wrapper_div_interacted_button.querySelector('.wpapp_other_amt_input');

                //Get the reference field value.
                var pp_reference_field = wrapper_div_interacted_button.querySelector('.wp_pp_button_reference');

                let pp_bn_data = {};
                pp_bn_data.transient_name = '<?php echo esc_js($transient_name); ?>';
                pp_bn_data.on_page_button_id = '<?php echo esc_js($on_page_embed_button_id); ?>';
                pp_bn_data.selected_val = selectElement.value;//value of the selected option. (we can use it get the item name from the transient).
                pp_bn_data.other_amount_val = other_amount_input ? other_amount_input.value : '';
                pp_bn_data.pp_reference_field = pp_reference_field ? pp_reference_field.value : '';
                console.log('WPAPP Button Data Below');
                console.log(pp_bn_data);

                //Ajax action: <prefix>_pp_create_order 
                let post_data = 'action=wpapp_pp_create_order&data=' + JSON.stringify(pp_bn_data) + '&_wpnonce=<?php echo $wp_nonce; ?>';
                //console.log('Post Data: ', post_data);//Debugging purposes only.

                try {
                    // Using fetch for AJAX request. This is supported in all modern browsers.
                    const response = await fetch("<?php echo admin_url( 'admin-ajax.php' ); ?>", {
                        method: "post",
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: post_data
                    });

                    const response_data = await response.json();

                    if (response_data.order_id) {
                        console.log('Create-order API call to PayPal completed successfully.');
                        //If we need to see the order details, uncomment the following line.
                        //const order_data = response_data.order_data;
                        //console.log('Order data: ' + JSON.stringify(order_data));
                        return response_data.order_id;
                    } else {
                        const error_message = JSON.stringify(response_data);
                        console.error('Error occurred during create-order call to PayPal. ' + error_message);
                        throw new Error(error_message);
                    }
                } catch (error) {
                    console.error(error);
                    alert('Could not initiate PayPal Checkout...\n\n' + JSON.stringify(error));
                }
            }

            async function onApproveHandler(data, actions) {
                console.log('Successfully created a transaction.');

                // Show the spinner while we process this transaction.
                // First, select the button container by its ID.
                const pp_button_container = document.getElementById('<?php echo esc_js($on_page_embed_button_id); ?>');
                // Then, navigate to the parent element of the PPCP button container.
                const parent_wrapper = pp_button_container.parentElement;
                // Finally, within the parent wrapper, select the spinner container
                const pp_button_spinner_container = parent_wrapper.querySelector('.wpapp-pp-button-spinner-container');
                //console.log('Button spinner container: ', pp_button_spinner_container);

                pp_button_container.style.display = 'none'; //Hide the buttons
                pp_button_spinner_container.style.display = 'inline-block'; //Show the spinner.

                // Capture the order in PayPal using the PayPal API.
                // https://developer.paypal.com/docs/checkout/standard/integrate/
                // The server-side capture-order API is used. Then the Capture-ID is returned.
                console.log('Setting up the AJAX request for capture-order call.');
                let pp_bn_data = {};
                pp_bn_data.order_id = data.orderID;
                pp_bn_data.on_page_button_id = '<?php echo esc_js($on_page_embed_button_id); ?>';
                pp_bn_data.transient_name = '<?php echo esc_js($transient_name); ?>';
                //pp_bn_data.custom_field = encodeURIComponent(custom_data);//If you have any custom data to send to the server (it needs to be URI encoded so special characters are not lost.)

                //Ajax action: <prefix>_pp_capture_order
                let post_data = 'action=wpapp_pp_capture_order&data=' + JSON.stringify(pp_bn_data) + '&_wpnonce=<?php echo $wp_nonce; ?>';
                try {
                    const response = await fetch("<?php echo admin_url( 'admin-ajax.php' ); ?>", {
                        method: "post",
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: post_data
                    });

                    const response_data = await response.json();
                    const txn_data = response_data.txn_data;
                    const error_detail = txn_data?.details?.[0];
                    const error_msg = response_data.error_msg;//Our custom error message.
                    // Three cases to handle:
                    // (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                    // (2) Other non-recoverable errors -> Show a failure message
                    // (3) Successful transaction -> Show confirmation or thank you message

                    if (response_data.capture_id) {
                        // Successful transaction -> Show confirmation or thank you message
                        console.log('Capture-order API call to PayPal completed successfully.');

                        //Redirect to the Thank you page URL if it is set.
                        return_url = '<?php echo esc_url_raw($return_url); ?>';
                        if( return_url ){
                            //redirect to the Thank you page URL.
                            console.log('Redirecting to the Thank you page URL: ' + return_url);
                            window.location.href = return_url;
                            return;
                        } else {
                            //No return URL is set. Just show a success message.
                            console.log('No return URL is set in the settings. Showing a success message.');

                            //We are going to show the success message in the wpapp-ppcp-button-wrapper's container.
                            txn_success_msg = '<?php echo esc_attr($txn_success_message).' '.esc_attr($txn_success_extra_msg); ?>';
                            // Select all elements with the class 'wpapp-ppcp-button-wrapper'
                            var button_divs = document.querySelectorAll('.wpapp-ppcp-button-wrapper');

                            // Loop through the NodeList and update each element
                            button_divs.forEach(function(div, index) {
                                div.innerHTML = '<div id="wpapp-btn-txn-success-msg-' + index + '" class="wpapp-btn-txn-success-msg">' + txn_success_msg + '</div>';
                            });

                            // Scroll to the success message container of the button we are interacting with.
                            const interacted_button_container_element = document.getElementById(<?php echo esc_attr($elements_wrapper_id); ?>);
                            if (interacted_button_container_element) {
                                interacted_button_container_element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                            return;
                        }

                    } else if (error_detail?.issue === "INSTRUMENT_DECLINED") {
                        // Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                        console.log('Recoverable INSTRUMENT_DECLINED error. Calling actions.restart()');
                        return actions.restart();
                    } else if ( error_msg && error_msg.trim() !== '' ) {
                        //Our custom error message from the server.
                        console.error('Error occurred during PayPal checkout process.');
                        console.error( error_msg );
                        alert( error_msg );
                    } else {
                        // Other non-recoverable errors -> Show a failure message
                        console.error('Non-recoverable error occurred during PayPal checkout process.');
                        console.error( error_detail );
                        //alert('Unexpected error occurred with the transaction. Enable debug logging to get more details.\n\n' + JSON.stringify(error_detail));
                    }

                    //Return the button and the spinner back to their orignal display state.
                    pp_button_container.style.display = 'block'; // Show the buttons
                    pp_button_spinner_container.style.display = 'none'; // Hide the spinner

                } catch (error) {
                    console.error(error);
                    alert('PayPal returned an error! Transaction could not be processed. Enable the debug logging feature to get more details...\n\n' + JSON.stringify(error));
                }
            }

            function onErrorHandler(err) {
                console.error('An error prevented the user from checking out with PayPal. ' + JSON.stringify(err));
                alert( '<?php echo esc_js(__("Error occurred during PayPal checkout process.", "wordpress-accept-paypal-payment")); ?>\n\n' + JSON.stringify(err) );
            }
            
            function onCancelHandler (data) {
                console.log('Checkout operation cancelled by the customer.');
                //Return to the parent page which the button does by default.
            }            

        });
    </script>

    <style>
        @keyframes wpapp-pp-button-spinner {
            to {transform: rotate(360deg);}
        }
        .wpapp-pp-button-spinner {
            margin: 0 auto;
            text-indent: -9999px;
            vertical-align: middle;
            box-sizing: border-box;
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 5px solid #ccc;
            border-top-color: #0070ba;
            animation: wpapp-pp-button-spinner .6s linear infinite;
        }
        .wpapp-pp-button-spinner-container {
            width: 100%;
            text-align: center;
            margin-top:10px;
            display: none;
        }
    </style>
    <div class="wpapp-pp-button-spinner-container">
        <div class="wpapp-pp-button-spinner"></div>
    </div>    
    </div><!-- end of button-wrapper -->
    <?php
    //Get the output from the buffer and clean the buffer.
    $ppcp_output = ob_get_clean();

    //Combine the widget output and the button output.
    $output = '<div class="wpapp_widget">'. $widget_output . $ppcp_output . '</div>';

    //The caller function will echo or append this output.
    return $output;

}

/******************************************
 * Set the visitor ID (if not already set)
 ******************************************/
$wpapp_global_visitor_id = '';
function wpapp_set_visitor_id_to_cookie(){
    if ( !isset($_COOKIE['wpapp_visitor_id']) || empty($_COOKIE['wpapp_visitor_id']) ) {
        //The cookie superglobal won't be available in the server until the next page load shince this is just getting set in the client side. 
        //On the next page load, the client will send the cookie to the server when it will be available in the $_COOKIE superglobal array for PHP to use.
        //So for this page load, we will set the visitor ID to a global variable that we can use in the shortcode function of the current page load.
        global $wpapp_global_visitor_id;
        $wpapp_global_visitor_id = uniqid();
        // 1 day = 86400.
        setcookie('wpapp_visitor_id', $wpapp_global_visitor_id, time() + (86400 * 30), "/"); 
    }
}

/*
* Use the 'plugins_loaded' hook instead of 'init' for this. This way, the cookie is set before auth cookie related code is run in WordPress which can cause headers already sent errors.
*/
add_action('plugins_loaded', 'wpapp_set_visitor_id_to_cookie');//Set the visitor ID (if not already set).
