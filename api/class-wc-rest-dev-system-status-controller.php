<?php
/**
 * REST API WC System Status controller
 *
 * Handles requests to the /system_status endpoint.
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
class WC_REST_Dev_System_Status_Controller extends WC_REST_System_Status_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Returns setting values for the site that are useful for debugging
	 * purposes. For full settings access, use the settings api.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings       = parent::get_settings();
		$settings_extra = array();
		$admin_notices  = get_option( 'woocommerce_admin_notices', array() );

		$settings_extra['setup_wizard_ran'] = ! ( in_array( 'install', $admin_notices ) );

		return array_merge( $settings, $settings_extra );
	}

}
