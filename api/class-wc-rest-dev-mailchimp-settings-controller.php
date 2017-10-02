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

#TODO:
# * permission check

class MailChimp_Woocommerce_Params_Checker extends MailChimp_Woocommerce_Admin {

	/**
	 * @return MailChimp_Woocommerce_Admin
	 */
	public static function connect()
	{
		$env = mailchimp_environment_variables();

		return new self('mailchimp-woocommerce', $env->version);
	}

	public function validateApiKey( $params ) {
		return $this->validatePostApiKey( $params );
	}

	public function validateStoreInfo( $params ) {
		return $this->validatePostStoreInfo( $params );
	}

	public function validateCampaignDefaults( $params ) {
		return $this->validatePostCampaignDefaults( $params );
	}

	public function validateNewsletterSettings( $params ) {
		return $this->validatePostNewsletterSettings( $params );
	}
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
	 * Get payment gateways.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_settings( $request ) {
		$options = get_option('mailchimp-woocommerce', array() );
		$options['active_tab'] = isset($options['active_tab']) ? $options['active_tab'] : "api_key";
		return rest_ensure_response( $options );
	}

	public function update_api_key( $request ) {
		$parameters     = $request->get_params();
		$handler        = MailChimp_Woocommerce_Params_Checker::connect();
		$data           = $handler->validateApiKey( $parameters );
		$options        = get_option('mailchimp-woocommerce', array());
    $merged_options = (isset($data) && is_array($data)) ? array_merge($options, $data) : $options;
		update_option('mailchimp-woocommerce', $merged_options);
		return rest_ensure_response( $merged_options );
	}

	/**
	 * Get the payment gateway schema, conforming to JSON Schema.
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
		$parameters     = $request->get_params();
		$handler        = MailChimp_Woocommerce_Params_Checker::connect();
		$data           = $handler->validateStoreInfo( $parameters );
		$options        = get_option('mailchimp-woocommerce', array());
		$merged_options = (isset($data) && is_array($data)) ? array_merge($options, $data) : $options;
		update_option('mailchimp-woocommerce', $merged_options);
		return rest_ensure_response( $merged_options );
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
		$parameters     = $request->get_params();
		$handler        = MailChimp_Woocommerce_Params_Checker::connect();
		$data           = $handler->validateCampaignDefaults( $parameters );
		$options        = get_option('mailchimp-woocommerce', array());
		$merged_options = (isset($data) && is_array($data)) ? array_merge($options, $data) : $options;
		update_option('mailchimp-woocommerce', $merged_options);
		return rest_ensure_response( $merged_options );
	}

	public function get_newsletter_settings( $request ) {
		$handler        = MailChimp_Woocommerce_Params_Checker::connect();
		$data           = $handler->getMailChimpLists();
		return rest_ensure_response( $data );
	}

	public function update_newsletter_settings( $request ) {
		$parameters     = $request->get_params();
		$handler        = MailChimp_Woocommerce_Params_Checker::connect();
		$data           = $handler->validateNewsletterSettings( $parameters );
		$options        = get_option('mailchimp-woocommerce', array());
		$merged_options = (isset($data) && is_array($data)) ? array_merge($options, $data) : $options;
		update_option('mailchimp-woocommerce', $merged_options);
		return rest_ensure_response( $merged_options );
	}

	/**
	 * Get any query params needed.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

}
