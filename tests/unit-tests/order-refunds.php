<?php
/**
 * Tests for the order refunds REST API.
 *
 * @package WooCommerce\Tests\API
 * @since 3.0.0
 */
class WC_Tests_API_Order_Refunds extends WC_REST_Unit_Test_Case {

	/**
	 * Array of refunds to track.
	 * @var array
	 */
	protected $refunds = array();

	/**
	 * An order to hold these refunds.
	 * @var int
	 */
	protected $order_id;

	/**
	 * Setup our test server.
	 */
	public function setUp() {
		parent::setUp();
		$this->endpoint = new WC_REST_DEV_Orders_Controller();
		$this->user = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		$order = WC_Helper_Order::create_order();
		$this->order_id = $order->get_id();
	}

	/**
	 * Cleanup.
	 */
	public function stoppit_and_tidyup() {
		wp_delete_post( $this->order_id, true );
		foreach ( $this->refunds as $refund ) {
			$refund->delete();
		}
		$this->refunds = array();
	}

	/**
	 * Test route registration.
	 * @since 3.0.0
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wc/v3/orders/(?P<order_id>[\d]+)/refunds', $routes );
		$this->assertArrayHasKey( '/wc/v3/orders/(?P<order_id>[\d]+)/refunds/(?P<id>[\d]+)', $routes );
	}

	/**
	 * Test getting all refunds for an order.
	 * @since 3.0.0
	 */
	public function test_get_items() {
		wp_set_current_user( $this->user );

		// Create 2 partial order refunds.
		for ( $i = 0; $i < 2; $i++ ) {
			$this->refunds[] = WC_Helper_Order_Refund::create_refund( $this->order_id, $this->user );
		}

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', "/wc/v3/orders/$this->order_id/refunds" ) );
		$refunds  = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 2, count( $refunds ) );
		$this->stoppit_and_tidyup();
	}

	/**
	 * Tests getting a single order refund.
	 * @since 3.0.0
	 */
	public function test_get_item() {
		wp_set_current_user( $this->user );		
		$refund          = WC_Helper_Order_Refund::create_refund_with_items( $this->order_id, $this->user );
		$this->refunds[] = $refund;

		$request  = new WP_REST_Request( 'GET', "/wc/v3/orders/$this->order_id/refunds/" . $refund->get_id() );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'date_created', $data );
		$this->assertEquals( $data['amount'], '5.00' );
		$this->assertEquals( $data['reason'], 'Testing' );
		$this->assertArrayHasKey( 'line_items', $data );
		$this->assertEquals( 1, count( $data['line_items'] ) );

		$this->stoppit_and_tidyup();
	}

	/**
	 * Tests creating an order refund by a given user.
	 * @since 3.0.0
	 */
	public function test_create_refund() {
		wp_set_current_user( $this->user );
		$request = new WP_REST_Request( 'POST', "/wc/v3/orders/$this->order_id/refunds" );
		$request->set_body_params( array(
			'amount'     => '5.0',
			'reason'     => 'This is testing content.',
			'api_refund' => false,
		) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$refund   = new WC_Order_Refund( $data['id'] );

		$this->assertEquals( 201, $response->get_status() );
		// Verify the API response has correct data
		$this->assertArrayHasKey( 'date_created', $data );
		$this->assertEquals( $data['amount'], '5.00' );
		$this->assertEquals( $data['reason'], 'This is testing content.' );
		$this->assertEquals( $data['refunded_by'], get_current_user_id() );

		// Verify that Note object has correct data
		$this->assertEquals( $refund->get_amount(), '5.00' );
		$this->assertEquals( $refund->get_reason(), 'This is testing content.' );

		$refund->delete();
	}
	
	/**
	 * Tests creating an order refund by a given user.
	 * @since 3.0.0
	 */
	public function test_create_refund_with_items() {
		wp_set_current_user( $this->user );
		$line_items = WC_Helper_Order_Refund::create_refund_line_items( $this->order_id, 1 );
		$request    = new WP_REST_Request( 'POST', "/wc/v3/orders/$this->order_id/refunds" );
		$request->set_body_params( array(
			'amount'     => '5.0',
			'reason'     => 'This is testing content.',
			'api_refund' => false,
			'line_items' => $line_items,
		) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$refund   = new WC_Order_Refund( $data['id'] );

		$this->assertEquals( 201, $response->get_status() );
		// Verify the API response has correct data
		$this->assertArrayHasKey( 'date_created', $data );
		$this->assertArrayHasKey( 'line_items', $data );
		$this->assertEquals( $data['amount'], '5.00' );
		$this->assertEquals( $data['reason'], 'This is testing content.' );
		$this->assertEquals( $data['refunded_by'], get_current_user_id() );
		$this->assertEquals( 1, count( $data['line_items'] ) );

		// Verify that Note object has correct data
		$this->assertEquals( $refund->get_amount(), '5.00' );
		$this->assertEquals( $refund->get_reason(), 'This is testing content.' );
		$items = $refund->get_items();
		$this->assertEquals( 1, count( $items ) );

		$refund->delete();
	}
}