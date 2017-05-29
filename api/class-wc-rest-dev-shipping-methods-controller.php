<?php
/**
 * REST API WC Shipping Methods controller
 *
 * Handles requests to the /shipping_methods endpoint.
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
 * @package WooCommerce/API
 * @extends WC_REST_Controller
 */
class WC_REST_Dev_Shipping_Methods_Controller extends WC_REST_Shipping_Methods_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
