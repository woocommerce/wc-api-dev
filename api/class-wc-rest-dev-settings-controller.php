<?php
/**
 * REST API Settings controller
 *
 * Handles requests to the /settings endpoints.
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
 * REST API Settings controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Settings_Controller extends WC_REST_Settings_Controller {

	/**
	 * WP REST API namespace/version.
	 */
	protected $namespace = 'wc/v3';

}
