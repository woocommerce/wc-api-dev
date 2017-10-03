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

if ( ! function_exists( 'add_settings_error' ) && ! is_admin() ) {
        function add_settings_error( $setting, $code, $message, $type = 'error' ) {}
}
