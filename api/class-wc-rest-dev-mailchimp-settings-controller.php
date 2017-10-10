<?php
/**
 * REST API WC MailChimp settings
 *
 * Handles requests that interact with MailChimp plugin.
 *
 * @author   Automattic
 * @category API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package WooCommerce/API
 */
class WC_REST_Dev_MailChimp_Settings_Controller extends WC_REST_Controller {

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
				'permission_callback' => array( $this, 'permissions_check' ),
			)
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/api_key', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_api_key' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/store_info', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_store_info' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		'schema' => array( $this, 'get_store_info_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/campaign_defaults', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_campaign_defaults' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/newsletter_setting', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_newsletter_settings' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/newsletter_setting', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_newsletter_settings' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/sync', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_sync_status' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/sync', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'resync' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		) );
	}

	/**
	 * Makes sure the current user has access to WRITE the settings APIs.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|boolean
	 */
	public function permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'edit' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_edit', __( 'Sorry, you cannot edit this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get current MailChimp settings.
	 *
	 * @param WP_REST_Request
	 * @return WP_REST_Response
	 *
	 */
	public function get_settings( $request ) {
		$options = get_option( 'mailchimp-woocommerce', array() );
		$options['mailchimp_active_tab'] = isset( $options['mailchimp_active_tab'] ) ? $options['mailchimp_active_tab'] : 'api_key';
		return rest_ensure_response( $options );
	}

	/**
	 * Set MailChimp API key
	 *
	 * @param WP_REST_Request, MailChimp API key
	 * @return WP_REST_Response, updated MailChimp settings
	 *
	 */
	public function update_api_key( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'api_key';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$data                               = $handler->validate( $parameters );

		update_option( 'mailchimp-woocommerce', $data );

		return rest_ensure_response( $data );
	}

	/**
	 * Update merchants store information
	 *
	 * @param WP_REST_Request, store information
	 * @return WP_REST_Response, updated MailChimp settings
	 *
	 */
	public function update_store_info( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'store_info';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$data                               = $handler->validate( $parameters );

		update_option( 'mailchimp-woocommerce', $data );

		return rest_ensure_response( $data );
	}


	/**
	 * Update campaign defaluts settings
	 * this route we will be able to remove in future as it represent
	 * potentialy not needed step during MailChimp setup
	 *
	 * @param WP_REST_Request, campaign information
	 * @return WP_REST_Response, updated MailChimp settings
	 *
	 */
	public function update_campaign_defaults( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'campaign_defaults';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$data                               = $handler->validate( $parameters );

		update_option( 'mailchimp-woocommerce', $data );

		return rest_ensure_response( $data );
	}


	/**
	 * Get current newsletter settings
	 *
	 * @param WP_REST_Request,
	 * @return WP_REST_Response, list of newsletters
	 *
	 */
	public function get_newsletter_settings( $request ) {
		$handler = MailChimp_Woocommerce_Admin::connect();
		$data    = $handler->getMailChimpLists();

		return rest_ensure_response( $data );
	}

	/**
	 * Update newsletter settings
	 *
	 * @param WP_REST_Request, newsletter settings
	 * @return WP_REST_Response, updated MailChimp settings
	 *
	 */
	public function update_newsletter_settings( $request ) {
		$parameters                         = $request->get_params();
		$parameters['mailchimp_active_tab'] = 'newsletter_settings';
		$handler                            = MailChimp_Woocommerce_Admin::connect();
		$options                            = get_option( 'mailchimp-woocommerce', array() );
		$mailchimp_active_tab               = $options['active_tab'];
		$data                               = $handler->validate( $parameters );

		// if previous active tab was sync then we still want sync
		// because this call is just an update and not part of setup
		if( 'sync' === $mailchimp_active_tab  ) {
			$data['mailchimp_active_tab'] = 'sync';
		}

		update_option( 'mailchimp-woocommerce', $data);

		return rest_ensure_response( $data );
	}

	/**
	 * Synchronization status between MailChimp plugin and Mailchimp
	 *
	 * @param WP_REST_Request,
	 * @return WP_REST_Response, synchronization status
	 *
	 */
	public function get_sync_status( $request ) {
		$handler                  = MailChimp_Woocommerce_Admin::connect();
		$mailchimp_total_products = $mailchimp_total_orders = 0;
		$store_id                 = mailchimp_get_store_id();
		$product_count            = mailchimp_get_product_count();
		$order_count              = mailchimp_get_order_count();
		$store_syncing            = false;
		$last_updated_time        = get_option( 'mailchimp-woocommerce-resource-last-updated' );
		$account_name             = 'n/a';
		$mailchimp_list_name      = 'n/a';

		if ( ! empty( $last_updated_time ) ) {
				$last_updated_time = mailchimp_date_local( $last_updated_time );
		}

		if ( ( $mailchimp_api = mailchimp_get_api() ) && ( $store = $mailchimp_api->getStore( $store_id ) ) ) {

			$store_syncing = $store->isSyncing();

			if ( ( $account_details = $handler->getAccountDetails() ) ) {
				$account_name = $account_details['account_name'];
			}

			try {
				$products = $mailchimp_api->products( $store_id, 1, 1 );
				$mailchimp_total_products = $products['total_items'];
				if ( $mailchimp_total_products > $product_count ) {
					$mailchimp_total_products = $product_count;
				}
			} catch (\Exception $e) { $mailchimp_total_products = 0; }

			try {
				$orders = $mailchimp_api->orders( $store_id, 1, 1 );
				$mailchimp_total_orders = $orders['total_items'];
				if ( $mailchimp_total_orders > $order_count ) {
					$mailchimp_total_orders = $order_count;
				}
			} catch (\Exception $e) { $mailchimp_total_orders = 0; }

			$mailchimp_list_name = $handler->getListName();
		}

		$data = array();

		$data['last_updated_time']        = $last_updated_time->format( 'D, M j, Y g:i A' );
		$data['store_syncing']            = $store_syncing;
		$data['mailchimp_total_products'] = $mailchimp_total_products;
		$data['product_count']            = $product_count;
		$data['mailchimp_total_orders']   = $mailchimp_total_orders;
		$data['order_count']              = $order_count;
		$data['account_name']             = $account_name;
		$data['mailchimp_list_name']      = $mailchimp_list_name;
		$data['store_id']                 = $store_id;

		return rest_ensure_response( $data );
	}


	/**
	 * Force resynchronization
	 *
	 * @param WP_REST_Request,
	 * @return WP_REST_Response, Synchronization status
	 *
	 */
	public function resync( $request ) {
		$input                         = array();
		$input['mailchimp_active_tab'] = 'sync';
		$handler                       = MailChimp_Woocommerce_Admin::connect();

		$handler->validate( $input );

		return $this->get_sync_status( $request );
	}

}
