<?php
/**
 * REST API Reports controller
 *
 * Handles requests to the reports/top_sellers endpoint.
 *
 * @author   WooThemes
 * @category API
 * @package  WooCommerce/API
 * @since    2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_REST_Report_Top_Sellers_Controller' ) ) {
	/**
	 * REST API Report Top Sellers controller class.
	 *
	 * @package WooCommerce/API
	 */
	class WC_REST_Dev_Report_Top_Sellers_Controller extends WC_REST_Report_Top_Sellers_Controller {

		/**
		 * Endpoint namespace.
		 *
		 * @var string
		 */
		protected $namespace = 'wc/v3';
	}
}
