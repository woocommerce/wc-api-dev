<?php
/**
 * Adds a Store link to the masterbar
 *
 * @since 0.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_api_dev_masterbar_css() {
	wp_enqueue_style( 'wp-api-dev-masterbar', '/wp-content/plugins/wc-api-dev/assets/css/masterbar.css', array(), WC_API_Dev::CURRENT_VERSION );	
}
add_action( 'wp_enqueue_scripts', 'wc_api_dev_masterbar_css' );
add_action( 'admin_enqueue_scripts', 'wc_api_dev_masterbar_css' );

add_action( 'jetpack_masterbar', function() {
	global $wp_admin_bar;

	if ( isset( $wp_admin_bar ) ) {
		$strip_http = '/.*?:\/\//i';
		$site_slug  = preg_replace( $strip_http, '', get_home_url() );
		$site_slug  = str_replace( '/', '::', $site_slug );

		$store_url = 'https://wordpress.com/store/' . $site_slug;

		$wp_admin_bar->add_menu( array(
			'parent' => 'blog',
			'id'     => 'store',
			// TODO: translation domain change
			'title'  => esc_html__( 'Store (BETA)', 'storefront' ),
			'href'   => $store_url,
			'meta'   => array(
				'class' => 'mb-icon-spacer',
			)
		) );
	}
} );
