<?php
/**
 * REST API Reports controller
 *
 * Handles requests to the reports/sales endpoint.
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
 * REST API Report Sales controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Report_Sales_Controller extends WC_REST_Report_Sales_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';
}
