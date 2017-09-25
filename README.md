# WooCommerce API Dev

This is a repository for the development version of the WooCommerce REST API. This feature plugin provides a place where the next version of the WooCommerce REST API can be worked on, independent of the current WooCommerce release cycle. These changes are brought over to [WooCommerce core](https://github.com/woocommerce/woocommerce) when they are ready for release.

* Current stable API version: [**v2**](https://github.com/woocommerce/woocommerce/tree/master/includes/api).
* Current development version: **v3**.

## Contributing

Please read the [WooCommerce contributor guidelines](https://github.com/woocommerce/woocommerce/blob/master/.github/CONTRIBUTING.md) for more information how you can contribute.

Endpoints are located in the `api/` folder. Endpoints inherit from the stable version of the endpoint. If you need to change the behavior of an endpoint, you can do so in these classes. You can also introduce new endpoints by adding them to the root plugin file [wc-api-dev.php](https://github.com/woocommerce/wc-api-dev/blob/master/wc-api-dev.php) (mirrors core's [class-wc-api.php](https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-api.php)).

phpunit tests for the API are located in the `tests/unit-tests/` folder and are also merged and shipped with WooCommerce core. You can use the same helpers/framework files that core uses, or introduce new ones.

## Translation

For strings located in API endpoints, use `woocommerce` as your text-domain. These endpoints will at some point be merged back into WooCommerce Core.

For other changes (such as the `hotfixes/` folder, which is being used to power an in-development interface for managing stores on WordPress.com) that are not to be merged into core, you can use `wc-api-dev` as your text-domain. These will most likely be split-out into a separate plugin soon.
