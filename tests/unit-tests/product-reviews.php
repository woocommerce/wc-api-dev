<?php
/**
 * Tests for the product reviews REST API.
 *
 * @package WooCommerce\Tests\API
 * @since 3.0.0
 */

class Product_Reviews extends WC_REST_Unit_Test_Case {

	/**
	 * Setup our test server, endpoints, and user info.
	 */
	public function setUp() {
		parent::setUp();
		$this->endpoint = new WC_REST_DEV_Product_Reviews_Controller();
		$this->user = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
	}

	/**
	 * Test route registration.
	 *
	 * @since 3.0.0
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wc/v3/products/reviews', $routes );
		$this->assertArrayHasKey( '/wc/v3/products/(?P<product_id>[\d]+)/reviews', $routes );
		$this->assertArrayHasKey( '/wc/v3/products/(?P<product_id>[\d]+)/reviews/(?P<id>[\d]+)', $routes );
	}

	/**
	 * Test getting all product reviews (/products/reviews).
	 */
	public function test_get_product_reviews() {
		wp_set_current_user( $this->user );

		$product = WC_Helper_Product::create_simple_product();

		// Create a review that is different from the ones created by create_product_review.
		wp_insert_comment( array(
			'comment_post_ID'      => $product->get_id(),
			'comment_author'       => 'admin',
			'comment_author_email' => 'woo@woo.local',
			'comment_author_url'   => '',
			'comment_date'         => '2016-01-01T11:11:11',
			'comment_content'      => 'Hello world',
			'comment_approved'     => 0,
			'comment_type'         => 'review',
		) );

		for ( $i = 0; $i < 4; $i++ ) {
			WC_Helper_Product::create_product_review( $product->get_id() );
		}

		$product2 = WC_Helper_Product::create_simple_product();
		for ( $i = 0; $i < 5; $i++ ) {
			WC_Helper_Product::create_product_review( $product2->get_id() );
		}

		$product3 = WC_Helper_Product::create_simple_product();
		for ( $i = 0; $i < 5; $i++ ) {
			$review_id = WC_Helper_Product::create_product_review( $product3->get_id() );
		}

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/reviews' ) );

		$all_product_reviews = $response->get_data();
		$headers             = $response->get_headers();

		// Test pagination
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 10, count( $all_product_reviews ) );
		$this->assertEquals( 15, $headers['X-WP-Total'] );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );

		$request = new WP_REST_Request( 'GET', '/wc/v3/products/reviews' );
		$request->set_param( 'page', '2' );
		$response = $this->server->dispatch( $request );
		$product_reviews = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 5, count( $product_reviews ) );

		$request = new WP_REST_Request( 'GET', '/wc/v3/products/reviews' );
		$request->set_param( 'per_page', '15' );
		$response = $this->server->dispatch( $request );
		$product_reviews = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 15, count( $product_reviews ) );

		// Test status
		$request = new WP_REST_Request( 'GET', '/wc/v3/products/reviews' );
		$request->set_param( 'status', 'pending' );
		$response = $this->server->dispatch( $request );
		$product_reviews = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 1, count( $product_reviews ) );

		// Test search
		$request = new WP_REST_Request( 'GET', '/wc/v3/products/reviews' );
		$request->set_param( 'search', 'Hello world' );
		$response = $this->server->dispatch( $request );
		$product_reviews = $response->get_data();

		// Test product filtering
		$request = new WP_REST_Request( 'GET', '/wc/v3/products/reviews' );
		$request->set_param( 'product', $product->get_id() );
		$response = $this->server->dispatch( $request );
		$product_reviews = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 5, count( $product_reviews ) );

		// Test response
		$this->assertContains( array(
			'id'               => $review_id,
			'date_created'     => $product_reviews[0]['date_created'],
			'date_created_gmt' => $product_reviews[0]['date_created_gmt'],
			'review'           => "<p>Review content here</p>\n",
			'rating'           => 0,
			'name'             => 'admin',
			'email'            => 'woo@woo.local',
			'avatar_urls'      => rest_get_avatar_urls( 'woo@woo.local' ),
			'verified'         => false,
			'status'           => 'approved',
			'product'          => array(
				'id'    => $product3->get_id(),
				'name'  => $product3->get_name(),
				'image' => '',
			),
			'_links' => array(
				'self'       => array(
					array(
						'href' => rest_url( '/wc/v3/products/' . $product3->get_id() . '/reviews/' . $review_id ),
					),
				),
				'collection' => array(
					array(
						'href' => rest_url( '/wc/v3/products/' . $product3->get_id() . '/reviews' ),
					),
				),
				'up' => array(
					array(
						'href' => rest_url( '/wc/v3/products/' . $product3->get_id() ),
					),
				),
			),
		), $all_product_reviews );

	}

	/**
	 * Tests to make sure /product/reviews cannot be viewed without valid permissions.
	 */
	public function test_get_product_reviews_without_permission() {
		wp_set_current_user( 0 );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/reviews' ) );
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test getting all product reviews for a specific product.
	 *
	 * @since 3.0.0
	 */
	public function test_get_all_product_reviews_for_product() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		// Create 10 products reviews for the product
		for ( $i = 0; $i < 10; $i++ ) {
			$review_id = WC_Helper_Product::create_product_review( $product->get_id() );
		}

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() . '/reviews' ) );
		$product_reviews = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 10, count( $product_reviews ) );
		$this->assertContains( array(
			'id'               => $review_id,
			'date_created'     => $product_reviews[0]['date_created'],
			'date_created_gmt' => $product_reviews[0]['date_created_gmt'],
			'review'           => "<p>Review content here</p>\n",
			'rating'           => 0,
			'name'             => 'admin',
			'email'            => 'woo@woo.local',
			'avatar_urls'      => rest_get_avatar_urls( 'woo@woo.local' ),
			'verified'         => false,
			'status'           => 'approved',
			'product'          => array(
				'id'    => $product->get_id(),
				'name'  => $product->get_name(),
				'image' => '',
			),
			'_links' => array(
				'self'       => array(
					array(
						'href' => rest_url( '/wc/v3/products/' . $product->get_id() . '/reviews/' . $review_id ),
					),
				),
				'collection' => array(
					array(
						'href' => rest_url( '/wc/v3/products/' . $product->get_id() . '/reviews' ),
					),
				),
				'up' => array(
					array(
						'href' => rest_url( '/wc/v3/products/' . $product->get_id() ),
					),
				),
			),
		), $product_reviews );
	}

	/**
	 * Tests to make sure product reviews cannot be viewed without valid permissions.
	 *
	 * @since 3.0.0
	 */
	public function test_get_all_product_reviews_for_product_without_permission() {
		wp_set_current_user( 0 );
		$product = WC_Helper_Product::create_simple_product();
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() . '/reviews' ) );
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Tests to make sure an error is returned when an invalid product is loaded.
	 *
	 * @since 3.0.0
	 */
	public function test_get_all_product_reviews_for_invalid_product() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/0/reviews' ) );
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Tests getting a single product review.
	 *
	 * @since 3.0.0
	 */
	public function test_get_product_review() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		$product_review_id = WC_Helper_Product::create_product_review( $product->get_id() );

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() . '/reviews/' . $product_review_id ) );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( array(
			'id'               => $product_review_id,
			'date_created'     => $data['date_created'],
			'date_created_gmt' => $data['date_created_gmt'],
			'review'           => "<p>Review content here</p>\n",
			'rating'           => 0,
			'name'             => 'admin',
			'email'            => 'woo@woo.local',
			'avatar_urls'      => rest_get_avatar_urls( 'woo@woo.local' ),
			'verified'         => false,
			'status'           => 'approved',
			'product'          => array(
				'id'    => $product->get_id(),
				'name'  => $product->get_name(),
				'image' => '',
			),
		), $data );
	}

	/**
	 * Tests getting a single product review without the correct permissions.
	 *
	 * @since 3.0.0
	 */
	public function test_get_product_review_without_permission() {
		wp_set_current_user( 0 );
		$product = WC_Helper_Product::create_simple_product();
		$product_review_id = WC_Helper_Product::create_product_review( $product->get_id() );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() . '/reviews/' . $product_review_id ) );
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Tests getting a product review with an invalid ID.
	 *
	 * @since 3.0.0
	 */
	public function test_get_product_review_invalid_id() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() . '/reviews/0' ) );
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Tests creating a product review.
	 *
	 * @since 3.0.0
	 */
	public function test_create_product_review() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/' . $product->get_id() . '/reviews' );
		$request->set_body_params( array(
			'review' => 'Hello world.',
			'name'   => 'Admin',
			'email'  => 'woo@woo.local',
			'rating' => '5',
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 201, $response->get_status() );
		$this->assertEquals( array(
			'id'               => $data['id'],
			'date_created'     => $data['date_created'],
			'date_created_gmt' => $data['date_created_gmt'],
			'review'           => 'Hello world.',
			'rating'           => 5,
			'name'             => 'Admin',
			'email'            => 'woo@woo.local',
			'avatar_urls'      => rest_get_avatar_urls( 'woo@woo.local' ),
			'verified'         => false,
			'status'           => 'approved',
			'product'          => array(
				'id'    => $product->get_id(),
				'name'  => $product->get_name(),
				'image' => '',
			),
		), $data );
	}

	/**
	 * Tests creating a product review without required fields.
	 *
	 * @since 3.0.0
	 */
	public function test_create_product_review_invalid_fields() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();

		// missing review
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/' . $product->get_id() . '/reviews' );
		$request->set_body_params( array(
			'name'   => 'Admin',
			'email'  => 'woo@woo.local',
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );

		// missing name
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/' . $product->get_id() . '/reviews' );
		$request->set_body_params( array(
			'review' => 'Hello world.',
			'email'  => 'woo@woo.local',
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );

		// missing email
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/' . $product->get_id() . '/reviews' );
		$request->set_body_params( array(
			'review' => 'Hello world.',
			'name'   => 'Admin',
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Tests updating a product review.
	 *
	 * @since 3.0.0
	 */
	public function test_update_product_review() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		$product_review_id = WC_Helper_Product::create_product_review( $product->get_id() );

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() . '/reviews/' . $product_review_id ) );
		$data     = $response->get_data();
		$this->assertEquals( "<p>Review content here</p>\n", $data['review'] );
		$this->assertEquals( 'admin', $data['name'] );
		$this->assertEquals( 'woo@woo.local', $data['email'] );
		$this->assertEquals( 0, $data['rating'] );

		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() . '/reviews/' . $product_review_id );
		$request->set_body_params( array(
			'review' => 'Hello world - updated.',
			'name'   => 'Justin',
			'email'  => 'woo2@woo.local',
			'rating' => 3,
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( 'Hello world - updated.', $data['review'] );
		$this->assertEquals( 'Justin', $data['name'] );
		$this->assertEquals( 'woo2@woo.local', $data['email'] );
		$this->assertEquals( 3, $data['rating'] );
	}

	/**
	 * Tests updating a product review without the correct permissions.
	 *
	 * @since 3.0.0
	 */
	public function test_update_product_review_without_permission() {
		wp_set_current_user( 0 );
		$product = WC_Helper_Product::create_simple_product();
		$product_review_id = WC_Helper_Product::create_product_review( $product->get_id() );

		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() . '/reviews/' . $product_review_id );
		$request->set_body_params( array(
			'review' => 'Hello world.',
			'name'   => 'Admin',
			'email'  => 'woo@woo.dev',
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Tests that updating a product review with an invalid id fails.
	 *
	 * @since 3.0.0
	 */
	public function test_update_product_review_invalid_id() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();

		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() . '/reviews/0' );
		$request->set_body_params( array(
			'review' => 'Hello world.',
			'name'   => 'Admin',
			'email'  => 'woo@woo.dev',
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test deleting a product review.
	 *
	 * @since 3.0.0
	 */
	public function test_delete_product_review() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		$product_review_id = WC_Helper_Product::create_product_review( $product->get_id() );

		$request = new WP_REST_Request( 'DELETE', '/wc/v3/products/' . $product->get_id() . '/reviews/' . $product_review_id );
		$request->set_param( 'force', true );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Test deleting a product review without permission/creds.
	 *
	 * @since 3.0.0
	 */
	public function test_delete_product_without_permission() {
		wp_set_current_user( 0 );
		$product = WC_Helper_Product::create_simple_product();
		$product_review_id = WC_Helper_Product::create_product_review( $product->get_id() );

		$request = new WP_REST_Request( 'DELETE', '/wc/v3/products/' . $product->get_id() . '/reviews/' . $product_review_id );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test deleting a product review with an invalid id.
	 *
	 * @since 3.0.0
	 */
	public function test_delete_product_review_invalid_id() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		$product_review_id = WC_Helper_Product::create_product_review( $product->get_id() );

		$request = new WP_REST_Request( 'DELETE', '/wc/v3/products/' . $product->get_id() . '/reviews/0' );
		$request->set_param( 'force', true );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test batch managing product reviews.
	 */
	public function test_product_reviews_batch() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();

		$review_1_id = WC_Helper_Product::create_product_review( $product->get_id() );
		$review_2_id = WC_Helper_Product::create_product_review( $product->get_id() );
		$review_3_id = WC_Helper_Product::create_product_review( $product->get_id() );
		$review_4_id = WC_Helper_Product::create_product_review( $product->get_id() );

		$request = new WP_REST_Request( 'POST', '/wc/v3/products/' . $product->get_id() . '/reviews/batch' );
		$request->set_body_params( array(
			'update' => array(
				array(
					'id'     => $review_1_id,
					'review' => 'Updated review.',
				),
			),
			'delete' => array(
				$review_2_id,
				$review_3_id,
			),
			'create' => array(
				array(
					'review' => 'New review.',
					'name'   => 'Justin',
					'email'  => 'woo3@woo.local',
				),
			),
		) );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 'Updated review.', $data['update'][0]['review'] );
		$this->assertEquals( 'New review.', $data['create'][0]['review'] );
		$this->assertEquals( $review_2_id, $data['delete'][0]['id'] );
		$this->assertEquals( $review_3_id, $data['delete'][1]['id'] );

		$request = new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() . '/reviews' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 3, count( $data ) );
	}

	/**
	 * Test the product review schema.
	 *
	 * @since 3.0.0
	 */
	public function test_product_review_schema() {
		wp_set_current_user( $this->user );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'OPTIONS', '/wc/v3/products/' . $product->get_id() . '/reviews' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$properties = $data['schema']['properties'];

		$this->assertEquals( 11, count( $properties ) );
		$this->assertArrayHasKey( 'id', $properties );
		$this->assertArrayHasKey( 'review', $properties );
		$this->assertArrayHasKey( 'date_created', $properties );
		$this->assertArrayHasKey( 'date_created_gmt', $properties );
		$this->assertArrayHasKey( 'rating', $properties );
		$this->assertArrayHasKey( 'name', $properties );
		$this->assertArrayHasKey( 'email', $properties );
		$this->assertArrayHasKey( 'avatar_urls', $properties );
		$this->assertArrayHasKey( 'verified', $properties );
		$this->assertArrayHasKey( 'status', $properties );
		$this->assertArrayHasKey( 'product', $properties );
	}
}
