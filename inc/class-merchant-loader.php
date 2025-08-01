<?php
/**
 * Merchant_Loader Class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Merchant_Loader' ) ) {
	class Merchant_Loader {

		/**
		 * The single class instance.
		 */
		private static $instance = null;

		/**
		 * Instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Includes.
			$this->includes();

			// Register scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_global_js_and_css' ) );

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

			// Add identifier to body class.
			add_filter( 'body_class', array( $this, 'add_body_class' ) );

			// Add row meta to the plugin screen.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

			// Add settings link to the plugin screen.
			add_filter( 'plugin_action_links_' . plugin_basename( MERCHANT_DIR . 'merchant.php' ), array( $this, 'settings_link' ) );
		}

		/**
		 * Include required classes.
		 */
		public function includes() {
			// Essential functions.
			require_once MERCHANT_DIR . 'inc/functions.php';

			// Helpers.
			require_once MERCHANT_DIR . 'inc/helpers.php';

			// Multi Language.
			require_once MERCHANT_DIR . 'inc/MultiLang/interface-language-strategy.php';
			require_once MERCHANT_DIR . 'inc/MultiLang/class-merchant-no-plugin-support.php';
			require_once MERCHANT_DIR . 'inc/MultiLang/class-merchant-polylang-support.php';
			require_once MERCHANT_DIR . 'inc/MultiLang/class-merchant-wpml-support.php';
			require_once MERCHANT_DIR . 'inc/MultiLang/class-merchant-translator.php';

			// Ajax callbacks.
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-ajax-callbacks.php';

			// Core classes.
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-option.php';
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-modules.php';
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-custom-css.php';
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-svg-icons.php';
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-db-tables.php';
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-general-hooks.php';

			// Metabox
			require_once MERCHANT_DIR . 'inc/classes/class-merchant-metabox.php';

			// The main class for adding modules.
			require_once MERCHANT_DIR . 'inc/modules/class-add-module.php';

			// Modules global settings.
			require_once MERCHANT_DIR . 'inc/modules/global-settings/global-settings.php';

			// Modules analytics
			require_once MERCHANT_DIR . 'inc/analytics/class-merchant-analytics-db-orm.php';
			require_once MERCHANT_DIR . 'inc/analytics/class-merchant-analytics-logger.php';
			require_once MERCHANT_DIR . 'inc/analytics/class-merchant-analytics-data-provider.php';
			require_once MERCHANT_DIR . 'inc/analytics/class-merchant-analytics-data-reports.php';
			require_once MERCHANT_DIR . 'inc/analytics/class-merchant-analytics-data-ajax.php';
			require_once MERCHANT_DIR . 'inc/analytics/class-merchant-analytics-data-hooks.php';

			// Modules (free and pro).
			foreach ( Merchant_Admin_Modules::$modules_data as $module_id => $module_data ) {
				if (
					defined( 'MERCHANT_PRO_VERSION' )
					&& version_compare( MERCHANT_PRO_VERSION, '1.3', '<' )
					&& isset( $module_data['pro'] ) && $module_data['pro']
				) {
					continue;
				}

				require_once MERCHANT_DIR . 'inc/modules/' . $module_id . '/class-' . $module_id . '.php';
			}

			// Compatibility Layer
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-botiga-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-divi-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-avada-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-astra-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-kadence-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-oceanwp-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-twenty-twenty-four-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-blocksy-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-flatsome-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-breakdance-builder.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-elementor-builder.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-bricks-builder.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-woo-payments-plugin.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-ohio-theme.php';
			require_once MERCHANT_DIR . 'inc/compatibility/class-merchant-woo-multi-currency.php';

			/**
			 * Hook 'merchant_admin_after_include_modules_classes'.
			 *
			 * @since 1.0
			 */
			do_action( 'merchant_admin_after_include_modules_classes' );
		}

		/**
		 * Add to body class.
		 */
		public function add_body_class( $classes ) {
			$theme = wp_get_theme();
			$theme = ( get_template_directory() !== get_stylesheet_directory() && $theme->parent() ) ? $theme->parent() : $theme;
			$theme_name = str_replace( ' ', '-', $theme->name );

			$classes[] = 'merchant-theme-' . strtolower( esc_attr( $theme_name ) );

			return $classes;
		}

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @param mixed $links Plugin Row Meta.
		 * @param mixed $file  Plugin Base file.
		 *
		 * @since 2.1.2
		 *
		 * @return array
		 */
		public function plugin_row_meta($links, $file) {
			if ( plugin_basename( MERCHANT_FILE ) !== $file ) {
				return $links;
			}

			/**
			 * The Merchant documentation URL.
			 *
			 * @since 2.1.2
			 */
			$docs_url = apply_filters( 'merchant_docs_url', 'https://docs.athemes.com/documentation/merchant/' );

			/**
			 * The Merchant landing page URL.
			 *
			 * @since 2.1.2
			 */
			$plugin_site = apply_filters( 'merchant_plugin_site_url', 'https://athemes.com/merchant/' );

			/**
			 * The Merchant changelog URL.
			 *
			 * @since 2.1.2
			 */
			$changelog = apply_filters( 'merchant_changelog_url', 'https://athemes.com/changelog/merchant/' );

			$row_meta = array(
				'docs'    => '<a href="' . esc_url( $docs_url ) . '" aria-label="' . esc_attr__( 'View Merchant documentation', 'merchant' ) . '" target="_blank">' . esc_html__(
						'Docs',
						'merchant'
					) . '</a>',
				'apidocs' => '<a href="' . esc_url( $plugin_site ) . '" aria-label="' . esc_attr__( 'View Merchant plugin site', 'merchant' ) . '" target="_blank">' . esc_html__(
						'Visit plugin site',
						'merchant' ) . '</a>',
				'support' => '<a href="' . esc_url( $changelog ) . '" aria-label="' . esc_attr__( 'View Merchant changelog', 'merchant' ) . '" target="_blank">' . esc_html__( 'Changelog',
						'merchant' ) . '</a>',
			);

			/**
			 * Filter the plugin row meta links.
			 *
			 * @since 2.1.2
			 *
			 * @param array  $row_meta The plugin row meta links.
			 * @param string $file    The plugin file.
			 */
			$row_meta = apply_filters( 'merchant_plugin_row_meta', $row_meta, $file );

			return array_merge( $links, $row_meta );
		}

		/**
		 * Add settings link to the Plugins page.
		 *
		 * @since 2.1.2
		 *
		 * @param array  $links       Plugin row links.
		 *
		 * @return array $links
		 */
		public function settings_link( $links ) {
			if ( ! merchant_pro_is_active() || ! merchant_pro_license_exists() ) {
				$url = merchant_admin_upgrade_link(
					'https://athemes.com/merchant/#pricing',
					array(),
					'plugins-page-upgrade-link'
				);
				$links['merchant-pro'] = sprintf(
					'<a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer"
				style="color: #00a32a; font-weight: 700;"
				onmouseover="this.style.color=\'#008a20\';"
				onmouseout="this.style.color=\'#00a32a\';"
				>%3$s</a>',
					esc_url( $url ),
					esc_attr__( 'Upgrade to Merchant Pro', 'merchant' ),
					esc_html__( 'Get Merchant Pro', 'merchant' )
				);
			}

			$custom['merchant-settings'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'page'    => 'merchant',
							'section' => 'settings',
						),
						admin_url( 'admin.php' )
					)
				),
				esc_attr__( 'Go to Merchant Settings page', 'merchant' ),
				esc_html__( 'Settings', 'merchant' )
			);

			return array_merge( $custom, (array) $links );
		}

		/**
		 * Register scripts.
		 * Scripts that might be used by multiple modules should be registered here.
		 *
		 */
		public function register_global_js_and_css() {
			// Register styles.
			$styles = array(

				// Grid.
				array(
					'handle' => 'merchant-grid',
					'src'    => 'assets/css/grid.min.css',
					'dep'    => array(),
					'ver'    => MERCHANT_VERSION,
					'media'  => 'all',
				),

				// Utilities.
				array(
					'handle' => 'merchant-utilities',
					'src'    => 'assets/css/utilities.min.css',
					'dep'    => array(),
					'ver'    => MERCHANT_VERSION,
					'media'  => 'all',
				),

				// Carousel.
				array(
					'handle' => 'merchant-carousel',
					'src'    => 'assets/css/carousel.min.css',
					'dep'    => array(),
					'ver'    => MERCHANT_VERSION,
					'media'  => 'all',
				),

				// Modal.
				array(
					'handle' => 'merchant-modal',
					'src'    => 'assets/css/modal.min.css',
					'dep'    => array(),
					'ver'    => MERCHANT_VERSION,
					'media'  => 'all',
				),

				// Tooltip.
				array(
					'handle' => 'merchant-tooltip',
					'src'    => 'assets/css/tooltip.min.css',
					'dep'    => array(),
					'ver'    => MERCHANT_VERSION,
					'media'  => 'all',
				),

				// Pagination.
				array(
					'handle' => 'merchant-pagination',
					'src'    => 'assets/css/pagination.min.css',
					'dep'    => array(),
					'ver'    => MERCHANT_VERSION,
					'media'  => 'all',
				),

			);

			foreach ( $styles as $style ) {
				wp_register_style( $style['handle'], MERCHANT_URI . $style['src'], $style['dep'], $style['ver'], $style['media'] );
			}

			// Register scripts.
			$scripts = array(

				// Scroll Direction
				array(
					'handle'    => 'merchant-scroll-direction',
					'src'       => 'assets/js/scroll-direction.min.js',
					'dep'       => array(),
					'in_footer' => true,
				),

				// Toggle Class
				array(
					'handle'    => 'merchant-toggle-class',
					'src'       => 'assets/js/toggle-class.min.js',
					'dep'       => array(),
					'in_footer' => true,
				),

				// Scroll To
				array(
					'handle'    => 'merchant-scroll-to',
					'src'       => 'assets/js/scroll-to.min.js',
					'dep'       => array(),
					'in_footer' => true,
				),

				// Custom Add To Cart Button
				array(
					'handle'    => 'merchant-custom-addtocart-button',
					'src'       => 'assets/js/custom-addtocart-button.min.js',
					'dep'       => array(),
					'in_footer' => true,
				),

				// Carousel
				array(
					'handle'    => 'merchant-carousel',
					'src'       => 'assets/js/carousel.min.js',
					'dep'       => array(),
					'in_footer' => true,
				),

				// Modal
				array(
					'handle'    => 'merchant-modal',
					'src'       => 'assets/js/modal.min.js',
					'dep'       => array(),
					'in_footer' => true,
				),

				// Copy To Clipboard
				array(
					'handle'          => 'merchant-copy-to-clipboard',
					'src'             => 'assets/js/copy-to-clipboard.min.js',
					'dep'             => array(),
					'in_footer'       => true,
					'localize_script' => array(
						'object' => 'merchantCopyToClipboard',
						'data'   => array(
							'i18n' => array(
								'copied' => esc_html__( 'Copied!', 'merchant' ),
							),
						),
					),
				),

				// Pagination
				array(
					'handle'    => 'merchant-pagination',
					'src'       => 'assets/js/pagination.min.js',
					'dep'       => array(),
					'in_footer' => true,
				),
			);

			foreach ( $scripts as $script ) {
				wp_register_script( $script['handle'], MERCHANT_URI . $script['src'], $script['dep'], MERCHANT_VERSION, $script['in_footer'] );

				if ( isset( $script['localize_script'] ) ) {
					wp_localize_script( $script['handle'], $script['localize_script']['object'], $script['localize_script']['data'] );
				}
			}
		}

		/**
		 * Enqueue styles and scripts.
		 */
		public function enqueue_styles_scripts() {

			/**
			 * Hook 'merchant_enqueue_before_main_css_js'
			 *
			 * @since 1.0
			 */
			do_action( 'merchant_enqueue_before_main_css_js' );

			wp_enqueue_style( 'merchant', MERCHANT_URI . 'assets/css/merchant.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_script( 'merchant', MERCHANT_URI . 'assets/js/merchant.min.js', array( 'jquery' ), MERCHANT_VERSION, true );

			$setting = array(
				'nonce'    => wp_create_nonce( 'merchant-nonce' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			);

			// Scroll to Top Button
			// TODO: Move this to the respective module class.
			if ( Merchant_Modules::is_module_active( 'scroll-to-top-button' ) ) {
				$setting['scroll_to_top'] = true;
			}

			// Auto External Links
			// TODO: Move this to the respective module class.
			if ( Merchant_Modules::is_module_active( 'auto-external-links' ) ) {
				$setting['auto_external_links'] = true;
			}

			// Cookie Banner
			if ( Merchant_Modules::is_module_active( 'cookie-banner' ) ) {
				$setting['cookie_banner']          = true;
				$setting['cookie_banner_duration'] = Merchant_Admin_Options::get( 'cookie-banner', 'cookie_duration', 365 );
			}

			/**
			 * Hook 'merchant_enqueue_after_main_css_js'
			 *
			 * @since 1.0
			 */
			do_action( 'merchant_enqueue_after_main_css_js' );

			/**
			 * Hook 'merchant_localize_script'
			 *
			 * @since 1.0
			 */
			$setting = apply_filters( 'merchant_localize_script', $setting );

			wp_localize_script( 'merchant', 'merchant', array( 
				'general' => array(
					'wooCurrencySymbol' => class_exists( 'Woocommerce' ) ? html_entity_decode( get_woocommerce_currency_symbol() ) : '',
					'wooCurrencyPosition' => class_exists( 'Woocommerce' ) ? get_option( 'woocommerce_currency_pos' ) : 'left',
					'wooThousandsSeparator' => class_exists( 'Woocommerce' ) ? wc_get_price_thousand_separator() : ',',
					'wooDecimalSeparator' => class_exists( 'Woocommerce' ) ? wc_get_price_decimal_separator() : '.',
					'wooNumberOfDecimals' => class_exists( 'Woocommerce' ) ? wc_get_price_decimals() : 2,

				),
				'setting' => $setting,
			) );
		}
	}

	Merchant_Loader::instance();
}
