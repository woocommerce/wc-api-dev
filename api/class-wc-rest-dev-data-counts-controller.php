<?php
/**
 * REST API Data object counts controller.
 *
 * Handles requests to the /data/counts endpoint.
 *
 * @author   Automattic
 * @category API
 * @package  WooCommerce/API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Data Counts controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Data_Counts_Controller extends WC_REST_Dev_Data_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'data/counts';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Return the list of order counts.
	 *
	 * @return array list of counts for all order statuses
	 */
	public function get_order_counts() {
		$statuses = array_keys( wc_get_order_statuses() );
		// Pre-fill the array with all statuses at zero
		$counts = array_combine( $statuses, array_fill( 0, count( $statuses ), 0 ) );
		foreach ( wc_get_order_types( 'order-count' ) as $type ) {
			$counts_for_type = (array) wp_count_posts( $type );
			foreach ( $statuses as $status ) {
				$counts[ $status ] += $counts_for_type[ $status ] ? $counts_for_type[ $status ] : 0;
			}
		}
		return $counts;
	}

	/**
	 * Return the list of product counts.
	 *
	 * @return array list of counts for all product statuses
	 */
	public function get_product_counts() {
		global $wpdb;
		$count = wp_cache_get( 'wc-counts-products' );
		if ( false !== $count ) {
			return $count;
		}

		$counts = array();
		$counts['all'] = (int) $wpdb->get_var(
			"SELECT COUNT( DISTINCT posts.ID )
				FROM {$wpdb->posts} as posts
				WHERE 1=1
				AND posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status = 'publish'"
		);

		$low_stock_amount = get_option( 'woocommerce_notify_low_stock_amount' );
		$no_stock_amount = get_option( 'woocommerce_notify_no_stock_amount' );

		$counts['out-of-stock'] = (int) $wpdb->get_var(
			"SELECT COUNT( DISTINCT posts.ID )
				FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
				INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
				WHERE 1=1
				AND posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status = 'publish'
				AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$no_stock_amount}'"
		);

		$counts['low-inventory'] = (int) $wpdb->get_var(
			"SELECT COUNT( DISTINCT posts.ID )
				FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
				INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
				WHERE 1=1
				AND posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status = 'publish'
				AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$low_stock_amount}'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$no_stock_amount}'"
		);

		wp_cache_set( 'wc-counts-products', $counts );
		return $counts;
	}

	/**
	 * Return the list of review counts.
	 *
	 * @return array list of counts for all review statuses
	 */
	public function get_review_counts() {
		global $wpdb;
		$count = wp_cache_get( 'wc-counts-reviews' );
		if ( false !== $count ) {
			return $count;
		}

		$sql = "SELECT comment_approved, COUNT( * ) AS total
			FROM {$wpdb->comments}
			JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->comments}.comment_post_ID
			WHERE {$wpdb->posts}.post_type IN ('product')
			GROUP BY comment_approved";

		$totals = (array) $wpdb->get_results( $sql, ARRAY_A );

		$comment_count = array(
			'approved'            => 0,
			'awaiting_moderation' => 0,
			'spam'                => 0,
			'trash'               => 0,
			'post-trashed'        => 0,
			'total_comments'      => 0,
			'all'                 => 0,
		);

		foreach ( $totals as $row ) {
			switch ( $row['comment_approved'] ) {
				case 'trash':
					$comment_count['trash'] = (int) $row['total'];
					break;
				case 'post-trashed':
					$comment_count['post-trashed'] = (int) $row['total'];
					break;
				case 'spam':
					$comment_count['spam'] = (int) $row['total'];
					$comment_count['total_comments'] += $row['total'];
					break;
				case '1':
					$comment_count['approved'] = (int) $row['total'];
					$comment_count['total_comments'] += $row['total'];
					$comment_count['all'] += $row['total'];
					break;
				case '0':
					$comment_count['awaiting_moderation'] = (int) $row['total'];
					$comment_count['total_comments'] += $row['total'];
					$comment_count['all'] += $row['total'];
					break;
				default:
					break;
			}
		}

		wp_cache_set( 'wc-counts-reviews', $comment_count );
		return $comment_count;
	}

	/**
	 * Return the list of counts per object.
	 *
	 * @param  WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$data = array(
			'orders'   => $this->get_order_counts(),
			'products' => $this->get_product_counts(),
			'reviews'  => $this->get_review_counts(),
		);

		return $this->prepare_item_for_response( $data, $request );
	}

	/**
	 * Prepare the data object for response.
	 *
	 * @param object $item Data object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data     = $this->add_additional_fields_to_object( $item, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filter counts returned from the API.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param array            $item     Object count data.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters( 'woocommerce_rest_prepare_data_counts', $response, $item, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param object $item Data object.
	 * @return array Links for the given count collection.
	 */
	protected function prepare_links( $item ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		return $links;
	}

	/**
	 * Get the counts schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'data_counts',
			'type'       => 'object',
			'properties' => array(
				'orders' => array(
					'type'        => 'object',
					'description' => __( 'Collection of order totals by order status.', 'woocommerce' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'products' => array(
					'type'        => 'object',
					'description' => __( 'Collection of product totals by stock status.', 'woocommerce' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'reviews' => array(
					'type'        => 'object',
					'description' => __( 'Collection of review totals by comment status.', 'woocommerce' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}

/**
 * Clear the products count cache when products are added or updated, or when
 * the no/low stock options are changed.
 */
function woocommerce_clear_product_count_cache( $id ) {
	wp_cache_delete( 'wc-counts-products' );
}
add_action( 'woocommerce_update_product', 'woocommerce_clear_product_count_cache' );
add_action( 'woocommerce_new_product', 'woocommerce_clear_product_count_cache' );
add_action( 'update_option_woocommerce_notify_low_stock_amount', 'woocommerce_clear_product_count_cache' );
add_action( 'update_option_woocommerce_notify_no_stock_amount', 'woocommerce_clear_product_count_cache' );

/**
 * Clear the reviews count cache when comments are added, deleted, or moderated,
 * only applies to comments on products.
 */
function woocommerce_clear_review_count_cache( $comment_id ) {
	$comment = get_comment( $comment_id );
	$post_id = $comment ? $comment->comment_post_ID : false;
	if ( ! $post_id || 'product' !== get_post_type( $post_id ) ) {
		return;
	}
	// This is a comment on a product - a review - so clear the review cache
	wp_cache_delete( 'wc-counts-reviews' );
}
add_action( 'delete_comment', 'woocommerce_clear_review_count_cache' );
add_action( 'wp_insert_comment', 'woocommerce_clear_review_count_cache' );
add_action( 'wp_set_comment_status', 'woocommerce_clear_review_count_cache' );
