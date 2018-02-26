<?php
/**
 * Tests for Data API.
 *
 * @package WooCommerce\Tests\API
 * @since 3.1.0
 */

class Data_API extends WC_REST_Unit_Test_Case {
	/**
	 * Setup our test server and user info.
	 */
	public function setUp() {
		parent::setUp();
		$this->user = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
	}

	/**
	 * Test route registration.
	 *
	 * @since 3.1.0
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wc/v3/data', $routes );
		$this->assertArrayHasKey( '/wc/v3/data/continents', $routes );
		$this->assertArrayHasKey( '/wc/v3/data/countries', $routes );
		$this->assertArrayHasKey( '/wc/v3/data/currencies', $routes );
		$this->assertArrayHasKey( '/wc/v3/data/currencies/current', $routes );
		$this->assertArrayHasKey( '/wc/v3/data/currencies/(?P<currency>[\w-]{3})', $routes );
	}

	/**
	 * Test getting the data index.
	 * @since 3.1.0
	 */
	public function test_get_index() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data' ) );
		$index = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertCount( 3, $index );
		$this->assertEquals( 'continents', $index[0]['slug'] );
		$this->assertEquals( 'countries', $index[1]['slug'] );
		$this->assertEquals( 'currencies', $index[2]['slug'] );
	}

	/**
	 * Test getting locations.
	 * @since 3.1.0
	 */
	public function test_get_locations() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/continents' ) );
		$locations = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( is_array( $locations ) );
		$this->assertGreaterThan( 1, count( $locations ) );
		$this->assertNotEmpty( $locations[0]['code'] );
		$this->assertNotEmpty( $locations[0]['name'] );
		$this->assertNotEmpty( $locations[0]['countries'] );
		$this->assertNotEmpty( $locations[0]['_links'] );
	}

	/**
	 * Test getting locations restricted to one continent.
	 * @since 3.1.0
	 */
	public function test_get_locations_from_continent() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/continents/na' ) );
		$locations = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( is_array( $locations ) );
		$this->assertEquals( 'NA', $locations['code'] );
		$this->assertNotEmpty( $locations['name'] );
		$this->assertNotEmpty( $locations['countries'] );
		$links = $response->get_links();
		$this->assertCount( 2, $links );
	}

	/**
	 * Test getting locations with no country specified
	 * @since 3.1.0
	 */
	public function test_get_locations_all_countries() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/countries' ) );
		$locations = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertGreaterThan( 1, count( $locations ) );
		$this->assertNotEmpty( $locations[0]['code'] );
		$this->assertNotEmpty( $locations[0]['name'] );
		$this->assertArrayHasKey( 'states', $locations[0] );
		$this->assertNotEmpty( $locations[0]['_links'] );
	}

	/**
	 * Test getting locations restricted to one country.
	 * Use a country (US) that includes locale info
	 * @since 3.1.0
	 */
	public function test_get_locations_from_country() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/countries/us' ) );
		$locations = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( is_array( $locations ) );
		$this->assertEquals( 'US', $locations['code'] );
		$this->assertNotEmpty( $locations['name'] );
		$this->assertCount( 54, $locations['states'] );
		$this->assertNotEmpty( $locations['currency_code'] );
		$this->assertNotEmpty( $locations['currency_pos'] );
		$this->assertNotEmpty( $locations['thousand_sep'] );
		$this->assertNotEmpty( $locations['decimal_sep'] );
		$this->assertNotEmpty( $locations['num_decimals'] );
		$this->assertNotEmpty( $locations['dimension_unit'] );
		$this->assertNotEmpty( $locations['weight_unit'] );
		$links = $response->get_links();
		$this->assertCount( 2, $links );
	}

	/**
	 * Test getting locations from an invalid code.
	 * @since 3.1.0
	 */
	public function test_get_locations_from_invalid_continent() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/continents/xx' ) );
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test getting locations from an invalid code.
	 * @since 3.1.0
	 */
	public function test_get_locations_from_invalid_country() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/countries/xx' ) );
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test getting locations without permissions.
	 * @since 3.1.0
	 */
	public function test_get_continents_without_permission() {
		wp_set_current_user( 0 );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/continents' ) );
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test getting locations without permissions.
	 * @since 3.1.0
	 */
	public function test_get_countries_without_permission() {
		wp_set_current_user( 0 );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/countries' ) );
		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test getting currencies.
	 */
	public function test_get_currencies() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/currencies' ) );
		$currencies = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( is_array( $currencies ) );
		$this->assertGreaterThan( 1, count( $currencies ) );
		$this->assertNotEmpty( $currencies[0]['code'] );
		$this->assertNotEmpty( $currencies[0]['name'] );
		$this->assertNotEmpty( $currencies[0]['symbol'] );
		$this->assertNotEmpty( $currencies[0]['_links'] );
	}

	/**
	 * Test getting a single currency.
	 */
	public function test_get_currency() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/currencies/CAD' ) );
		$currency = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'CAD', $currency['code'] );
		$this->assertEquals( 'Canadian dollar', $currency['name'] );
		$this->assertEquals( '&#36;', $currency['symbol'] );
		$links = $response->get_links();
		$this->assertCount( 2, $links );
	}

	/**
	 * Test getting current currency.
	 */
	public function test_get_current_currency() {
		$current = get_option( 'woocommerce_currency' );
		update_option( 'woocommerce_currency', 'BTC' );

		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/currencies/current' ) );
		$currency = $response->get_data();
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'BTC', $currency['code'] );
		$this->assertEquals( 'Bitcoin', $currency['name'] );
		$this->assertEquals( '&#3647;', $currency['symbol'] );
		$links = $response->get_links();
		$this->assertCount( 2, $links );

		update_option( 'woocommerce_currency', $current );
	}

	/**
	 * Test getting currency from an invalid code.
	 */
	public function test_get_currency_from_invalid_code() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/currencies/xxy' ) );
		$this->assertEquals( 404, $response->get_status() );
		$this->assertEquals( 'woocommerce_rest_data_invalid_currency', $response->data['code'] );
	}

	/**
	 * Test getting currency from an code that is too long.
	 */
	public function test_get_currency_from_long_code() {
		wp_set_current_user( $this->user );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/currencies/xxyy' ) );
		$this->assertEquals( 404, $response->get_status() );
		$this->assertEquals( 'rest_no_route', $response->data['code'] );
	}

	/**
	 * Test getting currencies without permissions.
	 */
	public function test_get_currency_without_permission() {
		wp_set_current_user( 0 );
		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/wc/v3/data/currencies' ) );
		$this->assertEquals( 401, $response->get_status() );
	}

}
