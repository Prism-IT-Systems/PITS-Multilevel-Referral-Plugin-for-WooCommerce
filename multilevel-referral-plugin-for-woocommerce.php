<?php

/**
 * Plugin Name: Multilevel Referral Plugin for WooCommerce
 * Description: The WooCommerce Multilevel Plugin is a WooCommerce Add-On Plugin. Attract new customers, grow and market your business for free using a social referral program. Made especially for WooCommerce store owners, Multilevel Referral Plugin for WooCommerce rewards your clients for sharing your website with their friends, family, and colleagues.
 * Version: 2.28.2
 * Requires Plugins: woocommerce
 * WC requires at least: 3.0.0
 * WC tested up to: 10.0.4
 * Author: Prism I.T. Systems
 * Author URI: http://www.prismitsystems.com
 * Developer: Prism I.T. Systems
 * Developer URI: http://www.prismitsystems.com
 * Text Domain: multilevel-referral-plugin-for-woocommerce
 * Domain Path: /languages
 * Copyright: &copy;    2009-2025 PRISM I.T. SYSTEMS.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}
require_once ABSPATH . 'wp-includes/pluggable.php';
define( 'WOOCOMMERCE_MULTILEVEL_REFERRAL_NAME', 'Multilevel Referral Plugin for WooCommerce' );
define( 'WOOCOMMERCE_MULTILEVEL_REFERRAL_REQUIRED_PHP_VERSION', '5.3' );
// because of get_called_class().
define( 'WOOCOMMERCE_MULTILEVEL_REFERRAL_REQUIRED_WP_VERSION', '3.1' );
// because of esc_textarea().
define( 'WOOCOMMERCE_MULTILEVEL_REFERRAL_VER', '2.28.2' );
define( 'WOOCOMMERCE_MULTILEVEL_REFERRAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOOCOMMERCE_MULTILEVEL_REFERRAL_URL', plugin_dir_url( __FILE__ ) );
/* High-performance order storage compatible */
add_action( 'before_woocommerce_init', 'woocommerce_multilevel_referral_high_performance_compatible' );
/**
 * High-performance order storage compatible.
 */
function woocommerce_multilevel_referral_high_performance_compatible() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

if ( function_exists( 'woocommerce_multilevel_referral_fs' ) ) {
	woocommerce_multilevel_referral_fs()->set_basename( false, __FILE__ );
} else {
	// DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
	if ( ! function_exists( 'woocommerce_multilevel_referral_fs' ) ) {
		/**
		 * Create a helper function for easy SDK access.
		 *
		 * @return Freemius Freemius instance.
		 */
		function woocommerce_multilevel_referral_fs() {
			global $woocommerce_multilevel_referral_fs;
			if ( ! isset( $woocommerce_multilevel_referral_fs ) ) {
				// Include Freemius SDK.
				require_once __DIR__ . '/vendor/freemius/start.php';
				$woocommerce_multilevel_referral_fs = fs_dynamic_init(
					array(
						'id'               => '12292',
						'slug'             => 'multilevel-referral-plugin-for-woocommerce',
						'type'             => 'plugin',
						'public_key'       => 'pk_76635ea6cec771cc09d9d49823c0d',
						'is_premium'       => false,
						'premium_suffix'   => 'Pro',
						'has_addons'       => true,
						'has_paid_plans'   => true,
						'is_org_compliant' => true,
						'trial'            => array(
							'days'               => 3,
							'is_require_payment' => false,
						),
						'has_affiliation'  => 'all',
						'menu'             => array(
							'slug'    => 'wc_referral',
							'support' => false,
							'parent'  => array(
								'slug' => 'wc_referral',
							),
						),
						'is_live'          => true,
					)
				);
			}
			return $woocommerce_multilevel_referral_fs;
		}

		// Init Freemius.
		woocommerce_multilevel_referral_fs();
		// Signal that SDK was initiated.
		do_action( 'woocommerce_multilevel_referral_fs_loaded' );
	}
	// plugin's main file logic ...
	add_action( 'init', 'woocommerce_multilevel_referral_plugin_init' );
	if ( ! function_exists( 'woocommerce_multilevel_referral_plugin_init' ) ) {
		/**
		 * Initialize plugin.
		 */
		function woocommerce_multilevel_referral_plugin_init() {
			$locale = ( is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale() );
			$locale = apply_filters( 'woocommerce_multilevel_referral_plugin_locale', $locale, 'multilevel-referral-plugin-for-woocommerce' );
			unload_textdomain( 'multilevel-referral-plugin-for-woocommerce' );
			load_textdomain( 'multilevel-referral-plugin-for-woocommerce', WOOCOMMERCE_MULTILEVEL_REFERRAL_DIR . 'languages/multilevel-referral-plugin-for-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'multilevel-referral-plugin-for-woocommerce', false, WOOCOMMERCE_MULTILEVEL_REFERRAL_DIR . 'languages' );
		}

	}
	/**
	 * Checks if the system requirements are met.
	 *
	 * @return bool True if system requirements are met, false if not.
	 */
	if ( ! function_exists( 'woocommerce_multilevel_referral_requirements_check' ) ) {
		/**
		 * Check system requirements.
		 *
		 * @return bool True if requirements are met, false otherwise.
		 */
		function woocommerce_multilevel_referral_requirements_check() {
			global $wp_version;
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
			// to get is_plugin_active() early.
			if ( version_compare( PHP_VERSION, WOOCOMMERCE_MULTILEVEL_REFERRAL_REQUIRED_PHP_VERSION, '<' ) ) {
				return false;
			}
			if ( version_compare( $wp_version, WOOCOMMERCE_MULTILEVEL_REFERRAL_REQUIRED_WP_VERSION, '<' ) ) {
				return false;
			}
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				return false;
			}
			return true;
		}

	}
	/**
	 * Prints an error that the system requirements weren't met.
	 */
	if ( ! function_exists( 'woocommerce_multilevel_referral_requirements_error' ) ) {
		/**
		 * Display requirements error.
		 */
		function woocommerce_multilevel_referral_requirements_error() {
			global $wp_version;
			require_once __DIR__ . '/views/requirements-error.php';
		}

	}
	/**
	 * Prints an error that the system requirements weren't met.
	 */
	if ( ! function_exists( 'woocommerce_multilevel_referral_requirements_library' ) ) {
		/**
		 * Display requirements library error.
		 */
		function woocommerce_multilevel_referral_requirements_library() {
			global $wp_version;
			require_once __DIR__ . '/views/requirements-lib-error.php';
		}

	}
	/*
	 * Check requirements and load main class
	 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
	 */
	if ( woocommerce_multilevel_referral_requirements_check() ) {
		if ( ! function_exists( 'imagecreatefrompng' ) ) {
			add_action( 'admin_notices', 'woocommerce_multilevel_referral_requirements_library' );
		}
		require_once __DIR__ . '/classes/class-woocommerce-multilevel-referral-module.php';
		require_once __DIR__ . '/includes/functions.php';
		if ( is_admin() ) {
			require_once __DIR__ . '/classes/admin/class-woocommerce-multilevel-referral-user-table.php';
			require_once __DIR__ . '/classes/admin/class-woocommerce-multilevel-referral-credit-log.php';
			require_once __DIR__ . '/classes/admin/class-woocommerce-multilevel-referral-order-credit-list.php';
			require_once __DIR__ . '/classes/admin/class-woocommerce-multilevel-referral-general-settings.php';
			require_once __DIR__ . '/classes/admin/class-woocommerce-multilevel-referral-user.php';
			require_once __DIR__ . '/classes/admin/class-woocommerce-multilevel-referral-settings.php';
			require_once __DIR__ . '/classes/admin/class-woocommerce-multilevel-referral-product-meta-box.php';
		}
		require_once __DIR__ . '/classes/class-woocommerce-multilevel-referral.php';
		require_once __DIR__ . '/classes/class-woocommerce-multilevel-referral-program.php';
		require_once __DIR__ . '/classes/class-woocommerce-multilevel-referral-users.php';
		require_once __DIR__ . '/classes/class-woocommerce-multilevel-referral-order.php';
		require_once __DIR__ . '/classes/class-woocommerce-multilevel-referral-compatibility.php';
		if ( class_exists( 'WooCommerce_Multilevel_Referral' ) ) {
			$GLOBALS['woocommerce_multilevel_referral_instance'] = WooCommerce_Multilevel_Referral::get_instance();
			register_activation_hook( __FILE__, array( $GLOBALS['woocommerce_multilevel_referral_instance'], 'activate' ) );
			register_deactivation_hook( __FILE__, array( $GLOBALS['woocommerce_multilevel_referral_instance'], 'deactivate' ) );
		}
	} else {
		add_action( 'admin_notices', 'woocommerce_multilevel_referral_requirements_error' );
	}
}
/**
 * Programmatic Migration from Old Slugs.
 */
function woocommerce_multilevel_referral_migrate_from_old_version() {
	// List of known old plugin main file paths.
	$old_plugins = array( 'multilevel-referral-plugin-for-woocommerce-premium/woocommerce-multilevel-commision.php', 'multilevel-referral-plugin-for-woocommerce/woocommerce-multilevel-commision.php' );
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	foreach ( $old_plugins as $plugin_path ) {
		if ( is_plugin_active( $plugin_path ) ) {
			deactivate_plugins( $plugin_path );
			// Set a flag to show a notice once.
			add_action(
				'admin_notices',
				function () use ( $plugin_path ) {
					echo '<div class="notice notice-warning is-dismissible"><p>';
					printf(
					/* translators: %s: plugin directory path */
						esc_html__( 'The old version of the plugin (%s) has been deactivated to avoid conflicts with the newly renamed version.', 'multilevel-referral-plugin-for-woocommerce' ),
						'<code>' . esc_html( $plugin_path ) . '</code>'
					);
					echo '</p></div>';
				}
			);
		}
	}
}

add_action( 'admin_init', 'woocommerce_multilevel_referral_migrate_from_old_version' );
