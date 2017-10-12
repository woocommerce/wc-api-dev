<?php
/**
 * add_settings_error is only available in UI contecext
 * adding a mock version to prevent API failures
 *
 * @since 0.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//TODO:
//  - use this function to capture messages and feed them back to Calypso on return from API.

// Parts of the MailChimp plugin reused for REST response purpose are signaling to the user via UI
// that the information he has provided are not correct - this happens for example when some fields
// are missing in the form user should fill in. It is done via add_settings_error.
// This function is part of UI and within REST rest_api_ini it is not available - which makes sense
// because REST has nothing to do with UI. Missing function was causing fata errors.
// Loading template.php is not an option bacause it would significantly slow down the response.
// The solution is to have dummy add_settings_error available only in REST context

if ( ! function_exists( 'add_settings_error' ) && defined( 'REST_REQUEST' ) && REST_REQUEST ) {
	function add_settings_error( $setting, $code, $message, $type = 'error' ) {}
}
