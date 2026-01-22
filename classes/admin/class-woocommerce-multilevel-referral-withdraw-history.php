<?php
/**
 * Withdraw History Table
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Withdraw_History' ) ) {
	/**
	 * WooCommerce Multilevel Referral Withdraw History List Table Class.
	 *
	 * @package Multilevel_Referral_Plugin_For_WooCommerce
	 */
	class WooCommerce_Multilevel_Referral_Withdraw_History extends WP_List_Table {
		/**
		 * Prepare the items for the table to process
		 *
		 * @return void
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$hidden   = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();

			$this->process_bulk_action();

			$data         = $this->table_data();
			$per_page     = 20;
			$current_page = $this->get_pagenum();
			$total_items  = count( $data );

			usort( $data, array( $this, 'sort_data' ) );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$data                  = array_slice(
				$data,
				( ( $current_page - 1 ) * $per_page ),
				$per_page
			);
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items           = $data;
		}

		/**
		 * Override the parent columns method. Defines the columns to use in your listing table
		 *
		 * @return Array
		 */
		public function get_columns() {
			$c = array(
				'cb'                => '<input type="checkbox" />',
				'display_name'      => __( 'Name', 'multilevel-referral-plugin-for-woocommerce' ),
				'mobile_number'     => __( 'Mobile Number', 'multilevel-referral-plugin-for-woocommerce' ),
				'merchant_order_id' => __( 'Merchant Order id', 'multilevel-referral-plugin-for-woocommerce' ),
				'transaction_id'    => __( 'Transaction id', 'multilevel-referral-plugin-for-woocommerce' ),
				'redeems'           => __( 'Amount', 'multilevel-referral-plugin-for-woocommerce' ),
				'date'              => __( 'date', 'multilevel-referral-plugin-for-woocommerce' ),
				'status'            => __( 'Status', 'multilevel-referral-plugin-for-woocommerce' ),
				'message'           => __( 'Message', 'multilevel-referral-plugin-for-woocommerce' ),
				'payment_method'    => __( 'Payment Method', 'multilevel-referral-plugin-for-woocommerce' ),
			);
			return $c;
		}
		/**
		 * Define which columns are hidden
		 *
		 * @return Array
		 */
		public function get_hidden_columns() {
			return array();
		}
		/**
		 * Define the sortable columns
		 *
		 * @return Array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'display_name'      => array( 'display_name', true ),
				'mobile_number'     => array( 'mobile_number', false ),
				'merchant_order_id' => array( 'merchant_order_id', false ),
				'transaction_id'    => array( 'transaction_id', false ),
				'redeems'           => array( 'redeems', false ),
				'date'              => array( 'date', false ),
				'status'            => array( 'status', false ),
				'message'           => array( 'message', false ),
				'payment_method'    => array( 'payment_method', false ),
			);
			return $sortable_columns;
		}
		/**
		 * Get the table data
		 *
		 * @return Array
		 */
		private function table_data() {
			global $wpdb;
			$data                 = array();
			$table_redeem_history = $wpdb->prefix . 'redeem_history';
			$table_ref_program    = $wpdb->prefix . 'referal_program';
			$url_data             = woocommerce_multilevel_referral_get_query_vars();
			$where_clauses        = array( "transaction_id !== ''" );
			$params               = array();
			if ( isset( $url_data['search_by_name'] ) && ! empty( $url_data['search_by_name'] ) ) {
				$str            = sanitize_text_field( wp_unslash( $url_data['search_by_name'] ) );
				$wp_user_query2 = new WP_User_Query(
					array(
						// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'meta_query' => array(
							'relation' => 'OR',
							array(
								'key'     => 'first_name',
								'value'   => $str,
								'compare' => 'LIKE',
							),
							array(
								'key'     => 'last_name',
								'value'   => $str,
								'compare' => 'LIKE',
							),
						),
					)
				);
				$users2         = $wp_user_query2->get_results();
				$arr_ids        = array();
				foreach ( $users2 as $key => $value ) {
					$arr_ids[] = $value->ID;
				}
				if ( ! empty( $arr_ids ) ) {
					$arr_ids_clean   = implode( ',', array_map( 'intval', $arr_ids ) );
					$where_clauses[] = "user_id in ($arr_ids_clean)";
				} else {
					$where_clauses[] = '1=0';
				}
			}
			if ( isset( $url_data['search_by_mobile'] ) && ! empty( $url_data['search_by_mobile'] ) ) {
				$where_clauses[] = 'mobile_number = %s';
				$params[]        = sanitize_text_field( wp_unslash( $url_data['search_by_mobile'] ) );
			}
			if ( isset( $url_data['search_start_date'] ) && ! empty( $url_data['search_start_date'] ) && isset( $url_data['search_end_date'] ) && ! empty( $url_data['search_end_date'] ) ) {
				$where_clauses[] = '(date BETWEEN %s AND %s)';
				$params[]        = sanitize_text_field( wp_unslash( $url_data['search_start_date'] ) );
				$params[]        = sanitize_text_field( wp_unslash( $url_data['search_end_date'] ) );
			}
			$final_where = implode( ' AND ', $where_clauses );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query_string = "select * from $table_redeem_history where $final_where";
			if ( ! empty( $params ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$query_string = $wpdb->prepare( $query_string, $params );
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$results = $wpdb->get_results( $query_string );
			foreach ( $results as $key => $value ) {
				if ( ! $value->mobile_number ) {
					$value->mobile_number = '-';
				}
				$data[] = array(
					'id'                => $value->id,
					'display_name'      => $value->user_id,
					'mobile_number'     => $value->mobile_number,
					'merchant_order_id' => $value->merchant_order_id,
					'transaction_id'    => $value->transaction_id,
					'redeems'           => number_format( $value->amount, 2 ),
					'date'              => $value->date,
					'status'            => $value->status,
					'message'           => $value->message,
					'payment_method'    => $value->payment_method,
				);
			}
			return $data;
		}
		/**
		 * Define what data to show on each column of the table.
		 *
		 * @param  array $item Data.
		 *
		 * @return mixed
		 */
		public function column_display_name( $item ) {
			$user_id   = $item['display_name'];
			$user_info = get_userdata( $user_id );
			$username  = trim( $user_info->first_name . ' ' . $user_info->last_name );
			$url_data  = woocommerce_multilevel_referral_get_query_vars();
			$actions   = array();

			if ( isset( $url_data['page'] ) ) {
				$page    = sanitize_text_field( wp_unslash( $url_data['page'] ) );
				$link    = sprintf( '?page=%s&tab=withdraw-history&action=%s&delete_cb=%s', $page, 'delete', intval( $item['id'] ) );
				$actions = array(
					'delete' => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), esc_html__( 'Delete', 'multilevel-referral-plugin-for-woocommerce' ) ),
				);
			}

			return sprintf( '%1$s %2$s', esc_html( $username ), $this->row_actions( $actions ) );
		}
		/**
		 * Display redeems column.
		 *
		 * @param array $item Item data.
		 *
		 * @return string Redeems string.
		 */
		public function column_redeems( $item ) {
			$set_redeem_str = get_woocommerce_currency_symbol() . ' ' . $item['redeems'];

			return $set_redeem_str;
		}
		/**
		 * Display checkbox column.
		 *
		 * @param array $item Item data.
		 *
		 * @return string Checkbox HTML.
		 */
		public function column_cb( $item ) {
			$cb_box = sprintf( '<input type="checkbox" name="delete_cb[]" value="%d" />', intval( $item['id'] ) );

			return $cb_box;
		}
		/**
		 * Display default column.
		 *
		 * @param array  $item Item data.
		 * @param string $column_name Column name.
		 *
		 * @return mixed Column value.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'display_name':
				case 'mobile_number':
				case 'merchant_order_id':
				case 'transaction_id':
				case 'redeems':
				case 'date':
				case 'status':
				case 'message':
				case 'payment_method':
					return $item[ $column_name ];

				default:
					return $item;
			}
		}
		/**
		 * Allows you to sort the data by the variables set in the $_GET.
		 *
		 * @param array $a First item.
		 * @param array $b Second item.
		 *
		 * @return int Comparison result.
		 */
		private function sort_data( $a, $b ) {
			// Set defaults.
			$orderby  = 'date';
			$order    = 'desc';
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			// If orderby is set, use this as the sort column.
			if ( ! empty( $url_data['orderby'] ) ) {
				$orderby = sanitize_text_field( wp_unslash( $url_data['orderby'] ) );
			}
			// If order is set use this as the order.
			if ( ! empty( $url_data['order'] ) ) {
				$order = sanitize_text_field( wp_unslash( $url_data['order'] ) );
			}
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
			if ( 'asc' === $order ) {
				return $result;
			}

			return -$result;
		}
		/**
		 * Get bulk actions.
		 *
		 * @return array Bulk actions.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => 'Delete',
			);

			return $actions;
		}
		/**
		 * Display message when no items found.
		 */
		public function no_items() {
			esc_html_e( 'No records found.', 'multilevel-referral-plugin-for-woocommerce' );
		}
		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			global $wpdb;

			// security check!
			if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
				$nonce  = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
				$action = 'bulk-' . $this->_args['plural'];

				if ( ! wp_verify_nonce( $nonce, $action ) ) {
					wp_die( 'Nope! Security check failed!' );
				}
			}

			$action = $this->current_action();
			switch ( $action ) {
				case 'delete':
					if ( isset( $_GET['delete_cb'] ) ) {

						$delete_items = is_array( $_GET['delete_cb'] )
							? array_map( 'intval', $_GET['delete_cb'] )
							: array( intval( $_GET['delete_cb'] ) );

						if ( empty( $delete_items ) ) {
							break;
						}
						foreach ( array_map( 'intval', $delete_items ) as $delete_id ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->delete(
								$wpdb->prefix . 'redeem_history',
								array( 'id' => $delete_id ),
								array( '%d' )
							);
						}
						add_action( 'admin_notices', array( $this, 'mef_delete_admin_notice__success' ) );
					}
					break;
				case 'save':
					wp_die( 'Save something' );
					break;
				default:
					// do nothing or something else.
					break;
			}
		}
		/**
		 * Display success notice after deletion.
		 */
		public function mef_delete_admin_notice__success() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Withdraw transaction deleted', 'multilevel-referral-plugin-for-woocommerce' ); ?></p>
			</div>
			<?php
		}
	}
}
