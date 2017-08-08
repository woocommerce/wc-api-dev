<?php
/**
 * Order details table shown in emails.
 *
 * This matches version 2.5.0 of the email-order-details plain template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

echo strtoupper( sprintf( __( 'Order number: %s', 'wc-api-dev' ), $order->get_order_number() ) ) . "\n";
echo wc_format_datetime( $order->get_date_created() ) . "\n";
echo "\n" . wc_get_email_order_items( $order, array(
	'show_sku'      => $sent_to_admin,
	'show_image'    => false,
	'image_size'    => array( 32, 32 ),
	'plain_text'    => true,
	'sent_to_admin' => $sent_to_admin,
) );

echo "==========\n\n";

if ( $totals = $order->get_order_item_totals() ) {
	foreach ( $totals as $total ) {
		echo $total['label'] . "\t " . $total['value'] . "\n";
	}
}

if ( $sent_to_admin ) {
	echo "\n" . sprintf( __( 'View order: %s', 'wc-api-dev' ), wc_api_dev_email_get_wpcom_order_link( $order->get_id() ) ) . "\n";
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
