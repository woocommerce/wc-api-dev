<?php
/**
 * Tests for Data API.
 *
 * @package WooCommerce\Tests\API
 */

class Data_Counts_API extends WC_REST_Unit_Test_Case {
	/**
	 * Array of order to track
	 * @var array
	 */
	protected $orders = array();

	/**
	 * Setup our test server.
	 */
	public function setUp() {
		parent::setUp();
		$this->endpoint = new WC_REST_DEV_Orders_Controller();
		$this->user = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
	}


	/**
	 * Test route registration.
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wc/v3/data', $routes );
		$this->assertArrayHasKey( '/wc/v3/data/counts', $routes );
	}

	/**
	 * Cleanup.
	 */
	public function stoppit_and_tidyup() {
		foreach ( $this->orders as $order ) {
			wp_delete_post( $order->get_id(), true );
		}
		$this->orders = array();
	}

	/**
	 * Test getting counts.
	 */
	public function test_get_counts() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/counts' ) );
		$counts = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( is_array( $counts ) );
		$this->assertGreaterThan( 1, count( $counts ) );
		$this->assertNotEmpty( $counts['orders'] );
		$this->assertNotEmpty( $counts['products'] );
		$this->assertEquals( $counts['products']['all'], 0 );
		$this->assertNotEmpty( $counts['reviews'] );
		$this->assertEquals( $counts['reviews']['all'], 0 );
	}
	
	/**
	 * Test getting counts using the site's data.
	 */
	public function test_get_order_counts() {
		wp_set_current_user( $this->user );
		// Create 10 orders.
		for ( $i = 0; $i < 10; $i++ ) {
			$this->orders[] = WC_Helper_Order::create_order( $this->user );
		}

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/counts' ) );
		$counts = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertNotEmpty( $counts['orders'] );
		$this->assertEquals( $counts['orders']['wc-pending'], 10 );
		
		$this->stoppit_and_tidyup();
	}
}

