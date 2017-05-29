<?php
/**
 * REST API Product Shipping Classes controller
 *
 * Handles requests to the products/shipping_classes endpoint.
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
 * REST API Product Shipping Classes controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Product_Shipping_Classes_Controller extends WC_REST_Product_Shipping_Classes_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';
}
