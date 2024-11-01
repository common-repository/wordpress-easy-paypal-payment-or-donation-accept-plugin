<?php
/*
Plugin Name: Easy Accept Payments via PayPal
Version: 5.1.2
Plugin URI: https://www.tipsandtricks-hq.com/wordpress-easy-paypal-payment-or-donation-accept-plugin-120
Author: Tips and Tricks HQ
Author URI: https://www.tipsandtricks-hq.com/
Description: Easy to use Wordpress plugin to accept paypal payment for a service or product or donation in one click. Can be used in the sidebar, posts and pages.
License: GPL2
*/

//Slug - wpapp

if (!defined('ABSPATH')){//Exit if accessed directly
    exit;
}

define('WPAPP_PLUGIN_VERSION', '5.1.2');
define('WPAPP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define('WPAPP_PLUGIN_URL', plugins_url('', __FILE__));
define('WPAPP_PLUGIN_ADMIN_URL', admin_url('options-general.php?page=wordpress-easy-paypal-payment-or-donation-accept-plugin/admin/wpapp_admin_menu.php'));

define('WPAPP_ENABLE_SANDBOX', false);//Enable sandbox mode

include_once( WPAPP_PLUGIN_PATH . 'admin/wpapp_admin_menu.php');
include_once( WPAPP_PLUGIN_PATH . 'shortcode_view.php');
include_once( WPAPP_PLUGIN_PATH . 'wpapp_paypal_utility.php');
include_once( WPAPP_PLUGIN_PATH . 'wpapp-debug-logging-functions.php' );
include_once( WPAPP_PLUGIN_PATH . 'wpapp_ppcp_button.php' );
include_once( WPAPP_PLUGIN_PATH . 'lib/paypal/class-tthq-paypal-main.php' );

// Add the settings link
function wpapp_add_settings_link( $links, $file ) {
    if ( $file == plugin_basename( __FILE__ ) ) {
	$settings_link = '<a href="'.WPAPP_PLUGIN_ADMIN_URL.'">' . (__( "Settings", "wp-accept-payments-for-paypal" )) . '</a>';
	array_unshift( $links, $settings_link );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'wpapp_add_settings_link', 10, 2 );

function wp_pp_plugin_install() {
    // Some default options
    add_option('wp_pp_payment_email', get_bloginfo('admin_email'));
    add_option('paypal_payment_currency', 'USD');
    add_option('wp_pp_payment_subject', 'Plugin Service Payment');
    add_option('wp_pp_payment_item1', 'Basic Service - $10');
    add_option('wp_pp_payment_value1', '10');
    add_option('wp_pp_payment_item2', 'Gold Service - $20');
    add_option('wp_pp_payment_value2', '20');
    add_option('wp_pp_payment_item3', 'Platinum Service - $30');
    add_option('wp_pp_payment_value3', '30');
    add_option('wp_paypal_widget_title_name', 'Paypal Payment');
    add_option('payment_button_type', 'https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif');
    add_option('wp_pp_show_other_amount', '-1');
    add_option('wp_pp_show_ref_box', '1');
    add_option('wp_pp_ref_title', 'Your Email Address');
    add_option('wp_pp_return_url', home_url());
    add_option('wp_pp_cancel_url', home_url());
    add_option('wpapp_collect_shipping_address', '-1');
    add_option('wpapp_enable_debug_logging', '-1');
}

register_activation_hook(__FILE__, 'wp_pp_plugin_install');

function wpapp_buy_now_any_amt_handler($args) {
    $output = wppp_render_paypal_button_with_other_amt($args);
    return $output;
}

function wpapp_buy_now_button_shortcode($args) {
    $output = wppp_render_paypal_button_form($args);
    return $output;
}

function wp_ppp_process($content) {
    if (strpos($content, "<!-- wp_paypal_payment -->") !== FALSE) {
        $content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
        $content = str_replace('<!-- wp_paypal_payment -->', Paypal_payment_accept(), $content);
    }
    return $content;
}

function show_wp_paypal_payment_widget($args) {
    extract($args);

    $wp_paypal_payment_widget_title_name_value = get_option('wp_paypal_widget_title_name');
    echo $before_widget;
    echo $before_title . $wp_paypal_payment_widget_title_name_value . $after_title;
    echo Paypal_payment_accept();
    echo $after_widget;
}

function wp_paypal_payment_widget_control() {
    ?>
    <p>
    <? _e("Set the Plugin Settings from the Settings menu"); ?>
    </p>
    <?php
}

function wp_paypal_payment_init() {
    wp_register_style('wpapp-styles', WPAPP_PLUGIN_URL . '/wpapp-styles.css', array(), WPAPP_PLUGIN_VERSION);
    wp_enqueue_style('wpapp-styles');

    //Widget code
    $widget_options = array('classname' => 'widget_wp_paypal_payment', 'description' => __("Display WP Paypal Payment."));
    wp_register_sidebar_widget('wp_paypal_payment_widgets', __('WP Paypal Payment'), 'show_wp_paypal_payment_widget', $widget_options);
    wp_register_widget_control('wp_paypal_payment_widgets', __('WP Paypal Payment'), 'wp_paypal_payment_widget_control');

    // View log file (if requested).
    if( is_admin() ){
        //Only do this in the admin area.
        $action = isset( $_GET['wpapp-action'] ) ? sanitize_text_field( stripslashes ( $_GET['wpapp-action'] ) ) : '';
        if ( ! empty( $action ) && $action === 'view_log' ) {
            check_admin_referer( 'wpapp_view_log_nonce' );
            wpapp_read_log_file();
        }
    }
}

function wpapp_shortcode_plugin_enqueue_jquery() {
    wp_enqueue_script('jquery');
}

add_filter('the_content', 'wp_ppp_process');

if (!is_admin()) {
    add_shortcode('wp_paypal_payment', 'Paypal_payment_accept');
    add_shortcode('wp_paypal_payment_box', 'wpapp_buy_now_button_shortcode');
    add_shortcode('wp_paypal_payment_box_for_any_amount', 'wpapp_buy_now_any_amt_handler');

    add_filter('widget_text', 'do_shortcode');
}

add_action('init', 'wpapp_shortcode_plugin_enqueue_jquery');
add_action('init', 'wp_paypal_payment_init');
