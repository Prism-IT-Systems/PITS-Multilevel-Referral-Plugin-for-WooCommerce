<?php
/**
 * Admin User Table
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

if ( ! class_exists( 'WooCommerce_Multilevel_Referral_User_Table' ) ) :
	/**
	 * WooCommerce Multilevel Referral User Table Class.
	 *
	 * @package Multilevel_Referral_Plugin_For_WooCommerce
	 * @since   2.28.1
	 */
	class WooCommerce_Multilevel_Referral_User_Table extends WP_List_Table {
		/**
		 * Is site users.
		 *
		 * @var bool
		 */
		public $is_site_users;
		/**
		 * Constructor.
		 */
		public function __construct() {
			global $status, $page, $woocommerce_multilevel_referral_program;
			parent::__construct(
				array(
					'singular' => __( 'User', 'multilevel-referral-plugin-for-woocommerce' ), // Singular name of the listed records.
					'plural'   => __( 'Users', 'multilevel-referral-plugin-for-woocommerce' ), // Plural name of the listed records.
					'ajax'     => false, // Should this table support ajax?
				)
			);
			$woocommerce_multilevel_referral_program = WooCommerce_Multilevel_Referral_Program::get_instance();
			add_action( 'admin_head', array( &$this, 'admin_header' ) );
		}
		/**
		 * Admin header.
		 *
		 * @return void
		 */
		public function admin_header() {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			$page     = ( isset( $url_data['page'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $url_data['page'] ) ) ) : false;
			if ( 'wc_referral' !== $page ) {
				return;
			}
			echo '<style type="text/css">';
			echo '.search_email{ width:42%}';
			echo '</style>';
		}
		/**
		 * No items message.
		 *
		 * @return void
		 */
		public function no_items() {
			echo esc_html( __( 'No users found, dude.', 'multilevel-referral-plugin-for-woocommerce' ) );
		}
		/**
		 * Column default.
		 *
		 * @param array  $item        Item data.
		 * @param string $column_name Column name.
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'display_name':
				case 'email':
				case 'join_date':
				case 'referrer_name':
				case 'referral_code':
				case 'no_of_followers':
				case 'total_credits':
				case 'view_hierarchie':
					return $item[ $column_name ];
				default:
					return $item;
			}
		}
		/**
		 * Bulk actions.
		 *
		 * @param string $which Which position.
		 * @return void
		 */
		public function bulk_actions( $which = '' ) {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( is_null( $this->_actions ) ) {
				$this->_actions = $this->get_bulk_actions();
				$no_new_actions = $this->_actions;
				$this->_actions = apply_filters( "woocommerce_multilevel_referral_bulk_actions_{$this->screen->id}", $this->_actions );
				$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
				$two            = '1';
			} else {
				$two = '2';
			}
			if ( 1 === (int) $two ) {
				if ( isset( $url_data['user_status'] ) && '0' === $url_data['user_status'] ) {
					echo '<input type="hidden" name="user_status" value="0" />';
				}
				echo '<input type="text" name="search_by_name" placeholder="' . esc_html( __( 'Search By Name', 'multilevel-referral-plugin-for-woocommerce' ) ) . '" value="' . ( isset( $url_data['search_by_name'] ) ? esc_html( sanitize_text_field( wp_unslash( $url_data['search_by_name'] ) ) ) : '' ) . '" class="woocommerce_multilevel_referral_search_by_name"/>';
				echo '<input type="text" name="search_by_email" class="search_email" placeholder="' . esc_html( __( 'Search By Email', 'multilevel-referral-plugin-for-woocommerce' ) ) . '" value="' . ( isset( $url_data['search_by_email'] ) ? esc_html( sanitize_text_field( wp_unslash( $url_data['search_by_email'] ) ) ) : '' ) . '"/>';
				echo '<lable>' . esc_html( __( 'Date Range', 'multilevel-referral-plugin-for-woocommerce' ) ) . ' :</lable>';
				echo '<input type="text" name="search_by_join_sdate" placeholder="YYYY-MM-DD" value="' . ( isset( $url_data['search_by_join_sdate'] ) ? esc_html( sanitize_text_field( wp_unslash( $url_data['search_by_join_sdate'] ) ) ) : '' ) . '" class="woocommerce_multilevel_referral_search_by_date"/>';
				echo '<input type="text" name="search_by_join_edate" placeholder="YYYY-MM-DD" value="' . ( isset( $url_data['search_by_join_edate'] ) ? esc_html( sanitize_text_field( wp_unslash( $url_data['search_by_join_edate'] ) ) ) : '' ) . '" class="woocommerce_multilevel_referral_search_by_date" />';
				submit_button( esc_html( __( 'Apply', 'multilevel-referral-plugin-for-woocommerce' ) ), 'action', '', false, array( 'id' => 'doaction' ) );
				echo '<input type="button" value="' . esc_html( __( 'Reset', 'multilevel-referral-plugin-for-woocommerce' ) ) . '" class="button action" id="reset_button"><br />';
				if ( ! isset( $url_data['tab'] ) && ! isset( $url_data['user_status'] ) ) {
					echo '<div class="in_active_user_panel"><a class="button-secondary" href="' . esc_url( admin_url( 'admin.php?page=wc_referral&user_status=0' ) ) . '">' . esc_html( __( 'Deleted Referrals', 'multilevel-referral-plugin-for-woocommerce' ) ) . '</a></div>';
				}
			}
		}
		/**
		 * Get sortable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'join_date'       => array( 'join_date', false ),
				'display_name'    => array( 'display_name', true ),
				'email'           => array( 'email', false ),
				'referrer_name'   => array( 'referrer_name', false ),
				'referral_code'   => array( 'referral_code', false ),
				'no_of_followers' => array( 'no_of_followers', false ),
				'total_credits'   => array( 'total_credits', false ),
				'view_hierarchie' => array( 'view_hierarchie', false ),
				'deactivate_date' => array( 'deactivate_date', false ),
			);
			return $sortable_columns;
		}
		/**
		 * Get columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			$c        = array(
				'display_name'    => __( 'Name', 'multilevel-referral-plugin-for-woocommerce' ),
				'email'           => __( 'Email', 'multilevel-referral-plugin-for-woocommerce' ),
				'referrer_name'   => __( 'Referrer', 'multilevel-referral-plugin-for-woocommerce' ),
				'referral_code'   => __( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ),
				'join_date'       => __( 'Join Date', 'multilevel-referral-plugin-for-woocommerce' ),
				'no_of_followers' => __( 'Total Referrals', 'multilevel-referral-plugin-for-woocommerce' ),
				'total_credits'   => __( 'Total Credits', 'multilevel-referral-plugin-for-woocommerce' ),
				'referral_level'  => __( 'Referral Level', 'multilevel-referral-plugin-for-woocommerce' ),
				'view_hierarchie' => __( 'Hierarchy', 'multilevel-referral-plugin-for-woocommerce' ),
			);
			if ( isset( $url_data['user_status'] ) && '0' === $url_data['user_status'] ) {
				unset( $c['no_of_followers'] );
				unset( $c['total_credits'] );
				unset( $c['view_hierarchie'] );
				unset( $c['referral_level'] );
				$c['deactivate_date']    = __( 'Deactive Date', 'multilevel-referral-plugin-for-woocommerce' );
				$c['view_inaciver_user'] = '';
			}
			return $c;
		}
		/**
		 * Usort reorder.
		 *
		 * @param array $a First item.
		 * @param array $b Second item.
		 * @return int
		 */
		public function usort_reorder( $a, $b ) {
			// If no sort, default to title.
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			$orderby  = ( ! empty( $url_data['orderby'] ) ) ? sanitize_text_field( wp_unslash( $url_data['orderby'] ) ) : 'username';
			// If no order, default to asc.
			$order = ( ! empty( $url_data['order'] ) ) ? sanitize_text_field( wp_unslash( $url_data['order'] ) ) : 'asc';
			// Determine sort order.
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
			// Send final sort direction to usort.
			return ( 'asc' === $order ) ? $result : -$result;
		}
		/**
		 * Get bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array();
			return $actions;
		}
		/**
		 * Column checkbox.
		 *
		 * @param array $item Item data.
		 * @return string
		 */
		public function column_cb( $item ) {
				return sprintf(
					'<input type="checkbox" name="user[]" value="%s" />',
					$item['ID']
				);
		}
		/**
		 * Prepare items.
		 *
		 * @return void
		 */
		public function prepare_items() {
			$url_data       = woocommerce_multilevel_referral_get_query_vars();
			$usersearch     = isset( $url_data['search_by_email'] ) ? trim( sanitize_text_field( wp_unslash( $url_data['search_by_email'] ) ) ) : '';
			$per_page       = ( $this->is_site_users ) ? 'site_users_network_per_page' : 'users_per_page';
			$users_per_page = $this->get_items_per_page( $per_page );
			$paged          = $this->get_pagenum();

			$meta_query_args[] = array();
			$args              = array(
				'number'     => $users_per_page,
				'offset'     => ( $paged - 1 ) * $users_per_page,
				'search'     => $usersearch,
				'fields'     => 'all_with_meta',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query' => $meta_query_args,
			);
			if ( '' !== $args['search'] ) {
				$args['search'] = '*' . $args['search'] . '*';
			}
			if ( $this->is_site_users ) {
				$args['blog_id'] = $this->site_id;
			}
			if ( isset( $url_data['orderby'] ) ) {
				$args['orderby'] = sanitize_text_field( wp_unslash( $url_data['orderby'] ) );
			}
			if ( isset( $url_data['order'] ) ) {
				$args['order'] = sanitize_text_field( wp_unslash( $url_data['order'] ) );
			}
			if ( isset( $url_data['orderby'] ) && 'no_of_followers' === $url_data['orderby'] ) {
				$args['orderby'] = 'meta_value_num';
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['meta_key'] = 'total_referrals';
			}
			if ( isset( $url_data['orderby'] ) && 'total_credits' === $url_data['orderby'] ) {
				$args['orderby'] = 'meta_value_num';
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['meta_key'] = 'total_credits';
			}
			if ( isset( $url_data['orderby'] ) && 'join_date' === $url_data['orderby'] ) {
				$args['orderby'] = 'id';
			}
			if ( isset( $url_data['search_by_name'] ) && '' !== $url_data['search_by_name'] ) {
				$name_list = explode( ' ', sanitize_text_field( wp_unslash( $url_data['search_by_name'] ) ) );
				$data      = array();
				foreach ( $name_list as $name ) {
					$data[]           = array(
						'key'     => 'first_name',
						'value'   => $name,
						'compare' => 'LIKE',
					);
					$data['relation'] = 'OR';
					$data[]           = array(
						'key'     => 'last_name',
						'value'   => $name,
						'compare' => 'LIKE',
					);
				}
				$meta_query_args[] = array(
					$data,
				);
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				$args['meta_query'] = $meta_query_args;
			}
			/**
			 * Filter the query arguments used to retrieve users for the current users list table.
			 *
			 * @since 4.4.0
			 *
			 * @param array $args Arguments passed to WP_User_Query to retrieve items for the current
			 *                    users list table.
			 */
			$args = apply_filters( 'woocommerce_multilevel_referral_users_list_table_query_args', $args );
			// Query the user IDs for this page.
			$wp_user_search        = new WP_User_Query( $args );
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items           = $wp_user_search->get_results();
			$this->set_pagination_args(
				array(
					'total_items' => $wp_user_search->get_total(),
					'per_page'    => $users_per_page,
				)
			);
		}
		/**
		 * Display rows.
		 *
		 * @return void
		 */
		public function display_rows() {
			global $woocommerce_multilevel_referral_users;
			// Query the post counts for this page.
			if ( ! $this->is_site_users ) {
				$post_counts = count_many_users_posts( array_keys( $this->items ) );
			}
			$woocommerce_multilevel_referral_users = WooCommerce_Multilevel_Referral_Users::get_instance();
			foreach ( $this->items as $userid => $user_object ) {
				if ( is_multisite() && empty( $user_object->allcaps ) ) {
					continue;
				}
				echo "\n\t" . wp_kses_post( $this->single_row( $user_object, '', '', isset( $post_counts ) ? $post_counts[ $userid ] : 0 ) );
			}
		}
		/**
		 * Single row.
		 *
		 * @param WP_User $user_object User object.
		 * @param string  $style       Style.
		 * @param string  $role        Role.
		 * @param int     $numposts    Number of posts.
		 * @return string
		 */
		public function single_row( $user_object, $style = '', $role = '', $numposts = 0 ) {
			global $woocommerce_multilevel_referral_program, $woocommerce_multilevel_referral_users;
			$url_data = woocommerce_multilevel_referral_get_query_vars();
			if ( ! ( $user_object instanceof WP_User ) ) {
				$user_object = get_userdata( (int) $user_object );
			}
			$user_object->filter = 'display';
			$email               = $user_object->user_email;
			if ( $this->is_site_users ) {
				$url = "site-users.php?id={$this->site_id}&amp;";
			} else {
				$url = 'users.php?';
			}
			// Set up the hover actions for this user.
			$actions  = array();
			$checkbox = '';
			// Check if the user for this row is editable.
			if ( current_user_can( 'list_users' ) ) {
				// Set up the user editing link.
				$reqsturi  = isset( $_SERVER['REQUEST_URI'] ) ? rawurlencode( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';
				$edit_link = esc_url( add_query_arg( 'wp_http_referer', $reqsturi, get_edit_user_link( $user_object->ID ) ) );
				if ( current_user_can( 'edit_user', $user_object->ID ) ) {
					$edit            = "<strong><a href=\"$edit_link\">$user_object->user_login</a></strong><br />";
					$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit', 'multilevel-referral-plugin-for-woocommerce' ) . '</a>';
				} else {
					$edit = "<strong>$user_object->user_login</strong><br />";
				}
				/**
				 * Filter the action links displayed under each user in the Users list table.
				 *
				 * @since 2.8.0
				 *
				 * @param array   $actions     An array of action links to be displayed.
				 *                             Default 'Edit', 'Delete' for single site, and
				 *                             'Edit', 'Remove' for Multisite.
				 * @param WP_User $user_object WP_User object for the currently-listed user.
				 */
				$actions = apply_filters( 'woocommerce_multilevel_referral_user_row_actions', $actions, $user_object );
				// Set up the checkbox ( because the user is editable, otherwise it's empty ).
				// translators: %s is the user login.
				$checkbox = '<label class="screen-reader-text" for="user_' . $user_object->ID . '">' . sprintf( __( 'Select %s', 'multilevel-referral-plugin-for-woocommerce' ), $user_object->user_login ) . '</label>'
					. "<input type='checkbox' name='users[]' id='user_{$user_object->ID}' value='{$user_object->ID}' />";
			} else {
				$edit = '<strong>' . $user_object->user_login . '</strong>';
			}
			$avatar = get_avatar( $user_object->ID, 32 );
			$r      = "<tr id='user-$user_object->ID'>";
			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
			$available_credits                             = $woocommerce_multilevel_referral_program->available_credits( $user_object->ID );
			$parent_user                                   = $woocommerce_multilevel_referral_users->referral_user( 'referral_parent', 'user_id', $user_object->ID );
			$referrer_name                                 = '';
			if ( 0 !== $parent_user ) {
				$referrer_info = get_user_meta( $parent_user );
				if ( isset( $referrer_info['first_name'][0] ) && '' !== $referrer_info['first_name'][0] ) {
					$referrer_name .= $referrer_info['first_name'][0];
				}
				if ( isset( $referrer_info['last_name'][0] ) && '' !== $referrer_info['last_name'][0] ) {
					$referrer_name .= ' ' . $referrer_info['last_name'][0];
				}
				if ( '' === $referrer_name && isset( $referrer_info['nickname'][0] ) && '' !== $referrer_info['nickname'][0] ) {
					$referrer_name .= $referrer_info['nickname'][0];
				}
				$referrer_name = '<a href="' . get_edit_user_link( $parent_user ) . '">' . $referrer_name . '</a>';
			} else {
				$referrer_name = '-';
			}
			$referral_user_info = $woocommerce_multilevel_referral_users->get_referral_user( $user_object->ID );
			$referral_link      = get_the_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
			update_user_meta( $user_object->ID, 'total_credits', $available_credits );
			$deactive_date = '';
			if ( isset( $url_data['user_status'] ) && '0' === $url_data['user_status'] ) {
				$deactive_date = $woocommerce_multilevel_referral_users->referral_user( 'update_date', 'user_id', $user_object->ID );
			}
			foreach ( $columns as $column_name => $column_display_name ) {
				$classes = "$column_name column-$column_name";
				if ( $primary === $column_name ) {
					$classes .= ' has-row-actions column-primary';
				}
				if ( 'posts' === $column_name ) {
					$classes .= ' num'; // Special case for that column.
				}
				if ( in_array( $column_name, $hidden, true ) ) {
					$classes .= ' hidden';
				}
				$data       = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';
				$attributes = "class='$classes' $data";
				if ( 'cb' === $column_name ) {
					$r .= "<th scope='row' class='check-column'>$checkbox</th>";
				} else {
					$r .= "<td $attributes>";
					switch ( $column_name ) {
						case 'username':
							$r .= "$avatar $edit";
							break;
						case 'display_name':
							$r .= apply_filters( 'woocommerce_multilevel_referral_display_name', ucwords( $user_object->first_name . ' ' . $user_object->last_name ), $user_object );
							break;
						case 'email':
							$r .= "<a href='" . esc_url( "mailto:$email" ) . "'>$email</a>";
							break;
						case 'referrer_name':
							$r .= $referrer_name;
							break;
						case 'referral_code':
							$link = add_query_arg( 'ru', $referral_user_info['referral_code'], $referral_link );
							$r   .= '<div><a target="_blank" href="' . $link . '">' . $referral_user_info['referral_code'] . '</a></div>';
							break;
						case 'join_date':
							$r .= $referral_user_info['join_date'];
							break;
						case 'deactivate_date':
							$r .= $deactive_date;
							break;
						case 'no_of_followers':
							$r .= '<div class="woocommerce_multilevel_referral_referrals_count" data-name="' . ucwords( $user_object->first_name . ' ' . $user_object->last_name ) . '" data-id="' . $user_object->ID . '" area-label="' . __( 'Calculating Referral Count', 'multilevel-referral-plugin-for-woocommerce' ) . '"><img title="' . __( 'Calculating Referral Count', 'multilevel-referral-plugin-for-woocommerce' ) . '" src="' . WOOCOMMERCE_MULTILEVEL_REFERRAL_URL . 'images/9a7c10bc-b17b-467e-b42d-4f2faf643445.svg" alt="' . __( 'Loader Image', 'multilevel-referral-plugin-for-woocommerce' ) . '"/></div>';
							break;
						case 'total_credits':
							$r .= wc_price( $available_credits ? $available_credits : 0 );
							break;
						case 'view_hierarchie':
							$r .= '<div class="woocommerce_multilevel_referral_view"><img title="' . __( 'Calculating Referral Count', 'multilevel-referral-plugin-for-woocommerce' ) . '" src="' . WOOCOMMERCE_MULTILEVEL_REFERRAL_URL . 'images/9a7c10bc-b17b-467e-b42d-4f2faf643445.svg" alt="' . __( 'Loader Image', 'multilevel-referral-plugin-for-woocommerce' ) . '"/></div>';
							break;
						case 'referral_level':
							global $woocommerce_multilevel_referral_users;
							$referral_level = $woocommerce_multilevel_referral_users->get_referral_user_level( $user_object->ID );
							$r             .= __( 'Level', 'multilevel-referral-plugin-for-woocommerce' ) . sprintf( " <span class='referral_level_count'>%d</span>", $referral_level );
							if ( $referral_level > 1 ) {
									$url_data['changeReferralsLevel'] = $user_object->ID;
									$url                              = add_query_arg( $url_data, admin_url( 'admin.php' ) );
									$r                               .= ' <a href="' . $url . '" class="move_level_up" title="' . __( 'Move to up level', 'multilevel-referral-plugin-for-woocommerce' ) . '" data-ID="' . $user_object->ID . '"></a>';
							}
							break;
						case 'view_inaciver_user':
							$r .= '<a href="#" class="active_referral_user" data-id="' . $user_object->ID . '">' . __( 'Add back to referrals', 'multilevel-referral-plugin-for-woocommerce' ) . '</a>';
							break;
						default:
							/**
							 * Filter the display output of custom columns in the Users list table.
							 *
							 * @since 2.8.0
							 *
							 * @param string $output      Custom column output. Default empty.
							 * @param string $column_name Column name.
							 * @param int    $user_id     ID of the currently-listed user.
							 */
							$r .= apply_filters( 'woocommerce_multilevel_referral_manage_users_custom_column', '', $column_name, $user_object->ID );
					}
					if ( $primary === $column_name ) {
						$r .= $this->row_actions( $actions );
					}
					$r .= '</td>';
				}
			}
			$r .= '</tr>';
			return $r;
		}
	} //class
endif;
