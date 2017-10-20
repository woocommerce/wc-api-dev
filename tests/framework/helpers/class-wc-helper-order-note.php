<?php

/**
 * Class WC_Helper_Order_Note.
 *
 * This helper class should ONLY be used for unit tests!.
 */
class WC_Helper_Order_Note {

	/**
	 * Create an order note.
	 *
	 * @since   2.4
	 *
	 * @param int    $order_id
	 * @param int    $customer_id
	 *
	 * @return WC_Order
	 */
	public static function create_note( $order_id, $customer_id = 1 ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		// Create the note.
		$note_id = $order->add_order_note( 'This is an order note.', false );

		if ( ! $note_id ) {
			return false;
		}

		$note = get_comment( $note_id );
		return $note;
	}
}
