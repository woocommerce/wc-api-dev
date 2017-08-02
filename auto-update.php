<?php
/**
 * Keeps wc-api-dev up to date based on GitHub releases.
 */
class WC_API_Dev_Updater {

	// Plugin file path / slug
	protected $file = 'wc-api-dev/wc-api-dev.php';
	protected $github_response = null;

	/**
	 * Hooks into the plugin update system.
	 */
	public function __construct() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'auto_update_plugin', array( $this, 'auto_update' ), 10, 2 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Adds the wc-api-dev plugin to the list of allowed plugins for auto update.
	 */
	function auto_update( $update, $item ) {
		if ( 'wc-api-dev' === $item->slug ) {
			return true;
		} else {
			return $update;
		}
	}

	/**
	 * Fetch the first non pre-release zip from GitHub releases and store the response for later use.
	 */
	private function maybe_fetch_github_response() {
		if ( false === ( $gh_response = get_transient( 'wc_api_dev_gh_response' ) ) ) {
			$request_uri = 'https://api.github.com/repos/woocommerce/wc-api-dev/releases';
			$response    = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri ) ), true );
			if ( is_array( $response ) ) {
				foreach ( $response as $entry ) {
					if ( false === ( bool ) $entry['prerelease'] ) {
						$gh_response = $entry;
						set_transient( 'wc_api_dev_gh_response', $entry, 12 * HOUR_IN_SECONDS );
						break;
					}
				}
			}
		}
		$this->github_response = $gh_response;
	}

	/**
	 * Add our plugin to the list of plugins to update, if we find the version is out of date.
	 */
	public function modify_transient( $transient ) {
		$this->maybe_fetch_github_response();

		if (
			empty( $this->github_response['tag_name'] ) ||
			empty( $this->github_response['zipball_url'] )
		) {
			return $transient;
		}

		$out_of_date = version_compare( $this->github_response['tag_name'], WC_API_Dev::CURRENT_VERSION, '>' );
		if ( $out_of_date ) {
			$plugin = array(
				'url'         => 'https://github.com/woocommerce/wc-api-dev',
				'plugin'      => $this->file,
				'slug'        => 'wc-api-dev',
				'package'     => $this->github_response['zipball_url'],
				'new_version' => $this->github_response['tag_name']
			);
			$transient->response[ $this->file ] = (object) $plugin;
		}

		return $transient;
	}

	/**
	 * Displays some basic information from our README if the user tries to
	 * view plugin info for the update via the manual update screen.
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( ! empty( $args->slug ) ) {
			if ( 'wc-api-dev' === $args->slug ) {
				$this->maybe_fetch_github_response();
				$plugin_data = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/' . $this->file );

				// Make sure our GitHub responses are present. If not, we can still show info from the plugin README below.
				$tag_name = ! empty( $this->github_response['tag_name'] ) ? $this->github_response['tag_name'] : '';
				$published_at = ! empty( $this->github_response['published_at'] ) ? $this->github_response['published_at'] : '';
				$body = ! empty( $this->github_response['body'] ) ? $this->github_response['body'] : '';
				$zipball_url = ! empty( $this->github_response['zipball_url'] ) ? $this->github_response['zipball_url'] : '';

				$plugin      = array(
					'name'              => $plugin_data['Name'],
					'slug'              => $this->file,
					'version'           => $tag_name,
					'author'            => $plugin_data['AuthorName'],
					'author_profile'    => $plugin_data['AuthorURI'],
					'last_updated'      => $published_at,
					'homepage'          => $plugin_data['PluginURI'],
					'short_description' => $plugin_data['Description'],
					'sections'          => array(
						'Description'   => $plugin_data['Description'],
						'Updates'       => $body,
					),
					'download_link'     => $zipball_url,
				);
				return (object) $plugin;
			}
		}
		return $result;
	}

	/**
	 * Move the updated plugin, which is installed in a temp directory (woocommerce-wc-api-dev-hash)
	 * to the correct plugin directory, and reactivate the plugin.
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;
		if ( 'wc-api-dev/wc-api-dev.php' !== $hook_extra['plugin'] ) {
			return $response;
		}
		$install_directory = plugin_dir_path( __FILE__ );
		$wp_filesystem->move( $result['destination'], $install_directory );
		$result['destination'] = $install_directory;
		activate_plugin( $this->file );

		// Prevent double notice being displayed. At this point we don't need the plugin injected.
		remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10 );

		return $result;
	}
}
