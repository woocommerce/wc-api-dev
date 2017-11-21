<?php
/**
 * REST API Orders controller
 *
 * Handles requests to the /orders endpoint.
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
 * REST API Orders controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Orders_Controller extends WC_REST_Orders_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Prepare objects query.
	 *
	 * @since  3.0.0
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		// This is needed to get around an array to string notice in WC_REST_Orders_Controller::prepare_objects_query
		$statuses = $request['status'];
		unset( $request['status'] );
		$args = parent::prepare_objects_query( $request );

		$args['post_status'] = array();
		foreach ( $statuses as $status ) {
			if ( 'any' === $status ) {
				// Set status to "any" and short-circuit out.
				$args['post_status'] = 'any';
				break;
			}
			$args['post_status'][] = 'wc-' . $status;
		}

		return $args;
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['status'] = array(
			'default'           => 'any',
			'description'       => __( 'Limit result set to orders assigned a specific status.', 'woocommerce' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'string',
				'enum' => array_merge( array( 'any' ), $this->get_order_statuses() ),
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Get the Order's schema, conforming to JSON Schema.
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

		$params['properties']['tax_lines']['items']['properties']['meta_data']['items']['properties']['value']['type'] = 'mixed';

		$params['properties']['shipping_lines']['items']['properties']['method_title']['type'] = 'mixed';
		$params['properties']['shipping_lines']['items']['properties']['method_id']['type'] = 'mixed';
		$params['properties']['shipping_lines']['items']['properties']['meta_data']['items']['properties']['value']['type'] = 'mixed';

		$params['properties']['fee_lines']['items']['properties']['name']['type'] = 'mixed';
		$params['properties']['fee_lines']['items']['properties']['meta_data']['items']['properties']['value']['type'] = 'mixed';

		$params['properties']['coupon_lines']['items']['properties']['code']['type'] = 'mixed';
		$params['properties']['coupon_lines']['items']['properties']['meta_data']['items']['properties']['value']['type'] = 'mixed';

		return $params;
	}
}
