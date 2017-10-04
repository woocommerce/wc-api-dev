<?php
/**
 * Adds a Store link to the masterbar
 *
 * @since 0.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			'title'  => esc_html__( 'Store', 'jetpack' ),
			'href'   => $store_url,
			'meta'   => array(
				'class' => 'mb-icon-spacer',
			)
		) );
	}
} );
