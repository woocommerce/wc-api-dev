<?php
/**
 * REST API WC MailChimp settings
 *
 * Handles requests that interact with MailChimp plugin.
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
 * @package WooCommerce/API
 */
class WC_REST_Dev_MailChimp_Settings_Controller extends WC_REST_Settings_Controller {

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
	protected $rest_base = 'mailchimp';

	/**
	 * MailChimp settings.
	 *
	 * @param  WP_REST_Request    $request    Request object.
	 * @return WP_REST_Response   $response   Response data.
	 */

	/**
	 * Register MailChimp settings routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
			)
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/api_key', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_api_key' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		'schema' => array( $this, 'get_api_key_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/store_info', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_store_info' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		'schema' => array( $this, 'get_store_info_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/campaign_defaults', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_campaign_defaults' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		//'schema' => array( $this, 'get_store_info_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/newsletter_setting', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_newsletter_settings' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		//'schema' => array( $this, 'get_api_key_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/newsletter_setting', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_newsletter_settings' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		//'schema' => array( $this, 'get_api_key_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/sync', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_sync_status' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		//'schema' => array( $this, 'get_api_key_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/sync', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'resync' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		//'schema' => array( $this, 'get_api_key_schema' ),
		) );
	}

	/**
	 * Check whether a given request has permission to view payment gateways.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		// if ( ! wc_rest_check_manager_permissions( 'payment_gateways', 'read' ) ) {
		// 	return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		// }
		return true;
	}

	/**
	 * Get current MailChimp settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_settings( $request ) {
		$options = get_option('mailchimp-woocommerce', array() );
		$options['mailchimp_active_tab'] = isset($options['mailchimp_active_tab']) ? $options['mailchimp_active_tab'] : "api_key";
		return rest_ensure_response( $options );
	}

	public function update_api_key( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'api_key';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$data                               = $handler->validate( $parameters );

		update_option('mailchimp-woocommerce', $data);

		return rest_ensure_response( $data );
	}

	/**
	 * Get the MailChimp api key schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_api_key_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'store_settings',
			'type'       => 'object',
			'properties' => array(
				'api_key' => array(
					'description' => __( 'MailChimp api key.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	public function update_store_info( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'store_info';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$data                               = $handler->validate( $parameters );

		update_option('mailchimp-woocommerce', $data);

		return rest_ensure_response( $data );
	}

	/**
	 * Get the payment gateway schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_store_info_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'store_info',
			'type'       => 'object',
			'properties' => array(
				'store_name' => array(
					'description' => __( 'Store name.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_street' => array(
					'description' => __( 'Store street.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_city' => array(
					'description' => __( 'Store city.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_state' => array(
					'description' => __( 'Store state.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_postal_code' => array(
					'description' => __( 'Store postal code.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_country' => array(
					'description' => __( 'Store country.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_phone' => array(
					'description' => __( 'Store_phone', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_locale' => array(
					'description' => __( 'Store locale', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_currency_code' => array(
					'description' => __( 'Store currency code', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'store_phone' => array(
					'description' => __( 'Store phone', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	public function update_campaign_defaults( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'campaign_defaults';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$data                               = $handler->validate( $parameters );

		update_option('mailchimp-woocommerce', $data);

		return rest_ensure_response( $data );
	}

	public function get_newsletter_settings( $request ) {
		$handler = MailChimp_Woocommerce_Admin::connect();
		$data    = $handler->getMailChimpLists();

		return rest_ensure_response( $data );
	}

	public function update_newsletter_settings( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'newsletter_settings';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$options                            = get_option('mailchimp-woocommerce', array());
		$mailchimp_active_tab               = $options['mailchimp_active_tab'];
	  $data                               = $handler->validate( $parameters );

		// if previous active tab was sync then we still want sync
		// because this call is just an update and not part of setup
		if( $mailchimp_active_tab == 'sync' ) {
			$data['mailchimp_active_tab'] = 'sync';
			update_option('mailchimp-woocommerce', $data);
		}

		return rest_ensure_response( $data );
	}

	public function get_sync_status( $request ) {
		$handler                  = MailChimp_Woocommerce_Admin::connect();
		$mailchimp_total_products = $mailchimp_total_orders = 0;
		$store_id                 = mailchimp_get_store_id();
		$product_count            = mailchimp_get_product_count();
		$order_count              = mailchimp_get_order_count();
		$store_syncing            = false;
		$last_updated_time        = get_option('mailchimp-woocommerce-resource-last-updated');
		$account_name             = 'n/a';
		$mailchimp_list_name      = 'n/a';

		if (!empty($last_updated_time)) {
		    $last_updated_time = mailchimp_date_local($last_updated_time);
		}

		if (($mailchimp_api = mailchimp_get_api()) && ($store = $mailchimp_api->getStore($store_id))) {

		    $store_syncing = $store->isSyncing();

		    if (($account_details = $handler->getAccountDetails())) {
		        $account_name = $account_details['account_name'];
		    }

		    try {
		        $products = $mailchimp_api->products($store_id, 1, 1);
		        $mailchimp_total_products = $products['total_items'];
		        if ($mailchimp_total_products > $product_count) $mailchimp_total_products = $product_count;
		    } catch (\Exception $e) { $mailchimp_total_products = 0; }

		    try {
		        $orders = $mailchimp_api->orders($store_id, 1, 1);
		        $mailchimp_total_orders = $orders['total_items'];
		        if ($mailchimp_total_orders > $order_count) $mailchimp_total_orders = $order_count;
		    } catch (\Exception $e) { $mailchimp_total_orders = 0; }

		    $mailchimp_list_name = $handler->getListName();
		}

		$data = array();

		$data[ 'last_updated_time' ]        = $last_updated_time->format('D, M j, Y g:i A');
		$data[ 'store_syncing' ]            = $store_syncing;
		$data[ 'mailchimp_total_products' ] = $mailchimp_total_products;
		$data[ 'product_count' ]            = $product_count;
 		$data[ 'mailchimp_total_orders' ]   = $mailchimp_total_orders;
		$data[ 'order_count' ]              = $order_count;
		$data[ 'account_name' ]             = $account_name;
		$data[ 'mailchimp_list_name' ]      = $mailchimp_list_name;
		$data[ 'store_id' ]                 = $store_id;


		return rest_ensure_response( $data );
	}

	public function resync( $request ) {
		$input                         = array();
		$input['mailchimp_active_tab'] = 'sync';
		$handler                       = MailChimp_Woocommerce_Admin::connect();

		$handler->validate( $input );

		return $this->get_sync_status( $request );
	}

}
