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
class WC_REST_Dev_Plugins_Setting_Options_Controller extends WC_REST_Dev_Setting_Options_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'plugins_settings/(?P<group_id>[\w-]+)';

	/**
	 * Boolean for if a setting type is a valid supported setting type.
	 *
	 * @since  3.0.0
	 * @param  string $type
	 * @return bool
	 */
	public function is_setting_type_valid( $type ) {
		return true;
	}


		/**
	 * Get all settings in a group.
	 *
	 * @param string $group_id Group ID.
	 * @return array|WP_Error
	 */
	public function get_group_settings( $group_id ) {
		$is_group_valid = apply_filters( 'woocommerce_plugin_settings-' . $group_id, false );

		if ( ! $is_group_valid ) {
			return new WP_Error( 'rest_setting_setting_group_unknown', __( 'Invalid setting group.', 'woocommerce' ), array( 'status' => 404 ) );
		}

		return parent::get_group_settings( $group_id );
	}

}
