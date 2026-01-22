<?php
/**
 * Product Meta Box
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 * @since   2.28.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Register product meta box using a class.
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 * @since   2.28.1
 */
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Product_Meta_Box' ) ) {
	/**
	 * WooCommerce Multilevel Referral Product Meta Box Class
	 *
	 * @package Multilevel_Referral_Plugin_For_WooCommerce
	 * @since   2.28.1
	 */
	class WooCommerce_Multilevel_Referral_Product_Meta_Box {
		/**
		 * Constructor.
		 */
		public function __construct() {
		}
	}

	new WooCommerce_Multilevel_Referral_Product_Meta_Box();
}
