<?php
/**
 * REST API Products controller
 *
 * Handles requests to the /products endpoint.
 *
 * @author   Automattic
 * @category API
 * @package  WooCommerce/API
 * @since    2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Products controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Products_Controller extends WC_REST_Products_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Get the images for a product or product variation.
	 *
	 * @param WC_Product|WC_Product_Variation $product Product instance.
	 * @return array
	 */
	protected function get_images( $product ) {
		$images = array();
		$attachment_ids = array();

		// Add featured image.
		if ( has_post_thumbnail( $product->get_id() ) ) {
			$attachment_ids[] = $product->get_image_id();
		}

		// Add gallery images.
		$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

		// Build image data.
		foreach ( $attachment_ids as $position => $attachment_id ) {
			$attachment_post = get_post( $attachment_id );
			if ( is_null( $attachment_post ) ) {
				continue;
			}

			$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
			if ( ! is_array( $attachment ) ) {
				continue;
			}

			$images[] = array(
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

		return $images;
	}

	/**
	 * Set product images.
	 *
	 * @throws WC_REST_Exception REST API exceptions.
	 * @param WC_Product $product Product instance.
	 * @param array      $images  Images data.
	 * @return WC_Product
	 */
	protected function set_product_images( $product, $images ) {
		if ( is_array( $images ) ) {
			$gallery = array();

			foreach ( $images as $image ) {
				$attachment_id = isset( $image['id'] ) ? absint( $image['id'] ) : 0;

				if ( 0 === $attachment_id && isset( $image['src'] ) ) {
					$upload = wc_rest_upload_image_from_url( esc_url_raw( $image['src'] ) );

					if ( is_wp_error( $upload ) ) {
						if ( ! apply_filters( 'woocommerce_rest_suppress_image_upload_error', false, $upload, $product->get_id(), $images ) ) {
							throw new WC_REST_Exception( 'woocommerce_product_image_upload_error', $upload->get_error_message(), 400 );
						} else {
							continue;
						}
					}

					$attachment_id = wc_rest_set_uploaded_image_as_attachment( $upload, $product->get_id() );
				}

				if ( ! wp_attachment_is_image( $attachment_id ) ) {
					throw new WC_REST_Exception( 'woocommerce_product_invalid_image_id', sprintf( __( '#%s is an invalid image ID.', 'woocommerce' ), $attachment_id ), 400 );
				}

				$featured_image = $product->get_image_id();

				if ( isset( $image['position'] ) && 0 === absint( $image['position'] ) ) {
					$product->set_image_id( $attachment_id );
				} elseif( empty( $image['position'] ) && empty( $featured_image ) ) {
					$product->set_image_id( $attachment_id );
				} else {
					$gallery[] = $attachment_id;
				}

				// Set the image alt if present.
				if ( ! empty( $image['alt'] ) ) {
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', wc_clean( $image['alt'] ) );
				}

				// Set the image name if present.
				if ( ! empty( $image['name'] ) ) {
					wp_update_post( array( 'ID' => $attachment_id, 'post_title' => $image['name'] ) );
				}
			}

			$product->set_gallery_image_ids( $gallery );
		} else {
			$product->set_image_id( '' );
			$product->set_gallery_image_ids( array() );
		}

		return $product;
	}

}
