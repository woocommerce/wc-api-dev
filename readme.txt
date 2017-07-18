=== WooCommerce API Dev ===
Contributors: automattic, woothemes
Tags: woocommerce, rest-api, api
Requires at least: 4.6
Tested up to: 4.8
Stable tag: 0.7.0
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
