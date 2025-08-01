<?php

/**
 * Essential functions.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get template part.
 *
 */
function merchant_get_template_part( $folder_path = '', $name = '', $args = array(), $return_results = false ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$folder_path = ! empty( $folder_path ) ? "/$folder_path/" : '';

	$template = '';

	// Look in yourtheme/merchant/folder-path/name.php and yourtheme/merchant/folder-path/name.php.
	if ( $name ) {
		$template = locate_template( array( "merchant{$folder_path}{$name}.php" ) );
	}

	// Get default.
	if ( ! $template && $name ) {
		// Try to get it from the PRO dir if it exists.
		if ( defined( 'MERCHANT_PRO_DIR' ) && file_exists( MERCHANT_PRO_DIR . "templates{$folder_path}{$name}.php" ) ) {
			$template = MERCHANT_PRO_DIR . "templates{$folder_path}{$name}.php";
			// Otherwise take it from the base dir.
		} elseif ( file_exists( MERCHANT_DIR . "templates{$folder_path}{$name}.php" ) ) {
			$template = MERCHANT_DIR . "templates{$folder_path}{$name}.php";
		}
	}

	/**
	 * Hook: 'merchant_get_template_part'
	 *
	 * @since 1.0
	 */
	$template = apply_filters( 'merchant_get_template_part', $template, $folder_path, $name );


	if ( $template ) {
		// Whether to return template HTML as string or to echo it
		if ( $return_results ) {
			ob_start();
			include( $template );

			return ob_get_clean();
		}

		return include( $template );
	}
}

/**
 * Check if Merchant Pro is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_pro_active' ) ) {
	function merchant_is_pro_active() {
		return defined( 'MERCHANT_PRO_VERSION' );
	}
}

/**
 * Check if Botiga theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_botiga_active' ) ) {
	function merchant_is_botiga_active() {
		return defined( 'BOTIGA_VERSION' );
	}
}

/**
 * Check if Divi theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_divi_active' ) ) {
	function merchant_is_divi_active() {
		return defined( 'ET_CORE_VERSION' );
	}
}

/**
 * Check if Avada theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_avada_active' ) ) {
	function merchant_is_avada_active() {
		return defined( 'AVADA_VERSION' );
	}
}

/**
 * Check if Kadence theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_kadence_active' ) ) {
	function merchant_is_kadence_active() {
		return class_exists( '\Kadence\Theme' );
	}
}

/**
 * Check if OceanWP theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_oceanwp_active' ) ) {
	function merchant_is_oceanwp_active() {
		return class_exists( 'OCEANWP_Theme_Class' );
	}
}

/**
 * Check if Blocksy theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_blocksy_active' ) ) {
	function merchant_is_blocksy_active() {
		return class_exists( 'Blocksy_Manager' );
	}
}

/**
 * Check if Flatsome theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_flatsome_active' ) ) {
    function merchant_is_flatsome_active() {
        return class_exists( 'Flatsome' );
    }
}

/**
 * Check if Astra theme or its Pro version is installed and active.
 *
 * @param $is_pro_active
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_astra_active' ) ) {
	function merchant_is_astra_active( $is_pro_active = false ) {
		if ( $is_pro_active ) {
			return function_exists( 'astra_has_pro_woocommerce_addon' ) && astra_has_pro_woocommerce_addon();
		}

		return defined( 'ASTRA_THEME_VERSION' );
	}
}

/**
 * Check if Storefront theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_storefront_active' ) ) {
	function merchant_is_storefront_active() {
		return class_exists( 'Storefront' );
	}
}

/**
 * Check if WooCommerce Germanized theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_woocommerce_germanized_active' ) ) {
	function merchant_is_woocommerce_germanized_active() {
		return class_exists( 'WooCommerce_Germanized' );
	}
}

/**
 * Check if Breakdance Builder is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_breakdance_active' ) ) {
	function merchant_is_breakdance_active() {
		return defined( '__BREAKDANCE_VERSION' );
	}
}

/**
 * Check if Brick Builder is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_bricks_builder_active' ) ) {
	function merchant_is_bricks_builder_active() {
		return defined( 'BRICKS_VERSION' );
	}
}

/**
 * Check if Elementor or its Pro version is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_elementor_active' ) ) {
	function merchant_is_elementor_active( $is_pro_active = false ) {
		if ( $is_pro_active ) {
			return defined( 'ELEMENTOR_PRO_VERSION' );
		}

		return defined( 'ELEMENTOR_VERSION' );
	}
}

/**
 * Check if Ohio theme is installed and active.
 *
 * @return bool
 */
if ( ! function_exists( 'merchant_is_ohio_active' ) ) {
	function merchant_is_ohio_active() {
		return function_exists( 'ohio_setup' );
	}
}

if ( ! function_exists( 'merchant_supply_defaults' ) ) {
	/**
	 * Recursively merge two arrays, preserving distinct values.
	 *
	 * Merges the provided arguments into the defaults recursively. If a key exists in both arrays
	 * and both values are arrays, they are merged recursively. Otherwise, the value from the
	 * arguments array overrides the default value.
	 *
	 * @param array $defaults The default array containing base values.
	 * @param array $args     The arguments array containing values to override or supplement defaults.
	 *
	 * @return array The merged array with defaults supplemented or overridden by arguments.
	 */
	function merchant_supply_defaults( array $defaults, array $args ) {
		$merged = $defaults;
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = merchant_supply_defaults( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}
}

if ( ! function_exists( 'merchant_admin_upgrade_link' ) ) {
	/**
	 * Get the Merchant Pro upgrade link.
	 *
	 * @param string $url  The base URL for the upgrade link.
	 * @param array  $args The arguments for the upgrade link. (Key-value pairs)
	 * @param string $type The type of upgrade link.
	 *
	 * @return string The upgrade link.
	 */
	function merchant_admin_upgrade_link( $url, $args = array(), $type = 'plugins-page-upgrade-link' ) {
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		/**
		 * Modify upgrade link.
		 *
		 * @param string $url  The upgrade link URL.
		 * @param string $type The type of upgrade link.
		 *
		 * @since 2.1.5
		 *
		 */
		return apply_filters( 'merchant_upgrade_link', $url, $type );
	}
}

if ( ! function_exists( 'merchant_pro_is_active' ) ) {
	/**
	 * Check if Merchant Pro is active.
	 *
	 * @return bool
	 */
	function merchant_pro_is_active() {
		return defined( 'MERCHANT_PRO_VERSION' );
	}
}

if ( ! function_exists( 'merchant_pro_license_exists' ) ) {
	/**
	 * Check if Merchant Pro license exists.
	 *
	 * @return bool
	 */
	function merchant_pro_license_exists() {
		return ! empty( get_option( 'merchant_pro_license_key' ) );
	}
}

/**
 * Check if any shortcode starts with merchant doesn't exist.
 * If the shortcode is not registered, register it with return null to guarantee it exists.
 */
if ( ! function_exists( 'merchant_modules_shortcode_exists' ) ) {
	function merchant_modules_shortcode_exists() {
		/**
		 * Filter the shortcodes.
		 *
		 * @param array $shortcodes modules shortcodes
		 *
		 * @since 1.8
		 */
		$shortcodes = apply_filters( 'merchant_modules_shortcodes',
			array(
				'merchant_module_stock_scarcity',
				'merchant_module_wait_list',
				'merchant_module_volume_discounts',
				'merchant_module_payment_logos',
				'merchant_module_cart_reserved_timer',
				'merchant_module_product_brand_image',
				'merchant_module_size_chart',
				'merchant_module_reasons_to_buy',
				'merchant_module_recently_viewed_products',
				'merchant_module_trust_badges',
				'merchant_module_advanced_reviews',
				'merchant_module_frequently_bought_together',
				'merchant_module_buy_x_get_y',
				'merchant_module_product_bundles',
				'merchant_module_quick_social_links',
				'merchant_module_product_navigation_links',
				'merchant_module_product_video',
				'merchant_module_product_audio',
				'merchant_module_countdown_timer',
				'merchant_module_product_labels',
				'merchant_module_wishlist',
				'merchant_module_clear_cart',
				'merchant_module_free_shipping_progress_bar_single_product_page',
				'merchant_module_free_shipping_progress_bar_cart_page',
				'merchant_module_free_shipping_progress_bar_checkout_page',
				'merchant_module_quick_view',
				'merchant_module_real_time_search',
				'merchant_reviews_carousel',
				'merchant_module_complementary_products',
			)
		);

		// Loop through the shortcodes and register them if they don't exist.
		array_map( static function ( $shortcode ) {
			if ( ! shortcode_exists( $shortcode ) ) {
				add_shortcode( $shortcode, '__return_null' );
			}
		}, $shortcodes );
	}
}
add_action( 'init', 'merchant_modules_shortcode_exists' );