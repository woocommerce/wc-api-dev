=== WooCommerce API Dev ===
Contributors: automattic, woothemes
Tags: woocommerce, rest-api, api
Requires at least: 4.6
Tested up to: 4.9
Stable tag: 0.9.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A feature plugin providing a bleeding edge version of the WooCommerce REST API.

== Description ==

This is a repository for the development version of the WooCommerce REST API. This feature plugin provides a place where the next version of the WooCommerce REST API can be worked on, independent of the current WooCommerce release cycle. These changes are brought over to [WooCommerce core](https://github.com/woocommerce/woocommerce) when they are ready for release.

* Current stable API version: [**v2**](https://github.com/woocommerce/woocommerce/tree/master/includes/api).
* Current development version: **v3**.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== Changelog ==

= 0.9.7 =
* `data/counts` endpoint removed (moved to wc-calypso-bridge)
* `products/reviews` endpoint removed (moved to wc-calypso-bridge)

= 0.9.6 =
* new `data/counts` endpoint added

= 0.9.5 =
* Fixes `single_select_page` settings (like `woocommerce_shop_page_id`) not showing up in responses.

= 0.9.4 =
* Added currency and dimensions to selected countries in the data/continents response

= 0.9.3 =
* Removed MailChimp files which are now in wc-calypso-bridge
* Fixed regex issue that caused gutenberg to crash
* Removed webhook test dependencies which are being removed in WooCommerce 3.3

= 0.9.2 =
* Rest API: Backport woocommerce/woocommerce#17849 to fix schema types in WP 4.9

= 0.9.1 =
* Rest API: Update the schema types for tax_class and price

= 0.9.0 =
* Removed all non core wc API logic from the plugin.
* Added Author to note orders/{orderId}/notes responses

= 0.8.9 =
* Added MailChimp API endpoints for Store on .com
* Added auto db upgrade handling

= 0.8.8 =
* Added customizer guided tour for nux.
* Added Store menu link to Jetpack Masterbar.

= 0.8.4 =
* Remove auto-install via github as it is not reliable.
* Add composer autoload support.
* Add check for existing installs to avoid conflicts.

= 0.8.3 =
* Fix - Update order link in order details emails.

= 0.8.2 =
* Fix - Don't auto enable the PayPal payment method.

= 0.8.1 =
* Fix - Fix auto plugin update logic
* Fix - Don't auto enable the cheque payment method.
* Adds a new config constant (WC_API_DEV_ENABLE_HOTFIXES) to make it easy to disable hotfixes.

= 0.8.0 =
* Update orders endpoint to accept multiple statuses
* Fix - Email subject and footers becoming out of sync

= 0.7.1 =
* Fix - add another URI to watch for when disabling sync during API requests

= 0.7.0 =
* Fix - disable jetpack sync during rest api requests to avoid slow responses

= 0.6.0 =
* Fix value default return on settings endpoints
* Fix broken variation image set
* Add a method supports response to payment methods
* Added the ability to keep this plugin up to date

= 0.5.0 =
* Added a /settings/batch endpoint
* Fixed broken orders refund endpoint

= 0.4.0 =
* Adds store address (two lines), city and postcode to the settings/general endpoint

= 0.3.0 =
* Adds endpoints for getting a list of supported currencies, along with their name and symbol.
* Limit regex check for currency code to 3 characters.

= 0.2.0 =
* Removes the 'visible' property from the variations endpoint and adds 'status'. (See https://github.com/woocommerce/woocommerce/pull/15216)
* Don't return parent image when no variation image is set.

= 0.1.0 =
* Initial release
