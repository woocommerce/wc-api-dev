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

	/**
	 * Hooks into the automatically registered general settings group.
	 */
	public function get_group_settings( $group_id ) {
		if ( 'general' === $group_id ) {
			add_action( 'woocommerce_settings-general', array( $this, 'split_currency_setting_options' ) );
		}
		return parent::get_group_settings( $group_id );
	}

	/**
	 * Takes the options array from `woocommerce_currency` and splits the currency value
	 * into a label and symbol, so the currency currency symbol can easily be derived
	 * from the API.
	 */
	public function split_currency_setting_options( $settings ) {
		$setting_key = false;
		foreach ( $settings as $key => $setting ) {
			if ( 'woocommerce_currency' !== $setting['id'] ) {
				continue;
			}
			$setting_key = $key;
		}

		if ( ! $setting_key ) {
			return $settings;
		}

		$currencies   = get_woocommerce_currencies();
		$prev_options = $settings[ $setting_key ]['options'];
		$options      = array();
		foreach ( $prev_options as $code => $full_string ) {
			$options[ $code ] = array(
				'label'  => $currencies[ $code ],
				'symbol' => get_woocommerce_currency_symbol( $code ),
			);
		}
		$settings[ $setting_key ]['options'] = $options;

		return $settings;
	}

}
