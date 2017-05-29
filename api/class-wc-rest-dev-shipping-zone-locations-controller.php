<?php
/**
 * REST API Shipping Zone Locations controller
 *
 * Handles requests to the /shipping/zones/<id>/locations endpoint.
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
 * REST API Shipping Zone Locations class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Shipping_Zone_Locations_Controller extends WC_REST_Shipping_Zone_Locations_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
