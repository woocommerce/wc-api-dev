<?php
/**
 * REST API variations controller
 *
 * Handles requests to the /products/<product_id>/variations endpoints.
 *
 * @author   Automattic
 * @category API
 * @package  WooCommerce/API
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API variations controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Product_Variations_Controller extends WC_REST_Product_Variations_Controller {

	/**
 	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Get the image for a product variation.
	 *
	 * @param WC_Product_Variation $variation Variation
	 * @return array
	 */
	protected function get_images( $variation ) {
		if ( has_post_thumbnail( $variation->get_id() ) ) {
			$attachment_id = $variation->get_image_id();
		} else {
			$attachment_id = current( $variation->get_gallery_image_ids() );
		}

		$attachment_post = get_post( $attachment_id );
		if ( is_null( $attachment_post ) ) {
			$image = array();
		}

		$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
		if ( ! is_array( $attachment ) ) {
			$image = array();
		}

		if ( ! isset ( $image ) ) {
			$image = array(
				'id'                => (int) $attachment_id,
				'date_created'      => wc_rest_prepare_date_response( $attachment_post->post_date, false ),
				'date_created_gmt'  => wc_rest_prepare_date_response( strtotime( $attachment_post->post_date_gmt ) ),
				'date_modified'     => wc_rest_prepare_date_response( $attachment_post->post_modified, false ),
				'date_modified_gmt' => wc_rest_prepare_date_response( strtotime( $attachment_post->post_modified_gmt ) ),
				'src'               => current( $attachment ),
				'name'              => get_the_title( $attachment_id ),
				'alt'               => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'position'          => (int) $position,
			);
		}

		return array( $image );
	}

}
