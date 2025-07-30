<?php
/**
 * Merchant_Admin_Menu Class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Merchant_Admin_Menu' ) ) {

	class Merchant_Admin_Menu {
		public $page_title       = 'Merchant';
		public $plugin_slug      = 'merchant';
		public $capability       = 'manage_options';
		public $priority         = 58;
		public $notifications    = array();
		private static $instance = null;

		/**
		 * Instance.
		 *
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 */
		public function __construct() {
			if ( defined( 'MERCHANT_AWL_ACTIVE' ) ) {
				return;
			}

			if( $this->is_patcher_page() ) {
				add_action('admin_enqueue_scripts', array( $this, 'enqueue_patcher_scripts' ));
			}

			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
			add_action( 'admin_enqueue_scripts', array( $this, 'analytics_assets' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'dashboard_analytics_widget' ) );
			add_action( 'wp_ajax_merchant_notifications_read', array( $this, 'ajax_notifications_read' ) );
			add_action('admin_footer', array( $this, 'footer_internal_scripts' ));
		}

        /**
         * Add dashboard widget.
         *
         * @return void
         */
		public function dashboard_analytics_widget() {
            if( class_exists( 'WooCommerce' ) ) {
	            wp_add_dashboard_widget(
		            'merchant_modules_revenue',         // Widget slug.
		            esc_html__( 'Daily added revenue by Merchant', 'merchant' ),   // Title.
		            array( $this, 'dashboard_analytics_widget_content' ) // Display function.
	            );
            }
		}

        /**
         * Dashboard widget content.
         *
         * @return void
         */
		public function dashboard_analytics_widget_content() {
			$reports     = new Merchant_Analytics_Data_Reports();
			$date_ranges = $reports->get_last_and_previous_7_days_ranges();
			?>
            <div class="merchant-analytics-widget widget-chart-section">
                <div class="chart" data-period="<?php
				echo esc_attr( wp_json_encode( $reports->get_revenue_chart_report( $date_ranges['recent_period']['start'], $date_ranges['recent_period']['end'] ) ) )
				?>"></div>
                <div class="foot">
                    <div class="date-range">
                        <span>
                            <input type="text" class="date-range-input" readonly value="<?php
                            echo esc_attr( implode( ' - ', array_values( $date_ranges['recent_period'] ) ) ) ?>" placeholder="<?php
                            esc_attr_e( 'Select date range', 'merchant' ); ?>">
                        </span>
                        <span class="merchant-analytics-loading-spinner"></span>
                    </div>
                    <div class="analytics-link">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=merchant&section=analytics' ) ); ?>"><?php
			                esc_html_e( 'View Full Analytics', 'merchant' ); ?></a>
                    </div>
                </div>
            </div>
			<?php
		}

        /**
         * Enqueue assets for analytics page.
         *
         * @return void
         */
		public function analytics_assets( $hook ) {
            global $pagenow;
            $section = sanitize_text_field( $_GET['section'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if (
                    ( $hook === 'toplevel_page_merchant' && $section !== 'settings' && class_exists( 'WooCommerce' ) )
				|| ( $pagenow === 'index.php' && class_exists( 'WooCommerce' ) )
			) {
				wp_enqueue_style('date-picker', MERCHANT_URI . 'assets/vendor/air-datepicker/air-datepicker.css', array(), MERCHANT_VERSION, 'all' );
				wp_enqueue_style( 'merchant-analytics', MERCHANT_URI . 'assets/css/admin/analytics.css', array(), MERCHANT_VERSION );
				wp_enqueue_script('date-picker', MERCHANT_URI . 'assets/vendor/air-datepicker/air-datepicker.js', array( 'jquery' ), MERCHANT_VERSION, true );
				wp_enqueue_script( 'apexcharts', MERCHANT_URI . 'assets/js/vendor/apexcharts.min.js', array( 'jquery' ), MERCHANT_VERSION, true );
				wp_enqueue_script( 'merchant-analytics', MERCHANT_URI . 'assets/js/admin/analytics.js', array( 'jquery', 'apexcharts' ), MERCHANT_VERSION, true );
				wp_localize_script( 'merchant-analytics', 'merchant_analytics', array(
					'nonce'           => wp_create_nonce( 'merchant' ),
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'currency_name'   => get_woocommerce_currency(),
					'currency_symbol' => get_woocommerce_currency_symbol(),
					'labels'          => array(
						'orders'     => esc_html__( 'orders', 'merchant' ),
						'orders_aov' => esc_html__( 'Orders AOV', 'merchant' ),
					),
				) );

				wp_localize_script( 'merchant-analytics', 'merchant_datepicker_locale', array(
					wp_json_encode(
						array(
							'days'        => array(
								esc_html__( 'Sunday', 'merchant' ),
								esc_html__( 'Monday', 'merchant' ),
								esc_html__( 'Tuesday', 'merchant' ),
								esc_html__( 'Wednesday', 'merchant' ),
								esc_html__( 'Thursday', 'merchant' ),
								esc_html__( 'Friday', 'merchant' ),
								esc_html__( 'Saturday', 'merchant' ),
							),
							'daysShort'   => array(
								esc_html__( 'Sun', 'merchant' ),
								esc_html__( 'Mon', 'merchant' ),
								esc_html__( 'Tue', 'merchant' ),
								esc_html__( 'Wed', 'merchant' ),
								esc_html__( 'Thu', 'merchant' ),
								esc_html__( 'Fri', 'merchant' ),
								esc_html__( 'Sat', 'merchant' ),
							),
							'daysMin'     => array(
								esc_html__( 'Su', 'merchant' ),
								esc_html__( 'Mo', 'merchant' ),
								esc_html__( 'Tu', 'merchant' ),
								esc_html__( 'We', 'merchant' ),
								esc_html__( 'Th', 'merchant' ),
								esc_html__( 'Fr', 'merchant' ),
								esc_html__( 'Sa', 'merchant' ),
							),
							'months'      => array(
								esc_html__( 'January', 'merchant' ),
								esc_html__( 'February', 'merchant' ),
								esc_html__( 'March', 'merchant' ),
								esc_html__( 'April', 'merchant' ),
								esc_html__( 'May', 'merchant' ),
								esc_html__( 'June', 'merchant' ),
								esc_html__( 'July', 'merchant' ),
								esc_html__( 'August', 'merchant' ),
								esc_html__( 'September', 'merchant' ),
								esc_html__( 'October', 'merchant' ),
								esc_html__( 'November', 'merchant' ),
								esc_html__( 'December', 'merchant' ),
							),
							'monthsShort' => array(
								esc_html__( 'Jan', 'merchant' ),
								esc_html__( 'Feb', 'merchant' ),
								esc_html__( 'Mar', 'merchant' ),
								esc_html__( 'Apr', 'merchant' ),
								esc_html__( 'May', 'merchant' ),
								esc_html__( 'Jun', 'merchant' ),
								esc_html__( 'Jul', 'merchant' ),
								esc_html__( 'Aug', 'merchant' ),
								esc_html__( 'Sep', 'merchant' ),
								esc_html__( 'Oct', 'merchant' ),
								esc_html__( 'Nov', 'merchant' ),
								esc_html__( 'Dec', 'merchant' ),
							),
							'clear'       => esc_html__( 'Clear', 'merchant' ),
						)
					),
				) );
			}
		}

		/**
		 * Is aThemes Patcher page.
		 * 
		 * @return bool
		 */
		public function is_patcher_page() {
			global $pagenow;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $pagenow === 'admin.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'athemes-patcher-preview-mp' );
		}

		/**
		 * Enqueue aThemes Patcher preview scripts and styles.
		 * 
		 * @return void
		 */
		public function enqueue_patcher_scripts() {
			wp_enqueue_style( 'wp-components' );
		}

		/**
		 * Include required classes.
		 *
		 * @return void
		 */
		public function add_admin_menu() {
			global $submenu;

			// Dashboard.
			add_menu_page(
				$this->page_title,
				$this->page_title,
				$this->capability,
				$this->plugin_slug,
				array( $this, 'page_dashboard' ),
				MERCHANT_URI . 'assets/images/merchant-logo.svg',
				$this->priority
			);

			// Dashboard Sub Item.
			add_submenu_page(
				$this->plugin_slug,
				esc_html__('Dashboard', 'merchant'),
				esc_html__('Dashboard', 'merchant'),
				$this->capability,
				$this->plugin_slug,
				'',
				1
			);

			// All Modules.
			add_submenu_page(
				$this->plugin_slug,
				esc_html__('Modules', 'merchant'),
				esc_html__('Modules', 'merchant'),
				$this->capability,
				'admin.php?page=merchant&section=modules',
				'',
				2
			);

			if( class_exists( 'WooCommerce' ) ) {
				// Campaigns.
				add_submenu_page(
					$this->plugin_slug,
					esc_html__( 'Campaigns', 'merchant' ),
					esc_html__( 'Campaigns', 'merchant' ),
					$this->capability,
					'admin.php?page=merchant&section=campaigns',
					'',
					3
				);

				// Analytics.
				add_submenu_page(
					$this->plugin_slug,
					esc_html__( 'Analytics', 'merchant' ),
					esc_html__( 'Analytics', 'merchant' ),
					$this->capability,
					'admin.php?page=merchant&section=analytics',
					'',
					4
				);
			}

			// Settings.
			add_submenu_page(
				$this->plugin_slug,
				esc_html__('Settings', 'merchant'),
				esc_html__('Settings', 'merchant'),
				$this->capability,
				'admin.php?page=merchant&section=settings',
				'',
				5
			);

			// Add 'aThemes Patcher' link
			add_submenu_page( // phpcs:ignore WPThemeReview.PluginTerritory.NoAddAdminPages.add_menu_pages_add_submenu_page
				'merchant',
				esc_html__('Patcher', 'merchant'),
				esc_html__('Patcher', 'merchant'),
				$this->capability,
				'athemes-patcher-preview-mp',
				array( $this, 'html_patcher' ),
				6
			);


			// Add 'Upgrade' link.
			if ( ! defined( 'MERCHANT_PRO_VERSION' ) ) {
				$url = merchant_admin_upgrade_link(
					'https://athemes.com/merchant-upgrade',
					array(
						'utm_source'   => 'theme_submenu_page',
						'utm_medium'   => 'button',
						'utm_campaign' => 'Merchant',
					),
					'theme-submenu-page-upgrade-link'
				);
				add_submenu_page(
					$this->plugin_slug,
					esc_html__( 'Upgrade to Pro', 'merchant' ),
					esc_html__( 'Upgrade to Pro', 'merchant' ),
					$this->capability,
					esc_url( $url ),
					'',
					7
				);
			}
		}

        /**
         * Add admin bar menu.
         *
         * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
         */
		public function add_admin_bar_menu( $wp_admin_bar ) {
			// Check if the current user has the capability to manage options
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Parent menu item (Dashboard)
			$wp_admin_bar->add_node( array(
				'id'    => 'merchant-dashboard',
				'title' => esc_html__( 'Merchant', 'merchant' ), // Use your plugin name or page title
				'href'  => admin_url( 'admin.php?page=merchant' ), // Link to the main dashboard
				'meta'  => array(
					'title' => esc_html__( 'Merchant Dashboard', 'merchant' ),
				),
			) );

			// Dashboard Sub Item
			$wp_admin_bar->add_node( array(
				'id'     => 'merchant-dashboard-sub',
				'parent' => 'merchant-dashboard',
				'title'  => esc_html__( 'Dashboard', 'merchant' ),
				'href'   => admin_url( 'admin.php?page=merchant' ),
				'meta'   => array(
					'title' => esc_html__( 'Dashboard', 'merchant' ),
				),
			) );

			// All Modules
			$wp_admin_bar->add_node( array(
				'id'     => 'merchant-modules',
				'parent' => 'merchant-dashboard',
				'title'  => esc_html__( 'Modules', 'merchant' ),
				'href'   => admin_url( 'admin.php?page=merchant&section=modules' ),
				'meta'   => array(
					'title' => esc_html__( 'Modules', 'merchant' ),
				),
			) );

			if( class_exists( 'WooCommerce' ) ) {
				// Settings
				$wp_admin_bar->add_node( array(
					'id'     => 'merchant-settings',
					'parent' => 'merchant-dashboard',
					'title'  => esc_html__( 'Settings', 'merchant' ),
					'href'   => admin_url( 'admin.php?page=merchant&section=settings' ),
					'meta'   => array(
						'title' => esc_html__( 'Settings', 'merchant' ),
					),
				) );

				// Campaigns
				$wp_admin_bar->add_node( array(
					'id'     => 'merchant-campaigns',
					'parent' => 'merchant-dashboard',
					'title'  => esc_html__( 'Campaigns', 'merchant' ),
					'href'   => admin_url( 'admin.php?page=merchant&section=campaigns' ),
					'meta'   => array(
						'title' => esc_html__( 'Campaigns', 'merchant' ),
					),
				) );
			}

			// Analytics
			$wp_admin_bar->add_node( array(
				'id'     => 'merchant-analytics',
				'parent' => 'merchant-dashboard',
				'title'  => esc_html__( 'Analytics', 'merchant' ),
				'href'   => admin_url( 'admin.php?page=merchant&section=analytics' ),
				'meta'   => array(
					'title' => esc_html__( 'Analytics', 'merchant' ),
				),
			) );

			// Upgrade to Pro (if not already defined)
			if ( ! defined( 'MERCHANT_PRO_VERSION' ) ) {
				$url = merchant_admin_upgrade_link(
					'https://athemes.com/merchant-upgrade',
					array(
						'utm_source'   => 'theme_submenu_page',
						'utm_medium'   => 'button',
						'utm_campaign' => 'Merchant',
					),
					'adminbar-submenu-page-upgrade-link'
				);
				$wp_admin_bar->add_node( array(
					'id'     => 'merchant-upgrade',
					'parent' => 'merchant-dashboard',
					'title'  => esc_html__( 'Upgrade to Pro', 'merchant' ),
					'href'   => esc_url( $url ),
					'meta'   => array(
						'title'  => esc_html__( 'Upgrade to Pro', 'merchant' ),
						'target' => '_blank', // Open link in a new tab
					),
				) );
			}
		}

		/**
		 * Get Notifications
		 *
		 * @return array
		 */
		public function get_notifications() {
			$notifications = get_transient( 'merchant_notifications' );

			if ( ! empty( $notifications ) ) {
				$this->notifications = $notifications;
			} else {

				/**
				 * Hook: merchant_changelog_api_url
				 * 
				 * @since 1.0
				 */
				$response = wp_remote_get( apply_filters( 'merchant_changelog_api_url', 'https://athemes.com/wp-json/wp/v2/notifications?theme=7103&per_page=3' ) );

				if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
					$this->notifications = json_decode( wp_remote_retrieve_body( $response ) );
					set_transient( 'merchant_notifications', $this->notifications, 24 * HOUR_IN_SECONDS );
				}
			}

			return $this->notifications;
		}

		/**
		 * Check if the latest notification is read
		 *
		 * @return bool
		 */
		public function is_latest_notification_read() {

			if ( ! isset( $this->notifications ) || empty( $this->notifications ) ) {
				return false;
			}
			
			$user_id        = get_current_user_id();
			$user_read_meta = get_user_meta( $user_id, 'merchant_dashboard_notifications_latest_read', true );

			$last_notification_date      = strtotime( is_string( $this->notifications[0]->post_date ) ? $this->notifications[0]->post_date : '' );
			$last_notification_date_ondb = $user_read_meta ? strtotime( $user_read_meta ) : false;

			if ( ! $last_notification_date_ondb ) {
				return false;
			}

			if ( $last_notification_date > $last_notification_date_ondb ) {
				return false;
			}

			return true;
		}

		/**
		 * Ajax notifications.
		 *
		 * @return void
		 */
		public function ajax_notifications_read() {
			check_ajax_referer( 'merchant', 'nonce' );

			// Check current user capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to do this.' );
			}

			$latest_notification_date = ( isset( $_POST[ 'latest_notification_date' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'latest_notification_date' ] ) ) : false;

			update_user_meta( get_current_user_id(), 'merchant_dashboard_notifications_latest_read', $latest_notification_date );

			wp_send_json_success();
		}

		/**
		 * Include Page dashboard
		 *
		 * @return void
		 */
		public function page_dashboard() {
			require_once MERCHANT_DIR . 'admin/pages/page-dashboard.php';
		}

		/**
		 * Footer internal scripts
		 *
		 * @return void
		 */
		public function footer_internal_scripts() {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? str_replace( '/wp-admin/', '', $_SERVER['REQUEST_URI'] ) : '';
			?>
			<style>
				#adminmenu .toplevel_page_merchant .wp-submenu li.current a {
                    color: rgba(240, 246, 252, 0.7);
                    font-weight: 400;
                }

				#adminmenu .toplevel_page_merchant .wp-submenu li a[href="<?php echo $request_uri; //phpcs:ignore ?>"] {
					color: #fff;
                    font-weight: 600;
				}

				#adminmenu .toplevel_page_merchant .wp-submenu a[href="https://athemes.com/merchant-upgrade?utm_source=theme_submenu_page&utm_medium=button&utm_campaign=Merchant"] {
					background-color: green;
					color: #FFF;
				}
			</style>
            <script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function () {
                    const merchantUpsellMenuItem = document.querySelector('#adminmenu .toplevel_page_merchant .wp-submenu a[href="https://athemes.com/merchant-upgrade?utm_source=theme_submenu_page&utm_medium=button&utm_campaign=Merchant"]');

                    if (merchantUpsellMenuItem) {
                        merchantUpsellMenuItem.addEventListener('click', function (e) {
                            e.preventDefault();

                            const href = this.getAttribute('href');
                            window.open(href, '_blank');
                        });
                    }
                });
            </script>
			<?php
		}

		/**
		 * HTML aThemes Patcher.
		 *
		 * @return void 
		 */
		public function html_patcher() {
			require_once MERCHANT_DIR . 'admin/pages/page-patcher.php';
		}
	}

	Merchant_Admin_Menu::instance();

}
