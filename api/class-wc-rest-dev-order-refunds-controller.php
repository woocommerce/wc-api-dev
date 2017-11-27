<?php
/**
 * REST API Order Refunds controller
 *
 * Handles requests to the /orders/<order_id>/refunds endpoint.
 *
 * @author   WooThemes
 * @category API
 * @package  WooCommerce/API
 * @since    2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Order Refunds controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Order_Refunds_Controller extends WC_REST_Order_Refunds_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Get the Order refund's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$params = parent::get_item_schema();

		$params['properties']['meta_data']['items']['properties']['value']['type'] = 'mixed';

		$params['properties']['line_items']['items']['properties']['name']['type'] = 'mixed';
		$params['properties']['line_items']['items']['properties']['product_id']['type'] = 'mixed';
		$params['properties']['line_items']['items']['properties']['tax_class']['type'] = 'string';
		$params['properties']['line_items']['items']['properties']['price']['type'] = 'number';
		$params['properties']['line_items']['items']['properties']['meta_data']['items']['properties']['value']['type'] = 'mixed';

		return $params;
	}

}
