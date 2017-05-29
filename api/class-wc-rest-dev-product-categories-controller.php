<?php
/**
 * REST API Product Categories controller
 *
 * Handles requests to the products/categories endpoint.
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
 * REST API Product Categories controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Product_Categories_Controller extends WC_REST_Product_Categories_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
