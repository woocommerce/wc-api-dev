<?php
/**
 * Tests for the order notes REST API.
 *
 * @package WooCommerce\Tests\API
 * @since 3.0.0
 */
class WC_Tests_API_Order_Notes extends WC_REST_Unit_Test_Case {

	/**
	 * Array of notes to track.
	 * @var array
	 */
	protected $notes = array();

	/**
	 * An order to hold these notes.
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
		foreach ( $this->notes as $note ) {
			wc_delete_order_note( $note->comment_ID );
		}
		$this->notes = array();
	}

	/**
	 * Test route registration.
	 * @since 3.0.0
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wc/v3/orders/(?P<order_id>[\d]+)/notes', $routes );
		$this->assertArrayHasKey( '/wc/v3/orders/(?P<order_id>[\d]+)/notes/(?P<id>[\d]+)', $routes );
	}

	/**
	 * Test getting all notes for an order.
	 * @since 3.0.0
	 */
	public function test_get_items() {
		wp_set_current_user( $this->user );

		// Create 3 order notes.
		for ( $i = 0; $i < 3; $i++ ) {
			$this->notes[] = WC_Helper_Order_Note::create_note( $this->order_id, $this->user );
		}

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', "/wc/v3/orders/$this->order_id/notes" ) );
		$notes    = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 3, count( $notes ) );
		$this->stoppit_and_tidyup();
	}
	
	/**
	 * Tests to make sure order notes cannot be viewed without valid permissions.
	 *
	 * @since 3.0.0
	 */
	public function test_get_items_without_permission() {
		wp_set_current_user( 0 );
		$this->notes[] = WC_Helper_Order_Note::create_note( $this->order_id );
		$response      = $this->server->dispatch( new WP_REST_Request( 'GET', "/wc/v3/orders/$this->order_id/notes" ) );
		$this->assertEquals( 401, $response->get_status() );
		$this->stoppit_and_tidyup();
	}

	/**
	 * Tests getting a single order note.
	 * @since 3.0.0
	 */
	public function test_get_item() {
		wp_set_current_user( $this->user );
		$note          = WC_Helper_Order_Note::create_note( $this->order_id, $this->user );
		$this->notes[] = $note;
		$request       = new WP_REST_Request( 'GET', "/wc/v3/orders/$this->order_id/notes/$note->comment_ID" );
		$response      = $this->server->dispatch( $request );
		$data          = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $note->comment_ID, $data['id'] );
		$this->assertArrayHasKey( 'customer_note', $data );
		$this->assertArrayHasKey( 'date_created', $data );
		$this->assertArrayHasKey( 'author', $data );
		$this->assertArrayHasKey( 'note', $data );
		$this->assertEquals( $data['author'], 'system' );
		$this->assertEquals( $data['note'], 'This is an order note.' );
		$this->stoppit_and_tidyup();
	}
	
	/**
	 * Tests creating an order note by a given user.
	 * @since 3.0.0
	 */
	public function test_create_note() {
		wp_set_current_user( $this->user );
		$request = new WP_REST_Request( 'POST', "/wc/v3/orders/$this->order_id/notes" );
		$request->set_body_params( array(
			'customer_note' => false,
			'added_by_user' => true,
			'note'          => 'This is testing content.',
		) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$note     = wc_get_order_note( $data['id'] );
		$user     = get_user_by( 'id', get_current_user_id() );

		$this->assertEquals( 201, $response->get_status() );
		// Verify the API response has correct data
		$this->assertArrayHasKey( 'customer_note', $data );
		$this->assertArrayHasKey( 'date_created', $data );
		$this->assertArrayHasKey( 'note', $data );
		$this->assertArrayHasKey( 'author', $data );
		$this->assertEquals( $data['note'], 'This is testing content.' );
		$this->assertEquals( $data['author'], $user->display_name );

		// Verify that Note object has correct data
		$this->assertEquals( $note->customer_note, false );
		$this->assertEquals( $note->added_by, $user->display_name );
		$this->assertEquals( $note->content, 'This is testing content.' );

		wc_delete_order_note( $data['id'] );
	}

	/**
	 * Tests creating an order note by the "system".
	 * @since 3.0.0
	 */
	public function test_create_system_note() {
		wp_set_current_user( $this->user );
		$request = new WP_REST_Request( 'POST', "/wc/v3/orders/$this->order_id/notes" );
		$request->set_body_params( array(
			'customer_note' => true,
			'note'          => 'This is testing content.',
		) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$note     = wc_get_order_note( $data['id'] );

		$this->assertEquals( 201, $response->get_status() );
		// Verify the API response has correct data
		$this->assertArrayHasKey( 'customer_note', $data );
		$this->assertArrayHasKey( 'date_created', $data );
		$this->assertArrayHasKey( 'note', $data );
		$this->assertArrayHasKey( 'author', $data );
		$this->assertEquals( $data['note'], 'This is testing content.' );
		$this->assertEquals( $data['author'], 'system' );

		// Verify that Note object has correct data
		$this->assertEquals( $note->customer_note, true );
		$this->assertEquals( $note->added_by, 'system' );
		$this->assertEquals( $note->content, 'This is testing content.' );

		wc_delete_order_note( $data['id'] );
	}

	/**
	 * Tests creating an order note without required fields.
	 * @since 3.0.0
	 */
	public function test_create_order_note_invalid_fields() {
		wp_set_current_user( $this->user );
		$request = new WP_REST_Request( 'POST', "/wc/v3/orders/$this->order_id/notes" );
		$request->set_body_params( array(
			'customer_note' => false,
		) );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
	}
	
	/**
	 * Test deleting an order note.
	 * @since 3.0.0
	 */
	public function test_delete_order_note() {
		wp_set_current_user( $this->user );
		$note     = WC_Helper_Order_Note::create_note( $this->order_id, $this->user );
		$request  = new WP_REST_Request( 'DELETE', "/wc/v3/orders/$this->order_id/notes/$note->comment_ID" );
		$request->set_param( 'force', true );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( null, wc_get_order_note( $note->comment_ID ) );
	}

	/**
	 * Test deleting an order note without permission/creds.
	 * @since 3.0.0
	 */
	public function test_delete_order_note_without_permission() {
		wp_set_current_user( 0 );
		$note     = WC_Helper_Order_Note::create_note( $this->order_id );
		$request  = new WP_REST_Request( 'DELETE', "/wc/v3/orders/$this->order_id/notes/$note->comment_ID" );
		$request->set_param( 'force', true );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 401, $response->get_status() );
		wc_delete_order_note( $note->comment_ID );
	}

	/**
	 * Test deleting an order note with an invalid id.
	 *
	 * @since 3.0.0
	 */
	public function test_delete_order_note_invalid_id() {
		wp_set_current_user( $this->user );
		$request  = new WP_REST_Request( 'DELETE', "/wc/v3/orders/$this->order_id/notes/9999999" );
		$request->set_param( 'force', true );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}
}
