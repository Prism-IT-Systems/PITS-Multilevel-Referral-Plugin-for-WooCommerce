<?php
/**
 * WooCommerce Order Handler Class
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Order' ) ) {
	/**
	 * Main wocommerce order class handler
	 */
	class WooCommerce_Multilevel_Referral_Order extends WooCommerce_Multilevel_Referral {
		/**
		 * Maximum credit levels.
		 *
		 * @var int
		 */
		private $max_credit_levels;

		/**
		 * Current credit level.
		 *
		 * @var int
		 */
		private $current_credit_level;

		/**
		 * Constructor.
		 */
		public function __construct() {
			global $woocommerce_multilevel_referral_users, $woocommerce_multilevel_referral_program;
			$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
			$woocommerce_multilevel_referral_users   = WooCommerce_Multilevel_Referral_Users::get_instance();
			add_action( 'init', array( $this, 'woocommerce_multilevel_referral_init' ) );
			// Handle post events.
			add_action( 'woocommerce_before_cart', array( $this, 'woocommerce_multilevel_referral_store_credits_notice' ) );
			// Display available store credits on cart page.
			add_action( 'woocommerce_before_checkout_form', array( $this, 'woocommerce_multilevel_referral_store_credits_notice' ) );
			// Display available store credits on checkout page.
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'woocommerce_multilevel_referral_store_credit_info' ) );
			// Display used store credits on cart/checkout total section.
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'woocommerce_multilevel_referral_save_store_credits' ) );
			// Save credits on order.
			add_action( 'woocommerce_order_status_completed', array( $this, 'woocommerce_multilevel_referral_add_store_credits' ) );
			// Add credits on order.
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'woocommerce_multilevel_referral_remove_store_credits' ) );
			// Remove previous added credits on order cancellation.
			add_action( 'woocommerce_order_status_refunded', array( $this, 'woocommerce_multilevel_referral_remove_store_credits' ) );
			// Remove previous added credits on order cancellation.
			add_action( 'woocommerce_order_status_failed', array( $this, 'woocommerce_multilevel_referral_remove_store_credits' ) );
			// Remove previous added credits on order cancellation.
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'woocommerce_multilevel_referral_wc_new_order_column' ) );
			// Add new column to shop order page.
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'woocommerce_multilevel_referral_wc_manage_order_column' ), 20 );
			// Display custom order column.
			add_filter(
				'woocommerce_cart_totals_fee_html',
				array( $this, 'woocommerce_multilevel_referral_remove_link_for_credits' ),
				10,
				2
			);
			// Add remove link for Store credit to cart/checkout page.
			add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'woocommerce_multilevel_referral_filter_orders_by_referral_code' ), 11 );
			// Add filter to search by referral code.
			add_filter(
				'request',
				array( $this, 'woocommerce_multilevel_referral_referral_code_wise_order' ),
				20,
				1
			);
			// Add query vars to get orders referral code wise.
			$this->current_credit_level = 0;
		}

		/**
		 * Add query vars to shop order main query.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return array Modified query vars.
		 */
		public function woocommerce_multilevel_referral_referral_code_wise_order( $query_vars ) {
			global $typenow, $woocommerce_multilevel_referral_users;
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( isset( $url_data['referral_code'] ) && is_array( $url_data['referral_code'] ) && count( $url_data['referral_code'] ) ) {
				$user_id_list = $woocommerce_multilevel_referral_users->get_all_referral_user_id( array_map( 'sanitize_text_field', wp_unslash( $url_data['referral_code'] ) ), true );
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$query_vars['meta_key'] = '_customer_user';
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				$query_vars['meta_value']   = $user_id_list;
				$query_vars['meta_compare'] = 'IN';
			}
			return $query_vars;
		}

		/**
		 * Add referral code filter to shop order page
		 */
		public function woocommerce_multilevel_referral_filter_orders_by_referral_code() {
			global $typenow, $woocommerce_multilevel_referral_program;
			$url_data                                  = woocommerce_multilevel_referral_get_query_vars();
			$woocommerce_multilevel_referral_code_list = $woocommerce_multilevel_referral_program->get_referral_code_list();
			$woocommerce_multilevel_referral_get_referral_code = array();
			$woocommerce_multilevel_referral_get_referral_code = ( isset( $url_data['referral_code'] ) && is_array( $url_data['referral_code'] ) && count( $url_data['referral_code'] ) ? array_map( 'sanitize_text_field', wp_unslash( $url_data['referral_code'] ) ) : array() );
			require_once WOOCOMMERCE_MULTILEVEL_REFERRAL_DIR . '/views/admin/referral-code-filter.php';
		}

		/**
		 * Manage custom order column.
		 *
		 * @param string $column Column name.
		 */
		public function woocommerce_multilevel_referral_wc_manage_order_column( $column ) {
			global $post, $woocommerce_multilevel_referral_users;
			if ( 'referral_code' === $column ) {
				$order_id = $post->ID;
				// Get an instance of the WC_Order object.
				$order = wc_get_order( $order_id );
				// Get the user ID from WC_Order methods.
				$user_id = $order->get_user_id();
				$user_id = $woocommerce_multilevel_referral_users->referral_user( 'referral_parent', 'user_id', $user_id );
				$user    = $woocommerce_multilevel_referral_users->get_referral_user( $user_id );
				echo esc_html( ( isset( $user['referral_code'] ) ? $user['referral_code'] : '-' ) );
			}
		}

		/**
		 * Add new column to shop order page.
		 *
		 * @param array $columns Columns.
		 *
		 * @return array Modified columns.
		 */
		public function woocommerce_multilevel_referral_wc_new_order_column( $columns ) {
			$new_columns = array();
			foreach ( $columns as $column_name => $column_info ) {
				$new_columns[ $column_name ] = $column_info;
				if ( 'order_number' === $column_name ) {
					$new_columns['referral_code'] = __( 'Code', 'multilevel-referral-plugin-for-woocommerce' );
				}
			}
			return $new_columns;
		}

		/**
		 *  Handle post events
		 *
		 *  @return void
		 *  @throws Exception On error.
		 **/
		public function woocommerce_multilevel_referral_init() {
			$current_user_id = get_current_user_id();
			if ( ! $current_user_id ) {
				return;
			}
			try {
				// WP Validation.
				$validation_errors = new WP_Error();
				if ( isset( $_GET['remove_store_credit'] ) ) {
					WC()->session->set( 'store_credit', 0 );
				}
				// Check action first, then verify nonce - fail early if nonce is missing or invalid.
				if ( isset( $_POST['action'] ) && 'apply_store_credit' === $_POST['action'] ) {
					// Fail early if nonce is missing or invalid.
					if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'apply_store_credit' ) ) {
						throw new Exception( __( 'Security check failed. Please refresh the page and try again.', 'multilevel-referral-plugin-for-woocommerce' ) );
					}
					$user_store_credit     = round( get_user_meta( $current_user_id, 'woocommerce_multilevel_referral_store_credit', true ), 2 );
					$max_store_credit      = round( WC()->session->get( 'max_store_credit' ), 2 );
					$applied_credit_amount = ( isset( $_POST['applied_credit_amount'] ) ? round( sanitize_text_field( wp_unslash( $_POST['applied_credit_amount'] ) ), 2 ) : '' );
					if ( 0 === $applied_credit_amount || ( $user_store_credit !== $applied_credit_amount && $applied_credit_amount > $user_store_credit ) || ( $applied_credit_amount !== $max_store_credit && $max_store_credit < $applied_credit_amount ) ) {
						WC()->session->set( 'store_credit', 0 );
						throw new Exception( __( 'Please make sure that amount should be equal or less than the maximum limit.', 'multilevel-referral-plugin-for-woocommerce' ) );
					}
					WC()->session->set( 'store_credit', $applied_credit_amount );
					wc_add_notice( __( 'Store credits successfully applied', 'multilevel-referral-plugin-for-woocommerce' ) );
				}
			} catch ( Exception $e ) {
				wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
			}
			if ( isset( $_GET['store_credit_info'] ) && sanitize_text_field( wp_unslash( $_GET['store_credit_info'] ) ) ) {
				$data = $this->woocommerce_multilevel_referral_add_level_wise_store_credits( sanitize_text_field( wp_unslash( $_GET['store_credit_info'] ) ) );
				die;
			}
			if ( isset( $_GET['store_referral_info'] ) && sanitize_text_field( wp_unslash( $_GET['store_referral_info'] ) ) ) {
				$data = $this->woocommerce_multilevel_referral_add_store_credits( sanitize_text_field( wp_unslash( $_GET['store_referral_info'] ) ) );
				die;
			}
		}

		/**
		 *  Save credits on order processing.
		 *
		 *  @param int $order_id Order ID.
		 *
		 *  @return void
		 */
		public function woocommerce_multilevel_referral_save_store_credits( $order_id ) {
			global $woocommerce_multilevel_referral_program, $woocommerce_multilevel_referral_users;
			$order       = new WC_Order( $order_id );
			$customer_id = $order->get_customer_id();
			// check for guest user.
			if ( ! $customer_id ) {
				return;
			}
			if ( WC()->session->get( 'store_credit' ) ) {
				$used_store_credit = WC()->session->get( 'store_credit' );
				$user_credits      = get_user_meta( $customer_id, 'woocommerce_multilevel_referral_store_credit', true );
				$woocommerce_multilevel_referral_program->insert(
					array(
						'order_id' => $order_id,
						'user_id'  => $customer_id,
						'redeems'  => $used_store_credit,
					)
				);
				$user_credits = ( $user_credits === $used_store_credit ? 0.0 : $user_credits - $used_store_credit );
				update_user_meta( $customer_id, 'woocommerce_multilevel_referral_store_credit', $user_credits );
				update_post_meta( $order_id, '_store_credit', $used_store_credit );
				WC()->session->set( 'store_credit', 0 );
				WC()->session->set( 'exclude_product_name', '' );
			}
		}

		/**
		 * Check for past orders.
		 *
		 * @param int $customer_id Customer ID.
		 *
		 * @return bool True if customer has past orders.
		 */
		public function woocommerce_multilevel_referral_check_for_past_orders( $customer_id ) {
			// Get customer orders count using WooCommerce optimized query.
			// Using wc_get_orders() instead of get_posts() with meta_query for better performance.
			$customer_orders = wc_get_orders(
				array(
					'customer_id' => $customer_id,
					'limit'       => 2,
					'return'      => 'ids',
					'status'      => array_keys( wc_get_order_statuses() ),
				)
			);
			if ( count( $customer_orders ) > 1 ) {
				return true;
			}
			return false;
		}

		/**
		 * Add level wise store credits.
		 *
		 * @param int $order_id Order ID.
		 */
		public function woocommerce_multilevel_referral_add_level_wise_store_credits( $order_id ) {
			global $woocommerce_multilevel_referral_program, $woocommerce_multilevel_referral_users;
			try {
				$credit_type       = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
				$validation_errors = new WP_Error();
				$order             = new WC_Order( $order_id );
				$user_id           = $order->get_user_id();
				if ( ! $user_id ) {
					return;
				}
				$cart_sub_total               = 0;
				$total_earn_credits           = 0;
				$user_credits                 = floatval( get_user_meta( $user_id, 'woocommerce_multilevel_referral_store_credit', true ) );
				$used_store_credit            = floatval( get_post_meta( $order_id, '_store_credit', true ) );
				$exclude_products_from_credit = array();
				$max_month_earn_limit         = 0;
				if ( ! is_array( $exclude_products_from_credit ) ) {
					$exclude_products_from_credit = explode( ',', $exclude_products_from_credit );
				}
				$total_earn_credits      = 0;
				$discount                = floatval( $order->get_total_discount() );
				$order_total             = floatval( $order->get_subtotal() );
				$credit_for              = get_option( 'woocommerce_multilevel_referral_welcome_credit_for', 'new' );
				$arr_total_level_credits = array();
				$earning_method          = get_option( 'woocommerce_multilevel_referral_earning_method', 'product' );
				foreach ( $order->get_items() as $item ) {
					$product_price = ( isset( $item['line_subtotal'] ) ? floatval( $item['line_subtotal'] ) : 0 );
					$product_qty   = $item['qty'];
					if ( ! in_array( $item['product_id'], $exclude_products_from_credit, true ) ) {
						$cart_sub_total += $product_price;
					} else {
						continue;
					}
					$woocommerce_multilevel_referral_product_credit = $this->fnGetProductFinalCreditPercentage( $item['product_id'] );
					$rate                    = $product_price * 100 / $order_total;
					$product_discount        = $rate * $discount / 100;
					$product_used_credit     = $rate * $used_store_credit / 100;
					$actual_price            = $product_price - ( $product_discount + $product_used_credit );
					$arr_level_credit_prices = array();
					if ( 'commission' === $earning_method && 'percentage' === $credit_type ) {
						$actual_price = round( $actual_price * $woocommerce_multilevel_referral_product_credit / 100, 4 );
					}
					$arr_level_credits       = $this->fnGetProductFinalCreditByLevel(
						$item['product_id'],
						$actual_price,
						$order_id,
						$user_id,
						$product_qty
					);
					$arr_total_level_credits = array_map(
						function () {
							return array_sum( func_get_args() );
						},
						$arr_total_level_credits,
						$arr_level_credits
					);
					$total_earn_credits     += array_sum( $arr_level_credits );
				}
				return $arr_total_level_credits;
			} catch ( Exception $e ) {
				wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
			}
		}

		/**
		 * Add store credits.
		 *
		 * @param int $order_id Order ID.
		 */
		public function woocommerce_multilevel_referral_add_store_credits( $order_id ) {
			global $woocommerce_multilevel_referral_program, $woocommerce_multilevel_referral_users;
			try {
				$woocommerce_multilevel_referral_store_credit_added = get_post_meta( $order_id, 'woocommerce_multilevel_referral_store_credit_added', 0 );
				if ( $woocommerce_multilevel_referral_store_credit_added ) {
					return;
				}
				// WP Validation.
				$validation_errors = new WP_Error();
				$order             = new WC_Order( $order_id );
				$user_id           = $order->get_user_id();
				// check for guest user.
				if ( ! $user_id ) {
					return;
				}
				/**
				 * 26-02-2022
				 *
				 * Order bonus offer
				 */
				$bonus_offer_type = get_option( 'woocommerce_multilevel_referral_bouns_offere_type', 'woocommerce_multilevel_referral_user' );
				if ( 'woocommerce_multilevel_referral_order' === $bonus_offer_type ) {
					$order_offer_credit                              = get_option( 'woocommerce_multilevel_referral_order_level_credit', array() );
					$woocommerce_multilevel_referral_get_order_count = get_user_meta( $user_id, 'woocommerce_multilevel_referral_get_order_count', true );
					$count = ( ! empty( $woocommerce_multilevel_referral_get_order_count ) ? $woocommerce_multilevel_referral_get_order_count : 0 );
					if ( count( $order_offer_credit ) > $count ) {
						$get_order_count = $count + 1;
						update_user_meta( $user_id, 'woocommerce_multilevel_referral_get_order_count', $get_order_count );
						$check_levelbase_credit = $get_order_count;
					} else {
						return;
					}
				} else {
					$check_levelbase_credit = get_option( 'woocommerce-multilevel-referral-levelbase-credit', 0 );
				}
				if ( ! $woocommerce_multilevel_referral_users->referral_user( 'id', 'user_id', $user_id ) ) {
					return;
				}
				$check_join_referrl = $this->woocommerce_multilevel_referral_user_join_referral_program( $user_id );
				if ( $check_join_referrl ) {
					return;
				}
				$cart_sub_total               = 0;
				$total_earn_credits           = 0;
				$credit_type                  = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
				$user_credits                 = floatval( get_user_meta( $user_id, 'woocommerce_multilevel_referral_store_credit', true ) );
				$used_store_credit            = floatval( get_post_meta( $order_id, '_store_credit', true ) );
				$welcome_credit               = 0;
				$exclude_products_from_credit = array();
				$include_products_from_credit = array();
				if ( ! is_array( $exclude_products_from_credit ) ) {
					$exclude_products_from_credit = explode( ',', $exclude_products_from_credit );
				}
				if ( ! is_array( $include_products_from_credit ) ) {
					$include_products_from_credit = explode( ',', $include_products_from_credit );
				}
				$total_earn_credits = 0;
				$discount           = floatval( $order->get_total_discount() );
				$order_total        = floatval( $order->get_subtotal() );
				$has_past_orders    = $this->woocommerce_multilevel_referral_check_for_past_orders( $user_id );
				$credit_for         = get_option( 'woocommerce_multilevel_referral_welcome_credit_for', 'new' );
				$first_purchase     = $woocommerce_multilevel_referral_users->referral_user( 'referal_benefits', 'user_id', $user_id );
				if ( 'all' === $credit_for && ! $has_past_orders ) {
					$first_purchase = 0;
				}
				if ( 'no' === $credit_for ) {
					$first_purchase = 1;
				}
				$max_month_earn_limit = 0;
				$arr_product_credits  = array();
				foreach ( $order->get_items() as $item ) {
					$product_price = ( isset( $item['line_subtotal'] ) ? floatval( $item['line_subtotal'] ) : 0 );
					$product_qty   = $item['qty'];
					if ( ! in_array( $item['product_id'], $exclude_products_from_credit, true ) ) {
						$cart_sub_total += $product_price;
					} else {
						continue;
					}
					if ( is_array( $include_products_from_credit ) && count( $include_products_from_credit ) ) {
						if ( in_array( $item['product_id'], $include_products_from_credit, true ) ) {
							$cart_sub_total += $product_price;
						} else {
							continue;
						}
					}
					$woocommerce_multilevel_referral_product_credit = $this->woocommerce_multilevel_referral_get_product_final_credit_percentage( $item['product_id'] );
					$product_welcome_credit                         = 0;
					$rate                = $product_price * 100 / $order_total;
					$product_discount    = $rate * $discount / 100;
					$product_used_credit = $rate * $used_store_credit / 100;
					$actual_price        = $product_price - ( $product_discount + $product_used_credit );
					if ( 'percentage' === $credit_type ) {
						$product_credit = round( $actual_price * $woocommerce_multilevel_referral_product_credit / 100, 4 );
					} else {
						$product_credit = round( $woocommerce_multilevel_referral_product_credit * $product_qty, 4 );
					}
					if ( 0 === $welcome_credit ) {
						$total_earn_credits += $product_credit;
					}
					if ( ! $first_purchase && 0 === $welcome_credit ) {
						if ( 'percentage' === $credit_type ) {
							$product_credit = round( $product_credit * $woocommerce_multilevel_referral_product_credit / 100, 4 );
						} else {
							$product_credit = round( $woocommerce_multilevel_referral_product_credit * $product_qty, 4 );
						}
					}
					array_push(
						$arr_product_credits,
						array(
							'credit_points' => $product_credit,
							'rate'          => $woocommerce_multilevel_referral_product_credit,
						)
					);
				}
				if ( $cart_sub_total ) {
					$customer_credits = apply_filters( 'woocommerce_multilevel_referral_allow_new_customer_credits', true );
					if ( ! $first_purchase && $customer_credits ) {
						$woocommerce_multilevel_referral_program->insert(
							array(
								'order_id' => $order_id,
								'user_id'  => $user_id,
								'credits'  => $total_earn_credits,
							)
						);
						$woocommerce_multilevel_referral_users->updateAll(
							array(
								'referal_benefits' => 1,
							),
							$user_id
						);
						update_user_meta( $user_id, 'woocommerce_multilevel_referral_store_credit', $user_credits + $total_earn_credits );
						if ( ! is_admin() ) {
							// translators: %s is the total earned credit.
							wc_add_notice( sprintf( __( 'You have earned %s store points.', 'multilevel-referral-plugin-for-woocommerce' ), $total_earn_credits ) );
						}
					}
					$enable_customer_credit = false;
					$levelwise_credit       = false;
					$current_level          = false;
					$is_customer            = false;
					if ( $check_levelbase_credit ) {
						$enable_customer_credit = true;
						$levelwise_credit       = $this->woocommerce_multilevel_referral_add_level_wise_store_credits( $order_id );
						$current_level          = 1;
					}
					$get_order_have = get_user_meta( $user_id, 'woocommerce_multilevel_referral_start_earning', true );
					if ( ! $get_order_have ) {
						update_user_meta( $user_id, 'woocommerce_multilevel_referral_start_earning', 'success' );
					}
					update_post_meta( $order_id, 'woocommerce_multilevel_referral_store_credit_added', 1 );
					if ( 'woocommerce_multilevel_referral_order' === $bonus_offer_type ) {
						$this->woocommerce_multilevel_referral_add_credits_order_to_parent_new(
							$order_id,
							$user_id,
							$arr_product_credits,
							$max_month_earn_limit,
							$enable_customer_credit,
							$check_levelbase_credit,
							$levelwise_credit,
							$current_level
						);
					} else {
						$this->woocommerce_multilevel_referral_add_credits_to_parent_new(
							$order_id,
							$user_id,
							$arr_product_credits,
							$max_month_earn_limit,
							$enable_customer_credit,
							$check_levelbase_credit,
							$levelwise_credit,
							$current_level
						);
					}
				}
			} catch ( Exception $e ) {
				wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
			}
		}

		/**
		 * Join referral program check.
		 *
		 * @param int $user_id User ID.
		 */
		public function woocommerce_multilevel_referral_user_join_referral_program( $user_id ) {
			global $wpdb;
			if ( $user_id ) {
				$sql = 'SELECT id FROM ' . $wpdb->prefix . 'referal_users  WHERE user_id = ' . $user_id;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$checkval = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}referal_users WHERE user_id = %d", $user_id ) );
				if ( ! $checkval ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Get product credit percentage by level.
		 *
		 * @param int   $product_id Product ID.
		 * @param float $actual_price Actual price.
		 * @param int   $order_id Order ID.
		 * @param int   $customer_id Customer ID.
		 * @param int   $product_qty Product quantity.
		 *
		 * @return array Product credits by level.
		 */
		public function woocommerce_multilevel_referral_get_product_final_credit_by_level(
			$product_id,
			$actual_price,
			$order_id,
			$customer_id = 0,
			$product_qty = 0
		) {
			$arr_final_product_credits = array();
			global $woocommerce_multilevel_referral_level_wise_products, $woocommerce_multilevel_referral_users;
			$credit_type                                  = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
			$woocommerce_multilevel_referral_store_credit = floatval( get_option( 'woocommerce_multilevel_referral_store_credit', 0 ) );
			/**
			 * 24-02-2022
			 *
			 * Order offer
			 */
			$bonus_offer_type = get_option( 'woocommerce_multilevel_referral_bouns_offere_type', 'woocommerce_multilevel_referral_user' );
			if ( 'woocommerce_multilevel_referral_order' === $bonus_offer_type ) {
				$max_level_credits                     = get_option( 'woocommerce_multilevel_referral_order_level_credit', array() );
				$max_levels                            = count( $max_level_credits );
				$customer_bonus                        = 0;
				$max_product_level_credits             = 0;
				$max_customer_level_credits            = 0;
				$max_customer_product_level_credits    = 0;
				$max_customer_level_credits_wihout_ref = 0;
			} elseif ( 'woocommerce_multilevel_referral_user' === $bonus_offer_type ) {
				$max_levels                            = get_option( 'woocommerce-multilevel-referral-max-level', 1 );
				$max_level_credits                     = get_option( 'woocommerce-multilevel-referral-level-credit', array() );
				$customer_bonus                        = get_option( 'woocommerce_multilevel_referral_customer_based_bonus', 0 );
				$max_product_level_credits             = get_post_meta( $product_id, 'woocommerce-multilevel-referral-level-credit', true );
				$max_customer_level_credits            = get_option( 'woocommerce-multilevel-referral-level-c', 0 );
				$max_customer_product_level_credits    = get_post_meta( $product_id, 'woocommerce-multilevel-referral-level-c', true );
				$max_customer_level_credits_wihout_ref = get_option( 'woocommerce-multilevel-referral-level-c-new', 0 );
				$user_level_enable                     = get_user_meta( $customer_id, 'woocommerce-multilevel-referral-user-level-enable', true );
				if ( 'on' === $user_level_enable ) {
					$max_product_level_credits          = get_user_meta( $customer_id, 'woocommerce-multilevel-referral-level-credit', true );
					$max_customer_product_level_credits = get_user_meta( $customer_id, 'woocommerce-multilevel-referral-level-c', true );
				}
			}
			if ( $customer_id && $customer_bonus ) {
				$parent_user = $woocommerce_multilevel_referral_users->referral_user( 'referral_parent', 'user_id', $customer_id );
				if ( ! $parent_user ) {
					$max_customer_level_credits = $max_customer_level_credits_wihout_ref;
				}
			}
			$max_customer_level_credits                   = apply_filters(
				'woocommerce_multilevel_referral_max_customer_level_credits',
				$max_customer_level_credits,
				$product_id,
				$order_id
			);
			$max_customer_product_level_credits           = apply_filters(
				'woocommerce_multilevel_referral_max_customer_product_level_credits',
				$max_customer_product_level_credits,
				$product_id,
				$order_id
			);
			$max_category_level_credits                   = $this->woocommerce_multilevel_referral_get_category_credit_percentage_by_level( $product_id );
			$arr_final_product_credits['customer_credit'] = 0;
			if ( isset( $max_customer_product_level_credits ) && '' !== $max_customer_product_level_credits ) {
				$arr_final_product_credits['customer_credit'] = floatval( $max_customer_product_level_credits );
			} elseif ( isset( $max_category_level_credits['customer_credit'] ) && '' !== $max_category_level_credits['customer_credit'] ) {
				$arr_final_product_credits['customer_credit'] = floatval( $max_category_level_credits['customer_credit'] );
			} else {
				$arr_final_product_credits['customer_credit'] = floatval( $max_customer_level_credits );
			}
			if ( 'percentage' === $credit_type ) {
				$arr_final_product_credits['customer_credit'] = round( $actual_price * $arr_final_product_credits['customer_credit'] / 100, 4 );
			} else {
				$arr_final_product_credits['customer_credit'] = round( $arr_final_product_credits['customer_credit'] * $product_qty, 4 );
			}
			for ( $i = 0; $i < $max_levels; $i++ ) {
				$arr_final_product_credits[ $i ] = 0;
				if ( isset( $max_product_level_credits[ $i ] ) && '' !== $max_product_level_credits[ $i ] ) {
					$arr_final_product_credits[ $i ] = floatval( $max_product_level_credits[ $i ] );
				} elseif ( isset( $max_category_level_credits[ $i ] ) && '' !== $max_category_level_credits[ $i ] ) {
					$arr_final_product_credits[ $i ] = floatval( $max_category_level_credits[ $i ] );
				} else {
					$arr_final_product_credits[ $i ] = floatval( $max_level_credits[ $i ] );
				}
				if ( 'percentage' === $credit_type ) {
					$arr_final_product_credits[ $i ] = round( $actual_price * $arr_final_product_credits[ $i ] / 100, 4 );
				} else {
					$arr_final_product_credits[ $i ] = round( $arr_final_product_credits[ $i ] * $product_qty, 4 );
				}
			}
			$woocommerce_multilevel_referral_level_wise_products[ $order_id ][ $product_id ] = $arr_final_product_credits;
			return $arr_final_product_credits;
		}

		/**
		 * Get product final credit percentage.
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return float Product final credit percentage.
		 */
		public function woocommerce_multilevel_referral_get_product_final_credit_percentage( $product_id ) {
			$woocommerce_multilevel_referral_store_credit = floatval( get_option( 'woocommerce_multilevel_referral_store_credit', 0 ) );
			$product_credit                               = floatval( get_post_meta( $product_id, 'woocommerce_multilevel_referral_credits', true ) );
			if ( 0.0 === $product_credit ) {
				$cat_credit = $this->woocommerce_multilevel_referral_get_category_credit_percentage( $product_id );
				if ( 0 !== $cat_credit ) {
					$product_credit = $cat_credit;
				} else {
					$product_credit = $woocommerce_multilevel_referral_store_credit;
				}
			}
			return $product_credit;
		}

		/**
		 * Get category credit percentage by level.
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return array Category credit percentage by level.
		 */
		public function woocommerce_multilevel_referral_get_category_credit_percentage_by_level( $product_id ) {
			$product_terms         = wp_get_post_terms(
				$product_id,
				'product_cat',
				array(
					'fields' => 'ids',
				)
			);
			$arr_credit_percentage = array();
			if ( is_array( $product_terms ) && count( $product_terms ) > 0 ) {
				$arr_credit          = array();
				$arr_customer_credit = array();
				foreach ( $product_terms as $term ) {
					$term_meta = get_option( "product_cat_{$term}" );
					if ( isset( $term_meta['woocommerce_multilevel_referral_level_credit'] ) && $term_meta['woocommerce_multilevel_referral_level_credit'] ) {
						array_push( $arr_credit, $term_meta['woocommerce_multilevel_referral_level_credit'] );
					}
					if ( isset( $term_meta['woocommerce_multilevel_referral_level_c'] ) && $term_meta['woocommerce_multilevel_referral_level_c'] ) {
						array_push( $arr_customer_credit, $term_meta['woocommerce_multilevel_referral_level_c'] );
					}
				}
				$pref = get_option( 'woocommerce_multilevel_referral_cat_pref', 'lowest' );
				if ( is_array( $arr_customer_credit ) && count( $arr_customer_credit ) > 0 ) {
					if ( 'lowest' === $pref ) {
						asort( $arr_customer_credit );
					} else {
						arsort( $arr_customer_credit );
					}
					$arr_customer_credit                      = array_values( $arr_customer_credit );
					$arr_credit_percentage['customer_credit'] = $arr_customer_credit[0];
				}
				if ( is_array( $arr_credit ) && count( $arr_credit ) > 0 ) {
					foreach ( $arr_credit as $credit_a ) {
						if ( is_array( $credit_a ) && count( $credit_a ) > 0 ) {
							foreach ( $credit_a as $key => $percent ) {
								if ( $percent ) {
									$v_p = floatval( $percent );
									if ( isset( $arr_credit_percentage[ $key ] ) ) {
										if ( 'lowest' === $pref ) {
											$arr_credit_percentage[ $key ] = ( $v_p < $arr_credit_percentage[ $key ] ? $v_p : $arr_credit_percentage[ $key ] );
										} else {
											$arr_credit_percentage[ $key ] = ( $v_p > $arr_credit_percentage[ $key ] ? $v_p : $arr_credit_percentage[ $key ] );
										}
									} else {
										$arr_credit_percentage[ $key ] = $v_p;
									}
								}
							}
						}
					}
				}
			}
			return $arr_credit_percentage;
		}

		/**
		 * Get category credit percentage.
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return float Category credit percentage.
		 */
		public function woocommerce_multilevel_referral_get_category_credit_percentage( $product_id ) {
			$product_terms     = wp_get_post_terms(
				$product_id,
				'product_cat',
				array(
					'fields' => 'ids',
				)
			);
			$credit_percentage = 0;
			if ( is_array( $product_terms ) && count( $product_terms ) > 0 ) {
				$arr_credit = array();
				foreach ( $product_terms as $term ) {
					$term_meta = get_option( "product_cat_{$term}" );
					$t_r       = ( isset( $term_meta['woocommerce_multilevel_referral_cat_credit'] ) ? floatval( $term_meta['woocommerce_multilevel_referral_cat_credit'] ) : 0 );
					if ( $t_r > 0 && is_array( $term_meta ) && count( $term_meta ) ) {
						array_push( $arr_credit, floatval( $term_meta['woocommerce_multilevel_referral_cat_credit'] ) );
					}
				}
				$pref = get_option( 'woocommerce_multilevel_referral_cat_pref', 'lowest' );
				if ( is_array( $arr_credit ) && count( $arr_credit ) > 0 ) {
					$credit_percentage = ( 'lowest' === $pref ? min( $arr_credit ) : max( $arr_credit ) );
				}
			}
			return $credit_percentage;
		}

		/*
		 * End category credit percentage.
		 */
		/**
		 *  Deduct earn points from user account.
		 *
		 *  @param int $order_id Order ID.
		 *
		 *  @return void
		 */
		public function woocommerce_multilevel_referral_remove_store_credits( $order_id ) {
			global $woocommerce_multilevel_referral_program;
			$used_store_credit = get_post_meta( $order_id, '_store_credit', true );
			if ( $used_store_credit ) {
				$order        = new WC_Order( $order_id );
				$user_credits = get_user_meta( $order->user_id, 'woocommerce_multilevel_referral_store_credit', true );
				$woocommerce_multilevel_referral_program->insert(
					array(
						'order_id' => $order_id,
						'user_id'  => $order->user_id,
						'credits'  => $used_store_credit,
					)
				);
				$user_credits = $user_credits + $used_store_credit;
				update_user_meta( $order->user_id, 'woocommerce_multilevel_referral_store_credit', $user_credits );
				delete_post_meta( $order_id, '_store_credit' );
			} else {
				$this->woocommerce_multilevel_referral_remove_credits_from_parent( $order_id );
			}
			delete_post_meta( $order_id, 'woocommerce_multilevel_referral_store_credit_added' );
		}

		/**
		 *  Remove commission to referral parent.
		 *
		 *  @param int $order_id Order ID.
		 *
		 *  @return void
		 **/
		public function woocommerce_multilevel_referral_remove_credits_from_parent( $order_id ) {
			global $woocommerce_multilevel_referral_program;
			$user_credit_list = $woocommerce_multilevel_referral_program->get_credits_by_order( $order_id );
			if ( count( $user_credit_list ) > 0 ) {
				foreach ( $user_credit_list as $user_credit ) {
					$user_id            = $user_credit['user_id'];
					$user_credits       = $user_credit['credits'];
					$user_store_credits = get_user_meta( $user_id, 'woocommerce_multilevel_referral_store_credit', true );
					$woocommerce_multilevel_referral_program->insert(
						array(
							'order_id' => $order_id,
							'user_id'  => $user_id,
							'redeems'  => $user_credits,
						)
					);
					$user_store_credits = $user_store_credits - $user_credits;
					update_user_meta( $user_id, 'woocommerce_multilevel_referral_store_credit', $user_store_credits );
				}
			}
		}

		/**
		 * Add commission to referral parent.
		 *
		 * @param int   $order_id Order ID.
		 * @param int   $user_id User ID.
		 * @param array $arr_product_credits Product credits array.
		 * @param float $max_month_earn_limit Max month earn limit.
		 * @param bool  $is_customer Is customer.
		 * @param bool  $check_levelbase_credit Check level base credit.
		 * @param array $levelwise_credit Level wise credit.
		 * @param int   $current_level Current level.
		 *
		 * @return void
		 **/
		public function woocommerce_multilevel_referral_add_credits_to_parent_new(
			$order_id,
			$user_id,
			$arr_product_credits,
			$max_month_earn_limit,
			$is_customer,
			$check_levelbase_credit,
			$levelwise_credit,
			$current_level
		) {
			global $woocommerce_multilevel_referral_program, $woocommerce_multilevel_referral_users;
			$parent_user          = $woocommerce_multilevel_referral_users->referral_user( 'referral_parent', 'user_id', $user_id );
			$credit_type          = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
			$total_new_credits    = 0;
			$main_current_credits = 0;
			$current_credits      = 0;
			$arr_p_credits        = array();
			if ( $is_customer ) {
				$parent_user = $user_id;
				$is_customer = false;
			}
			$level_credit_price  = 0;
			$product_new_credits = 0;
			$level_credit_index  = 0;
			if ( $check_levelbase_credit ) {
				$current_credits    = 0;
				$level_credit_index = $current_level - 1;
				if ( isset( $levelwise_credit[ $current_level - 1 ] ) ) {
					$current_credits = $levelwise_credit[ $current_level - 1 ];
				}
				++$current_level;
			} else {
				foreach ( $arr_product_credits as $p_credit ) {
					$current_credits += floatval( $p_credit['credit_points'] );
					if ( 'percentage' === $credit_type ) {
						$product_new_credits = round( floatval( $p_credit['credit_points'] ) * floatval( $p_credit['rate'] ) / 100, 4 );
					} else {
						$product_new_credits = round( $p_credit['rate'], 4 );
					}
					array_push(
						$arr_p_credits,
						array(
							'credit_points' => $product_new_credits,
							'rate'          => $p_credit['rate'],
						)
					);
				}
			}
			$main_current_credits = $current_credits;
			$go_commission        = true;
			$flag                 = apply_filters(
				'woocommerce_multilevel_referral_allow_customer_credits',
				true,
				$order_id,
				$parent_user
			);
			if ( $parent_user && ( $current_credits !== $product_new_credits || 'fixed' === $credit_type ) && $go_commission && $flag ) {
				$current_month_earning = $woocommerce_multilevel_referral_program->get_current_month_earning( $user_id );
				$max_month_earn_limit  = ( $max_month_earn_limit ? $max_month_earn_limit : 0 );
				if ( 0 === $max_month_earn_limit || $max_month_earn_limit > $current_month_earning ) {
					try {
						$user_credits    = floatval( get_user_meta( $parent_user, 'woocommerce_multilevel_referral_store_credit', true ) );
						$current_credits = apply_filters(
							'woocommerce_multilevel_referral_add_credit_to_user',
							$current_credits,
							$order_id,
							$parent_user
						);
						$this->woocommerce_multilevel_referral_product_commission_add( $order_id, $parent_user, $level_credit_index );
						$woocommerce_multilevel_referral_program->insert(
							array(
								'order_id' => $order_id,
								'user_id'  => $parent_user,
								'credits'  => $current_credits,
							)
						);
						update_user_meta( $parent_user, 'woocommerce_multilevel_referral_store_credit', $user_credits + $current_credits );
					} catch ( Exception $e ) {
						wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
					}
				}
				++$this->current_credit_level;
				$this->woocommerce_multilevel_referral_add_credits_to_parent_new(
					$order_id,
					$parent_user,
					$arr_p_credits,
					$max_month_earn_limit,
					$is_customer,
					$check_levelbase_credit,
					$levelwise_credit,
					$current_level
				);
			} elseif ( isset( $levelwise_credit[ $current_level - 1 ] ) ) {
				$this->woocommerce_multilevel_referral_add_credits_to_parent_new(
					$order_id,
					$parent_user,
					$arr_p_credits,
					$max_month_earn_limit,
					$is_customer,
					$check_levelbase_credit,
					$levelwise_credit,
					$current_level
				);
			}
		}

		/**
		 * Add credits order to parent new.
		 *
		 * @param int   $order_id Order ID.
		 * @param int   $user_id User ID.
		 * @param array $arr_product_credits Product credits array.
		 * @param float $max_month_earn_limit Max month earn limit.
		 * @param bool  $is_customer Is customer.
		 * @param bool  $check_levelbase_credit Check level base credit.
		 * @param array $levelwise_credit Level wise credit.
		 * @param int   $current_level Current level.
		 *
		 * @return void
		 **/
		public function woocommerce_multilevel_referral_add_credits_order_to_parent_new(
			$order_id,
			$user_id,
			$arr_product_credits,
			$max_month_earn_limit,
			$is_customer,
			$check_levelbase_credit,
			$levelwise_credit,
			$current_level
		) {
			global $woocommerce_multilevel_referral_program, $woocommerce_multilevel_referral_users;
			$total_new_credits    = 0;
			$main_current_credits = 0;
			$current_credits      = 0;
			$parent_user          = $woocommerce_multilevel_referral_users->referral_user( 'referral_parent', 'user_id', $user_id );
			$credit_type          = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
			$arr_p_credits        = array();
			$level_credit_price   = 0;
			$product_new_credits  = 0;
			$level_credit_index   = $check_levelbase_credit;
			if ( $check_levelbase_credit ) {
				$current_credits = 0;
				if ( isset( $levelwise_credit[ $check_levelbase_credit ] ) ) {
					$current_credits = $levelwise_credit[ $check_levelbase_credit ];
				}
			} else {
				foreach ( $arr_product_credits as $p_credit ) {
					$current_credits += floatval( $p_credit['credit_points'] );
					if ( 'percentage' === $credit_type ) {
						$product_new_credits = round( floatval( $p_credit['credit_points'] ) * floatval( $p_credit['rate'] ) / 100, 4 );
					} else {
						$product_new_credits = round( $p_credit['rate'], 4 );
					}
					array_push(
						$arr_p_credits,
						array(
							'credit_points' => $product_new_credits,
							'rate'          => $p_credit['rate'],
						)
					);
				}
			}
			$main_current_credits = $current_credits;
			$go_commission        = true;
			$flag                 = apply_filters(
				'woocommerce_multilevel_referral_allow_customer_credits',
				true,
				$order_id,
				$parent_user
			);
			if ( $parent_user && ( $current_credits !== $product_new_credits || 'fixed' === $credit_type ) && $go_commission && $flag ) {
				$current_month_earning = $woocommerce_multilevel_referral_program->get_current_month_earning( $user_id );
				$max_month_earn_limit  = ( $max_month_earn_limit ? $max_month_earn_limit : 0 );
				if ( 0 === $max_month_earn_limit || $max_month_earn_limit > $current_month_earning ) {
					try {
						$user_credits    = floatval( get_user_meta( $parent_user, 'woocommerce_multilevel_referral_store_credit', true ) );
						$current_credits = apply_filters(
							'woocommerce_multilevel_referral_add_credit_to_user',
							$current_credits,
							$order_id,
							$parent_user
						);
						$this->product_commission_add( $order_id, $parent_user, $level_credit_index );
						$woocommerce_multilevel_referral_program->insert(
							array(
								'order_id' => $order_id,
								'user_id'  => $parent_user,
								'credits'  => $current_credits,
							)
						);
						update_user_meta( $parent_user, 'woocommerce_multilevel_referral_store_credit', $user_credits + $current_credits );
					} catch ( Exception $e ) {
						wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
					}
				}
			}
		}

		/**
		 * Add product commission.
		 *
		 * @param int $order_id Order ID.
		 * @param int $user_id User ID.
		 * @param int $level_id Level ID.
		 */
		public function woocommerce_multilevel_referral_product_commission_add( $order_id, $user_id, $level_id ) {
			global $woocommerce_multilevel_referral_level_wise_products, $woocommerce_multilevel_referral_program;
			if ( isset( $woocommerce_multilevel_referral_level_wise_products[ $order_id ] ) ) {
				foreach ( $woocommerce_multilevel_referral_level_wise_products[ $order_id ] as $prod_id => $value ) {
					$data               = array();
					$data['user_id']    = $user_id;
					$data['order_id']   = $order_id;
					$data['product_id'] = $prod_id;
					if ( 0 === $level_id ) {
						if ( isset( $value['customer_credit'] ) ) {
							$data['credits'] = $value['customer_credit'];
						}
					} elseif ( isset( $value[ $level_id - 1 ] ) ) {
						$data['credits'] = $value[ $level_id - 1 ];
					}
					$data = apply_filters(
						'woocommerce_multilevel_referral_add_credit_oprations',
						$data,
						$order_id,
						$user_id,
						$prod_id
					);
					$woocommerce_multilevel_referral_program->insert_product_commission( $data );
				}
			}
		}

		/**
		 * Add credits to parent.
		 *
		 * @param int   $order_id Order ID.
		 * @param int   $user_id User ID.
		 * @param float $earn_credits Earn credits.
		 * @param float $woocommerce_multilevel_referral_store_credit Store credit.
		 * @param float $max_month_earn_limit Max month earn limit.
		 */
		public function woocommerce_multilevel_referral_add_credits_to_parent(
			$order_id,
			$user_id,
			$earn_credits,
			$woocommerce_multilevel_referral_store_credit,
			$max_month_earn_limit
		) {
			global $woocommerce_multilevel_referral_users, $woocommerce_multilevel_referral_program;
			$parent_user      = $woocommerce_multilevel_referral_users->referral_user( 'referral_parent', 'user_id', $user_id );
			$new_earn_credits = round( $earn_credits * $woocommerce_multilevel_referral_store_credit / 100, 4 );
			// Check parent user is exist or not.
			// Add earning while reached to max earning limit.
			if ( $parent_user && $new_earn_credits !== $earn_credits ) {
				$current_month_earning = $woocommerce_multilevel_referral_program->get_current_month_earning( $user_id );
				// Check monthly limit is reached or not.
				if ( 0 === $max_month_earn_limit || $max_month_earn_limit > $current_month_earning ) {
					try {
						$user_credits = get_user_meta( $parent_user, 'woocommerce_multilevel_referral_store_credit', true );
						$woocommerce_multilevel_referral_program->insert(
							array(
								'order_id' => $order_id,
								'user_id'  => $parent_user,
								'credits'  => $earn_credits,
							)
						);
						update_user_meta( $parent_user, 'woocommerce_multilevel_referral_store_credit', $user_credits + $earn_credits );
					} catch ( Exception $e ) {
						wc_add_notice( '<strong>' . __( 'Error', 'multilevel-referral-plugin-for-woocommerce' ) . ':</strong> ' . $e->getMessage(), 'error' );
					}
				}
				$this->woocommerce_multilevel_referral_add_credits_to_parent(
					$order_id,
					$parent_user,
					$new_earn_credits,
					$woocommerce_multilevel_referral_store_credit,
					$max_month_earn_limit
				);
			}
		}

		/**
		 *  Display notice when current user has earn points for withdrawl
		 *
		 *  @return void
		 **/
		public function woocommerce_multilevel_referral_store_credits_notice() {
			woocommerce_output_all_notices();
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
			if ( ! WC()->session->get( 'store_credit' ) && apply_filters( 'woocommerce_multilevel_referral_allow_redeem_points', true ) ) {
				global $woocommerce_multilevel_referral_program;
				$available_credits                            = $woocommerce_multilevel_referral_program->available_credits( get_current_user_id() );
				$woocommerce_multilevel_referral_store_credit = get_user_meta( get_current_user_id(), 'woocommerce_multilevel_referral_store_credit', true );
				if ( $available_credits !== $woocommerce_multilevel_referral_store_credit ) {
					$woocommerce_multilevel_referral_store_credit = $available_credits;
					update_user_meta( get_current_user_id(), 'woocommerce_multilevel_referral_store_credit', $available_credits );
				}
				if ( $woocommerce_multilevel_referral_store_credit ) {
					$max_use_credit = $this->woocommerce_multilevel_referral_get_store_credit();
					if ( ! $max_use_credit ) {
						return;
					}
					$notice = '';
					if ( WC()->session->get( 'exclude_product_name' ) ) {
						// translators: %s is the exclude product name.
						$notice = ' ' . sprintf( __( 'You can not use Store credit in following products: %s', 'multilevel-referral-plugin-for-woocommerce' ), WC()->session->get( 'exclude_product_name' ) );
					}
					$applied_credit_amount = '';
					$nonce                 = ( isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '' );
					if ( wp_verify_nonce( $nonce, 'apply_store_credit' ) ) {
						$applied_credit_amount = ( isset( $_POST['applied_credit_amount'] ) ? esc_html( sanitize_text_field( wp_unslash( $_POST['applied_credit_amount'] ) ) ) : esc_html( $max_use_credit ) );
					}
					echo wp_kses(
						self::render_template(
							'front/store-credits-notice.php',
							array(
								'data' => array(
									'store_credit'   => esc_html( $woocommerce_multilevel_referral_store_credit ),
									'nonce'          => esc_html( wp_create_nonce( 'apply_store_credit' ) ),
									'max_use_credit' => esc_html( $max_use_credit ),
									'applied_credit_amount' => $applied_credit_amount,
									'notice'         => wp_kses_post( $notice ),
								),
							)
						),
						woocommerce_multilevel_referral_get_wp_fs_allow_html()
					);
				}
			}
		}

		/**
		 *  Add Store Credit to cart page.
		 **/
		public function woocommerce_multilevel_referral_store_credit_info() {
			if ( WC()->session->get( 'store_credit' ) ) {
				$applied_store_credit = $this->woocommerce_multilevel_referral_get_store_credit();
				if ( $applied_store_credit > 0 ) {
					WC()->cart->add_fee( __( 'Store Credit', 'multilevel-referral-plugin-for-woocommerce' ), -1 * $applied_store_credit );
				}
			}
		}

		/**
		 *  Get current user store credit.
		 *
		 *  @return float Return credits.
		 **/
		public function woocommerce_multilevel_referral_get_store_credit() {
			global $woocommerce_multilevel_referral_users;
			$current_user_id = get_current_user_id();
			if ( ! $current_user_id ) {
				return;
			}
			if ( ! $woocommerce_multilevel_referral_users->referral_user( 'id', 'user_id', $current_user_id ) ) {
				return;
			}
			$woocommerce_multilevel_referral_store_credit = 0;
			$max_store_credit                             = 0;
			$cart_total                                   = 0;
			$applied_store_credit                         = WC()->session->get( 'store_credit' );
			$cart_discount_total                          = WC()->cart->get_cart_discount_total();
			$exclude_products_from_credit                 = array();
			$include_products_from_credit                 = array();
			$exclude_product_list                         = '';
			$seperator                                    = '';
			if ( ! is_array( $exclude_products_from_credit ) ) {
				$exclude_products_from_credit = explode( ',', $exclude_products_from_credit );
			}
			if ( ! is_array( $include_products_from_credit ) ) {
				$include_products_from_credit = explode( ',', $include_products_from_credit );
			}
			foreach ( WC()->cart->get_cart() as $item ) {
				if ( ! in_array( $item['product_id'], $exclude_products_from_credit, true ) ) {
					$cart_total += ( isset( $item['line_subtotal'] ) ? $item['line_subtotal'] : 0 );
				} else {
					$exclude_product_list .= $seperator . get_the_title( $item['product_id'] );
					$seperator             = ', ';
				}
				if ( is_array( $include_products_from_credit ) && count( $include_products_from_credit ) ) {
					if ( in_array( $item['product_id'], $include_products_from_credit, true ) ) {
						$cart_total += ( isset( $item['line_subtotal'] ) ? $item['line_subtotal'] : 0 );
					} else {
						$exclude_product_list .= $seperator . get_the_title( $item['product_id'] );
						$seperator             = ', ';
					}
				}
			}
			if ( $exclude_product_list ) {
				WC()->session->set( 'exclude_product_name', $exclude_product_list );
			} else {
				WC()->session->set( 'exclude_product_name', '' );
			}
			if ( $cart_discount_total === $cart_total ) {
				if ( WC()->session->get( 'store_credit' ) ) {
					WC()->session->set( 'store_credit', 0 );
					wc_add_notice( __( 'Store credits is removed becuase of cart total is same as discount.', 'multilevel-referral-plugin-for-woocommerce' ), 'notice' );
				}
				return 0;
			}
			if ( $cart_discount_total ) {
				$cart_total -= $cart_discount_total;
			}
			if ( $applied_store_credit ) {
				$woocommerce_multilevel_referral_store_credit = $applied_store_credit;
			} else {
				$woocommerce_multilevel_referral_store_credit = get_user_meta( $current_user_id, 'woocommerce_multilevel_referral_store_credit', true );
			}
			$woocommerce_multilevel_referral_max_redumption = 100;
			$max_store_credit                               = 0;
			if ( intval( $woocommerce_multilevel_referral_max_redumption ) ) {
				$max_store_credit = round( $cart_total * $woocommerce_multilevel_referral_max_redumption / 100, 2 );
			}
			if ( $woocommerce_multilevel_referral_store_credit > 0 && $cart_total > 0 && $max_store_credit > 0 ) {
				$store_credit     = ( $cart_total > $woocommerce_multilevel_referral_store_credit ? $woocommerce_multilevel_referral_store_credit : $cart_total );
				$max_store_credit = ( $max_store_credit < $store_credit ? $max_store_credit : $store_credit );
				if ( WC()->session->get( 'store_credit' ) ) {
					WC()->session->set( 'store_credit', $max_store_credit );
				}
				WC()->session->set( 'max_store_credit', $max_store_credit );
				return $max_store_credit;
			}
			return 0;
		}

		/**
		 *  Add remove link for store credit to cart/checkout page.
		 *
		 *  @param string $cart_totals_fee_html HTML of store credit.
		 *  @param float  $fee Applied store credit.
		 *
		 *  @return string Modified HTML with remove link.
		 **/
		public function woocommerce_multilevel_referral_remove_link_for_credits( $cart_totals_fee_html, $fee ) {
			$link = '';
			if ( __( 'Store Credit', 'multilevel-referral-plugin-for-woocommerce' ) === $fee->name ) {
				$link = '<a href="' . add_query_arg( 'remove_store_credit', true, get_the_permalink() ) . '">[' . __( 'Remove', 'multilevel-referral-plugin-for-woocommerce' ) . ']</a>';
			}
			return $cart_totals_fee_html . $link;
		}
	}

	// end WooCommerce_Multilevel_Referral_Order.
	$GLOBALS['woocommerce_multilevel_referral_order'] = new WooCommerce_Multilevel_Referral_Order();
}
