<?php
/**
 * Admin Referral Settings
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 * @since   2.28.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Settings' ) ) {
	/**
	 * WooCommerce Multilevel Referral Settings Class.
	 *
	 * @package Multilevel_Referral_Plugin_For_WooCommerce
	 * @since   2.28.1
	 */
	class WooCommerce_Multilevel_Referral_Settings extends WooCommerce_Multilevel_Referral_Module {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->register_hook_callbacks();
		}

		/**
		 * Register callbacks for actions and filters.
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'admin_menu', __CLASS__ . '::woocommerce_multilevel_referral_add_referal_menu_callbacker', 99 );
			add_action( 'all_admin_notices', __CLASS__ . '::woocommerce_multilevel_referral_add_referal_header_callbackr', 99 );
			add_action( 'pre_get_posts', __CLASS__ . '::woocommerce_multilevel_referral_change_banner_display_order' );
			add_filter( 'admin_body_class', array( $this, 'woocommerce_multilevel_referral_add_banner_body_class' ) );
			add_action( 'admin_footer', array( $this, 'close_banner_div' ) );
		}

		/**
		 * Add banner body class.
		 *
		 * @param string $classes Body classes.
		 * @return string
		 */
		public function woocommerce_multilevel_referral_add_banner_body_class( $classes ) {
			global $post;
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( ( isset( $url_data['post_type'] ) && 'wc_ml_ref_banner' === sanitize_text_field( wp_unslash( $url_data['post_type'] ) ) ) || ( isset( $post->post_type ) && 'wc_ml_ref_banner' === $post->post_type ) ) {
				$classes = ' toplevel_page_wc_referral ';
			}
			$class_name = 'woocommerce_multilevel_referral_free_plan';
			$classes   .= " {$class_name}";
			return $classes;
		}

		/**
		 * Change banner display order.
		 *
		 * @param WP_Query $query Query object.
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_change_banner_display_order( $query ) {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( isset( $url_data['post_type'] ) && 'wc_ml_ref_banner' === $url_data['post_type'] && $query->is_main_query() ) {
				$args = array(
					'title' => 'ASC',
				);
				$query->set( 'orderby', $args );
			}
		}

		/**
		 * Add referral menu callback.
		 *
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_add_referal_menu_callbacker() {
			$icon = WOOCOMMERCE_MULTILEVEL_REFERRAL_URL . 'images/woocommerce_multilevel_referral_icon.png';
			add_menu_page(
				__( 'Referral', 'multilevel-referral-plugin-for-woocommerce' ),
				__( 'Referral', 'multilevel-referral-plugin-for-woocommerce' ),
				'manage_options',
				'wc_referral',
				__CLASS__ . '::referal_program',
				$icon,
				55.6
			);
		}

		/**
		 * Add referral header callback.
		 *
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_add_referal_header_callbackr() {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( ! isset( $url_data['post_type'] ) || 'wc_ml_ref_banner' !== $url_data['post_type'] ) {
				return;
			}
			$obj_referal_users                       = WooCommerce_Multilevel_Referral_Users::get_instance();
			$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
			$users                                   = count_users();
			$total_referrals                         = $obj_referal_users->record_count();
			$total_credits                           = $woocommerce_multilevel_referral_program->total_statistic( 'credits' );
			$total_redeems                           = $woocommerce_multilevel_referral_program->total_statistic( 'redeems' );
			$data                                    = array(
				'total_users'     => $users['total_users'],
				'total_referrals' => $total_referrals,
				'total_credites'  => $total_credits,
				'total_redeems'   => $total_redeems,
			);
			$url_data['tab']                         = 'banners';
			echo wp_kses_post(
				self::render_template(
					'admin/referral-header.php',
					array(
						'woocommerce_multilevel_referral_data' => $data,
					)
				)
			);
			print '<div class="woocommerce-multilevel-referral_referral_table_shadow woocommerce-multilevel-referral_banner_section">';
		}

		/**
		 * Close banner div.
		 *
		 * @return void
		 */
		public function close_banner_div() {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( ! isset( $url_data['post_type'] ) || 'wc_ml_ref_banner' !== sanitize_text_field( wp_unslash( $url_data['post_type'] ) ) ) {
				return;
			}
			print '</div>';
		}

		/**
		 * Referral program.
		 *
		 * @return void
		 */
		public static function referal_program() {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			$template = ( isset( $url_data['tab'] ) ? sanitize_text_field( wp_unslash( $url_data['tab'] ) ) : 'referral-users' );
			$is_pro   = false;
			if ( ( 'advsettings' === $template || 'addons' === $template ) && ! $is_pro ) {
				return;
			}
			$option = 'per_page';
			$args   = array(
				'label'   => 'Orders',
				'default' => 5,
				'option'  => 'orders_per_page',
			);
			add_screen_option( $option, $args );
			self::woocommerce_multilevel_referral_save_referal_templates_callback();
			$obj_referal_users                       = WooCommerce_Multilevel_Referral_Users::get_instance();
			$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
			$users                                   = count_users();
			$total_referrals                         = $obj_referal_users->record_count();
			$total_credits                           = $woocommerce_multilevel_referral_program->total_statistic( 'credits' );
			$total_redeems                           = $woocommerce_multilevel_referral_program->total_statistic( 'redeems' );
			$data                                    = array(
				'total_users'          => $users['total_users'],
				'total_referrals'      => $total_referrals,
				'total_credites'       => $total_credits,
				'total_redeems'        => $total_redeems,
				'advance_setting_link' => '',
				'addons_link'          => '',
			);
			echo wp_kses_post(
				self::render_template(
					'admin/referral-header.php',
					array(
						'woocommerce_multilevel_referral_data' => $data,
					)
				)
			);
			print '<div class="woocommerce-multilevel-referral_referral_table_shadow">';
			$template_file = 'admin/' . strtolower( $template ) . '.php';
			echo wp_kses(
				self::render_template(
					$template_file,
					array(
						'woocommerce_multilevel_referral_data' => $data,
					)
				),
				woocommerce_multilevel_referral_get_wp_fs_allow_html()
			);
			print '</div>';
		}

		/**
		 * Save email templates.
		 *
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_save_referal_templates_callback() {
			// Fail early if nonce is missing or invalid.
			if ( isset( $_POST['woocommerce_multilevel_referral_template_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_template_nonce'] ) ), 'woocommerce_multilevel_referral_template_action' ) ) {
				try {
					if ( isset( $_POST['save_template'] ) ) {
						update_option( 'joining_mail_template', ( isset( $_POST['joining_mail_template'] ) ? sanitize_textarea_field( wp_unslash( $_POST['joining_mail_template'] ) ) : '' ) );
						update_option( 'joining_mail_subject', ( isset( $_POST['joining_mail_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['joining_mail_subject'] ) ) : '' ) );
						update_option( 'joining_mail_heading', ( isset( $_POST['joining_mail_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['joining_mail_heading'] ) ) : '' ) );
						update_option( 'referral_user_template', ( isset( $_POST['referral_user_template'] ) ? sanitize_textarea_field( wp_unslash( $_POST['referral_user_template'] ) ) : '' ) );
						update_option( 'referral_user_subject', ( isset( $_POST['referral_user_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['referral_user_subject'] ) ) : '' ) );
						update_option( 'referral_user_heading', ( isset( $_POST['referral_user_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['referral_user_heading'] ) ) : '' ) );
						update_option( 'expire_notification_template', ( isset( $_POST['expire_notification_template'] ) ? sanitize_textarea_field( wp_unslash( $_POST['expire_notification_template'] ) ) : '' ) );
						update_option( 'expire_notification_subject', ( isset( $_POST['expire_notification_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['expire_notification_subject'] ) ) : '' ) );
						update_option( 'expire_notification_heading', ( isset( $_POST['expire_notification_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['expire_notification_heading'] ) ) : '' ) );
						do_action( 'woocommerce_multilevel_referral_save_email_templates' );
					}
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Activate plugin.
		 *
		 * @param bool $network_wide Network wide activation.
		 * @return void
		 */
		public function activate( $network_wide ) {
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		public function deactivate() {
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {
		}

		/**
		 * Checks if the plugin was recently updated and upgrades if necessary.
		 *
		 * @mvc Controller
		 *
		 * @param string $db_version Database version.
		 * @return void
		 */
		public function upgrade( $db_version = 0 ) {
		}

		/**
		 * Checks that the object is in a correct state.
		 *
		 * @mvc Model
		 *
		 * @param string $valid An individual property to check, or 'all' to check all of them.
		 * @return bool True if valid.
		 */
		public function is_valid( $valid = 'all' ) {
			return true;
		}
	}

	new WooCommerce_Multilevel_Referral_Settings();
}
