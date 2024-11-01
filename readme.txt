=== Easy Accept Payments via PayPal ===
Contributors: Tips and Tricks HQ, Ruhul Amin, mbrsolution
Donate link: https://www.tipsandtricks-hq.com
Tags: PayPal, PayPal payment, WordPress PayPal, PayPal donation, Accept payment for services or product
Requires at least: 5.5
Tested up to: 6.6
Stable tag: 5.1.2
License: GPLv2 or later

Easy to use Wordpress plugin to accept PayPal payments for a service or product or donation in one click

== Description ==

Easy to use WordPress plugin to accept PayPal payments for a service or product or donation in one click. Can be used in the sidebar, posts and pages of your site.

For information, detailed documentation, video tutorial and updates, please visit the [WordPress PayPal Payment](https://www.tipsandtricks-hq.com/wordpress-easy-paypal-payment-or-donation-accept-plugin-120) Plugin Page

* Quick installation and setup.
* Easily take payment for a service from your site via PayPal.
* Easily create PayPal Buy Now buttons.
* Create the payment buttons on the fly and embed them anywhere on your site using a shortcode.
* Uses the New PayPal checkout API for secure payments.
* Add multiple payment widgets for different services or products.
* Ability to configure which currency you want to use to accept the payment.
* You will need to have your own PayPal account (creating a PayPal account is free).
* Integrate PayPal with your WordPress powered site.
* Accept donation on your WordPress site for a cause.
* Allow your users to specify an amount that they wish to pay. Useful when you need to accept variable payment amount.
* Ability to specify a reference text for the payment.
* Ability to specify a payment subject for the payment widget.
* Add PayPal Buy Now buttons anywhere on a WordPress page.
* Create a payment button widget to accept payment in any currency accepted by PayPal. 
* Ability to specify a payment subject for each paypal payment widget.
* Create a payment widget to accept any amount from your customer. Users will specify the amount to pay (useful for donations).
* Ability to return the user to a specific page after the payment.
* Option to collect the shipping address from the customer during the PayPal checkout process.

== Usage ==

https://www.youtube.com/watch?v=Jvy5E1ea8VA

https://www.youtube.com/watch?v=XL7Q8eU9dOY

1) Navigate to the 'PayPal PPCP' tab in the settings to set up your PayPal API credentials.
2) Adjust the settings as needed, then insert the shortcode [wp_paypal_payment] into a post, page, or sidebar widget where you wish to display the payment button.
3) For more versatility, you can use the [wp_paypal_payment_box] shortcode to incorporate various payment widgets, each with its unique configuration. View shortcode documentation

== Installation ==

1. Unzip and Upload the folder 'WP-accept-paypal-payment' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and configure the options eg. your email, Subject text etc.
4. See the usage section for details on how to place the paypal payment widget

== Frequently Asked Questions ==

== Screenshots ==

Visit the plugin site at https://www.tipsandtricks-hq.com/wordpress-easy-paypal-payment-or-donation-accept-plugin-120 for screenshots.

== Changelog ==

= 5.1.2 =
- Resolved an intermittent checkout error caused by recent PayPal API updates.

= 5.1.1 =
- Added Norwegian krone (NOK) to the list of supported currencies in the settings menu.

= 5.1.0 =
- The visitor ID is set using the plugins_loaded hook instead of the init hook.

= 5.0.9 = 
- Typo fixed in the thank you message.

= 5.0.8 =
- Added more debug logging statements for troubleshooting purposes.
- Increased the transient expiration time for better consistency.

= 5.0.7 =
- Added option to enable debug logging for troubleshooting purposes.
- Added an option to view and reset the log file from the settings menu.

= 5.0.6 =
- Added a new option in the settings menu to enable shipping address collection during the checkout process.

= 5.0.5 =
- Added more currency codes to the list of supported currencies in the settings menu.

= 5.0.4 =
- Fix for multiple payment widget shortcodes on the same page with other amount not working properly.

= 5.0.3 =
- The New PayPal API is the only supported option now. The old PayPal option has been removed.

= 5.0.2 =
- Currency code is always taken from the settings. The new PayPal API doesn't allow multiple different currency codes to be used in the SDK load on the same page.

= 5.0.1 =
- Fixed an issue with the "reference" parameter in the shortcode.

= 5.0 =
- Important: this version upgrades the PayPal API to the new PayPal Commerce Platform API for better security. It has breaking changes. Please read the following notes carefully.
- Important: After updating to this version, you will need to go to the settings menu of the plugin and configure your PayPal API credentials for the new Paypal API.
- It is recommended to test this version on a staging site before updating the live site.
- Link to the previous version (4.9.10) is available here: https://downloads.wordpress.org/plugin/wordpress-easy-paypal-payment-or-donation-accept-plugin.4.9.10.zip

= 4.9.10 =
- Added output escaping to one shortcode parameter.

= 4.9.9 =
- Added a settings link in the plugins menu so it can be accessed easily.

= 4.9.8 =
- Fixed the stable tag version number.
- Removed the use of HEREDOC or NOWDOC syntax.

= 4.9.7 =
- Updated the banner and icon graphics used in the plugin's page.
- Tested on WP6.0

= 4.9.6 =
- Added new shortcode parameters that can be used to specify placeholder value for the "reference" and "other amount" fields.

= 4.9.5 =
- Added a Cancel URL field in the settings. This can be used to specify a cancel URL for the [wp_paypal_payment_box] shortcode.

= 4.9.4 =
- Removed a warning from the settings menu of this plugin.
- Updated the settings menu header to use h2 tag.

= 4.9.3 =
- WordPress 4.7 compatibility.
- Fixed an issue with using quotation marks in Payment Subject.

= 4.9.2 =
- Added a CSS class to the other amount input field.
- Replaced the line-breaks in the default shortcode output to use CSS divs with a default margin of 10px. This should produce better output in any given WordPress theme.

= 4.9.1 = 
- Added sanitization and escaping.

= 4.9 =
- Removed some unnecessary files.
- Added nonce check in the settings.

= 4.8 =
- Added a new shortcode parameter (other_amount_label) to allow customization of the "Other Amount" text/label in the payment form.
- Added a new class name (buy_now_button_image) to the custom button image (so users can target that button image for customization via CSS).
- WordPress 4.4 compatibility.

= 4.7 =
- Added a new parameter (default_amount) in the other amount shortcode so you can specify a default amount that will be used to pre-fill the amount field.
- Added PayPal IPN validation option.

= 4.6 =
- Added two new filters to modify the reference input field name and value programmatically.
- Added a check to make sure a PayPal email address is specified in the widget shortcode.
- Added an option to specify the "cbt" parameter via the shortcode.
- Refactored some code to move all the admin dashboard related code to a separate file.

= 4.5 =
- The "Other Amount" input field type is now set to "number". This will work better on mobile devices.

= 4.4 =
- Added a new shortcode parameter so you can optionally set the "rm" variable via the shortcode.
- WordPress 4.2 compatibility.

= 4.3 = 
- WordPress 4.1 compatibility.

= 4.2 =
- Fixed a small issue using the other amount option with the shortcode [wp_paypal_payment].
- Cleaned up the settings area a bit and made the options more user-friendly.

= 4.1 =
- The currency code will now be shown after the "Other Amount" field.
- Added the option to create text based payment button. Use parameter "button_text" in the shortcode to use it.
- WordPress 4.0 compatibility.

= 4.0 = 
- Added two new filters to allow modification of the payer email parameter programmatically. The filters are 'wppp_widget_any_amt_email' and 'wppp_widget_email'.
- Added a new parameter in the shortcode to override tax value. The name of the new shortcode parameter is "tax".

= 3.9 =
- Added an option to exclude the "reference" field from the payment widget. Using the parameter reference="" in the shortcode will disable that field.

= 3.8 =
- Added a new feature to open the payment window in a new browser tab/window. Use the new_window parameter in the shortcode to use it.
- Fixed a minor bug in the [wp_paypal_payment_box_for_any_amount] shortcode.

= 3.7 =
- Added more parameters in the "wp_paypal_payment_box_for_any_amount" shortcode. New parameters are "reference" (for adding a reference field) and "currency" (for adding a currency code).
- Moved some inline CSS to a CSS file.

= 3.6 = 
- Added the ability to specify a cancel URL using the "cancel_url" parameter in the shortcode
- Added a new shortcode that allows you to create a payment widget for any amount.

= 3.5 =
- WordPress 3.8 compatibility

= 3.4 =
- Added an option to specify a custom button image for the payment button. You can use the "button_image" parameter in the shortcode to use a customized image for the buy button.

= 3.3 =
- Added an option in the shortcode to specify a payment subject. This can be handy if you have multiple payment widgets on your site.
- WordPress 3.7 compatibility
- Fixed some deprecated calls

= 3.2 =
- Added an option in the shortcode to set the country code to be used for the PayPal checkout page language.

= 3.1 =
- Added an option to specify a different amount (any amount your user whish to pay) via the shortcode.

Changelog for old versions can be found at the following URL
https://www.tipsandtricks-hq.com/wordpress-easy-paypal-payment-or-donation-accept-plugin-120
