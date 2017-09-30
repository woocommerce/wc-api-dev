<?php
/**
 * Prevent redirection during MailChimp activation.
 *
 * @since 0.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'option_mailchimp_woocommerce_plugin_do_activation_redirect', '__return_false' );
