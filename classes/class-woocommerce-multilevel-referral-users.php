<?php
/**
 * Referral Users Class
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Users' ) ) {
	/**
	 * Main / front controller class
	 */
	class WooCommerce_Multilevel_Referral_Users extends WooCommerce_Multilevel_Referral {
		/**
		 * Table name for referral users.
		 *
		 * @var string
		 */
		public $table_name;

		/**
		 * Array of followers count.
		 *
		 * @var array
		 */
		public $arr_followers_count;

		/**
		 * Array of update followers.
		 *
		 * @var array
		 */
		public $arr_update_followers;

		/**
		 * All visited users array.
		 *
		 * @var array
		 */
		protected $all_visited_users = array();

		/**
		 * Followers visited array.
		 *
		 * @var array
		 */
		private $followers_visited = array();

		/**
		 * Purchases visited array.
		 *
		 * @var array
		 */
		private $purchases_visited = array();

		/**
		 * Affiliate visited array.
		 *
		 * @var array
		 */
		private $affiliate_visited = array();

		/**
		 * Binary visited array.
		 *
		 * @var array
		 */
		private $binary_visited = array();

		/**
		 * Level visited array.
		 *
		 * @var array
		 */
		private $level_visited = array();

		/**
		 * Update followers visited array.
		 *
		 * @var array
		 */
		private $update_followers_visited = array();

		/**
		 * Instance of the class.
		 *
		 * @var WooCommerce_Multilevel_Referral_Users|null
		 */
		private static $instance = null;

		/**
		 * Get instance of the class.
		 *
		 * @return WooCommerce_Multilevel_Referral_Users
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->table_name           = $wpdb->prefix . 'referal_users';
			$this->arr_followers_count  = array();
			$this->arr_update_followers = array();
			$this->register_hook_callbacks();
		}

		/**
		 * Get the uploads directory path for this plugin.
		 *
		 * @return array Array with 'path' and 'url' keys.
		 */
		private function get_plugin_uploads_dir() {
			$upload_dir        = wp_upload_dir();
			$plugin_slug       = 'multilevel-referral-plugin-for-woocommerce';
			$plugin_upload_dir = array(
				'path' => $upload_dir['basedir'] . '/' . $plugin_slug . '/',
				'url'  => $upload_dir['baseurl'] . '/' . $plugin_slug . '/',
			);
			// Create directory if it doesn't exist.
			if ( ! file_exists( $plugin_upload_dir['path'] ) ) {
				wp_mkdir_p( $plugin_upload_dir['path'] );
			}
			return $plugin_upload_dir;
		}

		/**
		 * Register hook callbacks.
		 */
		public function register_hook_callbacks() {
			add_action( 'init', array( $this, 'join_referral_program' ) );
			add_action( 'init', array( $this, 'send_invitation' ) );
			add_action( 'woocommerce_register_form_start', array( $this, 'referral_register_start_fields' ) );
			add_action( 'woocommerce_register_form', array( $this, 'referral_register_fields' ) );
			add_action(
				'woocommerce_register_post',
				array( $this, 'referral_registration_validation' ),
				1,
				3
			);
			add_action( 'woocommerce_created_customer', array( $this, 'referral_customer_save_data' ), 10 );
			add_action( 'delete_user', array( $this, 'delete_user_callback' ) );
			add_shortcode( 'referral_link', array( $this, 'referral_link_callback' ) );
			add_shortcode( 'woocommerce_multilevel_referral_my_affiliate_tab', array( $this, 'woocommerce_multilevel_referral_my_affiliates' ) );
			add_shortcode( 'woocommerce_multilevel_referral_my_referral_tab', array( $this, 'woocommerce_multilevel_referral_my_referrals' ) );
			add_shortcode( 'woocommerce_multilevel_referral_stat_blocks', array( $this, 'woocommerce_multilevel_referral_referral_stats_blocks' ) );
			add_shortcode( 'woocommerce_multilevel_referral_invite_friends', array( $this, 'referral_user_invite_friends' ) );
			add_shortcode( 'woocommerce_multilevel_referral_show_credit_info', array( $this, 'referral_user_credit_info' ) );
			add_shortcode( 'woocommerce_multilevel_referral_show_affiliate_info', array( $this, 'woocommerceMultilevelReferralShowMyAffiliates' ) );
			add_action( 'init', array( $this, 'init_hook' ) );
			add_action( 'wp', array( $this, 'fnChangeShareContent' ) );
			add_action( 'wp_head', array( $this, 'fnShareOnWhatsup' ) );
			add_action(
				'profile_update',
				array( $this, 'woocommerce_multilevel_referral_update_user_to_referal' ),
				10,
				2
			);
		}

		/**
		 * Update user to referral program.
		 *
		 * @param int      $user_id User ID.
		 * @param stdClass $old_user_data Old user data.
		 */
		public function woocommerce_multilevel_referral_update_user_to_referal( $user_id, $old_user_data ) {
			if ( ! isset( $_POST['woocommerce_multilevel_referral_userform_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_userform_nonce'] ) ), 'woocommerce_multilevel_referral_userform_action' ) ) {
				return;
			}
			if ( isset( $_POST['add_user_to_referal'] ) ) {
				$parent_id     = ( isset( $_POST['parent_userid'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_userid'] ) ) : 0 );
				$benefit       = 0;
				$referral_code = $this->referral_code( $user_id );
				$this->insert(
					array(
						'user_id'          => $user_id,
						'referral_parent'  => $parent_id,
						'active'           => 1,
						'referral_code'    => $referral_code,
						'referral_email'   => ( isset( $_POST['referral_email'] ) ? sanitize_text_field( wp_unslash( $_POST['referral_email'] ) ) : '' ),
						'referal_benefits' => $benefit,
					)
				);
				update_user_meta( $user_id, 'total_referrals', 0 );
				$this->fnUpdateFollowersCount( $user_id );
			}
		}

		/**
		 * Share on WhatsApp.
		 */
		public function fnShareOnWhatsup() {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( isset( $url_data['share'] ) && md5( 'whatsup' ) === $url_data['share'] ) {
				$my_account_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
				$my_account_link = add_query_arg( 'ru', ( isset( $url_data['ru'] ) ? sanitize_text_field( wp_unslash( $url_data['ru'] ) ) : '' ), $my_account_link );
				$output          = '<meta property="og:url" content="' . $my_account_link . '" >';
				$output         .= '<meta property="og:title" content="' . ( ( isset( $url_data['title'] ) ? sanitize_text_field( wp_unslash( $url_data['title'] ) ) : '' ) ) . '" >';
				$output         .= '<meta property="og:description" content="' . ( ( isset( $url_data['content'] ) ? sanitize_text_field( wp_unslash( $url_data['content'] ) ) : '' ) ) . '" >';
				$output         .= '<meta property="og:image" content="' . ( ( isset( $url_data['image'] ) ? sanitize_text_field( wp_unslash( $url_data['image'] ) ) : '' ) ) . '" >';
				$output         .= '<meta property="og:image:width" content="500" >';
				$output         .= '<meta property="og:image:height" content="300" >';
				echo wp_kses_post( $output );
			}
		}

		/**
		 * Delete user from referral program.
		 *
		 * @param int $customer_id Deleted user id.
		 *
		 * @return void
		 */
		public function delete_user_callback( $customer_id ) {
			global $wpdb;
			$this->change_referral_user( $customer_id );
			$this->delete( $customer_id );
			$parent_user_id = get_user_meta( $customer_id, 'meta_value', true );
			$this->fnUpdateFollowersCount( $parent_user_id );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET meta_value = %s WHERE meta_key = 'referral_parent' AND user_id IN ( SELECT * from ( SELECT user_id FROM {$wpdb->usermeta} WHERE `meta_key` = 'referral_parent' AND `meta_value` = %s ) as a)", $parent_user_id, $customer_id ) );
		}

		/**
		 * Call of referral_link shortcode.
		 *
		 * @param array $atts Attributes of shortcode.
		 *
		 * @return string Link of referral program.
		 */
		public function referral_link_callback( $atts ) {
			global $woocommerce_multilevel_referral_customer_id, $referral_code;
			$pull_quote_atts = shortcode_atts(
				array(
					'text' => 'Click here',
				),
				$atts
			);
			$link            = add_query_arg( 'ru', $referral_code, get_the_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
			return '<a href="' . $link . '" target="_blank">' . $pull_quote_atts['text'] . '</a>';
		}

		/**
		 * Create table.
		 */
		public function create_table() {
			global $wpdb;
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'referal_users' ) ) !== $this->table_name ) {
				$sql = 'CREATE TABLE ' . $this->table_name . ' (
					id int(11) NOT NULL AUTO_INCREMENT,
					user_id int(11)  NOT NULL,
					referral_parent  int(11)  NOT NULL,
					active  TINYINT(1) NOT NULL DEFAULT 1,
					referral_code VARCHAR(5) NOT NULL,
					referal_benefits  TINYINT(1) NOT NULL DEFAULT 0,
					referral_email VARCHAR(50) NOT NULL,
					join_date  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
					update_date  TIMESTAMP NOT NULL DEFAULT 0,
					PRIMARY KEY  (id),
					INDEX `referral_users` (`referral_parent`, `user_id`)
					);';
				// we do not execute sql directly.
				// we are calling dbDelta which cant migrate database.
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );
                // phpcs:enable
			}
		}

		/**
		 * Insert record.
		 *
		 * @param array $data Data to insert.
		 */
		public function insert( $data ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching.
			$wpdb->insert( $this->table_name, $data );
		}

		/**
		 * Delete user from referral program.
		 *
		 * @param int $user_id User ID.
		 */
		public function delete( $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete(
				$this->table_name,
				array(
					'user_id' => $user_id,
				)
			);
		}

		/**
		 * Update user referral data.
		 *
		 * @param int $user_id User ID.
		 * @param int $referral_parent Referral parent ID.
		 * @param int $status Status (default: 1).
		 */
		public function update( $user_id, $referral_parent, $status = 1 ) {
			global $wpdb;
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$this->table_name,
				array(
					'active'          => $status,
					'update_date'     => gmdate( 'Y-m-d H:i:s' ),
					'referral_parent' => $referral_parent,
				),
				array(
					'user_id' => $user_id,
				)
			);
            // phpcs:enable
		}

		/**
		 * Update all user referral data.
		 *
		 * @param array $data Data to update.
		 * @param int   $user_id User ID.
		 */
		public function updateAll( $data, $user_id ) {
			global $wpdb;
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$this->table_name,
				$data,
				array(
					'user_id' => $user_id,
				)
			);
            // phpcs:enable
		}

		/**
		 * Update followers count.
		 *
		 * @param int  $customer_id Customer ID.
		 * @param bool $is_recursive Is recursive call.
		 */
		public function fnUpdateFollowersCount( $customer_id, $is_recursive = false ) {
			if ( empty( $customer_id ) ) {
				return;
			}
			if ( ! $is_recursive ) {
				$this->update_followers_visited = array();
			}
			if ( in_array( $customer_id, $this->update_followers_visited, true ) ) {
				return;
				// Prevent infinite loop.
			}
			$this->update_followers_visited[] = $customer_id;
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT referral_parent FROM {$wpdb->prefix}referal_users WHERE user_id = %d", $customer_id ) );
			if ( is_array( $rows ) && count( $rows ) > 0 ) {
				foreach ( $rows as $row ) {
					if ( isset( $row->referral_parent ) && '' !== $row->referral_parent && 0 !== $row->referral_parent ) {
						$cnt_followers = $this->fnGetFollowersCount( $row->referral_parent );
						update_user_meta( $row->referral_parent, 'total_referrals', $cnt_followers );
						$this->fnUpdateFollowersCount( $row->referral_parent, true );
					}
				}
			}
		}

		/**
		 * Read or write contents of file.
		 *
		 * @param string $mode Mode: 'read' or 'write'.
		 */
		public function fnReadWriteContentsOfFile( $mode = 'read' ) {
			$upload_dir = $this->get_plugin_uploads_dir();
			$file_path  = $upload_dir['path'] . 'referrals.tmp';
			if ( 'read' === $mode ) {
				if ( file_exists( $file_path ) ) {
					$str = wp_remote_get( $file_path );
					if ( ! is_wp_error( $str ) && '' !== $str['body'] ) {
						$this->arr_followers_count = json_decode( $str['body'], true );
						unset( $str );
					}
				}
			} else {
				// Ensure directory exists.
				if ( ! file_exists( $upload_dir['path'] ) ) {
					wp_mkdir_p( $upload_dir['path'] );
				}
				global $wp_filesystem;
				if ( empty( $wp_filesystem ) ) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
				}
				$wp_filesystem->put_contents( $file_path, wp_json_encode( $this->arr_followers_count ) );
			}
		}

		/**
		 * Get referrals IDs by level.
		 *
		 * @param int $parent_id Parent ID.
		 *
		 * @return array|int Referrals or 0.
		 */
		public function fnGetReferralsIdsByLevel( $parent_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}referal_users WHERE referral_parent=%d", $parent_id ) );
			if ( is_array( $rows ) && count( $rows ) > 0 ) {
				return $rows;
			}
			return 0;
		}

		/**
		 * Get followers count.
		 *
		 * @param int  $parent_id Parent ID.
		 * @param bool $is_recursive Is recursive call.
		 *
		 * @return int Followers count.
		 */
		public function fnGetFollowersCount( $parent_id, $is_recursive = false ) {
			if ( empty( $parent_id ) ) {
				return 0;
			}
			if ( ! $is_recursive ) {
				$this->followers_visited = array();
			}
			if ( in_array( $parent_id, $this->followers_visited, true ) ) {
				return 0;
				// Prevent infinite loop and double counting.
			}
			$this->followers_visited[] = $parent_id;
			global $wpdb, $woocommerce_multilevel_referral_cache;
			if ( isset( $woocommerce_multilevel_referral_cache['followers_count'] ) && isset( $woocommerce_multilevel_referral_cache['followers_count'][ $parent_id ] ) ) {
				return $woocommerce_multilevel_referral_cache['followers_count'][ $parent_id ];
			}
			if ( ! isset( $woocommerce_multilevel_referral_cache['followers_count'] ) ) {
				$woocommerce_multilevel_referral_cache['followers_count'] = array();
			}
			if ( isset( $this->arr_followers_count[ $parent_id ] ) ) {
				$woocommerce_multilevel_referral_cache['followers_count'][ $parent_id ] = $this->arr_followers_count[ $parent_id ];
				return $this->arr_followers_count[ $parent_id ];
			} else {
				$cnt_followers = 0;
				$sql           = 'SELECT user_id FROM ' . $this->table_name . ' WHERE referral_parent=' . $parent_id;
				if ( isset( $woocommerce_multilevel_referral_cache['referral_user_list'] ) && isset( $woocommerce_multilevel_referral_cache['referral_user_list'][ $parent_id ] ) ) {
					$rows = $woocommerce_multilevel_referral_cache['referral_user_list'][ $parent_id ];
				} else {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$rows = $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}referal_users WHERE referral_parent=%d", $parent_id ) );
					$woocommerce_multilevel_referral_cache['referral_user_list'][ $parent_id ] = $rows;
				}
				if ( is_array( $rows ) && count( $rows ) > 0 ) {
					$cnt_followers += count( $rows );
					foreach ( $rows as $row ) {
						$cnt_followers += $this->fnGetFollowersCount( $row->user_id, true );
					}
				}
				unset( $rows );
				unset( $sql );
				update_user_meta( $parent_id, 'total_referrals', $cnt_followers );
				$this->arr_followers_count[ $parent_id ]                                = $cnt_followers;
				$woocommerce_multilevel_referral_cache['followers_count'][ $parent_id ] = $cnt_followers;
				return $cnt_followers;
			}
		}

		/**
		 * Get customer total spent.
		 *
		 * @param int $customer_id Customer ID.
		 * @param int $month Month.
		 *
		 * @return float|null Total spent.
		 */
		public function woocommerce_multilevel_referral_get_customer_total_spent( $customer_id, $month ) {
			global $wpdb, $woocommerce_multilevel_referral_cache;
			if ( ! isset( $woocommerce_multilevel_referral_cache['customer_total_spent'] ) ) {
				$current_date     = gmdate( 'Y-m-d' );
				$end_date_current = gmdate( "Y-{$month}-01" );
				$end_date_current = gmdate( "Y-{$month}-t", strtotime( $end_date_current ) );
				$end_date_future  = gmdate( "Y-{$month}-t", strtotime( "{$end_date_current} +1 year" ) );
				$end_date_past    = gmdate( "Y-{$month}-t", strtotime( "{$end_date_current} -1 year" ) );
				if ( $current_date > $end_date_past && $end_date_current >= $current_date ) {
					$start_date = gmdate( 'Y-m-d', strtotime( "{$end_date_past} +1 day" ) );
					$end_date   = $end_date_current;
				} elseif ( $current_date > $end_date_current && $end_date_future >= $current_date ) {
					$start_date = gmdate( 'Y-m-d', strtotime( "{$end_date_current} +1 day" ) );
					$end_date   = $end_date_future;
				}
				$woocommerce_multilevel_referral_cache['customer_total_spent'] = array(
					'start_date' => $start_date,
					'end_date'   => $end_date,
				);
			}
			$start_date          = $woocommerce_multilevel_referral_cache['customer_total_spent']['start_date'];
			$end_date            = $woocommerce_multilevel_referral_cache['customer_total_spent']['end_date'];
			$statuses            = wc_get_is_paid_statuses();
			$prepare_values      = array( (int) $customer_id );
			$status_placeholders = '';
			if ( ! empty( $statuses ) ) {
				$status_placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
				$status_values       = array_map(
					static function ( $status ) {
						return 'wc-' . $status;
					},
					$statuses
				);
				$prepare_values      = array_merge( $prepare_values, $status_values );
			}
			$sql              = "\n\t\t\t\tSELECT SUM(meta2.meta_value)\n\t\t\t\tFROM {$wpdb->posts} AS posts\n\t\t\t\tLEFT JOIN {$wpdb->postmeta} AS meta  ON posts.ID = meta.post_id\n\t\t\t\tLEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id\n\t\t\t\tWHERE meta.meta_key = '_customer_user'\n\t\t\t\tAND meta.meta_value = %d\n\t\t\t\tAND posts.post_type = 'shop_order'\n\t\t\t\tAND posts.post_status IN ({$status_placeholders})\n\t\t\t\tAND posts.post_date >= %s\n\t\t\t\tAND posts.post_date <= %s\n\t\t\t\tAND meta2.meta_key = '_order_total'\n\t\t\t";
			$prepare_values[] = $start_date . ' 00:00:00';
			$prepare_values[] = $end_date . ' 23:59:59';
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$prepared_sql = $wpdb->prepare( $sql, ...$prepare_values );
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return $wpdb->get_var( $prepared_sql );
            // phpcs:enable
		}

		/**
		 * Get referral user.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return array Referral user data.
		 */
		public function get_referral_user( $user_id ) {
			global $wpdb;
			if ( ! $user_id ) {
				return array(
					'referral_code'    => '',
					'join_date'        => '',
					'referal_benefits' => '',
				);
			}
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT RU.referral_code, RU.join_date, RU.referal_benefits FROM {$wpdb->prefix}referal_users AS RU WHERE RU.user_id =%d", $user_id ), ARRAY_A );
			return $result;
		}

		/**
		 * Get referral user by field.
		 *
		 * @param string $user_field User field to return.
		 * @param string $where Where field.
		 * @param mixed  $user_id User ID value.
		 *
		 * @return mixed Referral user data.
		 */
		public function referral_user( $user_field, $where, $user_id ) {
			global $wpdb, $woocommerce_multilevel_referral_cache;
			if ( isset( $woocommerce_multilevel_referral_cache['referral_user_query'] ) && isset( $woocommerce_multilevel_referral_cache['referral_user_query'][ $where ] ) && isset( $woocommerce_multilevel_referral_cache['referral_user_query'][ $where ][ $user_field ] ) && isset( $woocommerce_multilevel_referral_cache['referral_user_query'][ $where ][ $user_field ][ $user_id ] ) ) {
				return $woocommerce_multilevel_referral_cache['referral_user_query'][ $where ][ $user_field ][ $user_id ];
			}
			if ( ! isset( $woocommerce_multilevel_referral_cache['referral_user_query'] ) ) {
				$woocommerce_multilevel_referral_cache['referral_user_query'] = array();
			}
			if ( ! isset( $woocommerce_multilevel_referral_cache['referral_user_query'][ $where ] ) ) {
				$woocommerce_multilevel_referral_cache['referral_user_query'][ $where ] = array();
			}
			if ( ! isset( $woocommerce_multilevel_referral_cache['referral_user_query'][ $where ][ $user_field ] ) ) {
				$woocommerce_multilevel_referral_cache['referral_user_query'][ $where ][ $user_field ] = array();
			}
			$allowed_fields = array(
				'id',
				'user_id',
				'referral_code',
				'referral_email',
				'join_date',
				'active',
				'referral_parent',
			);
			$allowed_where  = array( 'user_id', 'referral_code', 'referral_email' );
			if ( ! in_array( $user_field, $allowed_fields, true ) ) {
				return false;
			}
			if ( ! in_array( $where, $allowed_where, true ) ) {
				return false;
			}
			// Table name is internal and trusted.
			$table_name = esc_sql( $this->table_name );
			// Column names are validated against allowed list above.
			$user_field_escaped = esc_sql( $user_field );
			$where_escaped      = esc_sql( $where );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$user_data = $wpdb->get_var( $wpdb->prepare( "SELECT {$user_field_escaped} FROM {$table_name} WHERE {$where_escaped} = %s", $user_id ) );
			$woocommerce_multilevel_referral_cache['referral_user_query'][ $where ][ $user_field ][ $user_id ] = $user_data;
			return $user_data;
		}

		/**
		 * Change referral user parent.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return int|false Parent referral user ID or false.
		 */
		public function change_referral_user( $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$parent_referral_user = $wpdb->get_var( $wpdb->prepare( "SELECT referral_parent FROM {$wpdb->prefix}referal_users WHERE user_id =%d ", $user_id ) );
			if ( $parent_referral_user ) {
				$this->update( $user_id, $parent_referral_user, 0 );
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}referal_users SET referral_parent =%d WHERE referral_parent =%d", $parent_referral_user, $user_id ) );
			}
			return $parent_referral_user;
		}

		/**
		 * Activate referral user.
		 *
		 * @param int $user_id User ID.
		 */
		public function active_referral_user( $user_id ) {
			global $wpdb, $woocommerce_multilevel_referral_inactive_user_array;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$parent_referral_user = $wpdb->get_var( $wpdb->prepare( "SELECT referral_parent FROM {$wpdb->prefix}referal_users WHERE user_id = %d", $user_id ) );
			$this->update( $user_id, $parent_referral_user, 1 );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$active_user_list = $wpdb->get_col( $wpdb->prepare( "SELECT um.user_id FROM {$wpdb->usermeta} AS um JOIN {$wpdb->prefix}referal_users AS ru ON ru.user_id = um.user_id WHERE ru.active = 1 AND um.meta_value = %d AND um.`meta_key` = 'referral_parent'", $user_id ) );
			if ( count( $active_user_list ) ) {
				// Create placeholders for each item in the array.
				$placeholders   = implode( ',', array_fill( 0, count( $active_user_list ), '%d' ) );
				$prepare_values = array_merge( array( (int) esc_sql( $user_id ), gmdate( 'Y-m-d H:i:s' ) ), array_map( 'intval', $active_user_list ) );
                // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$query = "UPDATE {$wpdb->prefix}referal_users SET referral_parent = %d, update_date = %s WHERE active = 1 AND user_id IN ({$placeholders})";
				$wpdb->query( $wpdb->prepare( $query, ...$prepare_values ) );
                // phpcs:enable
			}
			$this->check_child_deactive_referral_user( $user_id );
			if ( count( $woocommerce_multilevel_referral_inactive_user_array ) > 0 ) {
				// Create placeholders for each item in the array.
				$inactive_users = array_map( 'intval', $woocommerce_multilevel_referral_inactive_user_array );
				$placeholders   = implode( ',', array_fill( 0, count( $inactive_users ), '%d' ) );
				$prepare_values = array_merge( array( (int) esc_sql( $user_id ), gmdate( 'Y-m-d H:i:s' ) ), $inactive_users );
                // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$query = "UPDATE {$wpdb->prefix}referal_users SET referral_parent = %d, update_date = %s WHERE active = 0 AND user_id IN ({$placeholders})";
				$wpdb->query( $wpdb->prepare( $query, ...$prepare_values ) );
                // phpcs:enable
			}
			echo esc_url( admin_url( 'admin.php?page=wc_referral&user_status=0&uid=' . $user_id ) );
			die;
		}

		/**
		 * Check child deactive referral user.
		 *
		 * @param int $user_id User ID.
		 */
		public function check_child_deactive_referral_user( $user_id ) {
			global $wpdb, $woocommerce_multilevel_referral_inactive_user_array;
			if ( ! isset( $woocommerce_multilevel_referral_inactive_user_array ) ) {
				$woocommerce_multilevel_referral_inactive_user_array = array();
			}
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$deactive_user_list = $wpdb->get_col( $wpdb->prepare( "SELECT um.user_id FROM {$wpdb->usermeta} AS um JOIN {$this->table_name} AS ru ON ru.user_id = um.user_id WHERE ru.active = 0 AND um.meta_value = %s AND um.`meta_key` = 'referral_parent'", $user_id ) );
			if ( count( $deactive_user_list ) ) {
				foreach ( $deactive_user_list as $deactive_user ) {
					$woocommerce_multilevel_referral_inactive_user_array[] = $deactive_user;
					$this->check_child_deactive_referral_user( $deactive_user );
				}
			}
		}

		/**
		 * Add new register fields for WooCommerce registration.
		 *
		 * @return string Register fields HTML.
		 */
		public function referral_register_start_fields() {
			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'woocommerce_multilevel_referral_ajax_nonce_action' ) ) {
				return;
			}
			if ( isset( $_GET['ru'] ) && ! isset( $_POST['referral_code'] ) && '' !== $_GET['ru'] ) {
				$referral_email = $this->referral_user( 'referral_email', 'referral_code', sanitize_text_field( wp_unslash( $_GET['ru'] ) ) );
				if ( $referral_email ) {
					$_POST['email'] = $referral_email;
				}
			}
			echo wp_kses( self::render_template( 'front/register-form-start-fields.php' ), woocommerce_multilevel_referral_get_wp_fs_allow_html() );
		}

		/**
		 * Add referral program form to register form.
		 */
		public function referral_register_fields() {
			// Only check nonce if this is a POST request with registration data.
			if ( ! empty( $_POST ) && isset( $_POST['woocommerce-register-nonce'] ) ) {
				// Fail early if nonce is invalid.
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ), 'woocommerce-register' ) ) {
					return;
					// Nonce verification failed, exit early.
				}
			}
			$referral_code = '';
			if ( isset( $_POST['referral_code'] ) ) {
				$referral_code = sanitize_text_field( wp_unslash( $_POST['referral_code'] ) );
			} elseif ( isset( $_GET['ru'] ) ) {
				$referral_code = sanitize_text_field( wp_unslash( $_GET['ru'] ) );
			} elseif ( isset( $_COOKIE['woocommerce_multilevel_referral_code'] ) ) {
				$referral_code = sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_multilevel_referral_code'] ) );
			}
			$data = array(
				'join_referral_program' => ( isset( $_POST['join_referral_program'] ) ? sanitize_text_field( wp_unslash( $_POST['join_referral_program'] ) ) : ( ( $referral_code && ! isset( $_POST['join_referral_program'] ) ? 0 : 2 ) ) ),
				'referral_email'        => ( isset( $_POST['referral_email'] ) ? sanitize_text_field( wp_unslash( $_POST['referral_email'] ) ) : '' ),
				'referral_code'         => $referral_code,
				'flag'                  => true,
			);
			$data = apply_filters( 'woocommerce_multilevel_referral_registation_referral_fields', $data );
			if ( isset( $data['flag'] ) && true === $data['flag'] ) {
				echo wp_kses(
					self::render_template(
						'front/register-form-end-fields.php',
						array(
							'data' => $data,
						)
					),
					woocommerce_multilevel_referral_get_wp_fs_allow_html()
				);
			}
		}

		/**
		 * Override checkout fields.
		 *
		 * @param array $woocommerce_multilevel_referral_fields Fields.
		 *
		 * @return array Modified fields.
		 */
		public function woocommerce_multilevel_referral_override_checkout_fields( $woocommerce_multilevel_referral_fields ) {
			$auto_join         = get_option( 'woocommerce_multilevel_referral_auto_register', 'no' );
			$arr_options       = array(
				'1' => __( 'I have the referral code and want to join referral program.', 'multilevel-referral-plugin-for-woocommerce' ),
				'2' => __( 'I don\'t have referral code or I lost it. But I wish to join referral program.', 'multilevel-referral-plugin-for-woocommerce' ),
				'3' => __( 'No, I don\'t want to be a part of referral program at this time.', 'multilevel-referral-plugin-for-woocommerce' ),
			);
			$referral_code     = ( isset( $_COOKIE['woocommerce_multilevel_referral_code'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_multilevel_referral_code'] ) ) : '' );
			$arr_referral_code = array(
				'type'        => 'text',
				'label'       => __( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ),
				'placeholder' => __( 'Enter referral code', 'multilevel-referral-plugin-for-woocommerce' ),
				'class'       => ( $referral_code ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hide' ) ),
				'default'     => $referral_code,
			);
			if ( 'yes' === $auto_join ) {
				$woocommerce_multilevel_referral_fields['account']['join_referral_program'] = array(
					'type'    => 'hidden',
					'default' => '2',
				);
				$woocommerce_multilevel_referral_fields['account']['termsandconditions']    = array(
					'type'    => 'hidden',
					'default' => '1',
				);
				$arr_referral_code['class']       = array( 'form-row-wide' );
				$arr_referral_code['placeholder'] = __( 'Enter referral code if you have one', 'multilevel-referral-plugin-for-woocommerce' );
				$woocommerce_multilevel_referral_fields['account']['referral_code'] = $arr_referral_code;
			} else {
				$woocommerce_multilevel_referral_fields['account']['join_referral_stage_one'] = array(
					'type'     => 'radio',
					'required' => true,
					'label'    => __( 'Do you want to join Referral Program?', 'multilevel-referral-plugin-for-woocommerce' ),
					'class'    => array( 'form-row-wide' ),
					'options'  => array(
						'2' => __( 'Yes', 'multilevel-referral-plugin-for-woocommerce' ),
						'3' => __( 'No', 'multilevel-referral-plugin-for-woocommerce' ),
					),
					'default'  => ( $referral_code ? 2 : '' ),
				);
				$woocommerce_multilevel_referral_fields['account']['join_referral_stage_two'] = array(
					'type'    => 'radio',
					'label'   => __( 'Do you have Referral code?', 'multilevel-referral-plugin-for-woocommerce' ),
					'class'   => ( $referral_code ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hide' ) ),
					'options' => array(
						'1' => __( 'Yes', 'multilevel-referral-plugin-for-woocommerce' ),
						'2' => __( 'No', 'multilevel-referral-plugin-for-woocommerce' ),
					),
					'default' => ( $referral_code ? 1 : '' ),
				);
				$woocommerce_multilevel_referral_fields['account']['join_referral_program']   = array(
					'type'  => 'hidden',
					'value' => 3,
				);
				$woocommerce_multilevel_referral_fields['account']['referral_code']           = $arr_referral_code;
				$woocommerce_multilevel_referral_fields['account']['termsandconditions']      = array(
					'type'        => 'checkbox',
					'label'       => __( 'I\'ve read and agree to the referral program', 'multilevel-referral-plugin-for-woocommerce' ) . ' <a href="' . esc_url( get_permalink( get_option( 'woocommerce_multilevel_referral_terms_and_conditions', 0 ) ) ) . '" target="_blank">' . __( 'terms and conditions', 'multilevel-referral-plugin-for-woocommerce' ) . '</a>',
					'class'       => ( $referral_code ? array( 'form-row-wide wpmlrp-checkbox' ) : array( 'form-row-wide wpmlrp-checkbox hide' ) ),
					'label_class' => array( '' ),
				);
			}
			return $woocommerce_multilevel_referral_fields;
		}

		/**
		 * Custom checkout field process.
		 */
		public function woocommerce_multilevel_referral_custom_checkout_field_process() {
			$nonce_value = ( isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) : ( ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' ) ) );
			if ( ! isset( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
				wp_die( 'Nonce verification failed!' );
			}
			$guest_checkout    = get_option( 'woocommerce_enable_guest_checkout' );
			$auto_join         = get_option( 'woocommerce_multilevel_referral_auto_register', 'no' );
			$validate_referral = false;
			if ( 'yes' === $guest_checkout && isset( $_POST['createaccount'] ) ) {
				$validate_referral = true;
			}
			if ( 'no' === $guest_checkout ) {
				$validate_referral = true;
			}
			if ( $validate_referral && isset( $_POST['join_referral_program'] ) ) {
				if ( 1 === (int) $_POST['join_referral_program'] ) {
					if ( isset( $_POST['referral_code'] ) && '' === $_POST['referral_code'] ) {
						wc_add_notice( __( '<strong>The Referral code</strong> is required field.', 'multilevel-referral-plugin-for-woocommerce' ), 'error' );
						return;
					}
					if ( ! isset( $_POST['termsandconditions'] ) ) {
						wc_add_notice( __( 'Please accept <strong>terms and conditions</strong> to join referral program.', 'multilevel-referral-plugin-for-woocommerce' ), 'error' );
					}
				}
				if ( 2 === (int) $_POST['join_referral_program'] ) {
					if ( ! isset( $_POST['termsandconditions'] ) && isset( $_POST['join_referral_stage_two'] ) ) {
						wc_add_notice( __( 'Please accept <strong>terms and conditions</strong> to join referral program.', 'multilevel-referral-plugin-for-woocommerce' ), 'error' );
					}
					if ( ! isset( $_POST['join_referral_stage_two'] ) && 'no' === $auto_join ) {
						wc_add_notice( __( '<strong>Do you have Referral code?</strong> is required field.', 'multilevel-referral-plugin-for-woocommerce' ), 'error' );
					}
				}
			}
			if ( isset( $_POST['referral_code'] ) && '' !== $_POST['referral_code'] ) {
				$parent_id = $this->referral_user( 'user_id', 'referral_code', sanitize_text_field( wp_unslash( $_POST['referral_code'] ) ) );
				if ( ! $parent_id ) {
					// translators: %s is the referral code.
					wc_add_notice( sprintf( __( 'There is no such referral code exist <strong>(%s)</strong> exist.', 'multilevel-referral-plugin-for-woocommerce' ), sanitize_text_field( wp_unslash( $_POST['referral_code'] ) ) ), 'error' );
				}
			}
		}

		/**
		 * Referral user my affiliate panel.
		 */
		public function referral_user_my_affiliate_panel() {
			echo wp_kses( $this->woocommerce_multilevel_referral_my_affiliates(), woocommerce_multilevel_referral_get_wp_fs_allow_html() );
		}

		/**
		 * Referral user account panel.
		 */
		public function referral_user_account_panel() {
			echo do_shortcode( '[woocommerce_multilevel_referral_my_referral_tab]' );
		}

		/**
		 * Shortcode to display Invite friends form.
		 */
		public function woocommerce_multilevel_referral_my_referrals() {
			if ( is_user_logged_in() ) {
				$html_block  = '';
				$html_block .= '<div class="referral_program_details sdfsdfsdf">';
				$check_user  = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
				if ( $check_user ) {
					$html_block .= $this->woocommerce_multilevel_referral_referral_stats_blocks();
				}
				$html_block .= '<div class="referral_program_sections"><div class="referral_program_content">';
				$html_block .= do_shortcode( '[woocommerce_multilevel_referral_invite_friends]', true );
				$html_block .= '</div></div></div>';
				echo wp_kses( $html_block, woocommerce_multilevel_referral_get_wp_fs_allow_html() );
			}
		}

		/**
		 * Display my affiliates.
		 */
		public function woocommerce_multilevel_referral_my_affiliates() {
			$html_block = '';
			if ( is_user_logged_in() ) {
				$html_block = '<div class="referral_program_details">';
				$check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
				if ( $check_user ) {
					$html_block .= $this->woocommerce_multilevel_referral_referral_stats_blocks();
					$html_block .= '<div class="referral_program_sections" style="padding-top: 30px;"><div class="referral_program_content">';
					$html_block .= $this->woocommerceMultilevelReferralShowMyAffiliates();
					$html_block .= $this->referral_user_credit_info();
					$html_block .= '</div></div>';
				} else {
					$html_block .= '<p>' . __( 'Please join our Referral Program to access this page.', 'multilevel-referral-plugin-for-woocommerce' ) . '</p>';
					$html_block  = apply_filters( 'woocommerce_multilevel_referral_my_affliates_join_programe_text', $html_block );
				}
				$html_block .= '</div>';
			}
			return $html_block;
		}

		/**
		 * Display referral stats blocks.
		 */
		public function woocommerce_multilevel_referral_referral_stats_blocks() {
			$html_block = '';
			if ( is_user_logged_in() ) {
				$html_block                             .= '<div class="referral_program_overview referral_top_section">';
				$current_user_id                         = get_current_user_ID();
				$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
				$data                                    = array(
					'referral_code'    => $this->referral_user( 'referral_code', 'user_id', $current_user_id ),
					'total_points'     => $woocommerce_multilevel_referral_program->available_credits( $current_user_id ),
					'total_followers'  => $this->fnGetFollowersCount( $current_user_id ),
					'total_withdraw'   => $woocommerce_multilevel_referral_program->total_withdraw_credit( $current_user_id ),
					'total_earn_point' => $woocommerce_multilevel_referral_program->total_earn_credit( $current_user_id ),
				);
				add_filter( 'woocommerce_currency_symbol', 'woocommerce_multilevel_referral_remove_wc_currency_symbols', 99 );
				$link        = add_query_arg( 'ru', $data['referral_code'], get_the_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
				$html_block .= '   <div class="referral_program_stats">
										<span class="referral_icon"></span>
										<span>' . __( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ) . '</span>
										<span class="show_output">' . $data['referral_code'] . '</span>
										<a class="copy_referral_link" data-content="' . __( 'Copied', 'multilevel-referral-plugin-for-woocommerce' ) . '" href="' . $link . '">' . __( 'Copy your Referral Link.', 'multilevel-referral-plugin-for-woocommerce' ) . '</a>
									</div>
									<div class="referral_program_stats total_avilable_credit">
										<span class="total_credit_icon"></span>
										<span>' . apply_filters( 'woocommerce_multilevel_referral_total_credits_available', __( 'Total Credits Available', 'multilevel-referral-plugin-for-woocommerce' ) ) . '</span>
										<span class="show_output">' . wc_price( apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', $data['total_points'] ) ) . '</span>
									</div>
									<div class="referral_program_stats">
										<span class="total_referral"></span>
										<span>' . __( 'Total Referrals', 'multilevel-referral-plugin-for-woocommerce' ) . '</span>
										<span class="show_output">' . $data['total_followers'] . '</span>
									</div>';
				$html_block  = apply_filters( 'woocommerce_multilevel_referral_tab_block', $html_block, $data );
				$html_block .= '</div>';
				remove_filter( 'woocommerce_currency_symbol', 'woocommerce_multilevel_referral_remove_wc_currency_symbols', 99 );
				$html_block = apply_filters( 'woocommerce_multilevel_referral_tabs', $html_block, $data );
			}
			return $html_block;
		}

		/**
		 * Referral user invite friends.
		 */
		public function referral_user_invite_friends() {
			if ( is_user_logged_in() ) {
				global $invitation_error;
				$woocommerce_multilevel_referral_html = '';
				$check_user                           = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
				if ( $check_user ) {
					if ( ! isset( $_POST['referral-invite-friends-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['referral-invite-friends-nonce'] ) ), 'referral-invite-friends' ) ) {
						return;
					}
					$woocommerce_multilevel_referral_html .= '<div class="woocommerce-multilevel-referral-invite-friends">';
					$woocommerce_multilevel_referral_html  = apply_filters( 'woocommerce_multilevel_referral_after_block', $woocommerce_multilevel_referral_html, $check_user );
					$email                                 = ( isset( $_POST['emails'] ) ? sanitize_text_field( wp_unslash( $_POST['emails'] ) ) : '' );
					$woocommerce_multilevel_referral_html .= '<p class="hide">
						<a href="#" class="button btn-invite-friends">' . __( 'Invite Friends', 'multilevel-referral-plugin-for-woocommerce' ) . '</a>
						</p>
						<div id="dialog-invitation-form">
						<h2>' . __( 'Invite your friends', 'multilevel-referral-plugin-for-woocommerce' ) . '</h2>
						<h4>' . __( 'Send an Invitation to your friend by adding his/her e-mail address. (If you want to invite sevaral at the same time, just add a comma in between.)', 'multilevel-referral-plugin-for-woocommerce' ) . '</h4>
						<form method="post">' . wp_nonce_field( 'referral-invite-friends', 'referral-invite-friends-nonce' ) . '
						<table class="shop_table shop_table_responsive">
						<tr>
						<td>
						<input type="text" name="emails"  class="input-text" value="' . $email . '" placeholder="Ex. test@demo.com, test2@demo.com" />
						</td>
						<td width="105px">
						<input type="submit" class="button btn-send-invitation" value="' . __( 'Invite', 'multilevel-referral-plugin-for-woocommerce' ) . '" />
						<input type="hidden" name="action" value="send_invitations" />
						</td>
						</tr>
						</table>
						</form>
						</div>';
					$woocommerce_multilevel_referral_html .= '</div>';
					$bannars                               = $this->woocommerceMultilevelReferralShowBanners();
					$woocommerce_multilevel_referral_html .= $bannars;
				} else {
					$referal_code = '';
					if ( isset( $_POST['referral_code'] ) ) {
						$referal_code = sanitize_text_field( wp_unslash( $_POST['referral_code'] ) );
					} elseif ( isset( $_COOKIE['woocommerce_multilevel_referral_code'] ) ) {
						$referal_code = sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_multilevel_referral_code'] ) );
					}
					$data                                  = array(
						'join_referral_program' => ( isset( $_POST['join_referral_program'] ) ? sanitize_text_field( wp_unslash( $_POST['join_referral_program'] ) ) : 1 ),
						'referral_email'        => ( isset( $_POST['referral_email'] ) ? sanitize_email( wp_unslash( $_POST['referral_email'] ) ) : '' ),
						'referral_code'         => ( isset( $_POST['referral_code'] ) ? sanitize_text_field( wp_unslash( $_POST['referral_code'] ) ) : '' ),
						'nonce'                 => wp_create_nonce( 'referral_program' ),
					);
					$woocommerce_multilevel_referral_html .= self::render_template(
						'front/join-form.php',
						array(
							'data' => $data,
						)
					);
					$woocommerce_multilevel_referral_html  = apply_filters( 'woocommerce_multilevel_referral_join_form_front', $woocommerce_multilevel_referral_html );
				}
				return $woocommerce_multilevel_referral_html;
			}
		}

		/**
		 * Get full URL.
		 *
		 * @param string $url URL.
		 *
		 * @return string Full URL.
		 */
		public function woocommerceMultilevelReferralGetFullUrl( $url ) {
			return $url;
		}

		/**
		 * Show banners.
		 *
		 * @return string Banners HTML.
		 */
		public function woocommerceMultilevelReferralShowBanners() {
			global $wp;
			$total_banners = 2;
			$banners       = get_option( 'woocommerce_multilevel_referral_default_banners' );
			$all_banners   = get_posts(
				array(
					'post_type'   => 'wc_ml_ref_banner',
					'numberposts' => $total_banners,
					'post__in'    => $banners,
					'order'       => 'ASC',
					'orderby'     => 'title',
				)
			);
			$i             = 0;
			$arr_banners   = get_option( 'woocommerce_multilevel_referral_pre_banners' );
			$first_banner  = array();
			$referral_code = __( 'Referral Code : ', 'multilevel-referral-plugin-for-woocommerce' );
			$code          = '';
			if ( ! function_exists( 'imagecreatefrompng' ) ) {
				return '<div id="woocommerce-multilevel-referral-social-media"><h2>' . __( 'Share on Social Media', 'multilevel-referral-plugin-for-woocommerce' ) . '</h2><h4><strong>' . __( 'Please contact site administrator for active social media sharing functionality.', 'multilevel-referral-plugin-for-woocommerce' ) . '</strong></h4></div>';
			}
			$current_user_id = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
			if ( $current_user_id ) {
				$code           = $this->referral_user( 'referral_code', 'user_id', $current_user_id );
				$referral_code .= $code;
			}
			$woocommerce_multilevel_referral_html = '<div id="woocommerce-multilevel-referral-social-media">
				<h2>' . __( 'Share on Social Media', 'multilevel-referral-plugin-for-woocommerce' ) . '</h2>
				<h4>' . __( 'Select a banner, write a title and a description, then click the icon of the social media you want to share on.', 'multilevel-referral-plugin-for-woocommerce' ) . '</h4>
				<div class="woocommerce-multilevel-referral-banners">
				<div class="woocommerce-multilevel-referral-banner-list">
				<label>' . __( 'Select Banner', 'multilevel-referral-plugin-for-woocommerce' ) . ' </label>
				<select data-loader="' . WOOCOMMERCE_MULTILEVEL_REFERRAL_URL . 'images/loadingAnimation.gif">';
			$first_banner                         = array(
				'attachId' => '',
				'thumbUrl' => '',
				'path'     => '',
				'title'    => '',
				'desc'     => '',
				'url'      => '',
				'id'       => '',
			);
			foreach ( $all_banners as $banner ) {
				$checked       = '';
				$preset_banner = 'no';
				if ( has_post_thumbnail( $banner->ID ) ) {
					$banner_thumbnail_id  = get_post_thumbnail_id( $banner->ID );
					$banner_thumbnail_url = wp_get_attachment_url( $banner_thumbnail_id );
					$banner_path          = get_attached_file( $banner_thumbnail_id );
					$q_url                = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
					$page_url             = add_query_arg( 'woocommerce_multilevel_referral_banner', $code . '-' . $current_user_id . '-' . $banner->ID . '-' . $banner_thumbnail_id, $q_url );
					$shareme_url          = $this->woocommerceMultilevelReferralGetFullUrl( $page_url );
					if ( $i < 1 ) {
						$first_banner['attachId'] = $banner_thumbnail_id;
						$first_banner['thumbUrl'] = $banner_thumbnail_url;
						$first_banner['path']     = $banner_path;
						$checked                  = 'checked="checked"';
						$first_banner['title']    = $banner->post_title;
						$first_banner['desc']     = $banner->post_excerpt;
						$first_banner['url']      = $shareme_url;
						$first_banner['id']       = $banner->ID;
					}
					$woocommerce_multilevel_referral_html .= '<option data-code="' . $code . '" data-url="' . $shareme_url . '"  data-attachid="' . $banner_thumbnail_id . '" value="' . $banner->ID . '" data-title="' . $banner->post_title . '" data-desc="' . $banner->post_excerpt . '" data-image="' . $banner_thumbnail_url . '">' . $banner->post_title . '</option>';
					++$i;
				}
			}
			$woocommerce_multilevel_referral_html .= '</select></div>
				<div class="woocommerce-multilevel-referral-banner-preview">';
			$image_url                             = $this->woocommerceMultilevelReferralGetFullUrl( $first_banner['thumbUrl'] );
			if ( count( $first_banner ) > 0 && '' !== $first_banner['path'] ) {
				$image_url = $this->writeTextonImage( $referral_code, $first_banner['path'], $current_user_id );
				$image_url = $this->woocommerceMultilevelReferralGetFullUrl( $image_url );
                // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				$woocommerce_multilevel_referral_html .= '<img  src="' . $image_url . '" alt="' . esc_attr( $first_banner['title'] ) . '">';
			}
			$woocommerce_multilevel_referral_html        .= '</div>';
			$woocommerce_multilevel_referral_html        .= '<div><p class="form-row form-row-wide"><label for="woocommerceMultilevelReferralBannerTitle" class="">' . __( 'Custom Banner Title', 'multilevel-referral-plugin-for-woocommerce' ) . '</label><input type="text" class="input-text " name="woocommerceMultilevelReferralBannerTitle" id="woocommerceMultilevelReferralBannerTitle" placeholder="' . __( 'Banner Title', 'multilevel-referral-plugin-for-woocommerce' ) . '" value="' . $first_banner['title'] . '"></p><p class="form-row form-row-wide"><label for="woocommerceMultilevelReferralBannerDescription" class="">' . __( 'Custom Banner Description', 'multilevel-referral-plugin-for-woocommerce' ) . '</label><textarea class="input-text" name="woocommerceMultilevelReferralBannerDescription" id="woocommerceMultilevelReferralBannerDescription" placeholder="' . __( 'Banner Description', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . $first_banner['desc'] . '</textarea></p></div>
				</div>
				<div class="woocommerce-multilevel-referral-share-wrapper" data-url="' . $first_banner['url'] . '" data-title="' . $first_banner['title'] . '" data-image="' . $image_url . '" data-description="' . $first_banner['desc'] . '">
				<span id="share42">
				<a rel="nofollow" class="woocommerce-multilevel-referral-button-fb"  href="#" data-count="fb"  title="' . __( 'Share on Facebook', 'multilevel-referral-plugin-for-woocommerce' ) . '" target="_blank"></a>
				<!--a rel="nofollow" class="wmc-button-gplus"  href="#" data-count="gplus"  title="' . __( 'Share on Google+', 'multilevel-referral-plugin-for-woocommerce' ) . '" target="_blank"></a-->
				<a rel="nofollow" class="woocommerce-multilevel-referral-button-lnkd"  href="#" data-count="lnkd"  title="' . __( 'Share on Linkedin', 'multilevel-referral-plugin-for-woocommerce' ) . '" target="_blank"></a>
				<a rel="nofollow" class="woocommerce-multilevel-referral-button-pin"  href="#" data-count="pin" title="' . __( 'Pin It', 'multilevel-referral-plugin-for-woocommerce' ) . '" target="_blank"></a>
				<a rel="nofollow" class="woocommerce-multilevel-referral-button-twi"  href="#" data-count="twi" title="' . __( 'Share on Twitter', 'multilevel-referral-plugin-for-woocommerce' ) . '" target="_blank"></a>
				<a rel="nofollow" class="woocommerce-multilevel-referral-button-whatsup" href="#" data-account="' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '" data-ru="' . $code . '" data-share="' . md5( 'whatsup' ) . '" data-count="whatsup" title="' . __( 'Share on What\'s up', 'multilevel-referral-plugin-for-woocommerce' ) . '"></a>
				</span>
				</div>';
			return $woocommerce_multilevel_referral_html .= '</div>';
		}

		/**
		 * Banner meta information.
		 */
		public function fnBannerMetaInformation() {
			global $wpdb;
			if ( is_single() ) {
				$post = get_post();
				if ( 'wc_ml_ref_banner' === $post->post_type ) {
					$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
					$size              = 'full';
					$image_url         = wp_get_attachment_image_src( $post_thumbnail_id, $size );
					$banner_path       = get_attached_file( $post_thumbnail_id );
					$arr_banners       = get_option( 'woocommerce_multilevel_referral_pre_banners' );
					if ( in_array( $post->ID, $arr_banners, true ) ) {
						global $current_user;
						wp_get_current_user();
						if ( 0 !== $current_user->ID ) {
							$current_user_id = $current_user->ID;
							$referral_code   = __( 'Referral Code : ', 'multilevel-referral-plugin-for-woocommerce' );
                            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$code           = $wpdb->get_var( $wpdb->prepare( "SELECT referral_code FROM {$wpdb->prefix}referal_users WHERE user_id =%d", $current_user_id ) );
							$referral_code .= $code;
							$image_url      = $this->writeTextonImage( $referral_code, $banner_path, $current_user_id );
							// writeTextonImage returns the full URL from uploads directory.
							$meta_info = '<script type="text/javascript">
								var FBAPP_ID = "1696793383871229";
								</script><meta property="og:type" content="article"><meta property="og:title" content="' . $post->post_title . '"><meta property="fb:app_id" content="1696793383871229" >
								<meta property="og:url" content="' . get_permalink( $post->ID ) . '" >
								<meta property="og:description" content="' . $post->post_excerpt . '" >
								<meta property="og:image" content="' . $image_url . '" >
								<meta property="og:image:width" content="500" >
								<meta property="og:image:height" content="300" >
								<meta name="twitter:card" content="summary_large_image" >
								<meta name="twitter:title" content="' . $post->post_title . '" >
								<meta name="twitter:url" content="' . get_permalink( $post->ID ) . '" >
								<meta name="twitter:description" content="' . $post->post_excerpt . '" >
								<meta name="twitter:image" content="' . $image_url . '" >
								<meta itemprop="name" content="' . $post->post_title . '">
								<meta itemprop="description" content="' . $post->post_excerpt . '">
								<meta itemprop="image" content="' . $image_url . '">';
							echo wp_kses_post( $meta_info );
						}
					}
				}
			}
		}

		/**
		 * Modify post thumbnail.
		 *
		 * @param string $html HTML.
		 * @param int    $post_id Post ID.
		 * @param int    $post_thumbnail_id Post thumbnail ID.
		 * @param string $size Size.
		 * @param array  $attr Attributes.
		 *
		 * @return string Modified HTML.
		 */
		public function fnModifyPostThumbnail(
			$html,
			$post_id,
			$post_thumbnail_id,
			$size,
			$attr
		) {
			if ( has_post_thumbnail() && is_user_logged_in() ) {
				$post_type       = get_post_type();
				$current_user_id = get_current_user_id();
				if ( 'wc_ml_ref_banner' === $post_type ) {
					$image_url = WOOCOMMERCE_MULTILEVEL_REFERRAL_URL . 'images/userbanners/banner-' . $current_user_id . '.jpg';
					$doc       = new DOMDocument();
					$doc->loadHTML( $html );
					$tags = $doc->getElementsByTagName( 'img' );
					foreach ( $tags as $tag ) {
						$old_src = $tag->getAttribute( 'src' );
						$tag->setAttribute( 'src', $image_url );
						$tag->setAttribute( 'srcset', $image_url );
					}
					$html = $doc->saveHTML();
				}
			}
			return $html;
		}

		/**
		 * Filter the content.
		 *
		 * @param string $content Content.
		 *
		 * @return string Filtered content.
		 */
		public function fnFilterTheContent( $content ) {
			if ( is_single() && in_the_loop() && is_main_query() ) {
				$link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
				if ( isset( $_GET['ru'] ) && '' !== $_GET['ru'] ) {
					if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'woocommerce_multilevel_referral_ajax_nonce_action' ) ) {
						wp_die( 'Nonce verification failed!' );
					}
					$link     = add_query_arg(
						array(
							'ru' => sanitize_text_field( wp_unslash( $_GET['ru'] ) ),
						),
						$link
					);
					$content .= '<div class="woocommerce-multilevel-referral-account-link"><a href="' . $link . '" title="' . __( 'Login / Register', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . __( 'Login / Register', 'multilevel-referral-plugin-for-woocommerce' ) . '</a></div>';
				}
			}
			return $content;
		}

		/**
		 * Check and get image type.
		 *
		 * @param string $path Image path.
		 *
		 * @return array Image type info.
		 */
		public function fnCheckAndGetImageType( $path ) {
			$path_parts = pathinfo( $path );
			$mime_type  = $path_parts['extension'];
			$img_obj    = '';
			if ( isset( $mime_type ) && '' !== $mime_type && function_exists( 'imagecreatefrompng' ) ) {
				switch ( $mime_type ) {
					case 'png':
						$img_obj = imagecreatefrompng( $path );
						break;
					case 'jpg':
					case 'jpeg':
						$img_obj = imagecreatefromjpeg( $path );
						break;
					case 'gif':
						$img_obj = imagecreatefromgif( $path );
						break;
					default:
						die( 'Invalid image type' );
				}
			}
			return array(
				'img'  => $img_obj,
				'type' => $mime_type,
			);
		}

		/**
		 * Create image by type.
		 *
		 * @param string   $mime_type MIME type.
		 * @param resource $img_obj Image object.
		 * @param string   $file_name File name.
		 *
		 * @return string Image URL.
		 */
		public function fnCreateImageByType( $mime_type, $img_obj, $file_name ) {
			$upload_dir = $this->get_plugin_uploads_dir();
			$banner_dir = $upload_dir['path'] . 'images/userbanners/';
			// Ensure directory exists.
			if ( ! file_exists( $banner_dir ) ) {
				wp_mkdir_p( $banner_dir );
			}
			$image_path = $banner_dir . $file_name;
			$image_url  = $upload_dir['url'] . 'images/userbanners/' . $file_name;
			$extension  = '';
			switch ( $mime_type ) {
				case 'png':
					imagepng( $img_obj, $image_path . '.png', 9 );
					$image_url .= '.png';
					$extension  = '.png';
					break;
				case 'jpg':
				case 'jpeg':
					imagejpeg( $img_obj, $image_path . '.jpeg', 100 );
					$image_url .= '.jpeg';
					$extension  = '.jpeg';
					break;
				case 'gif':
					imagegif( $img_obj, $image_path . '.gif' );
					$image_url .= '.gif';
					$extension  = '.gif';
					break;
				default:
					die( 'Invalid image type' );
			}
			if ( is_resource( $img_obj ) ) {
				imagedestroy( $img_obj );
			}
			if ( $extension && ! file_exists( $image_path . $extension ) ) {
				$image = wp_get_image_editor( $image_path . $extension );
				if ( ! is_wp_error( $image ) ) {
					$image->resize( null, 300, false );
					$image->save( $image_path . $extension );
				}
			}
			return $image_url;
		}

		/**
		 * Write text on image.
		 *
		 * @param string $code Code.
		 * @param string $path Image path.
		 * @param int    $user_id User ID.
		 * @param int    $attach_id Attachment ID.
		 *
		 * @return string Image URL.
		 */
		public function writeTextonImage(
			$code,
			$path,
			$user_id,
			$attach_id = 0
		) {
			$img_arr = $this->fnCheckAndGetImageType( $path );
			$img_url = '';
			if ( $img_arr['img'] && '' !== $img_arr['img'] ) {
				$color = imagecolorallocate(
					$img_arr['img'],
					0xff,
					0xff,
					0xff
				);
				$width = imagesx( $img_arr['img'] );
				// it will store width of image.
				$height = imagesy( $img_arr['img'] );
				// it will store height of image.
				$fontsize = round( 48 * ( 15.87 * $height ) / 100 / 100 );
				// size of font.
				$font          = WOOCOMMERCE_MULTILEVEL_REFERRAL_DIR . 'css/roboto-condensed-regular.ttf';
				$txt_box_width = $width - 20;
				do {
					$bbox      = imagettfbbox(
						$fontsize,
						0,
						$font,
						$code
					);
					$box_width = abs( $bbox[4] - $bbox[0] );
					$x         = ( $txt_box_width - $box_width ) / 2;
					--$fontsize;
				} while ( $box_width > $txt_box_width );
				$top_pos = 100 * $height / 630 - abs( $bbox[5] - $bbox[1] ) / 2;
				imagettftext(
					$img_arr['img'],
					$fontsize + 1,
					0,
					intval( $x ),
					intval( $top_pos ),
					$color,
					$font,
					$code
				);
				$url       = site_url();
				$box_width = $width;
				do {
					$bbox2     = imagettfbbox(
						$fontsize,
						0,
						$font,
						$url
					);
					$box_width = abs( $bbox2[4] - $bbox2[0] );
					$x         = ( $txt_box_width - $box_width ) / 2;
					--$fontsize;
				} while ( $box_width > $txt_box_width );
				$top_pos = $height - abs( $bbox2[5] - $bbox2[1] ) / 2;
				imagettftext(
					$img_arr['img'],
					$fontsize + 1,
					0,
					intval( $x ),
					intval( $top_pos ),
					$color,
					$font,
					$url
				);
				$img_url = $this->fnCreateImageByType( $img_arr['type'], $img_arr['img'], 'banner-' . $user_id . $attach_id );
				unset( $color );
			}
			return $img_url;
		}

		/**
		 * Change share content.
		 */
		public function fnChangeShareContent() {
			global $wp;
			$current_url = home_url( add_query_arg( array(), $wp->request ) );
			$query_param = get_query_var( 'woocommerce_multilevel_referral_banner' );
			if ( '' !== $query_param ) {
				$arr_param    = explode( '-', $query_param );
				$site_url     = site_url();
				$link         = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
				$url          = get_permalink( $arr_param[2] );
				$link         = add_query_arg( 'ru', $arr_param[0], $link );
				$banner_image = '';
				$referral_url = ( isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '' );
				if ( '' !== $referral_url ) {
					$arr_url      = wp_parse_url( $referral_url );
					$arr_home_url = wp_parse_url( $site_url );
					if ( $arr_home_url['host'] !== $arr_url['host'] ) {
						header( 'Location: ' . $link );
						exit;
					}
				}
				$referral_code                         = __( 'Referral Code : ', 'multilevel-referral-plugin-for-woocommerce' ) . $arr_param[0];
				$user_id                               = $arr_param[1];
				$woocommerce_multilevel_referral_post  = get_post( $arr_param[2] );
				$woocommerce_multilevel_referral_title = '';
				$woocommerce_multilevel_referral_desc  = '';
				$arr_custom_titles                     = get_transient( 'woocommerce_multilevel_referral_banner_' . $user_id . '_' . $arr_param[3] );
				if ( $arr_custom_titles ) {
					$woocommerce_multilevel_referral_title = $arr_custom_titles['title'];
					$woocommerce_multilevel_referral_desc  = $arr_custom_titles['desc'];
					$banner_image                          = $arr_custom_titles['imageURL'];
				}
				if ( ! $banner_image ) {
					$banner_path     = get_attached_file( $arr_param[3] );
					$arr_pre_banners = get_option( 'woocommerce_multilevel_referral_pre_banners' );
					$banner_image    = wp_get_attachment_url( $arr_param[3] );
					$banner_image    = $this->writeTextonImage( $referral_code, $banner_path, $user_id );
				}
				$woocommerce_multilevel_referral_title = ( '' === $woocommerce_multilevel_referral_title ? $woocommerce_multilevel_referral_post->post_title : $woocommerce_multilevel_referral_title );
				$woocommerce_multilevel_referral_desc  = ( '' === $woocommerce_multilevel_referral_desc ? $woocommerce_multilevel_referral_post->post_excerpt : $woocommerce_multilevel_referral_desc );
                // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				$html_contents = '<!doctype html><html lang="en-US"><head><meta property="og:type" content="article"><meta property="og:title" content="' . $woocommerce_multilevel_referral_title . '"><meta property="fb:app_id" content="1696793383871229" ><meta property="og:description" content="' . $woocommerce_multilevel_referral_desc . '" ><meta property="og:image" content="' . $banner_image . '" ><meta property="og:image:width" content="500" > <meta property="og:image:height" content="300" > <meta name="twitter:card" content="summary" ><meta name="twitter:title" content="' . $woocommerce_multilevel_referral_title . '" ><meta name="twitter:description" content="' . $woocommerce_multilevel_referral_desc . '" ><meta name="twitter:image" content="' . $banner_image . '" ><meta itemprop="name" content="' . $woocommerce_multilevel_referral_title . '"><meta itemprop="description" content="' . $woocommerce_multilevel_referral_desc . '"><meta itemprop="image" content="' . $banner_image . '"><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"><meta name="Description" content="' . $woocommerce_multilevel_referral_desc . '"><meta name="title" content="' . $woocommerce_multilevel_referral_title . '"><title>' . $woocommerce_multilevel_referral_title . ' &#8211;  ' . get_bloginfo( 'name' ) . '</title></head><body><h1>' . $woocommerce_multilevel_referral_title . '</h1><p><img src="' . $banner_image . '" alt="' . $woocommerce_multilevel_referral_title . '">' . $woocommerce_multilevel_referral_desc . '</p><script type="text/javascript">
					window.fbAsyncInit = function() {
					window.FB.init({
					appId            : \'1696793383871229\',
					autoLogAppEvents : true,
					xfbml            : true,
					version          : \'v2.11\'
					});
					};
					(function(d, s, id){
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) {return;}
					js = d.createElement(s); js.id = id;
					js.src = "https://connect.facebook.net/en_US/sdk.js";
					fjs.parentNode.insertBefore(js, fjs);
					}(document, \'script\', \'facebook-jssdk\'));
					if(window.location.search.indexOf("facebook_refresh") >= 0)
					{
					//Feature check browsers for support
					if(document.addEventListener && window.XMLHttpRequest && document.querySelector)
					{
					//DOM is ready
					document.addEventListener("DOMContentLoaded", function() {
					window.FB.login(function(response) {
					var httpRequest = new XMLHttpRequest();
					httpRequest.open("POST", "https://graph.facebook.com?access_token="+response.authResponse.accessToken, true);
					httpRequest.onreadystatechange = function () {
					if (httpRequest.readyState === 4) { console.log("httpRequest.responseText", httpRequest.responseText); }
					};
					//Default URL to send to Facebook
					var url = window.location;
					//og:url element
					var og_url = document.querySelector("meta[property=\'og:url\']");
					//var og_url = window.location.href;
					//Check if og:url element is present on page
					if(og_url !== null)
					{
					//Get the content attribute value of og:url
					var og_url_value = og_url.getAttribute("content");
					//If og:url content attribute isn\'t empty
					if(og_url_value !== "")
					{
					url = og_url_value;
					} else {
					console.warn(\'<meta property="og:url" content=""> is empty. Falling back to window.location\');
					}
					} else {
					console.warn(\'<meta property="og:url" content=""> is missing. Falling back to window.location\');
					}
					//Send AJAX
					httpRequest.send("scrape=true&id=" + encodeURIComponent(url));
					}, {perms:\'read_stream,publish_stream,offline_access\'});
					});
					} else {
					console.warn("Your browser doesn\'t support one of the following: document.addEventListener && window.XMLHttpRequest && document.querySelector");
					}
					}</script></body></html>';
				echo wp_kses_post( $html_contents );
				die;
			}
		}

		/**
		 * Credit log pagination.
		 */
		public function woocommerceMultilevelReferralCreditLogPagination() {
			check_ajax_referer( 'woocommerce_multilevel_referral_ajax_nonce_action', 'security' );
			$response = array();
			$pageno   = ( isset( $_POST['pageno'] ) ? absint( wp_unslash( $_POST['pageno'] ) ) : '' );
			if ( ! empty( $pageno ) ) {
				$current_user_id                             = get_current_user_id();
				$woocommerce_multilevel_referral_program     = WooCommerce_Multilevel_Referral_Program::get_instance();
				$data                                        = array(
					'records' => $woocommerce_multilevel_referral_program->select_all( 10, $pageno, $current_user_id ),
				);
				$all_items                                   = $woocommerce_multilevel_referral_program->getall_count( 0, 1, $current_user_id );
				$total_items                                 = $all_items[0]->records_count;
				$woocommerce_multilevel_referral_html_credit = '';
				if ( count( $data['records'] ) > 0 ) {
					$woocommerce_multilevel_referral_html_credit .= '<tr>
							<!--th>' . __( 'Order', 'multilevel-referral-plugin-for-woocommerce' ) . '</th-->
							<th>' . __( 'Date', 'multilevel-referral-plugin-for-woocommerce' ) . '</th>
							<th>' . __( 'Note', 'multilevel-referral-plugin-for-woocommerce' ) . '</th>
							</tr>';
					foreach ( $data['records'] as $row ) {
						$note  = '';
						$order = wc_get_order( $row['order_id'] );
						if ( ! is_bool( $order ) && $row['credits'] > 0 ) {
							$credits = wc_price( apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', $row['credits'] ) );
							if ( $order->get_user_id() === $row['user_id'] ) {
								if ( $order->get_status() === 'cancelled' || $order->get_status() === 'refunded' || $order->get_status() === 'failed' ) {
									// translators: 1: Credit amount, 2: Order ID.
									$translated_text = __( '%1$s Store credit is refund for order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
									$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_refund_credits', $translated_text ), $credits, '#' . $row['order_id'] );
								} else {
									// translators: 1: Credit amount, 2: Order ID.
									$translated_text = __( '%1$s Store credit is earned from order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
									$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_earned_credits', $translated_text ), $credits, '#' . $row['order_id'] );
								}
							} else {
								// translators: 1: Credit amount, 2: User name, 3: Order ID.
								$translated_text = __( '%1$s Store credit is earned through referral user ( %2$s order %3$s )  ', 'multilevel-referral-plugin-for-woocommerce' );
								$note            = sprintf(
									apply_filters( 'woocommerce_multilevel_referral_store_earned_credits_by_referral', $translated_text ),
									$credits,
									get_user_meta( $order->get_user_id(), 'first_name', true ) . ' ' . get_user_meta( $order->get_user_id(), 'last_name', true ),
									'#' . $row['order_id']
								);
							}
						}
						if ( ! is_bool( $order ) && $row['redeems'] > 0 ) {
							$redeems = wc_price( apply_filters( 'woocommerce_multilevel_referral_total_redeems_amount', $row['redeems'] ) );
							if ( $order->get_status() === 'cancelled' || $order->get_status() === 'refunded' || $order->get_status() === 'failed' ) {
								// translators: 1: Redeem amount, 2: Order ID.
								$translated_text = __( '%1$s Store credit is refund for order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
								$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_refund_credits', $translated_text ), $redeems, '#' . $row['order_id'] );
							} elseif ( $row['order_id'] ) {
								// translators: 1: Redeem amount, 2: Order ID.
								$translated_text = __( '%1$s Store credit is used in order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
								$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_used_credits', $translated_text ), $redeems, '#' . $row['order_id'] );
							} else {
								// translators: %s is the redeems.
								$note = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_expired_credits', __( '%s Store credit is expired.', 'multilevel-referral-plugin-for-woocommerce' ) ), $redeems );
							}
						}
						if ( 0 === $row['order_id'] ) {
							$credits = wc_price( apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', $row['credits'] ) );
							// translators: %s is the credits.
							$note = sprintf( __( 'You have %s credits for registration to the site.', 'multilevel-referral-plugin-for-woocommerce' ), $credits );
						}
						$note = apply_filters( 'woocommerce_multilevel_referral_credit_logs_notes', $note, $row );
						$woocommerce_multilevel_referral_html_credit .= '<tr>
							<!--td><a htref="">#' . $row['order_id'] . '</a></td-->
							<td data-title="' . __( 'Date', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . date_i18n( 'M d, Y', strtotime( $row['date'] ) ) . '</td>
							<td data-title="' . __( 'Note', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . $note . '</td>
							</tr>';
					}
					$page             = $pageno;
					$page             = ( 0 === $page ? 1 : $page );
					$records_per_page = 10;
					$start            = ( $page - 1 ) * $records_per_page;
					$adjacents        = 2;
					$prev             = $page - 1;
					$next             = $page + 1;
					$lastpage         = ceil( $total_items / $records_per_page );
					$lpm1             = $lastpage - 1;
					$pagination       = '';
					if ( $lastpage > 1 ) {
						if ( $page > 1 ) {
							$pagination .= "<button class='page-link button prev' data-page='" . $prev . "' >&laquo; Previous</button>";
						} else {
							$pagination .= "<button class='disabled prev'>&laquo; Previous</button>";
						}
						if ( $lastpage < 7 + $adjacents * 2 ) {
							for ( $counter = 1; $counter <= $lastpage; $counter++ ) {
								if ( $page === $counter ) {
									$pagination .= "<button class='current button'>{$counter}</button>";
								} else {
									$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
								}
							}
						} elseif ( $lastpage > 5 + $adjacents * 2 ) {
							if ( $page < 1 + $adjacents * 2 ) {
								for ( $counter = 1; $counter < 2 + $adjacents * 2; $counter++ ) {
									if ( $page === $counter ) {
										$pagination .= "<button class='current button'>{$counter}</button>";
									} else {
										$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
									}
								}
								$pagination .= '...';
								$pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
								$pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
							} elseif ( $lastpage - $adjacents * 2 > $page && $page > $adjacents * 2 ) {
								$pagination .= "<button class='page-link button' data-page='1' >1</button>";
								$pagination .= "<button class='page-link button' data-page='2' >2</button>";
								$pagination .= '...';
								for ( $counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++ ) {
									if ( $page === $counter ) {
										$pagination .= "<button class='current button'>{$counter}</button>";
									} else {
										$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
									}
								}
								$pagination .= '..';
								$pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
								$pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
							} else {
								$pagination .= "<button class='page-link button' data-page='1' >1</button>";
								$pagination .= "<button class='page-link button' data-page='2' >2</button>";
								$pagination .= '..';
								for ( $counter = $lastpage - ( 2 + $adjacents * 2 ); $counter <= $lastpage; $counter++ ) {
									if ( $page === $counter ) {
										$pagination .= "<button class='current button'>{$counter}</button>";
									} else {
										$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
									}
								}
							}
						}
						if ( $page < $counter - 1 ) {
							$pagination .= "<button class='page-link button next' data-page='" . $next . "' >Next &raquo;</button>";
						} else {
							$pagination .= "<button class='disabled next'>Next &raquo;</button>";
						}
					}
				} else {
					$woocommerce_multilevel_referral_html_credit .= '<p class="help">' . __( 'No records found.', 'multilevel-referral-plugin-for-woocommerce' ) . '</p>';
				}
			}
			$response['data']       = $woocommerce_multilevel_referral_html_credit;
			$response['pagination'] = $pagination;
			echo wp_json_encode( $response );
			exit;
		}

		/**
		 * Change banner.
		 */
		public function woocommerceMultilevelReferralChangeBanner() {
			check_ajax_referer( 'woocommerce_multilevel_referral_ajax_nonce_action', 'security' );
			global $wpdb, $woocommerce_multilevel_referral_is_transient_banner;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$code      = $wpdb->get_var( $wpdb->prepare( "SELECT referral_code FROM {$wpdb->prefix}referal_users WHERE user_id =%d", get_current_user_id() ) );
			$user_id   = get_current_user_id();
			$response  = array();
			$b_title   = ( isset( $_POST['bTitle'] ) ? sanitize_text_field( wp_unslash( $_POST['bTitle'] ) ) : '' );
			$b_desc    = ( isset( $_POST['bDesc'] ) ? wp_kses_post( wp_unslash( $_POST['bDesc'] ) ) : '' );
			$attach_id = ( isset( $_POST['attachId'] ) && '' !== $_POST['attachId'] ? absint( wp_unslash( $_POST['attachId'] ) ) : 0 );
			$img_url   = '';
			if ( $attach_id ) {
				$banner_path   = get_attached_file( absint( wp_unslash( $_POST['attachId'] ) ) );
				$referral_code = __( 'Referral Code : ', 'multilevel-referral-plugin-for-woocommerce' );
				if ( $code ) {
					$referral_code .= $code;
				}
				$img_url          = $this->writeTextonImage(
					$referral_code,
					$banner_path,
					$user_id,
					$attach_id
				);
				$response['type'] = 'success';
			} else {
				$response['type'] = 'failed';
			}
			$response['imageURL'] = $this->woocommerceMultilevelReferralGetFullUrl( $img_url . '?t=' . time() );
			if ( $woocommerce_multilevel_referral_is_transient_banner ) {
				$woocommerce_multilevel_referral_is_transient_banner = false;
				return $img_url;
			}
			echo wp_json_encode( $response );
			exit;
		}

		/**
		 * Save transient banner.
		 */
		public function woocommerceMultilevelReferralSaveTransientBanner() {
			check_ajax_referer( 'woocommerce_multilevel_referral_ajax_nonce_action', 'security' );
			global $woocommerce_multilevel_referral_is_transient_banner;
			$woocommerce_multilevel_referral_is_transient_banner = true;
			$user_id   = get_current_user_id();
			$response  = array();
			$b_title   = ( isset( $_POST['bTitle'] ) ? sanitize_text_field( wp_unslash( $_POST['bTitle'] ) ) : '' );
			$b_desc    = ( isset( $_POST['bDesc'] ) ? wp_kses_post( wp_unslash( $_POST['bDesc'] ) ) : '' );
			$attach_id = ( isset( $_POST['attachId'] ) && '' !== $_POST['attachId'] ? absint( wp_unslash( $_POST['attachId'] ) ) : 0 );
			if ( $attach_id ) {
				$img_url = $this->woocommerceMultilevelReferralChangeBanner();
				set_transient(
					'woocommerce_multilevel_referral_banner_' . $user_id . '_' . $attach_id,
					array(
						'title'    => $b_title,
						'desc'     => $b_desc,
						'imageURL' => $img_url,
					),
					60 * 60 * 1
				);
				$response['type'] = 'success';
			} else {
				$response['type'] = 'failed';
			}
			echo wp_json_encode( $response );
			exit;
		}

		/**
		 * Rewrite rules for referral banner.
		 */
		public function woocommerceMultilevelReferralRewrite() {
			add_rewrite_rule( '^woocommerce_multilevel_referral_banner$', 'index.php?woocommerce_multilevel_referral_banner=$1', 'top' );
			if ( get_transient( 'vpt_flush' ) ) {
				delete_transient( 'vpt_flush' );
				flush_rewrite_rules();
			}
		}

		/**
		 * Show the logged in users affiliate user list.
		 */
		public function woocommerceMultilevelReferralShowMyAffiliates() {
			global $wpdb;
			$woocommerce_multilevel_referral_html = '';
			$url_filter                           = site_url();
			$myaccount_page                       = get_option( 'woocommerce_myaccount_page_id' );
			$url_data                             = woocommerce_multilevel_referral_get_query_vars();
			if ( is_user_logged_in() && in_the_loop() && is_page( $myaccount_page ) ) {
				$url_filter = get_permalink( $myaccount_page ) . 'my-affliates/';
			}
			$active_sel = '';
			if ( isset( $url_data['filter'] ) ) {
				$active_sel = sanitize_text_field( wp_unslash( $url_data['filter'] ) );
			}
			$active_order = '';
			if ( isset( $url_data['orderby'] ) ) {
				$active_order = sanitize_text_field( wp_unslash( $url_data['orderby'] ) );
			}
			if ( is_user_logged_in() ) {
				$check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
				if ( $check_user ) {
					$myaccount_page                          = get_option( 'woocommerce_myaccount_page_id' );
					$current_user_id                         = get_current_user_id();
					$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
					$data                                    = array(
						'referral_code'   => $this->referral_user( 'referral_code', 'user_id', $current_user_id ),
						'total_points'    => $woocommerce_multilevel_referral_program->available_credits( $current_user_id ),
						'total_followers' => $this->fnGetFollowersCount( $current_user_id ),
					);
					$active_panel                            = 'referral-share-invite';
					if ( isset( $url_data['tab'] ) && 'referral-affiliates' === $url_data['tab'] ) {
						$active_panel    = 'referral-affiliates';
						$data['content'] = $this->woocommerceMultilevelReferralShowMyAffiliates();
					} else {
						$data['content'] = do_shortcode( '[woocommerce_multilevel_referral_invite_friends]', true );
					}
					$data['page_url']     = get_permalink( $myaccount_page );
					$data['active_panel'] = $active_panel;
				}
			}
			$arr_bread_crumb = array();
			$check_user      = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
			if ( $check_user ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$get_min_date = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(join_date) FROM {$wpdb->prefix}referal_users where user_id=%d", get_current_user_id() ) );
				$get_min_date = gmdate( 'Y-m-01 H:i:s', strtotime( $get_min_date ) );
				$date_ranges  = $this->dateRange(
					$get_min_date,
					gmdate( 'Y-m-d H:i:s' ),
					'+1 month',
					'Y-m-d'
				);
				$this_month   = gmdate( 'Y-m-d', strtotime( 'first day of this month' ) );
				if ( ! in_array( $this_month, $date_ranges, true ) ) {
					array_push( $date_ranges, $this_month );
				}
				$woocommerce_multilevel_referral_html .= '<div class="woocommerce-multilevel-referral-show-affiliates">';
				$woocommerce_multilevel_referral_html .= '<h2>' . __( 'My Affiliates', 'multilevel-referral-plugin-for-woocommerce' ) . '</h2>';
				$woocommerce_multilevel_referral_html .= '<div class="affliate-filter"><div class="filter_date"><label>' . __( 'Filter by', 'multilevel-referral-plugin-for-woocommerce' ) . '</label>';
				$woocommerce_multilevel_referral_html .= '<select id="my-affilicate_filters" data_url="' . $url_filter . '"><option value="">' . __( 'All', 'multilevel-referral-plugin-for-woocommerce' ) . '</option>';
				foreach ( $date_ranges as $key => $value ) {
					$val_date_formate                      = date_format( date_create( $value ), 'y-m-d' );
					$woocommerce_multilevel_referral_html .= '<option value="' . $val_date_formate . '" ' . ( ( isset( $url_data['filter'] ) && $url_data['filter'] === $val_date_formate ? 'selected' : '' ) ) . ' >' . date_format( date_create( $value ), 'M-Y' ) . '</option>';
				}
				$woocommerce_multilevel_referral_html .= '</select></div>';
				$woocommerce_multilevel_referral_html .= '<div class="filter_order"><label> Order by </label><select name="orderby" id="order_by_filter">';
				$woocommerce_multilevel_referral_html .= '<option value="asc" ' . ( ( 'asc' === $active_order ? 'selected' : '' ) ) . ' >' . __( 'Asc', 'multilevel-referral-plugin-for-woocommerce' ) . '</option><option value="desc" ' . ( ( 'desc' === $active_order ? 'selected' : '' ) ) . '>' . __( 'Desc', 'multilevel-referral-plugin-for-woocommerce' ) . '</option>';
				$woocommerce_multilevel_referral_html .= '</select></div></div>';
				$woocommerce_multilevel_referral_html .= '<table class="shop_table shop_table_responsive">';
				$woocommerce_multilevel_referral_html .= '<thead><tr><th align="center">' . __( 'Show/Hide', 'multilevel-referral-plugin-for-woocommerce' ) . '</th><th align="center">' . __( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ) . '</th><th align="center">' . __( 'Name', 'multilevel-referral-plugin-for-woocommerce' ) . '</th><th align="right">' . __( 'Referrals', 'multilevel-referral-plugin-for-woocommerce' ) . '</th><!--th>' . __( 'Affiliates Credit', 'multilevel-referral-plugin-for-woocommerce' ) . '</th--><th align="center">' . __( 'Join Date', 'multilevel-referral-plugin-for-woocommerce' ) . '</th></tr></thead>';
				$return_html                           = $this->woocommerceMultilevelReferralGetAffliateUsersList( $check_user );
				$woocommerce_multilevel_referral_html .= $return_html;
				if ( '' === $return_html ) {
					$woocommerce_multilevel_referral_html .= '<tr class="affliate-note"><td colspan="6"><p class="help">' . __( 'Could not find any affiliate users. Please invite more friends and colleagues to start earning credit points.', 'multilevel-referral-plugin-for-woocommerce' ) . '</p></td></tr>';
				} else {
					$woocommerce_multilevel_referral_html .= '<tr class="affliate-note"><td colspan="6"><p class="help"><Strong>' . __( 'Affiliates : ', 'multilevel-referral-plugin-for-woocommerce' ) . '</strong>' . __( 'This particular column shows the number of Affiliates for the corresponding affiliate member.', 'multilevel-referral-plugin-for-woocommerce' ) . '</p></td></tr>';
				}
				$woocommerce_multilevel_referral_html .= '</table>';
				$woocommerce_multilevel_referral_html .= '</div>';
			}
			return $woocommerce_multilevel_referral_html;
		}

		/**
		 * Get date range.
		 *
		 * @param string $first First date.
		 * @param string $last Last date.
		 * @param string $step Step.
		 * @param string $format Format.
		 *
		 * @return array Date range.
		 */
		public function dateRange(
			$first,
			$last,
			$step = '+1 day',
			$format = 'Y/m/d'
		) {
			$dates   = array();
			$current = strtotime( $first );
			$last    = strtotime( $last );
			while ( $current <= $last ) {
				$dates[] = gmdate( $format, $current );
				$current = strtotime( $step, $current );
			}
			return $dates;
		}

		/**
		 * Get affiliate users list.
		 *
		 * @param int    $parent_id Parent ID.
		 * @param array  $woocommerce_multilevel_referral_arr_class Array class.
		 * @param string $back_color Background color.
		 * @param string $rhtml HTML.
		 * @param bool   $is_recursive Is recursive.
		 *
		 * @return string HTML.
		 */
		public function woocommerceMultilevelReferralGetAffliateUsersList(
			$parent_id,
			$woocommerce_multilevel_referral_arr_class = array(),
			$back_color = '',
			$rhtml = '',
			$is_recursive = false
		) {
			if ( empty( $parent_id ) ) {
				return $rhtml;
			}
			if ( ! $is_recursive ) {
				$this->affiliate_visited = array();
			}
			if ( in_array( $parent_id, $this->affiliate_visited, true ) ) {
				return $rhtml;
			}
			$this->affiliate_visited[] = $parent_id;
			global $wpdb;
			$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
			$url_data                                = woocommerce_multilevel_referral_get_query_vars();
			$get_filter                              = ( isset( $url_data['filter'] ) ? sanitize_text_field( wp_unslash( $url_data['filter'] ) ) : 'none' );
			$referral_users                          = $woocommerce_multilevel_referral_program->get_referral_user_list( $parent_id, $get_filter );
			if ( is_array( $referral_users ) && count( $referral_users ) > 0 ) {
				foreach ( $referral_users as $key => $affiliate ) {
					$followers  = $this->fnGetFollowersCount( $affiliate->user_id );
					$class_name = '';
					if ( get_current_user_id() !== $parent_id && false === strpos( $class_name, 'woocommerce-multilevel-referral-child ' ) ) {
						$class_name = 'woocommerce-multilevel-referral-child';
					}
					if ( ! in_array( $parent_id, $woocommerce_multilevel_referral_arr_class, true ) ) {
						array_push( $woocommerce_multilevel_referral_arr_class, $parent_id );
					}
					$opacity = 1 / count( $woocommerce_multilevel_referral_arr_class );
					if ( get_current_user_id() === $parent_id ) {
						if ( 0 !== $key % 2 ) {
							$woocommerce_multilevel_referral_back_color = '230,230,230';
						} else {
							$woocommerce_multilevel_referral_back_color = '178,229,255';
						}
						$opacity = 1;
					}
					$woocommerce_multilevel_referral_finder = implode( '-', $woocommerce_multilevel_referral_arr_class );
					$class_name                            .= ' woocommerce-multilevel-referral-child-' . $woocommerce_multilevel_referral_finder;
					$user_info                              = get_userdata( $affiliate->user_id );
					$args                                   = array(
						'customer_id' => $affiliate->user_id,
					);
					$orders                                 = wc_get_orders( $args );
					$credits                                = 0;
					$order_ids                              = array();
					$tbl_referal_program                    = $wpdb->prefix . 'referal_program';
					foreach ( $orders as $key => $value ) {
						$order_id    = $value->get_id();
						$order_ids[] = $order_id;
					}
					$order_id = implode( ',', $order_ids );
					if ( ! empty( $order_id ) ) {
						$order_ids_array = array_map( 'intval', explode( ',', $order_id ) );
						$placeholders    = implode( ',', array_fill( 0, count( $order_ids_array ), '%d' ) );
						$query           = "SELECT sum(credits) as credit FROM {$wpdb->prefix}referal_program WHERE order_id IN ({$placeholders}) AND user_id = %d";
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql = $wpdb->prepare( $query, array_merge( $order_ids_array, array( (int) $affiliate->user_id ) ) );
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
						$credits_res = $wpdb->get_var( $sql );
					} else {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$credits_res = $wpdb->get_var( $wpdb->prepare( "SELECT sum(credits) as credit FROM {$wpdb->prefix}referal_program WHERE user_id =%d", $affiliate->user_id ) );
					}
					if ( $credits_res ) {
						$credits = $credits_res;
					}
					$display_cls = '';
					$cur_class   = 'woocommerce-multilevel-referral-child woocommerce-multilevel-referral-child-' . get_current_user_id();
					if ( $class_name === $cur_class ) {
						$display_cls = ' show_me';
					}
					$rhtml .= '<tr class="' . $class_name . $display_cls . '">';
					if ( intval( $affiliate->followers ) > 0 ) {
						$rhtml .= '<td align="center" data-title="' . __( 'Show/Hide', 'multilevel-referral-plugin-for-woocommerce' ) . '" class="view_hierarchie"><a href="javascript:void(0)" data-finder="' . $woocommerce_multilevel_referral_finder . '-' . $affiliate->user_id . '" class="view_hierarchie">' . __( 'View Hierarchy', 'multilevel-referral-plugin-for-woocommerce' ) . '  </a></td>';
					} else {
						$rhtml .= '<td align="center" data-title="' . __( 'Show/Hide', 'multilevel-referral-plugin-for-woocommerce' ) . '">-</td>';
					}
					$rhtml .= '<td  align="center" data-title="' . __( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . $this->referral_user( 'referral_code', 'user_id', $affiliate->user_id ) . '</td><td data-title="' . __( 'Name', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . $affiliate->first_name . '&nbsp;' . $affiliate->last_name . '</td><td align="right" data-title="' . __( 'Affiliates', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . $followers . '</td><!--td align="right" data-title="' . __( 'Affiliates Credit', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . number_format( $credits, 2 ) . '</td--><td align="right" data-title="' . __( 'Join Date', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . $user_info->data->user_registered . '</td>';
					$rhtml .= '</tr>';
					if ( intval( $affiliate->followers ) > 0 ) {
						$rhtml .= $this->woocommerceMultilevelReferralGetAffliateUsersList(
							$affiliate->user_id,
							$woocommerce_multilevel_referral_arr_class,
							$back_color,
							'',
							true
						);
					}
				}
			}
			return $rhtml;
		}

		/**
		 * Referral user credit info.
		 *
		 * @return string Credit info HTML.
		 */
		public function referral_user_credit_info() {
			if ( is_user_logged_in() ) {
				global $woocommerce_multilevel_referral_invitation_error;
				$check_user                                  = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
				$woocommerce_multilevel_referral_html_credit = '<div class="woocommerce-multilevel-referral-show-credits">';
				if ( $check_user ) {
					$current_user_id                         = $check_user;
					$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
					$total_items                             = 0;
					$all_items                               = $woocommerce_multilevel_referral_program->getall_count( 0, 1, $current_user_id );
					if ( ! empty( $all_items ) && isset( $all_items[0]->records_count ) ) {
						$total_items = $all_items[0]->records_count;
					}
					if ( ! isset( $_POST['referral-invite-friends-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['referral-invite-friends-nonce'] ) ), 'referral-invite-friends' ) ) {
						return;
					}
					$data = array(
						'referral_code'   => $this->referral_user( 'referral_code', 'user_id', $current_user_id ),
						'total_points'    => $woocommerce_multilevel_referral_program->available_credits( $current_user_id ),
						'total_followers' => $this->fnGetFollowersCount( $current_user_id ),
						'records'         => $woocommerce_multilevel_referral_program->select_all( 10, 1, $current_user_id ),
						'emails'          => ( isset( $_POST['emails'] ) ? sanitize_text_field( wp_unslash( $_POST['emails'] ) ) : '' ),
					);
					$woocommerce_multilevel_referral_html_credit .= '<h2>' . __( 'Credit Points Log', 'multilevel-referral-plugin-for-woocommerce' ) . '</h2>';
					if ( count( $data['records'] ) > 0 ) {
						$woocommerce_multilevel_referral_html_credit .= '<div class="woocommerce_multilevel_referral_main_table_wrap" > <div class="loader_main" ><div id="loader"></div></div>';
						$woocommerce_multilevel_referral_html_credit .= '<table class="shop_table shop_table_responsive my_account_orders">
							<tr>
							<!--th>' . __( 'Order', 'multilevel-referral-plugin-for-woocommerce' ) . '</th-->
							<th>' . __( 'Date', 'multilevel-referral-plugin-for-woocommerce' ) . '</th>
							<th>' . __( 'Note', 'multilevel-referral-plugin-for-woocommerce' ) . '</th>
							</tr>';
						foreach ( $data['records'] as $row ) {
							$note  = '';
							$order = wc_get_order( $row['order_id'] );
							if ( ! is_bool( $order ) && $row['credits'] > 0 ) {
								$credits = wc_price( apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', $row['credits'] ) );
								if ( $order->get_user_id() === $row['user_id'] ) {
									if ( $order->get_status() === 'cancelled' || $order->get_status() === 'refunded' || $order->get_status() === 'failed' ) {
										// translators: 1: Credit amount, 2: Order ID.
										$translated_text = __( '%1$s Store credit is refund for order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
										$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_refund_credits', $translated_text ), $credits, '#' . $row['order_id'] );
									} else {
										// translators: 1: Credit amount, 2: Order ID.
										$translated_text = __( '%1$s Store credit is earned from order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
										$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_earned_credits', $translated_text ), $credits, '#' . $row['order_id'] );
									}
								} else {
									// translators: 1: Credit amount, 2: User name, 3: Order ID.
									$translated_text = __( '%1$s Store credit is earned through referral user ( %2$s order %3$s )  ', 'multilevel-referral-plugin-for-woocommerce' );
									$note            = sprintf(
										apply_filters( 'woocommerce_multilevel_referral_store_earned_credits_by_referral', $translated_text ),
										$credits,
										get_user_meta( $order->get_user_id(), 'first_name', true ) . ' ' . get_user_meta( $order->get_user_id(), 'last_name', true ),
										'#' . $row['order_id']
									);
								}
							}
							if ( ! is_bool( $order ) && $row['redeems'] > 0 ) {
								$redeems = wc_price( apply_filters( 'woocommerce_multilevel_referral_total_redeems_amount', $row['redeems'] ) );
								if ( $order->get_status() === 'cancelled' || $order->get_status() === 'refunded' || $order->get_status() === 'failed' ) {
									// translators: 1: Redeem amount, 2: Order ID.
									$translated_text = __( '%1$s Store credit is refund for order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
									$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_refund_credits', $translated_text ), $redeems, '#' . $row['order_id'] );
								} elseif ( $row['order_id'] ) {
									// translators: 1: Redeem amount, 2: Order ID.
									$translated_text = __( '%1$s Store credit is used in order %2$s.', 'multilevel-referral-plugin-for-woocommerce' );
									$note            = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_used_credits', $translated_text ), $redeems, '#' . $row['order_id'] );
								} else {
									// translators: %s is the redeems.
									$note = sprintf( apply_filters( 'woocommerce_multilevel_referral_store_expired_credits', __( '%s Store credit is expired.', 'multilevel-referral-plugin-for-woocommerce' ) ), $redeems );
								}
							}
							if ( 0 === $row['order_id'] ) {
								$credits = wc_price( apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', $row['credits'] ) );
								// translators: %s is the credits.
								$note = sprintf( __( 'You have %s credits for registration to the site.', 'multilevel-referral-plugin-for-woocommerce' ), $credits );
							}
							$note = apply_filters( 'woocommerce_multilevel_referral_credit_logs_notes', $note, $row );
							$woocommerce_multilevel_referral_html_credit .= '<tr>
								<!--td><a htref="">#' . $row['order_id'] . '</a></td-->
								<td data-title="' . __( 'Date', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . date_i18n( 'M d, Y', strtotime( $row['date'] ) ) . '</td>
								<td data-title="' . __( 'Note', 'multilevel-referral-plugin-for-woocommerce' ) . '">' . $note . '</td>
								</tr>';
						}
						$woocommerce_multilevel_referral_html_credit .= '</table> </div>';
						$woocommerce_multilevel_referral_html_credit .= '</br>';
						$page             = 1;
						$page             = ( 0 === $page ? 1 : $page );
						$records_per_page = 10;
						$start            = ( $page - 1 ) * $records_per_page;
						$adjacents        = 2;
						$prev             = $page - 1;
						$next             = $page + 1;
						$lastpage         = ceil( $total_items / $records_per_page );
						$lpm1             = $lastpage - 1;
						$pagination       = '';
						if ( $lastpage > 1 ) {
							$pagination .= "<div class='pagination'>";
							if ( $page > 1 ) {
								$pagination .= "<button class='page-link button prev' data-page='" . $prev . "' >&laquo; Previous</button>";
							} else {
								$pagination .= "<button class='disabled prev'>&laquo; Previous</button>";
							}
							if ( $lastpage < 7 + $adjacents * 2 ) {
								for ( $counter = 1; $counter <= $lastpage; $counter++ ) {
									if ( $page === $counter ) {
										$pagination .= "<button class='current button'>{$counter}</button>";
									} else {
										$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
									}
								}
							} elseif ( $lastpage > 5 + $adjacents * 2 ) {
								if ( $page < 1 + $adjacents * 2 ) {
									for ( $counter = 1; $counter < 2 + $adjacents * 2; $counter++ ) {
										if ( $page === $counter ) {
											$pagination .= "<button class='current button'>{$counter}</button>";
										} else {
											$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
										}
									}
									$pagination .= '...';
									$pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
									$pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
								} elseif ( $lastpage - $adjacents * 2 > $page && $page > $adjacents * 2 ) {
									$pagination .= "<button class='page-link button' data-page='1' >1</button>";
									$pagination .= "<button class='page-link button' data-page='2' >2</button>";
									$pagination .= '...';
									for ( $counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++ ) {
										if ( $page === $counter ) {
											$pagination .= "<button class='current button'>{$counter}</button>";
										} else {
											$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
										}
									}
									$pagination .= '..';
									$pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
									$pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
								} else {
									$pagination .= "<button class='page-link button' data-page='1' >1</button>";
									$pagination .= "<button class='page-link button' data-page='2' >2</button>";
									$pagination .= '..';
									for ( $counter = $lastpage - ( 2 + $adjacents * 2 ); $counter <= $lastpage; $counter++ ) {
										if ( $page === $counter ) {
											$pagination .= "<button class='current button'>{$counter}</button>";
										} else {
											$pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
										}
									}
								}
							}
							if ( $page < $counter - 1 ) {
								$pagination .= "<button class='page-link button next' data-page='" . $next . "' >Next &raquo;</button>";
							} else {
								$pagination .= "<button class='disabled next'>Next &raquo;</button>";
							}
							$pagination .= '</div>';
						}
						$woocommerce_multilevel_referral_html_credit .= $pagination;
						$woocommerce_multilevel_referral_html_credit .= '<button class="page-link button">&raquo;</button>';
						$woocommerce_multilevel_referral_html_credit .= '</div>';
					}
				} else {
					$woocommerce_multilevel_referral_html_credit .= '<p class="help">' . __( 'No records found.', 'multilevel-referral-plugin-for-woocommerce' ) . '</p>';
				}
			}
			$woocommerce_multilevel_referral_html_credit .= '</div>';
			$woocommerce_multilevel_referral_html_credit  = apply_filters(
				'woocommerce_multilevel_referral_store_credits_contents',
				$woocommerce_multilevel_referral_html_credit,
				$data,
				$check_user
			);
			return $woocommerce_multilevel_referral_html_credit;
		}

		/**
		 * Send invitation to others to join Referral Program.
		 *
		 * @throws Exception Exception.
		 */
		public function send_invitation() {
			global $woocommerce_multilevel_referral_customer_id, $woocommerce_multilevel_referral_code, $woocommerce_multilevel_referral_invitation_error;
			try {
				// WP Validation.
				$validation_errors                                = new WP_Error();
				$woocommerce_multilevel_referral_invitation_error = false;
				if ( isset( $_POST['action'] ) && 'send_invitations' === $_POST['action'] ) {
					if ( ! isset( $_POST['referral-invite-friends-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['referral-invite-friends-nonce'] ) ), 'referral-invite-friends' ) ) {
						wp_die( 'Nonce verification failed!' );
					}
					unset( $_POST['action'] );
					if ( empty( $_POST['emails'] ) ) {
						throw new Exception( __( 'Please enter a valid E-mail address.', 'multilevel-referral-plugin-for-woocommerce' ) );
					}
					$email_array                                 = explode( ',', sanitize_text_field( wp_unslash( $_POST['emails'] ) ) );
					$woocommerce_multilevel_referral_customer_id = get_current_user_id();
					WC()->mailer();
					$current_user                         = wp_get_current_user();
					$email                                = $current_user->user_email;
					$first_name                           = $current_user->user_firstname;
					$last_name                            = $current_user->user_lastname;
					$woocommerce_multilevel_referral_code = $this->referral_user( 'referral_code', 'user_id', $woocommerce_multilevel_referral_customer_id );
					$invalid_arrray                       = array();
					$exist_email_array                    = array();
					$success_mail                         = false;
					foreach ( $email_array as $email ) {
						// check exist user join with program.
						// Referral user mail.
						$check_user = $this->user_join_referral_program( $email );
						if ( '' !== $email ) {
							if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) && email_exists( $email ) && $check_user ) {
								$exist_email_array[] = $email;
							} elseif ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
								$success_mail = true;
								do_action(
									'woocommerce_multilevel_referral_joining_user_notification',
									$email,
									$first_name,
									$last_name,
									$woocommerce_multilevel_referral_code,
									'referral_user',
									$woocommerce_multilevel_referral_customer_id
								);
							} else {
								$invalid_arrray[] = $email;
							}
						}
					}
					if ( count( $exist_email_array ) > 0 ) {
						$email_list     = '<ul><li>' . implode( '</li><li>', $exist_email_array ) . '</li></ul>';
						$message_part_1 = __( 'The user is already part of our referral program, please try with different E-mail address.', 'multilevel-referral-plugin-for-woocommerce' );
						throw new Exception( $message_part_1 . $email_list );
					}
					if ( ! $success_mail ) {
						$message_part_2 = __( 'E-mail address is invalid.', 'multilevel-referral-plugin-for-woocommerce' );
						throw new Exception( $message_part_2 );
					}
					if ( count( $invalid_arrray ) > 0 ) {
						$email_list     = '<ul><li>' . implode( '</li><li>', $invalid_arrray ) . '</li></ul>';
						$message_part_3 = __( 'We can not send invitation to below listed E-mail addresses.', 'multilevel-referral-plugin-for-woocommerce' );
						throw new Exception( $message_part_3 . $email_list );
					}
					wc_add_notice( __( 'Your invitations are sent succesfully!', 'multilevel-referral-plugin-for-woocommerce' ) );
				}
			} catch ( Exception $e ) {
				$woocommerce_multilevel_referral_invitation_error = true;
				wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
			}
		}

		/**
		 * User join Referral Program.
		 *
		 * @param string $email Email address.
		 *
		 * @return bool
		 */
		public function user_join_referral_program( $email ) {
			if ( email_exists( $email ) ) {
				global $wpdb;
				$user = get_user_by( 'email', $email );
				if ( $user ) {
					$user_id = $user->ID;
					$sql     = 'SELECT id FROM ' . $this->table_name . ' WHERE user_id = ' . $user_id;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$checkval = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}referal_users WHERE user_id = %d", $user_id ) );
					if ( $checkval ) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Hander for late join Referral Program.
		 *
		 * @return void
		 * @throws Exception Exception.
		 **/
		public function join_referral_program() {
			try {
				// WP Validation.
				$validation_errors = new WP_Error();
				if ( isset( $_POST['join_referral_program'] ) && isset( $_POST['referral_registration_validation_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['referral_registration_validation_nonce'] ) ), 'referral_program' ) ) {
					$validation_errors = $this->referral_registration_validation( null, null, $validation_errors );
					if ( $validation_errors->get_error_code() ) {
						unset( $_POST['_wpnonce'] );
						throw new Exception( $validation_errors->get_error_message() );
					}
					$this->referral_customer_save_data( get_current_user_id() );
					wc_add_notice( __( 'Thanks for joining the referral program', 'multilevel-referral-plugin-for-woocommerce' ) );
					unset( $_POST['_wpnonce'] );
				}
			} catch ( Exception $e ) {
				wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
			}
		}

		/**
		 * Validate the extra register fields.
		 *
		 * @param  string $username          Current username.
		 * @param  string $email             Current email.
		 * @param  object $validation_errors WP_Error object.
		 *
		 * @return void
		 */
		public function referral_registration_validation( $username, $email, $validation_errors ) {
			if ( ! isset( $_POST['referral_registration_validation_nonce'] ) ) {
				return;
			}
			if ( ! isset( $_POST['referral_registration_validation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['referral_registration_validation_nonce'] ) ), 'referral_program' ) ) {
				wp_die( 'Nonce verification failed!' );
			}
			$auto_join = get_option( 'woocommerce_multilevel_referral_auto_register', 'no' );
			if ( isset( $_POST['billing_first_name'] ) && '' === $_POST['billing_first_name'] ) {
				$validation_errors->add( 'empty required fields', __( 'Please enter the First name.', 'multilevel-referral-plugin-for-woocommerce' ) );
			}
			if ( isset( $_POST['billing_last_name'] ) && '' === $_POST['billing_last_name'] ) {
				$validation_errors->add( 'empty required fields', __( 'Please enter the Last name.', 'multilevel-referral-plugin-for-woocommerce' ) );
			}
			if ( isset( $_POST['referral_code'] ) && '' === $_POST['referral_code'] && isset( $_POST['join_referral_program'] ) && 1 === (int) $_POST['join_referral_program'] ) {
				if ( 'yes' !== $auto_join ) {
					$validation_errors->add( 'empty required fields', __( 'You must have to add referral code to join referral program.', 'multilevel-referral-plugin-for-woocommerce' ) );
				}
			}
			if ( isset( $_POST['email'] ) && ! is_email( sanitize_text_field( wp_unslash( $_POST['email'] ) ) ) ) {
				$validation_errors->add( 'invalid fields', __( 'E-mail address is invalid', 'multilevel-referral-plugin-for-woocommerce' ) );
			}
			if ( isset( $_POST['referral_code'] ) && '' !== $_POST['referral_code'] && isset( $_POST['join_referral_program'] ) && 1 === (int) $_POST['join_referral_program'] ) {
				$parent_id = $this->referral_user( 'user_id', 'referral_code', sanitize_text_field( wp_unslash( $_POST['referral_code'] ) ) );
				if ( ! $parent_id ) {
					// translators: %s is the referral code.
					$validation_errors->add( 'empty required fields', sprintf( __( 'There is no such referral code exist<strong>(%s)</strong> exist.', 'multilevel-referral-plugin-for-woocommerce' ), sanitize_text_field( wp_unslash( $_POST['referral_code'] ) ) ) );
					$_POST['wrong_referral_code'] = 'yes';
				}
			}
			if ( isset( $_POST['join_referral_program'] ) && isset( $_POST['referral_email'] ) && 2 === (int) $_POST['join_referral_program'] && '' !== $_POST['referral_email'] ) {
				if ( email_exists( sanitize_text_field( wp_unslash( $_POST['referral_email'] ) ) ) ) {
					// translators: %s is the referral email.
					$validation_errors->add( 'invalid fields', sprintf( __( 'This referral E-mail <strong>(%s)</strong> is already exist.', 'multilevel-referral-plugin-for-woocommerce' ), sanitize_text_field( wp_unslash( $_POST['referral_email'] ) ) ) );
				}
			}
			if ( isset( $_POST['join_referral_program'] ) && 3 !== (int) $_POST['join_referral_program'] ) {
				if ( ! isset( $_POST['termsandconditions'] ) || 1 !== (int) $_POST['termsandconditions'] ) {
					$validation_errors->add( 'Error', __( 'Please accept referral Program terms and conditions', 'multilevel-referral-plugin-for-woocommerce' ) );
				}
			}
			return $validation_errors;
		}

		/**
		 * Save the extra register fields.
		 *
		 * @param  int $user_id Current user ID.
		 *
		 * @return void
		 */
		public function referral_customer_save_data( $user_id ) {
			if ( ! isset( $user_id ) || empty( $user_id ) ) {
				return;
			}
			if ( ! isset( $_POST['referral_registration_validation_nonce'] ) ) {
				return;
			}
			if ( ! isset( $_POST['referral_registration_validation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['referral_registration_validation_nonce'] ) ), 'referral_program' ) ) {
				wp_die( 'Nonce verification failed!' );
			}
			global $woocommerce_multilevel_referral_customer_id, $woocommerce_multilevel_referral_code;
			$woocommerce_multilevel_referral_customer_id = $user_id;
			$parent_id                                   = 0;
			$first_name                                  = '';
			$last_name                                   = '';
			$email                                       = '';
			if ( isset( $_POST['billing_first_name'] ) ) {
				// WordPress default first name field.
				update_user_meta( $woocommerce_multilevel_referral_customer_id, 'first_name', sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) );
				// WooCommerce billing first name.
				update_user_meta( $woocommerce_multilevel_referral_customer_id, 'billing_first_name', sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) );
				$first_name = sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) );
			}
			if ( isset( $_POST['billing_last_name'] ) ) {
				// WordPress default last name field.
				update_user_meta( $woocommerce_multilevel_referral_customer_id, 'last_name', sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) );
				// WooCommerce billing last name.
				update_user_meta( $woocommerce_multilevel_referral_customer_id, 'billing_last_name', sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) );
				$last_name = sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) );
			}
			$auto_join = get_option( 'woocommerce_multilevel_referral_auto_register', 'no' );
			if ( isset( $_POST['referral_code'] ) && '' !== $_POST['referral_code'] ) {
				$parent_id = $this->referral_user( 'user_id', 'referral_code', sanitize_text_field( wp_unslash( $_POST['referral_code'] ) ) );
			} elseif ( 'yes' === $auto_join ) {
				$_POST['join_referral_program'] = 2;
			}
			if ( isset( $_POST['termsandconditions'] ) && 1 === (int) $_POST['termsandconditions'] ) {
				update_user_meta( $woocommerce_multilevel_referral_customer_id, 'termsandconditions', sanitize_text_field( wp_unslash( $_POST['termsandconditions'] ) ) );
			}
			if ( ( isset( $_POST['join_referral_program'] ) && $_POST['join_referral_program'] < 3 ) || $parent_id ) {
				$woocommerce_multilevel_referral_code = $this->referral_code( $woocommerce_multilevel_referral_customer_id );
				$credit_for                           = get_option( 'woocommerce_multilevel_referral_welcome_credit_for', 'new' );
				$benefit                              = 0;
				if ( isset( $_POST['action'] ) && 'join_referreal_program' === $_POST['action'] ) {
					if ( 'new' === $credit_for ) {
						$benefit = 1;
					}
				}
				if ( ! $this->referral_user( 'id', 'user_id', $woocommerce_multilevel_referral_customer_id ) ) {
					$plan_type = get_option( 'woocommerce_multilevel_referral_plan_type' );
					$this->insert(
						array(
							'user_id'          => $woocommerce_multilevel_referral_customer_id,
							'referral_parent'  => ( $parent_id ? $parent_id : 0 ),
							'active'           => 1,
							'referral_code'    => $woocommerce_multilevel_referral_code,
							'referral_email'   => ( isset( $_POST['referral_email'] ) ? sanitize_text_field( wp_unslash( $_POST['referral_email'] ) ) : '' ),
							'referal_benefits' => $benefit,
						)
					);
					update_user_meta( $woocommerce_multilevel_referral_customer_id, 'total_referrals', 0 );
					$this->fnUpdateFollowersCount( $woocommerce_multilevel_referral_customer_id );
				}
				if ( get_current_user_id() ) {
					$current_user = wp_get_current_user();
					$email        = $current_user->user_email;
					$first_name   = $current_user->user_firstname;
					$last_name    = $current_user->user_lastname;
				} else {
					$email = ( isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '' );
				}
				WC()->mailer();
				// Joining mail for new registered user.
				do_action(
					'woocommerce_multilevel_referral_joining_user_notification',
					$email,
					$first_name,
					$last_name,
					$woocommerce_multilevel_referral_code,
					'joining_mail',
					$woocommerce_multilevel_referral_customer_id
				);
				// Referral user mail.
				if ( isset( $_POST['referral_email'] ) && '' !== $_POST['referral_email'] ) {
					do_action(
						'woocommerce_multilevel_referral_joining_user_notification',
						sanitize_text_field( wp_unslash( $_POST['referral_email'] ) ),
						$first_name,
						$last_name,
						$woocommerce_multilevel_referral_code,
						'referral_user',
						$woocommerce_multilevel_referral_customer_id
					);
				}
				// break.
			}
		}

		/**
		 * Generate referral code
		 *
		 * @param int $customer_id Current customer ID.
		 *
		 * @return Unique Referral Code
		 */
		public function referral_code( $customer_id ) {
			global $wpdb;
			$temp_cid      = md5( 'R' . $customer_id );
			$referral_code = substr( $temp_cid, 0, 5 );
			$referral_code = apply_filters( 'woocommerce_multilevel_referral_code', $referral_code, $customer_id );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$exist_referral_code = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}referal_users WHERE referral_code =%s", $referral_code ) );
			if ( $exist_referral_code ) {
				$this->referral_code( $referral_code );
			}
			return $referral_code;
		}

		/**
		 * Get number of referral users.
		 *
		 * @return int Record count.
		 */
		public function record_count() {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_var( "SELECT count(B.ID)  FROM {$wpdb->prefix}referal_users AS A LEFT JOIN {$wpdb->prefix}users AS B ON A.user_id = B.ID WHERE A.active = 1" );
		}

		/**
		 * Add my account menu.
		 *
		 * @param array $items Menu items.
		 *
		 * @return array Modified menu items.
		 */
		public function add_my_account_menu( $items ) {
			$key = array_search( 'dashboard', array_keys( $items ), true );
			if ( false !== $key ) {
				$items = array_merge(
					array_splice( $items, 0, $key + 1 ),
					array(
						'referral' => __( 'Referral', 'multilevel-referral-plugin-for-woocommerce' ),
					),
					$items
				);
				$items = array_merge(
					array_splice( $items, 0, $key + 2 ),
					array(
						'my-affliates' => __( 'My Affiliates', 'multilevel-referral-plugin-for-woocommerce' ),
					),
					$items
				);
			} else {
				$items['referral']     = __( 'Referral', 'multilevel-referral-plugin-for-woocommerce' );
				$items['my-affliates'] = __( 'My Affiliates', 'multilevel-referral-plugin-for-woocommerce' );
			}
			return $items;
		}

		/**
		 * Add referral query var.
		 *
		 * @param array $vars Query vars.
		 *
		 * @return array Modified query vars.
		 */
		public function add_referral_query_var( $vars ) {
			$vars[] = 'referral';
			$vars[] = 'my-affliates';
			return $vars;
		}

		/**
		 * WooCommerce account referral endpoint hook.
		 */
		public function woocommerce_account_referral_endpoint_hook() {
			$this->referral_user_account_panel();
		}

		/**
		 * WooCommerce multilevel referral my affiliates endpoint content.
		 */
		public function woocommerce_multilevel_referral_my_affiliates_endpoint_content() {
			$this->referral_user_my_affiliate_panel();
		}

		/**
		 * Init hook.
		 */
		public function init_hook() {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			add_rewrite_endpoint( 'referral', EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( 'my-affliates', EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( 'woocommerce_multilevel_referral_banner', EP_ROOT | EP_PAGES );
			flush_rewrite_rules();
			add_action( 'wp_ajax_woocommerce_multilevel_referral_change_banner', array( $this, 'woocommerceMultilevelReferralChangeBanner' ) );
			add_action( 'wp_ajax_woocommerce_multilevel_referral_save_transient_banner', array( $this, 'woocommerceMultilevelReferralSaveTransientBanner' ) );
			add_action( 'wp_ajax_woocommerce_multilevel_referral_credit_log_pagination', array( $this, 'woocommerceMultilevelReferralCreditLogPagination' ) );
			if ( isset( $url_data['ru'] ) && '' !== $url_data['ru'] ) {
				setcookie(
					'woocommerce_multilevel_referral_code',
					sanitize_text_field( wp_unslash( $url_data['ru'] ) ),
					strtotime( '+30 days' ),
					'/'
				);
			}
			global $woocommerce;
			if ( isset( $woocommerce ) && version_compare( $woocommerce->version, '2.6.0', '>=' ) ) {
				/* Hooks for myaccount referral endpoint */
				add_filter( 'woocommerce_account_menu_items', array( $this, 'add_my_account_menu' ) );
				add_filter( 'query_vars', array( $this, 'add_referral_query_var' ) );
				add_action( 'woocommerce_account_referral_endpoint', array( $this, 'woocommerce_account_referral_endpoint_hook' ) );
				add_action( 'woocommerce_account_my-affliates_endpoint', array( $this, 'woocommerce_multilevel_referral_my_affiliates_endpoint_content' ) );
			} else {
				add_action( 'woocommerce_before_my_account', array( $this, 'referral_user_account_panel' ) );
			}
			add_filter( 'woocommerce_checkout_fields', array( $this, 'woocommerce_multilevel_referral_override_checkout_fields' ) );
			add_action( 'woocommerce_checkout_process', array( $this, 'woocommerce_multilevel_referral_custom_checkout_field_process' ) );
		}

		/**
		 * Get all referral user IDs.
		 *
		 * @param array $user_ids User IDs.
		 * @param bool  $first_level First level only.
		 * @param bool  $is_recursive Is recursive.
		 *
		 * @return array User IDs.
		 */
		public function get_all_referral_user_id( $user_ids = array(), $first_level = false, $is_recursive = false ) {
			if ( empty( $user_ids ) ) {
				return array();
			}
			if ( ! $is_recursive ) {
				$this->all_visited_users = array();
			}
			// Filter out already visited IDs to prevent cycles.
			$original_ids = ( is_array( $user_ids ) ? $user_ids : array( $user_ids ) );
			$user_ids     = array_diff( $original_ids, $this->all_visited_users );
			if ( empty( $user_ids ) ) {
				return array();
			}
			$this->all_visited_users = array_merge( $this->all_visited_users, $user_ids );
			global $wpdb;
			$place_holders = implode( ', ', array_fill( 0, count( $user_ids ), '%d' ) );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = "SELECT user_id FROM {$wpdb->prefix}referal_users WHERE referral_parent IN ({$place_holders})";
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$user_list = $wpdb->get_col( $wpdb->prepare( $query, ...array_map( 'intval', $user_ids ) ) );
			if ( $first_level ) {
				return $user_list;
			}
			if ( ! empty( $user_list ) ) {
				$user_ids  = array_merge( $user_ids, $user_list );
				$user_list = $this->get_all_referral_user_id( $user_list, false, true );
			}
			return array_unique( array_merge( $user_ids, $user_list ) );
		}

		/**
		 * Get orders by ID.
		 *
		 * @param int $id Order ID.
		 *
		 * @return array Order IDs.
		 */
		public function get_orders_by_id( $id ) {
			global $wpdb;
			$is_hpos_enabled = wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled();
			if ( $is_hpos_enabled ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID LIKE %s AND `post_type` = 'shop_order_placehold'", '%' . $id . '%' ) );
			} else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID LIKE %s AND `post_type` = 'shop_order'", '%' . $id . '%' ) );
			}
		}

		/**
		 * Get referral user level.
		 *
		 * @param int  $user_id User ID.
		 * @param int  $level Level.
		 * @param bool $is_recursive Is recursive.
		 *
		 * @return int Level.
		 */
		public function get_referral_user_level( $user_id, $level = 1, $is_recursive = false ) {
			if ( ! $is_recursive ) {
				$this->level_visited = array();
			}
			if ( in_array( $user_id, $this->level_visited, true ) ) {
				return $level;
			}
			$this->level_visited[] = $user_id;
			global $wpdb;
			$sql = 'SELECT `referral_parent` FROM ' . $this->table_name . ' WHERE `active` = 1 AND `user_id` = "' . $user_id . '"';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$parent_id = $wpdb->get_var( $wpdb->prepare( "SELECT referral_parent FROM {$wpdb->prefix}referal_users WHERE active = 1 AND user_id =%d ", $user_id ) );
			if ( $parent_id ) {
				++$level;
				$level = $this->get_referral_user_level( $parent_id, $level, true );
			}
			return $level;
		}

		/**
		 * Change referral user level.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return int Referral level.
		 */
		public function change_referral_user_level( $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$parent_id = $wpdb->get_var( $wpdb->prepare( "SELECT B.`referral_parent` FROM {$wpdb->prefix}referal_users AS A LEFT JOIN {$wpdb->prefix}referal_users AS B ON A.referral_parent = B.user_id WHERE A.`active` = 1 AND  B.`active` = 1 AND A.`user_id` = %d", $user_id ) );
			$parent_id = ( $parent_id ? $parent_id : 0 );
			$this->update( $user_id, $parent_id );
			$referral_level = $this->get_referral_user_level( $user_id );
			return $referral_level;
		}
	}

	// end WooCommerce_Multilevel_Referral_Users.
}
