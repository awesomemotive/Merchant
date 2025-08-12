<?php
/**
 * Plugin Name: Merchant
 * Plugin URI:  https://athemes.com/merchant
 * Description: All-in-one WooCommerce plugin for pre-orders, product labels, buy now, quick view, discount rules and more.
 * Version:     2.1.6
 * Author:      aThemes
 * Author URI:  https://athemes.com
 * License:     GPLv3 or later License
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: merchant
 * Domain Path: /languages
 *
 * WC requires at least: 6.0
 * WC tested up to: 10.0.0
 *
 * @package Merchant
 * @since 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Merchant constants.
define( 'MERCHANT_VERSION', '2.1.6' );
define( 'MERCHANT_DB_VERSION', '1.1.0' ); // Update only when the database structure changes. In inc/classes/class-merchant-db-tables.php
define( 'MERCHANT_FILE', __FILE__ );
define( 'MERCHANT_BASE', trailingslashit( plugin_basename( MERCHANT_FILE ) ) );
define( 'MERCHANT_DIR', trailingslashit( plugin_dir_path( MERCHANT_FILE ) ) );
define( 'MERCHANT_URI', trailingslashit( plugins_url( '/', MERCHANT_FILE ) ) );
define( 'MERCHANT_REVIEW_URL', 'https://wordpress.org/support/plugin/merchant/reviews/#new-post' );

/**
 * Merchant class.
 *
 */
class Merchant {

	/**
	 * The single class instance.
	 *
	 */
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

		// aThemes White Label Compatibility.
		if ( function_exists( 'athemes_wl_get_data' ) ) {
			$merchant_awl_data = athemes_wl_get_data();

			if ( ! empty( $merchant_awl_data[ 'activate_white_label' ] ) ) {
				define( 'MERCHANT_AWL_ACTIVE', true );
			}
		}

		// Translation.
		add_action( 'init', array( $this, 'translation' ) );

		// Declare WooCommerce HPOS Compatibility.
		add_action( 'before_woocommerce_init', function() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		} );

		// Declare incompatibility with Woo 8.3.0+ cart and checkout blocks.
		add_action( 'before_woocommerce_init', function() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
			}
		} );

		// Load the plugin functionality.
		$this->includes();

		// Initialize the merchant database tables.
		Merchant_DB_Tables::init();
	}

	/**
	 * Translation
	 * 
	 */
	public function translation() {
		load_plugin_textdomain( 'merchant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}

	/**
	 * Includes.
	 *
	 */
	public function includes() {
		require_once MERCHANT_DIR . 'admin/class-merchant-admin-loader.php';
		require_once MERCHANT_DIR . 'inc/class-merchant-loader.php';
	}
}

/**
 * Run the plugin.
 */
Merchant::instance();