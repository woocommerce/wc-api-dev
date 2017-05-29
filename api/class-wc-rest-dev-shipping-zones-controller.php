<?php
/**
 * REST API Shipping Zones controller
 *
 * Handles requests to the /shipping/zones endpoint.
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
 * REST API Shipping Zones class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Shipping_Zones_Controller extends WC_REST_Shipping_Zones_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
