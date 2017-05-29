<?php
/**
 * REST API Setting Options controller
 *
 * Handles requests to the /settings/$group/$setting endpoints.
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
 * REST API Setting Options controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Setting_Options_Controller extends WC_REST_Setting_Options_Controller {

	/**
	 * WP REST API namespace/version.
	 */
	protected $namespace = 'wc/v3';

}
