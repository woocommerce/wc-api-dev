<?php
/**
 * Customizer Guided Tour Class
 *
 * @author   WooThemes
 * @since    0.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Customizer_NUX_Guided_Tour' ) ) :

	class Customizer_NUX_Guided_Tour {
		/**
		 * Setup class.
		 *
		 * @since 0.8.8
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'customizer' ) );
		}

		/**
		 * Customizer.
		 *
		 * @since 0.8.8
		 */
		public function customizer() {
			global $pagenow;

			// Always load the assets when in the customizer.
			if ( 'customize.php' === $pagenow ) {
				add_action( 'customize_controls_enqueue_scripts',      array( $this, 'customize_scripts' ) );
				add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_templates' ) );
				if ( isset( $_GET['store-wpcom-nux'] ) ) {
					$this->isStorefrontActivated();
				}
			}
		}

		public function isStorefrontActivated() {
			$current_theme = wp_get_theme();

			if ( 'Storefront' == $current_theme->get( 'Name' ) ) {
				$redirect_url = add_query_arg( array(
					'sf_guided_tour' => '1',
					'sf_tasks' => 'homepage',
					'theme' => $_GET['theme'],
				), admin_url( '/customize.php' ) );

				wp_redirect( $redirect_url );
				exit;
			}
		}

		/**
		 * Customizer enqueues.
		 *
		 * @since 0.8.8
		 */
		public function customize_scripts() {
			global $storefront_version;
			$asset_path = WC_API_Dev::$plugin_asset_path ? WC_API_Dev::$plugin_asset_path : WC_API_Dev::MU_PLUGIN_ASSET_PATH;

			wp_enqueue_style( 'wc-store-nux-tour', $asset_path . 'assets/css/admin/customizer.css', array(), WC_API_Dev::CURRENT_VERSION, 'all' );
			wp_enqueue_script( 'wc-store-nux-tour', $asset_path . 'assets/js/admin/customizer.js', array( 'jquery', 'wp-backbone' ), WC_API_Dev::CURRENT_VERSION, true );

			wp_localize_script( 'wc-store-nux-tour', '_wpStoreNuxTourSteps', $this->guided_tour_steps() );
			wp_localize_script( 'wc-store-nux-tour', '_wpStoreNuxSettings', $this->guided_tour_settings() );
		}

		/**
		 * Template for steps.
		 *
		 * @since 0.8.8
		 */
		public function print_templates() {
			?>
			<script type="text/html" id="tmpl-wc-store-tour-step">
				<div class="sf-guided-tour-step">
					<a class="sf-guided-tour-close" href="#">
						<span class="dashicons dashicons-no-alt"></span>
					</a>
					<# if ( data.title ) { #>
						<h2>{{ data.title }}</h2>
					<# } #>
					{{{ data.message }}}
					<# if ( data.altStep ) { #>
						<a class="sf-nux-button sf-nux-alt-button {{ data.altStep.className }}" href="#">
							{{ data.altStep.buttonText }}
						</a>
					<# } #>
					<# if ( data.buttonText ) { #>
						<a class="sf-nux-button sf-nux-primary-button {{ data.className }}" href="#">
							{{ data.buttonText }}
						</a>
					<# } #>

					<# if ( data.showSkip ) { #>
						<a class="sf-guided-tour-skip" href="#">
							<?php esc_attr_e( 'Skip this step', 'wc-api-dev' ); ?>
						</a>
					<# } #>
				</div>
			</script>
			<?php
		}

		/**
		 * Guided tour settings.
		 *
		 * @since 0.8.7
		 */
		public function guided_tour_settings() {
			$show_tour = isset( $_GET['store-wpcom-nux'] );
			$theme_supports_woo = current_theme_supports( 'woocommerce' );
			$supported_themes = wc_get_core_supported_themes();
			$current_theme = get_template();

			return array(
				'autoStartTour' => ( bool ) $show_tour,
				'showTourAlert' => ( bool ) ! $theme_supports_woo && ! in_array( $current_theme, $supported_themes ),
				'alertMessage'  => sprintf( __( '%1$sYour current theme isn\'t ready for store features yet%2$s. Consequently your shop and product page layouts might display incorrectly.%3$sWe reccomend switching themes to %1$sStorefront%2$s. %4$sClick here%5$s to get started.%3$s %6$sLearn more about Storefront%5$s', 'storefront' ), '<strong>', '</strong>', '<br><br>', '<a href="/wp-admin/customize.php?theme=storefront&sf_guided_tour=1&sf_tasks=homepage">', '</a>', '<a href="https://woocommerce.com/storefront/" target="_blank">' ),
			);
		}

		/**
		 * Guided tour steps.
		 *
		 * @since 0.8.7
		 */
		public function guided_tour_steps() {
			$steps = array();

			$steps[] = array(
				'title'       => __( 'Customize Your Store', 'storefront' ),
				'message'     => __( '<p>Your current theme isn\'t ready for store features yet. Consequently your shop and product page layouts might display incorrectly.</p><p>We recommend switching themes to <strong>Storefront</strong> which will bring out the best in your shop. Don\'t worry, if you try Storefront now, it won\'t be activated until you save your changes in the Customizer</p>', 'storefront' ),
				'buttonText'  => __( 'I\'ll keep my current theme', 'storefront' ),
				'section'     => '#customize-info',
				'className'   => 'sf-button-secondary',
				'stat'        => '1-keep-try-step',
				'altStep'     => array(
					'buttonText'  => __( 'I\'ll try Storefront!', 'storefront' ),
					'action'      => 'expandThemes',
					'message'     => __( 'Click the thumbnail to get started with Storefront', 'storefront' ),
					'section'     => '#customize-control-theme_storefront .theme-screenshot',
					'stat'        => '1-click-try-sf',
				),
			);

			// Determine what the next step should be
			$needsLogo = ! has_custom_logo() && current_theme_supports( 'custom-logo' );
			$logoBullet = $needsLogo ? __( '<li>Add your logo</li>', 'storefront' ) : '';
			$stepMessage = __( '<p>Okay! Remember you can switch themes at any time.</p><p>To get your store looking great, let\'s run through some common tasks:</p><ul>%s<li>Add Shop pages to your menus</li><li>Set your shop page as the homepage</li></ul>', 'storefront' );

			$steps[] = array(
				'message'     => sprintf( $stepMessage, $logoBullet ),
				'buttonText'  => $needsLogo ? __( 'Add a logo', 'storefront' ) : __( 'Add menu items', 'storefront' ),
				'section'     => '#accordion-section-themes',
				'stat'        => $needsLogo ? '2-add-logo' : '2-add-menu',
				'altStep'     => array(
					'buttonText' => __( 'I\'ll figure it out for myself', 'storefront' ),
					'className'   => 'sf-button-secondary',
					'action'      => 'exit',
					'stat'        => '2-click-exit',
				),
			);

			if ( $needsLogo ) {
				$steps[] = array(
					'title'       => __( 'Add your logo', 'storefront' ),
					'action'      => 'addLogo',
					'message'     => __( '<p>Click the \'Select Logo\' button to upload your logo. After you upload your logo, click next to update your menus.</p>', 'storefront' ),
					'buttonText'  => __( 'Next', 'storefront' ),
					'section'     => 'title_tagline',
					'stat'        => '3-select-logo'
				);
			}

			$steps[] = array(
				'message'      => __( '<p>Choose a menu to add shop pages to.</p>', 'storefront' ),
				'section'      => '#sub-accordion-panel-nav_menus',
				'panel'        => 'nav_menus',
				'panelSection' => '.control-section-nav_menu',
				'action'       => 'updateMenus',
				'showSkip'     => ( bool ) true,
				'stat'         => '4-add-menu',
				'childSteps'   => array(
					array(
						'message'    => __( '<p>Here are the items currently added to your menu. Click the "Add Items" button.</p>', 'storefront' ),
						'section'    => '.control-section-nav_menu.open .add-new-menu-item',
						'stat'       => '4a-menu-items',
					),
					array(
						'message'    => __( '<p>Click on a page to add it to your menu. You can add links to your "shop", "cart", "checkout", and "my account" pages.</p>', 'storefront' ),
						'section'    => '#available-menu-items-post_type-page',
						'stat'       => '4b-menu-items-add',
					),
					array(
						'message'    => __( '<p>Add as many pages as you like. When you\'re happy, click "Next" and we\'ll setup your homepage.</p>', 'storefront' ),
						'section'    => '#available-menu-items-post_type-page',
						'buttonText' => __( 'Next', 'storefront' ),
						'stat'       => '4c-next',
					),
				)
			);

			// See if setting homepage as static page is needed or not.
			$show_on_front = get_option( 'show_on_front' );

			if ( $show_on_front != 'page' ) {
				$steps[] = array(
					'message'      => __( '<p>If you would like to set your shop page as your homepage select the "A static page" option.</p>', 'storefront' ),
					'section'      => '#customize-control-show_on_front',
					'action'       => 'resetChildTour',
					'showSkip'     => ( bool ) true,
					'suppressHide' => ( bool ) true,
					'stat'         => '5-static-page',
				);
			}

			$steps[] = array(
				'message'      => __( '<p>Select which page you\'d like to set as your homepage. If you want your shop to be the focal point of your site then choosing the "Shop" page would be a good choice.</p>', 'storefront' ),
				'section'      => '#_customize-dropdown-pages-page_on_front',
				'action'       => $show_on_front == 'page' ? 'resetChildTour' : 'verifyHomepage',
				'showSkip'     => ( bool ) true,
				'suppressHide' => $show_on_front == 'page' ? true : false,
				'stat'         => '6-set-shop-home',
			);

			$steps[] = array(
				'message'      => __( '<p>Awesome! Your shop should be good to go. There\'s lots more to explore in the Customizer but remember to save and publish your changes.</p>', 'storefront' ),
				'section'      => '#sub-accordion-panel-nav_menus',
				'showClose'    => 'true',
				'stat'         => '7-done',
			);

			return $steps;
		}
	}

endif;

return new Customizer_NUX_Guided_Tour();