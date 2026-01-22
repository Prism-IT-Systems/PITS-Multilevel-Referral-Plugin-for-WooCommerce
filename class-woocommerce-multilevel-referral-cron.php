<?php

/**
 * Cron Job Handler.
 *
 * Handles scheduled tasks for the Multilevel Referral Plugin for WooCommerce,
 * including reminder emails and automatic credit redemption.
 *
 * @package     Multilevel_Referral_Plugin_For_WooCommerce
 * @subpackage  Cron
 * @since       1.0.0
 */
defined( 'ABSPATH' ) || exit;
require_once '../../../wp-load.php';
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Cron' ) ) {
	/**
	 * Cron Job Handler Class.
	 */
	class WooCommerce_Multilevel_Referral_Cron {
		/**
		 * Constructor.
		 */
		public function __construct() {
		}
	}

	new WooCommerce_Multilevel_Referral_Cron();
}
