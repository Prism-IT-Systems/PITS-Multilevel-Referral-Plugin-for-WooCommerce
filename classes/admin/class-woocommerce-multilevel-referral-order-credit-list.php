<?php
/**
 * Admin Orderwise Credits Table
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 * @since   2.28.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Order_Credit_List' ) ) :
	/**
	 * WooCommerce Multilevel Referral Order Credit List Table Class.
	 *
	 * @package Multilevel_Referral_Plugin_For_WooCommerce
	 * @since   2.28.1
	 */
	class WooCommerce_Multilevel_Referral_Order_Credit_List extends WP_List_Table {
		/**
		 * Constructor.
		 */
		public function __construct() {
			global $woocommerce_multilevel_referral_program;
			parent::__construct(
				array(
					'singular' => __( 'Order', 'multilevel-referral-plugin-for-woocommerce' ), // Singular name of the listed records.
					'plural'   => __( 'Orders', 'multilevel-referral-plugin-for-woocommerce' ), // Plural name of the listed records.
					'ajax'     => false, // Should this table support ajax?
				)
			);
			$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
		}
		/** Text displayed when no customer data is available */
		public function no_items() {
			esc_html_e( 'No orders avaliable.', 'multilevel-referral-plugin-for-woocommerce' );
		}
		/**
		 * Render a column when no column specific method exists.
		 *
		 * @param array  $item        Item data.
		 * @param string $column_name Column name.
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'order_id':
					return edit_post_link( '#' . $item[ $column_name ], '', '', $item[ $column_name ] );
				case 'user_id':
						$user = get_user_by( 'id', $item['user_id'] );
						$name = $user->first_name . ' ' . $user->last_name;
					return $name;
				case 'credits':
					return wc_price( $item[ $column_name ] );
				default:
					return $item;
			}
		}
		/**
		 * Associative array of columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'order_id' => __( 'Order', 'multilevel-referral-plugin-for-woocommerce' ),
				'user_id'  => __( 'Customer Name', 'multilevel-referral-plugin-for-woocommerce' ),
				'credits'  => __( 'Total Credits from this Order', 'multilevel-referral-plugin-for-woocommerce' ),
			);
			return $columns;
		}
		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'order_id' => array( 'order_id', false ),
				'credits'  => array( 'credits', false ),
			);
			return $sortable_columns;
		}
		/**
		 * Handles data query and filter, sorting, and pagination.
		 *
		 * @return void
		 */
		public function prepare_items() {
			global $woocommerce_multilevel_referral_program, $wpdb;
			$columns       = $this->get_columns();
			$hidden        = array();
			$sortable      = $this->get_sortable_columns();
			$user_id       = null;
			$order_id      = null;
			$search_result = false;
			$all_record    = false;
			$url_data      = woocommerce_multilevel_referral_get_query_vars();
			if ( isset( $url_data['s'] ) && ! empty( $url_data['s'] ) ) {
				$obj_referal_users = WooCommerce_Multilevel_Referral_Users::get_instance();
				$user_id           = $obj_referal_users->referral_user( 'user_id', 'referral_code', sanitize_text_field( wp_unslash( $url_data['s'] ) ) );
				if ( $user_id ) {
					$user_id = $obj_referal_users->get_all_referral_user_id( array( $user_id ) );
				} else {
					$search_result = true;
				}
			}
			$this->_column_headers = array( $columns, $hidden, $sortable );
			if ( $search_result && isset( $url_data['s'] ) && ! empty( $url_data['s'] ) ) {
					$wp_user_query = new WP_User_Query(
						array(
										   // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							'meta_query' => array(
								'relation' => 'OR',
								array(
									'key'     => 'first_name',
									'value'   => sanitize_text_field( wp_unslash( $url_data['s'] ) ),
									'compare' => 'LIKE',
								),
								array(
									'key'     => 'last_name',
									'value'   => sanitize_text_field( wp_unslash( $url_data['s'] ) ),
									'compare' => 'LIKE',
								),
							),
						)
					);
					$users         = $wp_user_query->get_results();
				if ( $users && count( $users ) ) {
					$user_id = array();
					foreach ( $users as $user ) {
							$user_id[]     = $user->ID;
							$search_result = false;
							$all_record    = true;
					}
				}
			}
			if ( $search_result && isset( $url_data['s'] ) && ! empty( $url_data['s'] ) && is_int( intval( $url_data['s'] ) ) ) {
				$order_id = $obj_referal_users->get_orders_by_id( sanitize_text_field( wp_unslash( $url_data['s'] ) ) );
				if ( is_array( $order_id ) && count( $order_id ) ) {
					$search_result = false;
					$all_record    = true;
				}
			}
			$post_per_page = get_option( 'posts_per_page' );
			$per_page      = $this->get_items_per_page( 'orders_per_page', $post_per_page );
			$current_page  = $this->get_pagenum();
			$total_items   = $search_result ? 0 : $woocommerce_multilevel_referral_program->record_count( 'credits', $all_record, $user_id, $order_id );
			$this->set_pagination_args(
				array(
					'total_items' => $total_items, // We have to calculate the total number of items.
					'per_page'    => $per_page, // We have to determine how many items to show on a page.
				)
			);
			$this->items = $search_result ? array() : $woocommerce_multilevel_referral_program->get_credits( $per_page, $current_page, $user_id, $order_id );
		}
		/**
		 * Update credits form.
		 *
		 * @param array $item Item data.
		 * @return string
		 */
		public function column_update_credites( $item ) {
				return sprintf(
					'<input type="text" name="update_credites[%d]" value="%d" />',
					$item['order_id'],
					$item['credits']
				);
		}
		/**
		 * Search box.
		 *
		 * @param string $text     Search text.
		 * @param string $input_id Input ID.
		 * @return void
		 */
		public function search_box( $text, $input_id ) {
			?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_html( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_html( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
			<br />
			<span class="description_panel"><?php esc_html_e( 'You can search by Customer name, Order ID and Referral code', 'multilevel-referral-plugin-for-woocommerce' ); ?></span>
		</p>
			<?php
		}
	}
endif;
