<?php
/**
 * Prevents the cheque/check payment method from being auto enabled if settings haven't been configured.
 * It also sets the description/instructions defaults.
 *
 * @since 0.8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_api_dev_cheque_defaults( $settings ) {
	$settings['enabled']['default'] = 'no';
	$settings['description']['default'] = __( 'Pay for this order by check.', 'wc-api-dev' );
	$settings['instructions']['default'] = __( 'Make your check payable to...', 'wc-api-dev' );
	return $settings;
}
add_filter( 'woocommerce_settings_api_form_fields_cheque', 'wc_api_dev_cheque_defaults' );
