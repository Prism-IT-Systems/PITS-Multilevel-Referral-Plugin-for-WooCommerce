<?php
/**
 * Admin Users Class
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_User' ) ) :
	/**
	 * WooCommerce_Multilevel_Referral_User.
	 */
	class WooCommerce_Multilevel_Referral_User extends WooCommerce_Multilevel_Referral_Module {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->register_hook_callbacks();
		}
		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'show_user_profile', __CLASS__ . '::referal_fields' );
			add_action( 'edit_user_profile', __CLASS__ . '::referal_fields' );
			// Update user level credit.
			add_action( 'profile_update', __CLASS__ . '::woocommerce_multilevel_referral_update_user_level_credit_on_profile_update', 10, 2 );
			add_action( 'user_profile_update_errors', __CLASS__ . '::woocommerce_multilevel_referral_check_fields', 10, 3 );
		}
		/**
		 * Check referral fields on user profile update.
		 *
		 * @param WP_Error $errors WP_Error object.
		 * @param bool     $update Whether this is a user update.
		 * @param WP_User  $user User object.
		 */
		public static function woocommerce_multilevel_referral_check_fields( $errors, $update, $user ) {
			global $wpdb;
			if ( ! isset( $_POST['woocommerce_multilevel_referral_userform_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_userform_nonce'] ) ), 'woocommerce_multilevel_referral_userform_action' ) ) {
				return;
			}
			$user_id = $user->ID;
			if ( isset( $_POST['referal_code'] ) ) {
				$table_name    = $wpdb->prefix . 'referal_users';
				$referral_code = sanitize_text_field( wp_unslash( $_POST['referal_code'] ) );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$exist_referral_code = $wpdb->get_var( $wpdb->prepare( 'SELECT user_id FROM %s WHERE referral_code = %s', $table_name, $referral_code ) );
				if ( $exist_referral_code ) {
					if ( $exist_referral_code !== $user_id ) {
						$errors->add( 'demo_error', __( 'This referral code is already used.', 'multilevel-referral-plugin-for-woocommerce' ) );
					}
				} else {
					$data = array(
						'referral_code' => sanitize_text_field( wp_unslash( $_POST['referal_code'] ) ),
					);
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->update(
						$table_name,
						$data,
						array(
							'user_id' => $user_id,
						)
					);
				}
			}
		}
		/**
		 * Display referral fields on user profile.
		 *
		 * @param WP_User $user User object.
		 */
		public static function referal_fields( $user ) {
			$obj_referal_users = WooCommerce_Multilevel_Referral_Users::get_instance();
			$user              = $obj_referal_users->get_referral_user( $user->ID );
			echo wp_kses( self::render_template( 'admin/user.php', array( 'user' => $user ) ), woocommerce_multilevel_referral_get_wp_fs_allow_html() );
		}
		/**
		 * Update user level credit on profile update.
		 *
		 * @param int      $user_id User ID.
		 * @param stdClass $old_user_data Old user data.
		 */
		public static function woocommerce_multilevel_referral_update_user_level_credit_on_profile_update( $user_id, $old_user_data ) {
			// Check if the user ID is valid.
			if ( ! $user_id ) {
				return;
			}
			if ( ! isset( $_POST['woocommerce_multilevel_referral_userform_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_userform_nonce'] ) ), 'woocommerce_multilevel_referral_userform_action' ) ) {
				return;
			}
			// Get user data.
			// $user_info = get_userdata( $user_id ).
			// Update user meta based on the updated user data.
			if ( isset( $_POST['woocommerce_multilevel_referral_enable_user_level'] ) ) {
				update_user_meta( $user_id, 'woocommerce-multilevel-referral-user-level-enable', sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_enable_user_level'] ) ) );
			} else {
				update_user_meta( $user_id, 'woocommerce-multilevel-referral-user-level-enable', 'off' );
			}
			if ( isset( $_POST['woocommerce-multilevel-referral-level-c'] ) ) {
				update_user_meta( $user_id, 'woocommerce-multilevel-referral-level-c', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-level-c'] ) ) );
			}
			if ( isset( $_POST['woocommerce-multilevel-referral-level-credit'] ) ) {
				update_user_meta( $user_id, 'woocommerce-multilevel-referral-level-credit', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-level-credit'] ) ) );
			}
		}
		/**
		 * Get list of users count.
		 *
		 * @return int User count.
		 */
		public function record_count() {
			global $wpdb;
			$sql = "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = 'join_date' AND meta_value !== ''";
		  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = 'join_date' AND meta_value !== ''" );
		}
		/**
		 * Activate plugin.
		 *
		 * @param bool $network_wide Whether network-wide activation.
		 */
		public function activate( $network_wide ) {
		}
		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller           */
		public function deactivate() {
		}
		/**
		 * Initializes variables
		 *
		 * @mvc Controller           */
		public function init() {
		}
		/**
		 * Checks if the plugin was recently updated and upgrades if necessary
		 *
		 * @mvc Controller
		 *
		 * @param string $db_version             */
		public function upgrade( $db_version = 0 ) {
		}
		/**
		 * Checks that the object is in a correct state.
		 *
		 * @mvc Model
		 *
		 * @param string $valid An individual property to check, or 'all' to check all of them.
		 *
		 * @return bool
		 */
		public function is_valid( $valid = 'all' ) {
			return true;
		}
	}
endif;
