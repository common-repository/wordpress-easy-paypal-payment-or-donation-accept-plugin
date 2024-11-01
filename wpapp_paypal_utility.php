<?php

function wpapp_validate_paypl_ipn() {

    $wpapp_ipn_validated = true;

    // Reading POSTed data directly from POST causes serialization issues with array data in the POST.
    // Instead, read raw POST data from the input stream.
    $raw_post_data = file_get_contents('php://input');
    $raw_post_array = explode('&', $raw_post_data);
    $myPost = array();
    foreach ($raw_post_array as $keyval) {
        $keyval = explode('=', $keyval);
        if (count($keyval) == 2)
            $myPost[$keyval[0]] = urldecode($keyval[1]);
    }

    // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
    $req = 'cmd=_notify-validate';
    if (function_exists('get_magic_quotes_gpc')) {
        $get_magic_quotes_exists = true;
    }
    foreach ($myPost as $key => $value) {
        if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
            $value = urlencode(stripslashes($value));
        } else {
            $value = urlencode($value);
        }
        $req .= "&$key=$value";
    }

    // Step 2: POST IPN data back to PayPal to validate
    $params = array(
        'body'		 => $req,
        'timeout'	 => 60,
        'httpversion'	 => '1.1',
        'compress'	 => false,
        'decompress'	 => false,
        'user-agent'	 => 'PayPal Donations Plugin/TTHQ'
    );

    $connection_url = 'https://www.paypal.com/cgi-bin/webscr';
    $response = wp_safe_remote_post( $connection_url, $params );

    if ( ! is_wp_error( $response ) && strstr( $response[ 'body' ], 'VERIFIED' ) ) {
        // The IPN is verified, process it
        $wpapp_ipn_validated = true;
    } else {
        // IPN invalid, log for manual investigation
        $wpapp_ipn_validated = false;
    }

    if (!$wpapp_ipn_validated) {
        // IPN validation failed. Email the admin to notify this event.
        $admin_email = get_bloginfo('admin_email');
        $subject = 'IPN validation failed for a payment';
        $body = "This is a notification email from the WP Accept PayPal Payment plugin letting you know that a payment verification failed." .
        "\n\nPlease check your paypal account to make sure you received the correct amount in your account before proceeding" .
        wp_mail($admin_email, $subject, $body);
        exit;
    }
}

function wpapp_allowed_tags() {
    $my_allowed = wp_kses_allowed_html( 'post' );

    // form fields - input
    $my_allowed['input'] = array(
            'class' => array(),
            'id'    => array(),
            'name'  => array(),
            'value' => array(),
            'type'  => array(),
            'step' => array(),
            'min' => array(),
            'checked' => array(),
            'size' => array(),
            'readonly' => array(),
            'style' => array(),
    );
    // select
    $my_allowed['select'] = array(
            'class'  => array(),
            'id'     => array(),
            'name'   => array(),
            'value'  => array(),
            'type'   => array(),
    );
    // select options
    $my_allowed['option'] = array(
            'selected' => array(),
            'value' => array(),
            'data-product_name' => array(),
    );
    // button
    $my_allowed['button'] = array(
            'type' => array(),
            'class' => array(),
            'id' => array(),
            'style' => array(),
    );
    // style
    $my_allowed['style'] = array(
            'types' => array(),
    );

    return $my_allowed;
}
