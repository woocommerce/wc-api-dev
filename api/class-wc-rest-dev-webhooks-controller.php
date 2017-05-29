<?php
/**
 * REST API Webhooks controller
 *
 * Handles requests to the /webhooks endpoint.
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
 * REST API Webhooks controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Webhooks_Controller extends WC_REST_Webhooks_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
