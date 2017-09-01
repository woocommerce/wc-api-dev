<?php
/**
 * REST API Product Reviews Controller
 *
 * Handles requests to /products/<product_id>/reviews and /products/reviews.
 *
 * @author   WooThemes
 * @category API
 * @package  WooCommerce/API
 * @since    2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product Reviews Controller Class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Product_Reviews_Controller extends WC_REST_Product_Reviews_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * List route base.
	 *
	 * @var string
	 */
	protected $list_rest_base = 'products/reviews';

	/**
	 * Register Routes
	 *
	 * Registers a root /products/reviews endpoint that returns all reviews for a site.
	 */
	public function register_routes() {
		parent::register_routes();
		register_rest_route( $this->namespace, '/' . $this->list_rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_list_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_list_collection_params(),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Get all reviews for a site.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function get_list_items( $request ) {
		global $wpdb;

		$per_page = intval( $request['per_page'] );

		switch( $request['status'] ) {
			case 'pending':
				$status = 'hold';
				break;
			case 'approved':
				$status = 'approve';
				break;
			case 'trash':
				$status = 'trash';
				break;
			case 'spam':
				$status = 'spam';
				break;
			default:
				$status = 'all';
		}

		$args = array(
			'post_type'     => 'product',
			'parent'        => '',
			'type'          => '',
			'hierarchical'  => 'threaded',
			'number'        => $per_page,
			'offset'        => intval( ( $request['page'] - 1 ) * $per_page ),
			'status'        => $status,
			'order'         => $request['order'],
			'orderby'       => 'date_created',
			'no_found_rows' => false,
		);

		if ( ! empty( $request['product']) ) {
			$args['post_id'] = $request['product'];
		}

		if ( ! empty( $request['search']) ) {
			$args['search'] = $request['search'];
		}

		$reviews_query = new WP_Comment_Query;
		$reviews       = $reviews_query->query( $args );

		$data = array();
		foreach ( $reviews as $review_data ) {
			$review = $this->prepare_item_for_response( $review_data, $request );
			$review = $this->prepare_response_for_collection( $review );
			$data[] = $review;
		}

		$response = rest_ensure_response( $data );

		$response->header( 'X-WP-Total', (int) $reviews_query->found_comments );
		$response->header( 'X-WP-TotalPages', (int) $reviews_query->max_num_pages );

		return $response;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param WP_Comment $review Product review object.
	 * @param WP_REST_Request $request Request object.
	 * @return array Links for the given product review.
	 */
	protected function prepare_links( $review, $request ) {
		if ( empty( $request['product_id'] ) ) {
			$request['product_id'] = $review->comment_post_ID;
		}

		return parent::prepare_links( $review, $request );
	}

	/**
	 * Prepare a single product review output for response.
	 *
	 * v3 adds 'product_id' and 'approved'.
	 *
	 * @param WP_Comment $review Product review object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $review, $request ) {
		$product = new WC_Product( $review->comment_post_ID );
		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$images        = wp_get_attachment_image_src( $product->get_image_id(), 'full' );
		$product_image = is_array( $images ) ? current( $images ) : '';

		switch( $review->comment_approved ) {
			case '1':
				$status = 'approved';
				break;
			case 'spam':
				$status = 'spam';
				break;
			case 'trash':
				$status = 'trash';
				break;
			case '0':
			default:
				$status = 'pending';
				break;
		}

		$content = 'view' === $context ? wpautop( $review->comment_content ) : $review->comment_content;

		$data = array(
			'id'               => (int) $review->comment_ID,
			'date_created'     => wc_rest_prepare_date_response( $review->comment_date ),
			'date_created_gmt' => wc_rest_prepare_date_response( $review->comment_date_gmt ),
			'review'           => $content,
			'rating'           => (int) get_comment_meta( $review->comment_ID, 'rating', true ),
			'name'             => $review->comment_author,
			'email'            => $review->comment_author_email,
			'avatar_urls'      => rest_get_avatar_urls( $review->comment_author_email ),
			'verified'         => wc_review_is_from_verified_owner( $review->comment_ID ),
			'status'           => $status,
			'product'          => array(
				'id'    => (int) $review->comment_post_ID,
				'name'  => $product->get_name(),
				'image' => $product_image,
			)
		);

		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $review, $request ) );

		/**
		 * Filter product reviews object returned from the REST API.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Comment       $review   Product review object used to create response.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'woocommerce_rest_prepare_product_review', $response, $review, $request );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_list_collection_params() {
		$params = array();

		$params['context'] = $this->get_context_param( array( 'default' => 'view' ) );

		$params['status'] = array(
			'default'           => 'any',
			'description'       => __( 'Limit result set to reviews with a specific status.', 'woocommerce' ),
			'type'              => 'stringy',
			'enum'              => array( 'any', 'pending', 'approved', 'trash', 'spam' ),
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['page'] = array(
			'description'        => __( 'Current page of the collection.', 'woocommerce' ),
			'type'               => 'integer',
			'default'            => 1,
			'sanitize_callback'  => 'absint',
			'validate_callback'  => 'rest_validate_request_arg',
			'minimum'            => 1,
		);

		$params['per_page'] = array(
			'description'        => __( 'Maximum number of items to be returned in result set.', 'woocommerce' ),
			'type'               => 'integer',
			'default'            => 10,
			'minimum'            => 1,
			'maximum'            => 100,
			'sanitize_callback'  => 'absint',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		$params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.', 'woocommerce' ),
			'type'               => 'string',
			'default'            => 'desc',
			'enum'               => array( 'asc', 'desc' ),
			'validate_callback'  => 'rest_validate_request_arg',
		);

		$params['product'] = array(
			'description'       => __( 'Limit result set to reviews assigned a specific product.', 'woocommerce' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['search'] = array(
			'description'        => __( 'Limit results to those matching a string.', 'woocommerce' ),
			'type'               => 'string',
			'sanitize_callback'  => 'sanitize_text_field',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Get the Product Review's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'product_review',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'review' => array(
					'description' => __( 'The content of the review.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'date_created' => array(
					'description' => __( "The date the review was created, in the site's timezone.", 'woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'date_created_gmt' => array(
					'description' => __( "The date the review was created, as GMT.", 'woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'rating' => array(
					'description' => __( 'Review rating (0 to 5).', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'name' => array(
					'description' => __( 'Reviewer name.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'email' => array(
					'description' => __( 'Reviewer email.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'avatar_urls' => array(
					'description' => __( "URLs for the reviewer's avatar.", 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
				),
				'verified' => array(
					'description' => __( 'Shows if the reviewer bought the product or not.', 'woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status' => array(
					'description' => __( 'Status of the review', 'woocommerce' ),
					'type'        => 'string',
					'enum'        => array( 'pending', 'approved', 'trash', 'spam' ),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'product' => array(
					'description' => __( 'Basic information on the product that the review is for.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'properties' => array(
						'id' => array(
							'description' => __( 'ID of the product.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'name' => array(
							'description' => __( 'Name of the product.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'image' => array(
							'description' => __( 'Featured image for the product.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );
	}

}
