<?php

/**
 * Add To Cart Text
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Add To Cart Text Class.
 *
 */
class Merchant_Add_To_Cart_Text extends Merchant_Add_Module {

	/**
	 * Module ID.
	 */
	const MODULE_ID = 'add-to-cart-text';

	/**
	 * Is module preview.
	 *
	 */
	public static $is_module_preview = false;

	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		// Module id.
		$this->module_id = self::MODULE_ID;

		// WooCommerce only.
		$this->wc_only = true;

		// Parent construct.
		parent::__construct();

		// Module default settings.
		$this->module_default_settings = array(
				'simple_product_label'                 => esc_html__( 'Add to cart', 'merchant' ),
				'simple_product_shop_label'            => esc_html__( 'Add to cart', 'merchant' ),
				'simple_product_custom_single_label'   => 0,
				'variable_product_label'               => esc_html__( 'Add to cart', 'merchant' ),
				'variable_product_shop_label'          => esc_html__( 'Select options', 'merchant' ),
				'variable_product_custom_single_label' => 0,
				'out_of_stock_shop_label'              => esc_html__( 'Out of stock', 'merchant' ),
				'out_of_stock_custom_label'            => 0,
		);

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// Module section.
		$this->module_section = $this->module_data['section'];

		// Module options path.
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		// Is module preview page.
		if ( is_admin() && parent::is_module_settings_page() ) {
			self::$is_module_preview = true;

			// Enqueue admin styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );

			// Admin preview box.
			add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );
		}

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			// Init translations.
			$this->init_translations();
		}

		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

		// Customize add to cart text on the single product page.
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'customize_single_add_to_cart_text' ), 99, 2 );

		// Customize add to cart text on shop pages.
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'customize_shop_add_to_cart_text' ), 10, 2 );
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		if ( ! empty( $settings['simple_product_label'] ) ) {
			Merchant_Translator::register_string( $settings['simple_product_label'], esc_html__( 'Add to cart text simple product label', 'merchant' ) );
		}
		if ( ! empty( $settings['simple_product_shop_label'] ) ) {
			Merchant_Translator::register_string( $settings['simple_product_shop_label'], esc_html__( 'Add to cart text simple product shop label', 'merchant' ) );
		}
		if ( ! empty( $settings['variable_product_label'] ) ) {
			Merchant_Translator::register_string( $settings['variable_product_label'], esc_html__( 'Add to cart text variable product label', 'merchant' ) );
		}
		if ( ! empty( $settings['variable_product_shop_label'] ) ) {
			Merchant_Translator::register_string( $settings['variable_product_shop_label'], esc_html__( 'Add to cart text variable product shop label', 'merchant' ) );
		}
		if ( ! empty( $settings['out_of_stock_shop_label'] ) ) {
			Merchant_Translator::register_string( $settings['out_of_stock_shop_label'], esc_html__( 'Add to cart text out of stock label', 'merchant' ) );
		}
	}

	/**
	 * Admin enqueue CSS.
	 *
	 * @return void
	 */
	public function admin_enqueue_css() {
		if ( $this->is_module_settings_page() ) {
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
		}
	}

	/**
	 * Render admin preview
	 *
	 * @param Merchant_Admin_Preview $preview
	 * @param string $module
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( $module === self::MODULE_ID ) {
			// HTML.
			$preview->set_html( array( $this, 'admin_preview_content' ), $this->get_module_settings() );

			// Simple product add to cart text
			$preview->set_text( 'simple_product_shop_label', '.merchant-preview-add-to-cart-simple' );

			// Variable product add to cart text
			$preview->set_text( 'variable_product_shop_label', '.merchant-preview-add-to-cart-variable' );

			// Out of stock add to cart text
			$preview->set_text( 'out_of_stock_shop_label', '.merchant-preview-add-to-cart-out-of-stock' );

			// Out of stock add to cart text
			$preview->set_class( 'out_of_stock_custom_label', '.merchant-preview-product-out-of-stock', array(), 'display' );
		}

		return $preview;
	}

	/**
	 * Admin preview content.
	 *
	 * @return void
	 */
	public function admin_preview_content( $settings ) {
		?>

		<div class="merchant-preview-product">
			<div class="image-wrapper"></div>
			<h3><?php echo esc_html__( 'Simple product', 'merchant' ); ?></h3>
			<p><?php echo esc_html__( 'The product description normally goes here.', 'merchant' ); ?></p>
			<span class="price">
				<span class="woocommerce-Price-amount amount">
					<bdi><span class="woocommerce-Price-currencySymbol"><?php echo esc_html( get_woocommerce_currency_symbol() ) ?></span><?php echo esc_html__( '14.00', 'merchant' ) ?></bdi>
				</span>
			</span>
			<div class="merchant-preview-add-to-cart merchant-preview-add-to-cart-simple"><?php echo esc_html( $settings['simple_product_shop_label'] ); ?></div>
		</div>
		<div class="merchant-preview-product">
			<div class="image-wrapper"></div>
			<h3><?php echo esc_html__( 'Variable product', 'merchant' ); ?></h3>
			<p><?php echo esc_html__( 'The product description normally goes here.', 'merchant' ); ?></p>
			<span class="price">
				<span class="woocommerce-Price-amount amount">
					<bdi><span class="woocommerce-Price-currencySymbol"><?php echo esc_html( get_woocommerce_currency_symbol() ) ?></span><?php echo esc_html__( '20.00', 'merchant' ) ?></bdi>
				</span>
			</span>
			<div class="merchant-preview-add-to-cart merchant-preview-add-to-cart-variable"><?php echo esc_html( $settings['variable_product_shop_label'] ); ?></div>
		</div>
		<div class="merchant-preview-product merchant-preview-product-out-of-stock <?php echo esc_html( ( $settings['out_of_stock_custom_label'] ? 'display' : '' ) ) ?>">
			<div class="image-wrapper"></div>
			<h3><?php echo esc_html__( 'Out of stock product', 'merchant' ); ?></h3>
			<p><?php echo esc_html__( 'The product description normally goes here.', 'merchant' ); ?></p>
			<span class="price">
				<span class="woocommerce-Price-amount amount">
					<bdi><span class="woocommerce-Price-currencySymbol"><?php echo esc_html( get_woocommerce_currency_symbol() ) ?></span><?php echo esc_html__( '20.00', 'merchant' ) ?></bdi>
				</span>
			</span>
			<div class="merchant-preview-add-to-cart merchant-preview-add-to-cart-out-of-stock"><?php echo esc_html( $settings['out_of_stock_shop_label'] ); ?></div>
		</div>
		<?php
	}


	/**
	 * Customize add to cart text the single product page.
	 *
	 * @param string     $default_val The "add to cart" button default label
	 * @param WC_Product $product     The product object.
	 *
	 * @return string
	 */
	public function customize_single_add_to_cart_text( $default_val, $product ) {
        if( ! $product instanceof WC_Product ) {
            return $default_val; // Return default value if product is not an instance of WC_Product. (Stay safe)
        }

		/**
		 * Product specific
		 */
		$product_specific_label = get_post_meta( $product->get_id(), '_merchant_add_to_cart_text_single_label', true );

		if ( ! empty( $product_specific_label ) ) {
			return $product_specific_label;
		}

		/**
		 * Global
		 */
		$settings = $this->get_module_settings();

		// This hook can also be used on different pages.
		if ( is_single() ) {
			// Simple products
			if ( $settings['simple_product_custom_single_label'] && $product->is_type( 'simple' ) ) {
				return esc_html( Merchant_Translator::translate( $settings['simple_product_label'] ) );
			}

			// Variable products.
			if ( $settings['variable_product_custom_single_label'] && $product->is_type( 'variable' ) ) {
				return esc_html( Merchant_Translator::translate( $settings['variable_product_label'] ) );
			}
		} else {
			// Simple products
			if ( $product->is_type( 'simple' ) ) {
				return esc_html( Merchant_Translator::translate( $settings['simple_product_shop_label'] ) );
			}

			// Variable products.
			if ( $product->is_type( 'variable' ) ) {
				return esc_html( Merchant_Translator::translate( $settings['variable_product_shop_label'] ) );
			}
		}

		return $default_val;
	}

	/**
	 *  Customize add to cart text on shop pages.
	 *
	 * @param string     $default_val
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public function customize_shop_add_to_cart_text( $default_val, WC_Product $product ) {
		// Module settings
		$settings = $this->get_module_settings();

		if ( ! $product->is_in_stock() && $settings['out_of_stock_custom_label'] ) {
			return esc_html( Merchant_Translator::translate( $settings['out_of_stock_shop_label'] ) );
		}

		/**
		 * Product specific
		 */
		$product_specific_label = get_post_meta( $product->get_id(), '_merchant_add_to_cart_text_shop_label', true );

		if ( ! empty( $product_specific_label ) ) {
			return esc_html( $product_specific_label );
		}

		/**
		 * Global
		 */

		// Simple products
		if ( $product->is_type( 'simple' ) ) {
			return esc_html( Merchant_Translator::translate( $settings['simple_product_shop_label'] ) );
		}

		// Variable products.
		if ( $product->is_type( 'variable' ) ) {
			return esc_html( Merchant_Translator::translate( $settings['variable_product_shop_label'] ) );
		}

		return $default_val;
	}
}

// Metabox.
require_once MERCHANT_DIR . 'inc/modules/add-to-cart-text/admin/class-add-to-cart-text-metabox.php';

// Initialize the module.
add_action( 'init', function () {
	Merchant_Modules::create_module( new Merchant_Add_To_Cart_Text() );
} );
