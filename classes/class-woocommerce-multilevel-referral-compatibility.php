<?php
/**
 * Compatibility Class
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Compatibility' ) ) {
	/**
	 * Front controller class
	 */
	class WooCommerce_Multilevel_Referral_Compatibility {
		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( 'wolmart_account_dashboard_items', array( $this, 'wolmart_account_add_navigation_referral' ) );
		}
		/**
		 *  17-09-2021
		 *
		 *  Add referral menu tabs user account ( Wolmart theme conflict my-account/navigation.php )
		 *
		 * @param array $account_arr Account array.
		 *
		 * @return array Modified account array.
		 */
		public function wolmart_account_add_navigation_referral( $account_arr ) {
			$account_arr['referral']     = array( 'Referral', 'referral' );
			$account_arr['my-affliates'] = array( 'My Affiliates', 'my-affliates' );
			return $account_arr;
		}
	}
	new WooCommerce_Multilevel_Referral_Compatibility();
}
