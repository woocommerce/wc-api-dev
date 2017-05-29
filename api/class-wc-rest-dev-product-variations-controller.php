<?php
/**
 * REST API variations controller
 *
 * Handles requests to the /products/<product_id>/variations endpoints.
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
 * REST API variations controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Product_Variations_Controller extends WC_REST_Product_Variations_Controller {

	/**
 	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
