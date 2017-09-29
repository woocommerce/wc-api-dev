<?php
/**
 * Prevent redirection during MailChimp activation.
 *
 * @since 0.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_api_dev_mailchimp_no_redirect() {
	add_option('mailchimp_woocommerce_plugin_do_activation_redirect', false);
}
add_action( 'admin_init', 'wc_api_dev_mailchimp_no_redirect' );
