<?php
/**
 * REST API WC Payment gateways controller
 *
 * Handles requests to the /payment_gateways endpoint.
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
 */
class WC_REST_Dev_Payment_Gateways_Controller extends WC_REST_Payment_Gateways_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
