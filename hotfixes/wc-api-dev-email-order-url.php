<?php
/**
 * Provides a hotfix that points the admin order URL to WordPress.com instead of wp-admin.
 *
 * @since 0.8.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_api_dev_email_get_wpcom_order_link( $order_id ) {
	$strip_http = '/.*?:\/\//i';
	$site_slug  = preg_replace( $strip_http, '', get_home_url() );
	$site_slug  = str_replace( '/', '::', $site_slug );

	return 'https://wordpress.com/store/order/' . $site_slug . '/' . absint( $order_id );
}

function wc_api_dev_email_order_url( $markup, $sent_to_admin, $order ) {
	$order_url = $sent_to_admin ? wc_api_dev_email_get_wpcom_order_link( $order->get_id() ) : $order->get_view_order_url();

	$markup['url'] = $order_url;
	$markup['potentialAction'] = array(
		'@type'  => 'ViewAction',
		'name'   => 'View Order',
		'url'    => $order_url,
		'target' => $order_url,
	);

	return $markup;
}
add_filter( 'woocommerce_structured_data_order', 'wc_api_dev_email_order_url', 10, 3 );

function wc_api_dev_email_order_details_get_template( $located, $template_name, $args, $template_path, $default_path ) {
	if ( 'emails/email-order-details.php' === $template_name ) {
		return dirname( __FILE__ ) . '/email-templates/email-order-details.php';
	}
	if ( 'emails/plain/email-order-details.php' === $template_name ) {
		return dirname( __FILE__ ) . '/email-templates/plain/email-order-details.php';
	}
	return $located;
}
add_filter( 'wc_get_template', 'wc_api_dev_email_order_details_get_template', 10, 5 );
