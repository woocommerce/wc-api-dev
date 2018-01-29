<?php

/**
 * Class WC_Helper_Order_Refund.
 *
 * This helper class should ONLY be used for unit tests!.
 */
class WC_Helper_Order_Refund {

	/**
	 * Create an order refund.
	 *
	 * @since   2.4
	 *
	 * @param int    $order_id
	 * @param int    $customer_id
	 *
	 * @return WC_Order_Refund
	 */
	public static function create_refund( $order_id, $customer_id = 1 ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		// Create the refund.
		$refund = array(
			'amount' => 5.0,
			'reason' => 'Testing',
			'order_id' => $order_id,
		);
		$refund_obj = wc_create_refund( $refund );

		if ( ! $refund_obj || is_wp_error( $refund_obj ) ) {
			return false;
		}

		return $refund_obj;
	}
	
	/**
	 * Create an order refund with line_items.
	 *
	 * @since   2.4
	 *
	 * @param int    $order_id
	 * @param int    $customer_id
	 *
	 * @return WC_Order_Refund
	 */
	public static function create_refund_with_items( $order_id, $customer_id = 1 ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}
		$items = $order->get_items();
		$item_id = key( $items );
		// Create the refund.
		$refund = array(
			'amount' => 5.0,
			'reason' => 'Testing',
			'order_id' => $order_id,
			'line_items' => array(),
		);
		$refund['line_items'][ $item_id ] = array(
			'qty' => 1,
			'refund_total' => 5.0,
		);
		$refund_obj = wc_create_refund( $refund );

		if ( ! $refund_obj || is_wp_error( $refund_obj ) ) {
			return false;
		}

		return $refund_obj;
	}
	
	
	/**
	 * Create an array of line_items based on a given order.
	 *
	 * @since   2.4
	 *
	 * @param int    $order_id
	 * @param int    $count
	 *
	 * @return array
	 */
	public static function create_refund_line_items( $order_id, $count ) {
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$line_items = array();

		// Create line items up to count, as long as there are items to use.
		$item_id = key( $items );
		$i = 0;
		while ( $item_id && ( $i < $count ) ) {
			$line_items[ $item_id ] = array(
				'qty' => 1,
				'refund_total' => 5.0,
			);
			// Next loop…
			$item_id = key( $items );
			$i++;
		}
		return $line_items;
	}
}
