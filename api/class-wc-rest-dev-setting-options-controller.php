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
 * Filters new address settings into the settings general endpoint
 *
 * These new settings are being added to WC 3.x in
 * pull request https://github.com/woocommerce/woocommerce/pull/15636
 * This filter is used to insert them if not present.
 *
 * THIS FILTER SHOULD NOT BE PORTED TO WOOCOMMERCE CORE as the above PR
 * already takes care of this in WooCommerce core.
 */
function wc_rest_dev_add_address_settings_to_settings_general( $settings ) {
	$new_settings = array(
		array(
			'id' => 'woocommerce_store_address',
			'type' => 'text',
			'option_key' => 'woocommerce_store_address',
			'default' => '',
		),
		array(
			'id' => 'woocommerce_store_address_2',
			'type' => 'text',
			'option_key' => 'woocommerce_store_address_2',
			'default' => '',
		),
		array(
			'id' => 'woocommerce_store_city',
			'type' => 'text',
			'option_key' => 'woocommerce_store_city',
			'default' => '',
		),
		array(
			'id' => 'woocommerce_store_postcode',
			'type' => 'text',
			'option_key' => 'woocommerce_store_postcode',
			'default' => '',
		),
	);

	// For each of the new settings, make sure the setting id doesn't
	// already exist in the settings array and then add it
	$ids = array_column( $settings, 'id' );
	foreach ( $new_settings as $new_setting ) {
		if ( ! in_array( $new_setting['id'], $ids ) ) {
			$settings[] = $new_setting;
		}
	}

	return $settings;
}
add_filter( 'woocommerce_settings-general', 'wc_rest_dev_add_address_settings_to_settings_general', 999 );

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
