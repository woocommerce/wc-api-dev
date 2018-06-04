<?php
/**
 * WC API Dev
 * Loads a development version of the WooCommerce REST API.
 */
class WC_API_Dev {

	/**
	 * Current version of the API plugin.
	 */
	const CURRENT_VERSION = '0.9.7';

	/**
	 * Minimum version needed to run this version of the API.
	 */
	const WC_MIN_VERSION = '3.0.0';

	/**
	 * Class Instance.
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_init', array( $this, 'init' ) );
	}

	/**
	 * Loads API includes and registers routes.
	 */
	function init() {
		if ( $this->is_woocommerce_valid() ) {
			$this->includes();
			add_action( 'rest_api_init', array( $this, 'register_routes' ), 10 );
		}
	}

	/**
	 * Makes sure WooCommerce is installed and up to date.
	 */
	public function is_woocommerce_valid() {
		return (
			class_exists( 'woocommerce' ) &&
			version_compare(
				get_option( 'woocommerce_db_version' ),
				WC_API_Dev::WC_MIN_VERSION,
				'>='
			)
		);
	}

	/**
	 * REST API includes.
	 * New endpoints/controllers can be added here.
	 *
	 * Controllers for the feature plugin are prefixed with WC_REST_DEV (rather than WC_REST)
	 * so that this plugin can play nice with the WooCommerce Core classes.
	 * They would be renamed on future sync to WooCommerce.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-coupons-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-customer-downloads-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-customers-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-data-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-data-continents-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-data-countries-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-data-currencies-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-orders-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-order-notes-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-order-refunds-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-product-attribute-terms-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-product-attributes-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-product-categories-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-product-reviews-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-product-shipping-classes-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-product-tags-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-products-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-product-variations-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-report-sales-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-report-top-sellers-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-reports-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-settings-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-setting-options-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-shipping-zones-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-shipping-zone-locations-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-shipping-zone-methods-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-tax-classes-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-taxes-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-webhook-deliveries.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-webhooks-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-system-status-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-system-status-tools-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-shipping-methods-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-dev-payment-gateways-controller.php' );
	}

	/**
	 * Register REST API routes.
	 *
	 * New endpoints/controllers can be added here.
	 */
	public function register_routes() {
		$controllers = array(
			'WC_REST_Dev_Coupons_Controller',
			'WC_REST_Dev_Customer_Downloads_Controller',
			'WC_REST_Dev_Customers_Controller',
			'WC_REST_Dev_Data_Controller',
			'WC_REST_Dev_Data_Continents_Controller',
			'WC_REST_Dev_Data_Countries_Controller',
			'WC_REST_Dev_Data_Currencies_Controller',
			'WC_REST_Dev_Order_Notes_Controller',
			'WC_REST_Dev_Order_Refunds_Controller',
			'WC_REST_Dev_Orders_Controller',
			'WC_REST_Dev_Product_Attribute_Terms_Controller',
			'WC_REST_Dev_Product_Attributes_Controller',
			'WC_REST_Dev_Product_Categories_Controller',
			'WC_REST_Dev_Product_Reviews_Controller',
			'WC_REST_Dev_Product_Shipping_Classes_Controller',
			'WC_REST_Dev_Product_Tags_Controller',
			'WC_REST_Dev_Products_Controller',
			'WC_REST_Dev_Product_Variations_Controller',
			'WC_REST_Dev_Report_Sales_Controller',
			'WC_REST_Dev_Report_Top_Sellers_Controller',
			'WC_REST_Dev_Reports_Controller',
			'WC_REST_Dev_Settings_Controller',
			'WC_REST_Dev_Setting_Options_Controller',
			'WC_REST_Dev_Shipping_Zones_Controller',
			'WC_REST_Dev_Shipping_Zone_Locations_Controller',
			'WC_REST_Dev_Shipping_Zone_Methods_Controller',
			'WC_REST_Dev_Tax_Classes_Controller',
			'WC_REST_Dev_Taxes_Controller',
			'WC_REST_Dev_Webhook_Deliveries_Controller',
			'WC_REST_Dev_Webhooks_Controller',
			'WC_REST_Dev_System_Status_Controller',
			'WC_REST_Dev_System_Status_Tools_Controller',
			'WC_REST_Dev_Shipping_Methods_Controller',
			'WC_REST_Dev_Payment_Gateways_Controller',
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}

	}

	/**
	 * Class instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

WC_API_Dev::instance();
