# WooCommerce API Dev

This is a repository for the development version of the WooCommerce REST API. This feature plugin provides a place where the next version of the WooCommerce REST API can be worked on, independent of the current WooCommerce release cycle. These changes are brought over to [WooCommerce core](https://github.com/woocommerce/woocommerce) when they are ready for release.

* Current stable API version: [**v2**](https://github.com/woocommerce/woocommerce/tree/master/includes/api).
* Current development version: **v3**.

## Contributing

Please read the [WooCommerce contributor guidelines](https://github.com/woocommerce/woocommerce/blob/master/.github/CONTRIBUTING.md) for more information how you can contribute.

Endpoints are located in the `api/` folder. Endpoints inherit from the stable version of the endpoint. If you need to change the behavior of an endpoint, you can do so in these classes. You can also introduce new endpoints by adding them to the root plugin file [wc-api-dev.php](https://github.com/woocommerce/wc-api-dev/blob/master/wc-api-dev.php) (mirrors core's [class-wc-api.php](https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-api.php)).

phpunit tests for the API are located in the `tests/unit-tests/` folder and are also merged and shipped with WooCommerce core. You can use the same helpers/framework files that core uses, or introduce new ones.

## Creating a new release

When changes are ready to be released to the world properly, use the GitHub release manager. Please note that since version `0.6.0`, the plugin has the capability of auto-updating -- please make sure changes have been properly tested. You can still merge into master and create pre-releases, but creating a "public" release will cause sites to up date.

When creating a public release, do the following:

* Add a changelog and bump the version in `readme.txt`.
* Bump the version in the plugin header.
* The tag & name of the release should both match the new version number.
* Post your changelog notes in the description of the release.
* Create & attach a binary containing a zip for the latest plugin version. The binary name should be `wc-api-dev.zip`. Please double check that the folder inside the archive is also called `wc-api-dev` and not something like `wc-api-dev-###hash` or `wc-api-dev-master`. You can create this binary by downloading a zip from GitHub, unzipping, and renaming the folder.
* Publish your release.
