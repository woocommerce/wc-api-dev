<?php
/**
 * REST API Shipping Zone Methods controller
 *
 * Handles requests to the /shipping/zones/<id>/methods endpoint.
 *
 * @author   WooThemes
 * @category API
 * @package  WooCommerce/API
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Shipping Zone Methods class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Shipping_Zone_Methods_Controller extends WC_REST_Shipping_Zone_Methods_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
