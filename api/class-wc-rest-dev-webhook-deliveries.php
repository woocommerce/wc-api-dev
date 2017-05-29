<?php
/**
 * REST API Webhooks controller
 *
 * Handles requests to the /webhooks/<webhook_id>/deliveries endpoint.
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
 * REST API Webhook Deliveries controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Webhook_Deliveries_Controller extends WC_REST_Webhook_Deliveries_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

}
