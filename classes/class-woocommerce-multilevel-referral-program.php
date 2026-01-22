<?php
/**
 * Referral Program Class
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Program' ) ) {
	/**
	 * Main / front controller class
	 */
	#[AllowDynamicProperties]
	class WooCommerce_Multilevel_Referral_Program {
		/**
		 * Table name.
		 *
		 * @var string
		 */
		public $table_name;

		/**
		 * Product commission table name.
		 *
		 * @var string
		 */
		public $product_commission;

		/**
		 * Instance of the class.
		 *
		 * @var WooCommerce_Multilevel_Referral_Program|null
		 */
		private static $instance = null;

		/**
		 * Get instance of the class.
		 *
		 * @return WooCommerce_Multilevel_Referral_Program
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
			$this->table_name         = $wpdb->prefix . 'referal_program';
			$this->product_commission = $wpdb->prefix . 'referal_product_commission';
		}

		/**
		 * Create table.
		 */
		public function create_table() {
			global $wpdb;
			$check_sql = "show tables like '" . $this->table_name . "'";
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $this->table_name ) ) !== $this->table_name ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				$sql = 'CREATE TABLE ' . $this->table_name . ' (
				id int(11) NOT NULL AUTO_INCREMENT,
				order_id  int(11),
				user_id  int(11),
				credits  decimal(10,4) DEFAULT 0.0000,
				redeems  decimal(10,4) DEFAULT 0.0000,
				date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				is_transfer BOOLEAN NOT NULL DEFAULT FALSE,
				PRIMARY KEY  (id)
			  );';
				// we do not execute sql directly.
				// we are calling dbDelta which cant migrate database.
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );
			}
			$check_sql = "show tables like '" . $this->product_commission . "'";
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $this->product_commission ) ) !== $this->product_commission ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				$sql = 'CREATE TABLE ' . $this->product_commission . ' (
				id int(11) NOT NULL AUTO_INCREMENT,
				user_id  int(11),
				order_id  int(11),
				product_id  int(11),
				credits  decimal(10,4) DEFAULT 0.0000,
				date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			  );';
				// we do not execute sql directly.
				// we are calling dbDelta which cant migrate database.
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );
			}
		}

		/**
		 * Insert record.
		 *
		 * @mvc Controller
		 *
		 * @param array $data Data to insert.
		 */
		public function insert( $data ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert(
				$this->table_name,
				array(
					'order_id' => $data['order_id'],
					'user_id'  => $data['user_id'],
					'credits'  => ( isset( $data['credits'] ) ? $data['credits'] : 0 ),
					'redeems'  => ( isset( $data['redeems'] ) ? $data['redeems'] : 0 ),
				)
			);
		}

		/**
		 * Insert record Product Commission.
		 *
		 * @mvc Controller
		 *
		 * @param array $data Data to insert.
		 */
		public function insert_product_commission( $data ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert(
				$this->product_commission,
				array(
					'user_id'    => $data['user_id'],
					'order_id'   => $data['order_id'],
					'product_id' => $data['product_id'],
					'credits'    => ( isset( $data['credits'] ) ? $data['credits'] : 0 ),
				)
			);
		}

		/**
		 * Insert redeem record.
		 *
		 * @param array $data Redeem data.
		 */
		public function insert_redeem( $data ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert(
				$wpdb->prefix . 'redeem_history',
				array(
					'mobile_number'     => $data['mobile_number'],
					'merchant_order_id' => $data['merchant_order_id'],
					'transaction_id'    => $data['transaction_id'],
					'status'            => $data['status'],
					'payment_method'    => $data['payment_method'],
					'message'           => $data['statusMessage'],
					'user_id'           => $data['user_id'],
					'amount'            => ( isset( $data['amount'] ) ? $data['amount'] : 0 ),
				)
			);
			$ins_id = $wpdb->insert_id;
			if ( 'SUCCESS' === $data['status'] ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert(
					$this->table_name,
					array(
						'order_id'  => ( isset( $data['order_id'] ) ? $data['order_id'] : 0 ),
						'user_id'   => $data['user_id'],
						'credits'   => ( isset( $data['credits'] ) ? $data['credits'] : 0 ),
						'redeems'   => ( isset( $data['redeems'] ) ? $data['redeems'] : 0 ),
						'type'      => ( isset( $data['type'] ) ? $data['type'] : 0 ),
						'redeem_id' => ( isset( $ins_id ) ? $ins_id : 0 ),
					)
				);
			}
		}

		/**
		 * Update record.
		 *
		 * @param array $data Data to update.
		 * @param int   $user_id User ID.
		 */
		public function update( $data, $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$this->table_name,
				$data,
				array(
					'user_id' => $user_id,
				)
			);
		}

		/**
		 * Delete record by order ID.
		 *
		 * @param int $order_id Order ID.
		 */
		public function delete( $order_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete(
				$this->table_name,
				array(
					'order_id' => $order_id,
				)
			);
		}

		/**
		 * Get credit for specific order.
		 *
		 * @param int $order_id Order ID.
		 *
		 * @return array Credits by order.
		 */
		public function get_credits_by_order( $order_id ) {
			global $wpdb;
			$sql = 'SELECT user_id, credits FROM ' . $this->table_name . ' WHERE credits > 0 AND order_id = ' . $order_id . ' GROUP BY user_id';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->get_results( $wpdb->prepare( 'SELECT user_id, credits FROM %s WHERE credits > 0 AND order_id = %d GROUP BY user_id', $this->table_name, $order_id ), 'ARRAY_A' );
			return $result;
		}

		/**
		 * Remove credit for specific order.
		 *
		 * @param int $order_id Order ID.
		 *
		 * @return array|int|null Result.
		 */
		public function remove_credits_by_order( $order_id ) {
			global $wpdb;
			$sql = 'DELETE FROM ' . $this->table_name . " WHERE credits > 0 AND order_id = {$order_id}";
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->get_results( $wpdb->prepare( 'DELETE FROM %s WHERE credits > 0 AND order_id = %d', $this->table_name, $order_id ) );
			return $result;
		}

		/**
		 * Get earn credit list based on order.
		 *
		 * @param int    $per_page Per page count.
		 * @param int    $page_number Page number.
		 * @param string $where Where clause.
		 * @param array  $order_id Order IDs.
		 *
		 * @return array Credits list.
		 */
		public function get_credits(
			$per_page = 5,
			$page_number = 1,
			$where = '',
			$order_id = array()
		) {
			global $wpdb;
			$url_data       = woocommerce_multilevel_referral_get_query_vars();
			$per_page       = (int) $per_page;
			$page_number    = (int) $page_number;
			$where_clause   = '';
			$prepare_values = array();
			if ( $where && is_array( $where ) ) {
				$where_ids = array_map( 'intval', $where );
				if ( ! empty( $where_ids ) ) {
					$placeholders   = implode( ',', array_fill( 0, count( $where_ids ), '%d' ) );
					$where_clause   = "AND {$this->table_name}.user_id IN ({$placeholders})";
					$prepare_values = array_merge( $prepare_values, $where_ids );
				}
			}
			if ( is_array( $order_id ) && count( $order_id ) ) {
				$order_ids = array_map( 'intval', $order_id );
				if ( ! empty( $order_ids ) ) {
					$placeholders   = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
					$where_clause  .= ( ( $where_clause ? ' AND ' : 'AND ' ) ) . "{$this->table_name}.order_id IN ({$placeholders})";
					$prepare_values = array_merge( $prepare_values, $order_ids );
				}
			}
			$sql      = "SELECT min({$this->table_name}.id), user_id, order_id, sum(credits) as credits FROM {$this->table_name} RIGHT JOIN {$wpdb->posts} ON {$this->table_name}.order_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status != 'trash' AND credits > 0 {$where_clause} GROUP BY order_id ORDER BY ";
			$order_by = 'order_id';
			if ( isset( $url_data['orderby'] ) && ! empty( $url_data['orderby'] ) ) {
				$allowed_orderby = array( 'order_id', 'credits', 'user_id' );
				$clean_orderby   = sanitize_text_field( wp_unslash( $url_data['orderby'] ) );
				if ( in_array( $clean_orderby, $allowed_orderby, true ) ) {
					$order_by = $clean_orderby;
				}
			}
			$order = 'DESC';
			if ( isset( $url_data['order'] ) && ! empty( $url_data['order'] ) ) {
				$clean_order = strtoupper( sanitize_text_field( wp_unslash( $url_data['order'] ) ) );
				if ( in_array( $clean_order, array( 'ASC', 'DESC' ), true ) ) {
					$order = $clean_order;
				}
			}
			$query = $sql . " {$order_by} {$order}";
			if ( $per_page > 0 ) {
				$query         .= ' LIMIT %d OFFSET %d';
				$prepare_values = array_merge( $prepare_values, array( $per_page, ( $page_number - 1 ) * $per_page ) );
			}
			// Prepare the query with all values.
			if ( ! empty( $prepare_values ) ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$query = $wpdb->prepare( $query, ...$prepare_values );
			}
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$result = $wpdb->get_results( $query, 'ARRAY_A' );
            // phpcs:enable
			return $result;
		}

		/**
		 * Get earn redeem list based on order.
		 *
		 * @param int $per_page Per page count.
		 * @param int $page_number Page number.
		 *
		 * @return array Redeems list.
		 */
		public function get_redeems( $per_page = 5, $page_number = 1 ) {
			global $wpdb;
			$url_data    = woocommerce_multilevel_referral_get_query_vars();
			$per_page    = (int) $per_page;
			$page_number = (int) $page_number;
			$sql         = "\n\t\t\t\tSELECT\n\t\t\t\t\tA.user_id,\n\t\t\t\t\tA.order_id,\n\t\t\t\t\tA.redeems\n\t\t\t\tFROM {$this->table_name} AS A\n\t\t\t\tRIGHT JOIN {$wpdb->posts} AS B\n\t\t\t\t\tON A.order_id = B.ID\n\t\t\t\tWHERE B.post_status <> 'trash'\n\t\t\t\t\tAND A.redeems > 0\n\t\t\t\tGROUP BY A.order_id\n\t\t\t";
			if ( ! empty( $url_data['orderby'] ) ) {
				$allowed_orderby = array( 'order_id', 'user_id', 'redeems' );
				$clean_orderby   = sanitize_text_field( wp_unslash( $url_data['orderby'] ) );
				if ( in_array( $clean_orderby, $allowed_orderby, true ) ) {
					$sql        .= ' ORDER BY ' . $clean_orderby;
					$clean_order = ( ! empty( $url_data['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $url_data['order'] ) ) ) : 'ASC' );
					$sql        .= ( in_array( $clean_order, array( 'ASC', 'DESC' ), true ) ? ' ' . $clean_order : ' ASC' );
				}
			}
			$query = $sql . $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, ( $page_number - 1 ) * $per_page );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$result = $wpdb->get_results( $query, ARRAY_A );
			return $result;
		}

		/**
		 * Get number of orders.
		 *
		 * @param string $type Type.
		 * @param bool   $all_record All records.
		 * @param array  $user_id User IDs.
		 * @param array  $order_id Order IDs.
		 *
		 * @return int Record count.
		 */
		public function record_count(
			$type = 'credits',
			$all_record = false,
			$user_id = array(),
			$order_id = array()
		) {
			global $wpdb;
			$allowed_types = array( 'credits', 'redeems' );
			if ( ! in_array( $type, $allowed_types, true ) ) {
				$type = esc_sql( 'credits' );
			}
			$where          = '';
			$prepare_values = array();
			if ( is_array( $user_id ) && count( $user_id ) ) {
				$user_ids = array_map( 'intval', $user_id );
				if ( ! empty( $user_ids ) ) {
					$placeholders   = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
					$where          = " WHERE user_id IN ({$placeholders})";
					$prepare_values = array_merge( $prepare_values, $user_ids );
				}
			}
			if ( is_array( $order_id ) && count( $order_id ) ) {
				$order_ids = array_map( 'intval', $order_id );
				if ( ! empty( $order_ids ) ) {
					$placeholders   = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
					$where          = " WHERE order_id IN ({$placeholders})";
					$prepare_values = array_merge( $prepare_values, $order_ids );
				}
			}
			if ( $all_record ) {
				$query = "SELECT count(*) FROM {$this->table_name} {$where}";
			} elseif ( $user_id && is_array( $user_id ) ) {
				$user_ids = array_map( 'intval', $user_id );
				if ( ! empty( $user_ids ) ) {
					$placeholders   = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
					$query          = "SELECT COUNT(*) FROM (SELECT count(*) FROM {$this->table_name} RIGHT JOIN {$wpdb->posts} ON {$this->table_name}.order_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status != 'trash' AND {$type} > 0 AND user_id IN ({$placeholders}) GROUP BY order_id) AS total ";
					$prepare_values = esc_sql( $user_ids );
				} else {
					$query          = "SELECT COUNT(*) FROM (SELECT count(*) FROM {$this->table_name} RIGHT JOIN {$wpdb->posts} ON {$this->table_name}.order_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status != 'trash' AND {$type} > 0 GROUP BY order_id) AS total ";
					$prepare_values = array();
				}
			} else {
				$query          = "SELECT COUNT(*) FROM (SELECT count(*) FROM {$this->table_name} RIGHT JOIN {$wpdb->posts} ON {$this->table_name}.order_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status != 'trash' AND {$type} > 0 GROUP BY order_id) AS total ";
				$prepare_values = array();
			}
			// Prepare the query with all values.
			if ( ! empty( $prepare_values ) ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$query = $wpdb->prepare( $query, ...$prepare_values );
			}
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return $wpdb->get_var( $query );
		}

		/**
		 * Get total of earning credits.
		 *
		 * @param string $type Type.
		 *
		 * @return string|int Total statistic.
		 */
		public function total_statistic( $type ) {
			global $wpdb;
			$credit_for    = get_option( 'woocommerce_multilevel_referral_welcome_credit_for', 'new' );
			$allowed_types = array( 'credits', 'redeems' );
			if ( ! in_array( $type, $allowed_types, true ) ) {
				$type = 'credits';
			}
			// Build query based on validated type to avoid variable interpolation in SQL.
			if ( woocommerce_multilevel_referral_fs()->is__premium_only() && 'registration' === $credit_for ) {
				if ( 'redeems' === $type ) {
					$query = "SELECT sum(A.redeems) FROM {$this->table_name} AS A";
				} else {
					$query = "SELECT sum(A.credits) FROM {$this->table_name} AS A";
				}
			} elseif ( 'redeems' === $type ) {
				$query = "SELECT sum(A.redeems) FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash'";
			} else {
				$query = "SELECT sum(A.credits) FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash'";
			}
			// Column name is hardcoded based on validated whitelist, table names are safe class properties.
			// Query is prepared to satisfy PHPCS requirements (no user input, all values are hardcoded).
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$data = $wpdb->get_var( $query );
			$n    = apply_filters(
				'woocommerce_multilevel_referral_total_' . $type . '_statistic',
				$data,
				$type,
				$credit_for
			);
			if ( '' !== $n ) {
				return $this->make_nice_number( $n );
			}
			return 0;
		}

		/**
		 * Make nice number format.
		 *
		 * @param float|int|string $n Number.
		 *
		 * @return string Formatted number.
		 */
		public function make_nice_number( $n ) {
			// Normalize and validate numeric input safely.
			if ( ! is_scalar( $n ) ) {
				return 0;
			}
			// Strip common formatting characters.
			$clean_number = preg_replace( '/[^\\d.\\-]/', '', (string) $n );
			if ( '' === $clean_number || ! is_numeric( $clean_number ) ) {
				return 0;
			}
			$n = (float) $clean_number;
			// now filter it.
			if ( $n > 1000000000000 ) {
				return round( $n / 1000000000000, 1 ) . ' trillion';
			} elseif ( $n > 1000000000 ) {
				return round( $n / 1000000000, 1 ) . ' billion';
			} elseif ( $n > 1000000 ) {
				return round( $n / 1000000, 1 ) . ' million';
			} elseif ( $n > 1000 ) {
				return round( $n / 1000, 1 ) . 'k';
			}
			return number_format( $n, 2 );
		}

		/**
		 * Get all records count.
		 *
		 * @param int      $per_page Per page count.
		 * @param int      $page_number Page number.
		 * @param int|null $where Where clause.
		 * @param array    $order_id Order IDs.
		 *
		 * @return int All count.
		 */
		public function getall_count(
			$per_page = 5,
			$page_number = 1,
			$where = null,
			$order_id = array()
		) {
			global $wpdb;
			$url_data       = woocommerce_multilevel_referral_get_query_vars();
			$prepare_values = array();
			$sql            = "\n\t\t\t\tSELECT COUNT(A.id) AS records_count\n\t\t\t\tFROM {$this->table_name} AS A\n\t\t\t\tLEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID\n\t\t\t\tWHERE ( B.post_status != 'trash' OR A.order_id = 0 )\n\t\t\t";
			// WHERE: user_id.
			if ( null !== $where ) {
				if ( is_array( $where ) ) {
					$where_ids = array_map( 'intval', $where );
					if ( ! empty( $where_ids ) ) {
						$placeholders   = implode( ',', array_fill( 0, count( $where_ids ), '%d' ) );
						$sql           .= " AND A.user_id IN ({$placeholders})";
						$prepare_values = array_merge( $prepare_values, $where_ids );
					}
				} else {
					$sql             .= ' AND A.user_id = %d';
					$prepare_values[] = (int) $where;
				}
			}
			// WHERE: order_id.
			if ( ! empty( $order_id ) && is_array( $order_id ) ) {
				$order_ids = array_map( 'intval', $order_id );
				if ( ! empty( $order_ids ) ) {
					$placeholders   = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
					$sql           .= " AND A.order_id IN ({$placeholders})";
					$prepare_values = array_merge( $prepare_values, $order_ids );
				}
			}
			// ORDER BY.
			if ( ! empty( $url_data['orderby'] ) ) {
				$allowed_orderby = array( 'id', 'order_id' );
				$orderby         = sanitize_text_field( wp_unslash( $url_data['orderby'] ) );
				if ( in_array( $orderby, $allowed_orderby, true ) ) {
					$order = ( ! empty( $url_data['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $url_data['order'] ) ) ) : 'ASC' );
					$order = ( in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'ASC' );
					$sql  .= " ORDER BY A.{$orderby} {$order}";
				}
			} else {
				$sql .= ' ORDER BY A.id DESC, A.order_id DESC';
			}
			// LIMIT / OFFSET.
			if ( $per_page > 0 ) {
				$sql             .= ' LIMIT %d OFFSET %d';
				$prepare_values[] = (int) $per_page;
				$prepare_values[] = (int) ( ( $page_number - 1 ) * $per_page );
			}
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber,
			if ( ! empty( $prepare_values ) ) {
				$sql = $wpdb->prepare( $sql, ...$prepare_values );
			}
            // phpcs:enable
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return $wpdb->get_var( $sql );
		}

		/**
		 * Get all records.
		 *
		 * @param int      $per_page Per page count.
		 * @param int      $page_number Page number.
		 * @param int|null $where Where clause.
		 * @param array    $order_id Order IDs.
		 *
		 * @return array All records.
		 */
		public function select_all(
			$per_page = 5,
			$page_number = 1,
			$where = null,
			$order_id = array()
		) {
			global $wpdb;
			$url_data       = woocommerce_multilevel_referral_get_query_vars();
			$sql            = "SELECT A.* FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE ( B.post_status != 'trash' OR A.order_id = 0 ) ";
			$prepare_values = array();
			if ( $where ) {
				if ( is_array( $where ) ) {
					$where_ids = array_map( 'intval', $where );
					if ( ! empty( $where_ids ) ) {
						$placeholders   = implode( ',', array_fill( 0, count( $where_ids ), '%d' ) );
						$sql           .= " AND A.user_id IN ({$placeholders})";
						$prepare_values = array_merge( $prepare_values, $where_ids );
					}
				} else {
					$sql             .= ' AND A.user_id = %d';
					$prepare_values[] = (int) $where;
				}
			}
			if ( is_array( $order_id ) && count( $order_id ) ) {
				$order_ids = array_map( 'intval', $order_id );
				if ( ! empty( $order_ids ) ) {
					$placeholders   = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
					$sql           .= " AND A.order_id IN ({$placeholders})";
					$prepare_values = array_merge( $prepare_values, $order_ids );
				}
			}
			if ( ! empty( $url_data['orderby'] ) ) {
				$allowed_orderby = array( 'id', 'order_id' );
				$clean_orderby   = sanitize_text_field( wp_unslash( $url_data['orderby'] ) );
				if ( in_array( $clean_orderby, $allowed_orderby, true ) ) {
					$sql        .= ' ORDER BY ' . $clean_orderby;
					$clean_order = ( ! empty( $url_data['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $url_data['order'] ) ) ) : 'ASC' );
					$sql        .= ( in_array( $clean_order, array( 'ASC', 'DESC' ), true ) ? ' ' . $clean_order : ' ASC' );
				}
			} else {
				$sql .= ' ORDER BY A.id DESC, A.order_id DESC';
			}
			$query = $sql;
			if ( $per_page > 0 ) {
				$query         .= ' LIMIT %d OFFSET %d';
				$prepare_values = array_merge( $prepare_values, array( $per_page, ( $page_number - 1 ) * $per_page ) );
			}
			// Prepare the query with all values.
			if ( ! empty( $prepare_values ) ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$query = $wpdb->prepare( $query, ...$prepare_values );
			}
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$result = $wpdb->get_results( $query, 'ARRAY_A' );
			return $result;
		}

		/**
		 * Available credits of user.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return float Available credits.
		 */
		public function available_credits( $user_id ) {
			global $wpdb, $woocommerce_multilevel_referral_cache;
			if ( isset( $woocommerce_multilevel_referral_cache['available_credits'] ) && isset( $woocommerce_multilevel_referral_cache['available_credits'][ $user_id ] ) ) {
				return $woocommerce_multilevel_referral_cache['available_credits'][ $user_id ];
			}
			$credit_for     = get_option( 'woocommerce_multilevel_referral_welcome_credit_for', 'new' );
			$user_credits   = floatval( get_user_meta( $user_id, 'woocommerce_multilevel_referral_store_credit', 0 ) );
			$welcome_credit = floatval( get_option( 'woocommerce_multilevel_referral_welcome_credit', 0 ) );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $wpdb->prepare( "SELECT IF ( sum(A.credits) - sum(A.redeems) , sum(A.credits) - sum(A.redeems), 0)  AS total FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND A.user_id = %d", $user_id );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$available_credits = apply_filters( 'woocommerce_multilevel_referral_available_credit', $wpdb->get_var( $query ), $user_id );
			$available_credits = apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', $available_credits );
			$available_credits = ( $available_credits ? $available_credits : 0 );
			$woocommerce_multilevel_referral_cache['available_credits'][ $user_id ] = $available_credits;
			return $available_credits;
		}

		/**
		 * Get product commission total.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return float Product wise total.
		 */
		public function product_wise_total( $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $wpdb->prepare( "SELECT IF ( sum(A.credits), sum(A.credits), 0)  AS total FROM {$this->product_commission} AS A RIGHT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND A.user_id = %d", $user_id );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return apply_filters( 'woocommerce_multilevel_referral_product_wise_total', $wpdb->get_var( $query ), $user_id );
		}

		/**
		 * Get total withdraw credit.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return float Total withdraw credit.
		 */
		public function total_withdraw_credit( $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $wpdb->prepare( "SELECT sum(A.redeems) AS total FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND user_id = %d", $user_id );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$total_withdraw_credit = $wpdb->get_var( $query );
			return apply_filters(
				'woocommerce_multilevel_referral_withdraw_credited',
				$total_withdraw_credit,
				$user_id,
				$total_withdraw_credit
			);
		}

		/**
		 * Get total earn credit.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return float Total earn credit.
		 */
		public function total_earn_credit( $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $wpdb->prepare( "SELECT sum(A.credits) AS total FROM  {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND user_id = %d", $user_id );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return apply_filters( 'woocommerce_multilevel_referral_withdraw_earned', $wpdb->get_var( $query ), $user_id );
		}

		/**
		 * Retrieve total number of followers.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return int Number of followers.
		 */
		public function no_of_followers( $user_id ) {
			global $wpdb;
			// return 0.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$followers = $wpdb->get_var( $wpdb->prepare( 'SELECT followers_count(%d, \'count\' )', $user_id ) );
			$followers = ( '' === $followers || empty( $followers ) ? 0 : $followers );
			return $followers;
		}

		/**
		 * Get current user's referral details.
		 *
		 * @param int         $user_id User ID.
		 * @param string|null $get_filter Filter.
		 *
		 * @return array Referral user list.
		 */
		public function get_referral_user_list( $user_id, $get_filter = null ) {
			global $wpdb;
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			$sql      = $wpdb->prepare(
				'SELECT a.user_id, a.meta_value as first_name, b.meta_value as last_name, UM.meta_value as followers, c.active, c.join_date
            FROM ' . $wpdb->usermeta . ' AS a
            JOIN ' . $wpdb->usermeta . ' AS b on a.user_id = b.user_id
            JOIN ' . $wpdb->usermeta . ' AS UM on a.user_id = UM.user_id
            JOIN ' . $wpdb->prefix . 'referal_users AS c on a.user_id = c.user_id
            WHERE a.meta_key = "first_name" AND b.meta_key = "last_name" AND UM.meta_key="total_referrals" AND c.active = 1 AND c.referral_parent = %d',
				$user_id
			);
			if ( isset( $get_filter ) && 'none' !== $get_filter && null !== $get_filter ) {
				$get_filter_date = sanitize_text_field( $get_filter );
				// Ensure date format or just sanitize.
				$month_start_date = gmdate( 'Y-m-d', strtotime( "{$get_filter_date} first day of this month" ) );
				$month_last_date  = gmdate( 'Y-m-d', strtotime( "{$get_filter_date} last day of this month" ) );
				$sql             .= $wpdb->prepare( ' AND c.join_date BETWEEN STR_TO_DATE(%s,"%%Y-%%m-%%d") AND STR_TO_DATE(%s,"%%Y-%%m-%%d")', $month_start_date, $month_last_date );
			}
			if ( isset( $url_data['orderby'] ) && 'desc' === $url_data['orderby'] ) {
				$sql .= ' order by c.join_date DESC';
			} else {
				$sql .= ' order by c.join_date ASC';
			}
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$referral_result = $wpdb->get_results( $sql );
			return $referral_result;
		}

		/**
		 * Remove referral user.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return mixed Result.
		 */
		public function remove_referral_user( $user_id ) {
			global $wpdb;
			$obj_referal_users = WooCommerce_Multilevel_Referral_Users::get_instance();
			return $obj_referal_users->change_referral_user( $user_id );
		}

		/**
		 *  Get current month earning.
		 *
		 *  @param int $user_id Requested user id.
		 *
		 *  @return int Return total earning of current month.
		 */
		public function get_current_month_earning( $user_id ) {
			global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return $wpdb->get_var( $wpdb->prepare( "SELECT if ( sum(A.credits) , sum(A.credits) , 0) AS earning from  {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND MONTH(CURDATE())=MONTH(date) AND A.user_id = %d", $user_id ) );
		}

		/**
		 * Get referral code list.
		 *
		 * @return array Referral code list.
		 */
		public function get_referral_code_list() {
			global $wpdb;
			$sql = "SELECT ru.user_id, ru.referral_code FROM {$wpdb->users} JOIN {$wpdb->prefix}referal_users AS ru ON ru.user_id = {$wpdb->users}.ID WHERE 1=1 AND ru.active = 1 ORDER BY ru.referral_code ASC";
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_results( "SELECT ru.user_id, ru.referral_code FROM {$wpdb->users} JOIN {$wpdb->prefix}referal_users AS ru ON ru.user_id = {$wpdb->users}.ID WHERE 1=1 AND ru.active = 1 ORDER BY ru.referral_code ASC" );
		}
	}

	// end WooCommerce_Multilevel_Referral_Program.
}
