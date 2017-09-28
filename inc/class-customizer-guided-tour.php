<?php
/**
 * Customizer Guided Tour Class
 *
 * @author   WooThemes
 * @since    0.8.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Customizer_NUX_Guided_Tour' ) ) :

	class Customizer_NUX_Guided_Tour {
		/**
		 * Setup class.
		 *
		 * @since 0.8.7
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'customizer' ) );
		}

		/**
		 * Customizer.
		 *
		 * @since 0.8.7
		 */
		public function customizer() {
			global $pagenow;

			if ( 'customize.php' === $pagenow && isset( $_GET['wc-api-dev-tutorial'] ) ) {
				add_action( 'customize_controls_enqueue_scripts',      array( $this, 'customize_scripts' ) );
				add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_templates' ) );
				$this->isStorefrontActivated();
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
		 * @since 0.8.7
		 */
		public function customize_scripts() {
			global $storefront_version;

			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_style( 'wc-api-dev-guided-tour', WC_API_Dev::$plugin_url . 'assets/css/admin/customizer.css', array(), $storefront_version, 'all' );

			wp_enqueue_script( 'wc-api-dev-guided-tour', WC_API_Dev::$plugin_url . 'assets/js/admin/customizer' . $suffix . '.js', array( 'jquery', 'wp-backbone' ), $storefront_version, true );

			wp_localize_script( 'wc-api-dev-guided-tour', '_wpCustomizeWcApiDevGuidedTourSteps', $this->guided_tour_steps() );
		}

		/**
		 * Template for steps.
		 *
		 * @since 0.8.7
		 */
		public function print_templates() {
			?>
			<script type="text/html" id="tmpl-wc-api-guided-tour-step">
				<div class="sf-guided-tour-step">
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
		 * Guided tour steps.
		 *
		 * @since 0.8.7
		 */
		public function guided_tour_steps() {
			$steps = array();

			$steps[] = array(
				'title'       => __( 'Customize Your Store', 'storefront' ),
				'message'     => __( 'It looks like your current theme isn\'t ready for shop features yet - your shop pages might look a little funny.</p><p>We suggest switching themes to <b>Storefront</b> which will bring out the best in your shop. Don\'t worry, if you try Storefront now, it won\'t be activated until you save your changes in the Customizer', 'storefront' ),
				'buttonText' => __( 'I\'ll keep my current theme', 'storefront' ),
				'section'     => '#customize-info',
				'className'   => 'sf-button-secondary',
				'altStep'    => array(
					'buttonText' => __( 'I\'ll try Storefront!', 'storefront' ),
					'action'      => 'expandThemes',
					'message'     => __( 'Click the thumbnail to get started with Storefront', 'storefront' ),
					'section'     => '#customize-control-theme_storefront .theme-screenshot',
				)
			);

			// Determine what the next step should be
			$needsLogo = ! has_custom_logo() && current_theme_supports( 'custom-logo' );
			$logoBullet = $needsLogo ? __( '<li>Add your logo</li>', 'storefront' ) : '';

			$steps[] = array(
				'message'     => __( 'Okay! Remember you can switch themes at any time.</p><p>To get your store looking great, let\'s run through some common tasks:</p><ul>' . $logoBullet . '<li>Add Shop pages to your menus</li><li>Set your shope page as the homepage</li></ul><p>', 'storefront' ),
				'buttonText' => $needsLogo ? __( 'Add a logo', 'storefront' ) : __( 'Add menu items', 'storefront' ),
				'section'     => '#accordion-section-themes',
				'altStep'    => array(
					'buttonText' => __( 'I\'ll figure it out for myself', 'storefront' ),
					'className'   => 'sf-button-secondary',
					'action'      => 'exit',
				)
			);

			if ( $needsLogo ) {
				$steps[] = array(
					'title'       => __( 'Add your logo', 'storefront' ),
					'action'      => 'addLogo',
					'message'     => __( 'Click the \'Select Logo\' button to upload your logo. After you upload your logo, click next to update your menus.', 'storefront' ),
					'buttonText' => __( 'Next', 'storefront' ),
					'section'     => 'title_tagline',
				);
			}

			$steps[] = array(
				'message'      => __( 'Choose a menu to add shop pages to', 'storefront' ),
				'section'      => '#sub-accordion-panel-nav_menus',
				'panel'        => 'nav_menus',
				'panelSection' => '.control-section-nav_menu',
				'action'       => 'updateMenus',
				'showSkip'     => 'true',
				'childSteps'   => array(
					array(
						'message'    => __( 'Here are the items currently added to your menu. Click the "Add Items" button.', 'storefront' ),
						'section'    => '.add-new-menu-item',
					),
					array(
						'message'    => __( 'Click on a page to add it to your menu. You can add links to your "shop", "cart", "checkout", and "my account" pages.', 'storefront' ),
						'section'    => '#available-menu-items-post_type-page',
					),
				)
			);

			return $steps;
		}
	}

endif;

return new Customizer_NUX_Guided_Tour();