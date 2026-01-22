<?php
/**
 * Admin General Settings
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 * @since   2.28.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_General_Settings' ) ) {
	/**
	 * WooCommerce Multilevel Referral General Settings Class.
	 *
	 * @package Multilevel_Referral_Plugin_For_WooCommerce
	 * @since   2.28.1
	 */
	class WooCommerce_Multilevel_Referral_General_Settings extends WooCommerce_Multilevel_Referral_Module {
		/**
		 * Panel ID.
		 *
		 * @var string
		 */
		public $panel_id;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->panel_id = 'wmr_general';
			$this->register_hook_callbacks();
		}

		/**
		 * Register hook callbacks.
		 *
		 * @return void
		 */
		public function register_hook_callbacks() {
			global $woocommerce_multilevel_referral_cache;
			add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 30 );
			add_action( 'woocommerce_settings_tabs_' . $this->panel_id, __CLASS__ . '::settings_tab' );
			add_action( 'woocommerce_update_options_' . $this->panel_id, __CLASS__ . '::save_settings' );
			add_action( 'woocommerce_settings_' . $this->panel_id, __CLASS__ . '::start_panel' );
			add_action( 'woocommerce_settings_' . $this->panel_id . '_end', __CLASS__ . '::end_panel' );
			add_action( 'woocommerce_multilevel_referral_validation_notices', __CLASS__ . '::woocommerce_multilevel_referral_validation_error' );
			add_action(
				'woocommerce_product_data_tabs',
				__CLASS__ . '::woocommerce_multilevel_referral_custom_tab',
				10,
				1
			);
			add_action( 'woocommerce_product_data_panels', __CLASS__ . '::woocommerce_multilevel_referral_custom_tab_panel' );
			add_action( 'woocommerce_process_product_meta', __CLASS__ . '::woocommerce_multilevel_referral_add_custom_general_fields_save' );
			add_action( 'product_cat_add_form_fields', __CLASS__ . '::woocommerce_multilevel_referral_add_product_cat_fields' );
			add_action( 'product_cat_edit_form_fields', __CLASS__ . '::woocommerce_multilevel_referral_edit_product_cat_fields' );
			add_action(
				'edit_product_cat',
				__CLASS__ . '::woocommerce_multilevel_referral_product_cat_fields_save',
				10,
				2
			);
			add_action(
				'create_product_cat',
				__CLASS__ . '::woocommerce_multilevel_referral_product_cat_fields_save',
				10,
				2
			);
		}

		/**
		 * Validation error display.
		 *
		 * @param string $error Error message.
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_validation_error( $error ) {
			echo '<div class="woocommerce-multilevel-referral_error notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
		}

		/**
		 * Start panel.
		 *
		 * @return void
		 */
		public static function start_panel() {
			echo '<div id="wmr_general_setting_panel">';
		}

		/**
		 * End panel.
		 *
		 * @return void
		 */
		public static function end_panel() {
			echo '</div>';
		}

		/**
		 * Add settings tab.
		 *
		 * @param array $settings_tabs Settings tabs.
		 * @return array
		 */
		public static function add_settings_tab( $settings_tabs ) {
			$settings_tabs['wmr_general'] = __( 'Referral', 'multilevel-referral-plugin-for-woocommerce' );
			return $settings_tabs;
		}

		/**
		 * Settings tab.
		 *
		 * @return void
		 */
		public static function settings_tab() {
			woocommerce_admin_fields( self::get_settings() );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public static function get_settings() {
			$json_ids                 = array();
			$json_include_product_ids = array();
			$credit_options           = array(
				'no'  => __( 'Skip', 'multilevel-referral-plugin-for-woocommerce' ),
				'all' => __( 'All Users', 'multilevel-referral-plugin-for-woocommerce' ),
				'new' => __( 'New Users', 'multilevel-referral-plugin-for-woocommerce' ),
			);
			$credit_options_label     = '(' . __( 'PREMIUM', 'multilevel-referral-plugin-for-woocommerce' ) . ')';
			$arr_pages                = array(
				0 => __( 'Select Page', 'multilevel-referral-plugin-for-woocommerce' ),
			);
			$pages                    = get_pages();
			foreach ( $pages as $page ) {
				$arr_pages[ $page->ID ] = $page->post_title;
			}
			$month_list = array(
				'' => __( 'All', 'multilevel-referral-plugin-for-woocommerce' ),
			);
			for ( $i = 1; $i <= 12; $i++ ) {
				$key                = gmdate( 'm', strtotime( "2020/{$i}/01" ) );
				$month              = gmdate( 'F', strtotime( "2020/{$i}/01" ) );
				$month_list[ $key ] = $month;
			}
			$arr_settings = array(
				array(
					'title' => __( 'Referral Options', 'multilevel-referral-plugin-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wmr_general_setting_panel',
					'class' => 'referral_option_title',
				),
				array(
					'title'    => __( 'Type of Commission', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_credit_type',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 100px;',
					'type'     => 'select',
					'options'  => array(
						'percentage' => __( 'Percentage', 'multilevel-referral-plugin-for-woocommerce' ),
						'fixed'      => __( 'Fixed', 'multilevel-referral-plugin-for-woocommerce' ),
					),
					'desc_tip' => false,
				),
				array(
					'title'    => __( 'Global Store Credit', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '<br>' . __( '1. The defined credit points will be deposited in affiliate users account.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . __( '2. For more information about "How credit system works?" visit <a href="https://prismitsystemshelp.freshdesk.com/support/home" target="_blank">here</a>', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_store_credit',
					'css'      => 'width: 100px;text-align:right',
					'type'     => 'number',
					'min'      => '0',
					'desc_tip' => false,
				),
				array(
					'title'    => __( 'Referral Type', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '',
					'id'       => 'woocommerce_multilevel_referral_plan_type',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 100px;',
					'desc_tip' => __( 'Regular MLM supports n level child where Binary MLM type only allows two child.', 'multilevel-referral-plugin-for-woocommerce' ),
					'options'  => array(
						''       => __( 'Regular', 'multilevel-referral-plugin-for-woocommerce' ),
						'binary' => __( 'Binary', 'multilevel-referral-plugin-for-woocommerce' ),
					),
				),
				array(
					'title'    => __( 'Select Number of levels to distribute credit points.', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '<br>' . __( '1. The selected number of levels referrers are entitled to receive credit points.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . __( '2. This setting is only applicable for Recursive Credit System.', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_max_credit_levels',
					'css'      => 'width:100px;',
					'desc_tip' => false,
					'type'     => 'number',
				),
				array(
					'title'    => __( 'Welcome Credit for', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '<br>' . __( '1. All Users : All users including the existing ones will be presented with Welcome Credits on their first purchase.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . __( '2. New Users : Only the newly registered users will be presented with Welcome Credits on their first purchase. Existing users are not entitled for this benefit.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . __( '3. Registration : This option will give welcome credit on customer registration.', 'multilevel-referral-plugin-for-woocommerce' ) . ' ' . $credit_options_label . '<br>' . __( '4. Skip : This option will skip welcome credit for all customers. So customers will not receive welcome credit on their first purchase.', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_welcome_credit_for',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 100px;',
					'desc_tip' => false,
					'options'  => array(
						'no'           => __( 'Skip', 'multilevel-referral-plugin-for-woocommerce' ),
						'all'          => __( 'All Users', 'multilevel-referral-plugin-for-woocommerce' ),
						'new'          => __( 'New Users', 'multilevel-referral-plugin-for-woocommerce' ),
						'registration' => __( 'Registration', 'multilevel-referral-plugin-for-woocommerce' ),
					),
				),
				array(
					'title'    => __( 'Welcome Credit', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '<br>' . __( 'If Welcome credit has enabled for users, then these value will be used.', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_welcome_credit',
					'type'     => 'number',
					'css'      => 'width: 100px;text-align:right;',
					'desc_tip' => false,
				),
				array(
					'title'    => __( 'Credit validity by period', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '<br>' . __( 'This sets the number of months/years for expire credits.', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_credit_validity_number',
					'css'      => 'width:100px;',
					'desc_tip' => false,
					'type'     => 'number',
				),
				array(
					'title'    => '',
					'id'       => 'woocommerce_multilevel_referral_credit_validity_period',
					'default'  => '',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select set_position',
					'css'      => 'width: 100px;',
					'desc_tip' => false,
					'options'  => array(
						''      => __( 'Select expiry', 'multilevel-referral-plugin-for-woocommerce' ),
						'month' => __( 'Month', 'multilevel-referral-plugin-for-woocommerce' ),
						'year'  => __( 'Year', 'multilevel-referral-plugin-for-woocommerce' ),
					),
				),
				array(
					'title'    => __( 'Total volume of Referees starts until', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_referees_starts_from',
					'default'  => '',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select set_position',
					'css'      => 'width: 100px;',
					'desc_tip' => false,
					'options'  => $month_list,
				),
				array(
					'title'    => __( 'Notification Mail Time', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => __( 'This sets the number of days for send notification mail for expire credits.', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_notification_mail_time',
					'css'      => 'width:100px;',
					'desc_tip' => true,
					'type'     => 'number',
				),
				array(
					'title'    => __( 'Monthly max credit limit', 'multilevel-referral-plugin-for-woocommerce' ) . '(' . get_woocommerce_currency_symbol() . ')',
					'desc'     => __( 'The credit points will not be credited more than defined limit in the period of one month', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_max_credit_limit',
					'css'      => 'width:100px;',
					'desc_tip' => true,
					'type'     => 'number',
				),
				array(
					'title'    => __( 'Max Redemption (%)', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => __( 'You can define the limit for redemption. If you set 50% then user can not be redeem points more than 50% of product price.', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_max_redumption',
					'css'      => 'width:100px;',
					'desc_tip' => true,
					'type'     => 'number',
				),
				array(
					'title'             => __( 'Exclude products', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'              => __( 'Select the product which you want to be exclude from this referral program', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'                => 'woocommerce_multilevel_referral_exclude_products',
					'css'               => 'width:100%;',
					'desc_tip'          => true,
					'type'              => 'multiselect',
					'class'             => 'wc-product-search',
					'options'           => $json_ids,
					'placeholder'       => __( 'Exclude products', 'multilevel-referral-plugin-for-woocommerce' ),
					'custom_attributes' => array(
						'data-action'   => 'woocommerce_json_search_products',
						'data-multiple' => 'true',
					),
				),
				array(
					'title'             => __( 'Include products', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'              => __( 'Select the product which you want to be include for this referral program', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'                => 'woocommerce_multilevel_referral_include_products',
					'css'               => 'width:100%;',
					'desc_tip'          => true,
					'type'              => 'multiselect',
					'class'             => 'wc-product-search',
					'options'           => $json_include_product_ids,
					'placeholder'       => __( 'Include products', 'multilevel-referral-plugin-for-woocommerce' ),
					'custom_attributes' => array(
						'data-action'   => 'woocommerce_json_search_products',
						'data-multiple' => 'true',
					),
				),
				array(
					'title'    => __( 'Terms And Conditions Page', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => __( 'Select the terms and condition page', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_terms_and_conditions',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 100px;',
					'desc_tip' => true,
					'options'  => $arr_pages,
				),
				array(
					'title'    => __( 'Auto Join', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => __( 'Select "Yes" if you want to register users automatically to referral program', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_auto_register',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 100px;',
					'desc_tip' => true,
					'options'  => array(
						'no'  => __( 'No', 'multilevel-referral-plugin-for-woocommerce' ),
						'yes' => __( 'Yes', 'multilevel-referral-plugin-for-woocommerce' ),
					),
				),
				array(
					'title'    => __( 'Referral Code Require?', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '',
					'id'       => 'woocommerce_multilevel_referral_required_referral',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 100px;',
					'desc_tip' => true,
					'options'  => array(
						'no'  => __( 'No', 'multilevel-referral-plugin-for-woocommerce' ),
						'yes' => __( 'Yes', 'multilevel-referral-plugin-for-woocommerce' ),
					),
				),
				array(
					'title'    => __( 'Category Credit Preference', 'multilevel-referral-plugin-for-woocommerce' ),
					'desc'     => '<br>' . __( 'In case of multiple category selected for product, this setting will decide which credit percentage should be used. If "Highest" selected then highest percentage between all the categories will be considered, if "Lowest" selected lowest percentage will be considered', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_cat_pref',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width: 100px;',
					'desc_tip' => false,
					'options'  => array(
						'lowest'  => __( 'Lowest', 'multilevel-referral-plugin-for-woocommerce' ),
						'highest' => __( 'Highest', 'multilevel-referral-plugin-for-woocommerce' ),
					),
				),
				array(
					'title'    => __( 'Start Referral Program after First Order', 'multilevel-referral-plugin-for-woocommerce' ),
					'type'     => 'checkbox',
					'desc_tip' => __( 'if checked, User will get commission only when any user purchase at least one product.', 'multilevel-referral-plugin-for-woocommerce' ),
					'id'       => 'woocommerce_multilevel_referral_referal_first_order_features',
					'class'    => 'woocommerce_multilevel_referral_referal_first_order_features',
					'default'  => 'no',
				),
			);
			$add_settings = apply_filters( 'woocommerce_multilevel_referral_additional_settings', $arr_settings );
			array_push(
				$add_settings,
				array(
					'type' => 'sectionend',
					'id'   => 'wmr_general_setting_panel',
				)
			);
			$settings = apply_filters( 'woocommerce_multilevel_referral_general_settings', $add_settings );
			return apply_filters( 'woocommerce_multilevel_referral_get_settings', $settings );
		}

		/**
		 * Additional settings filter.
		 *
		 * @param array $arr_settings Settings array.
		 * @return int|false
		 */
		public function woocommerce_multilevel_referral_additional_settings( $arr_settings ) {
			$new = array_push( $arr_settings, array() );
			return $new;
		}

		/**
		 * Category add credit input field.
		 *
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_add_product_cat_fields() {
			global $post;
			$credit_type       = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
			$type_html         = '';
			$credit_type_class = 'woocommerce-multilevel-referral-hide';
			if ( 'percentage' === $credit_type ) {
				$type_html         = ' (%)';
				$credit_type_class = '';
			}
			?>
			<div class="form-field">
				<label for="term_meta[woocommerce_multilevel_referral_cat_credit]">
				<?php
				esc_html_e( 'Global Credit', 'multilevel-referral-plugin-for-woocommerce' );
				echo esc_html( $type_html );
				?>
				</label>
				<input type="number" step="0.01" placeholder ="
				<?php
				echo esc_html( get_option( 'woocommerce_multilevel_referral_store_credit' ) );
				?>
			" name="term_meta[woocommerce_multilevel_referral_cat_credit]" id="term_meta[woocommerce_multilevel_referral_cat_credit]" value="">
				<?php
				if ( ! $credit_type_class ) {
					?>
				<p class="description">
					<?php
					esc_html_e( 'Enter a credit percentage, this percentage will apply for all the products in this category', 'multilevel-referral-plugin-for-woocommerce' );
					?>
				</p>
					<?php
				} else {
					?>
				<p class="description">
					<?php
					esc_html_e( 'Enter a credit value, this value will apply for all the products in this category', 'multilevel-referral-plugin-for-woocommerce' );
					?>
				</p>
					<?php
				}
				?>
			</div>
			<?php
			$is_level_base_credit = get_option( 'woocommerce-multilevel-referral-levelbase-credit', 0 );
			if ( $is_level_base_credit ) {
				echo '<div class="form-field"><strong>' . esc_html__( 'Distribution of commission/credit for each level.', 'multilevel-referral-plugin-for-woocommerce' ) . '</strong>';
				$max_levels        = get_option( 'woocommerce-multilevel-referral-max-level', 1 );
				$max_level_credits = get_option( 'woocommerce-multilevel-referral-level-credit', array() );
				$customer_credits  = get_option( 'woocommerce_multilevel_referral_level_c', 0 );
				echo '<label for="term_meta[woocommerce_multilevel_referral_level_c]">' . esc_html__( 'Customer ', 'multilevel-referral-plugin-for-woocommerce' ) . esc_html( $type_html ) . '</label><input style="width:50px;text-align:center;" type="number" step="0.01" min="0" max="100" placeholder ="' . esc_html( $customer_credits ) . '" name="term_meta[woocommerce_multilevel_referral_level_c]" id="term_meta[woocommerce_multilevel_referral_level_c]" value="">';
				for ( $i = 0; $i < $max_levels; $i++ ) {
					echo '<label for="term_meta[woocommerce_multilevel_referral_level_credit]">' . esc_html__( 'Referrer Level ', 'multilevel-referral-plugin-for-woocommerce' ) . esc_html( $i + 1 ) . esc_html( $type_html ) . '</label><input style="width:50px;text-align:center;" type="number" step="0.01"  min="0" max="100" placeholder ="' . esc_html( $max_level_credits[ $i ] ) . '" name="term_meta[woocommerce_multilevel_referral_level_credit][]" id="term_meta[woocommerce_multilevel_referral_level_credit]" value="">';
				}
				echo '</div>';
			}
		}

		/**
		 * Edit product category fields.
		 *
		 * @param WP_Term $term Term object.
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_edit_product_cat_fields( $term ) {
			$t_id              = $term->term_id;
			$term_meta         = get_option( "product_cat_{$t_id}" );
			$credit_type       = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
			$type_html         = '';
			$credit_type_class = 'woocommerce-multilevel-referral-hide';
			wp_nonce_field( 'woocommerce_multilevel_referral_product_cat_action', 'woocommerce_multilevel_referral_product_cat_nonce' );
			if ( 'percentage' === $credit_type ) {
				$type_html         = ' (%)';
				$credit_type_class = '';
			}
			?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[woocommerce_multilevel_referral_cat_credit]">
			<?php
			esc_html_e( 'Affiliate Credit', 'multilevel-referral-plugin-for-woocommerce' );
			echo esc_html( $type_html );
			?>
			</label></th>
			<td>
				<input style="width:50px;text-align:center;" step="0.01"  type="number"  min="0" max="100" placeholder ="
				<?php
				echo esc_html( get_option( 'woocommerce_multilevel_referral_store_credit' ) );
				?>
			" name="term_meta[woocommerce_multilevel_referral_cat_credit]" id="term_meta[woocommerce_multilevel_referral_cat_credit]" value="
			<?php
			echo ( esc_html( $term_meta['woocommerce_multilevel_referral_cat_credit'] ) ? esc_html( $term_meta['woocommerce_multilevel_referral_cat_credit'] ) : '' );
			?>
			">
				<?php
				if ( ! $credit_type_class ) {
					?>
				<p class="description">
					<?php
					esc_html_e( 'Enter a credit percentage, this percentage will apply for all the products in this category', 'multilevel-referral-plugin-for-woocommerce' );
					?>
				</p>
					<?php
				} else {
					?>
				<p class="description">
					<?php
					esc_html_e( 'Enter a credit value, this value will apply for all the products in this category', 'multilevel-referral-plugin-for-woocommerce' );
					?>
				</p>
					<?php
				}
				?>
			</td>
		</tr>
			<?php
			$is_level_base_credit = get_option( 'woocommerce-multilevel-referral-levelbase-credit', 0 );
			if ( $is_level_base_credit ) {
				echo '<tr><td colspan="2"><Strong>' . esc_html__( 'Distribution of commission/credit for each level', 'multilevel-referral-plugin-for-woocommerce' ) . '</Strong></td>';
				$max_levels        = get_option( 'woocommerce-multilevel-referral-max-level', 1 );
				$max_level_credits = get_option( 'woocommerce-multilevel-referral-level-credit', array() );
				$customer_credits  = get_option( 'woocommerce_multilevel_referral_level_c', 0 );
				$c_value           = ( isset( $term_meta['woocommerce_multilevel_referral_level_c'] ) && '' !== $term_meta['woocommerce_multilevel_referral_level_c'] ? $term_meta['woocommerce_multilevel_referral_level_c'] : '' );
				?>
			<tr class="form-field">
					<th scope="row" valign="top"><label for="woocommerce_multilevel_referral_level_c">
					<?php
					echo esc_html__( 'Customer ', 'multilevel-referral-plugin-for-woocommerce' );
					?>
				</label></th>
					<td>
						<input style="width:50px;text-align:center;" step="0.01" type="number" min="0" max="100" placeholder ="
						<?php
						echo esc_html( $customer_credits );
						?>
				" name="term_meta[woocommerce_multilevel_referral_level_c]" id="woocommerce_multilevel_referral_level_c" value="
				<?php
				echo esc_html( $c_value );
				?>
				">
						<span class="
						<?php
						echo esc_html( $credit_type_class );
						?>
				">(%)</span>
					</td>
				</tr>
				<?php
				for ( $i = 0; $i < $max_levels; $i++ ) {
					$l_value = ( isset( $term_meta['woocommerce_multilevel_referral_level_credit'][ $i ] ) && '' !== $term_meta['woocommerce_multilevel_referral_level_credit'][ $i ] ? $term_meta['woocommerce_multilevel_referral_level_credit'][ $i ] : '' );
					?>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="woocommerce_multilevel_referral_level_credit_
					<?php
					echo esc_html( $i );
					?>
					">
					<?php
					echo esc_html__( 'Referrer Level ', 'multilevel-referral-plugin-for-woocommerce' ) . esc_html( $i + 1 );
					?>
					</label></th>
					<td>
						<input style="width:50px;text-align:center;" step="0.01" type="number" min="0" max="100" placeholder ="
						<?php
						echo esc_html( $max_level_credits[ $i ] );
						?>
					" name="term_meta[woocommerce_multilevel_referral_level_credit][]" id="woocommerce_multilevel_referral_level_credit_
					<?php
					echo esc_html( $i );
					?>
					" value="
					<?php
					echo esc_html( $l_value );
					?>
					">
						<span class="
						<?php
						echo esc_html( $credit_type_class );
						?>
					">(%)</span>
					</td>
				</tr>
					<?php
				}
			}
		}

		/**
		 * Save product category fields.
		 *
		 * @param int $term_id Term ID.
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_product_cat_fields_save( $term_id ) {
			if ( ! isset( $_POST['woocommerce_multilevel_referral_product_cat_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_product_cat_nonce'] ) ), 'woocommerce_multilevel_referral_product_cat_action' ) ) {
				return;
			}
			if ( isset( $_POST['term_meta'] ) ) {
				$t_id      = $term_id;
				$term_meta = get_option( "product_cat_{$t_id}" );
				$cat_keys  = array_keys( sanitize_text_field( wp_unslash( $_POST['term_meta'] ) ) );
				foreach ( $cat_keys as $key ) {
					if ( isset( $_POST['term_meta'][ $key ] ) ) {
						if ( 'woocommerce_multilevel_referral_cat_credit' === $key ) {
							$_POST['term_meta'][ $key ] = floatval( $_POST['term_meta'][ $key ] );
						}
						$term_meta[ $key ] = sanitize_text_field( wp_unslash( $_POST['term_meta'][ $key ] ) );
					}
				}
				// Save the option array.
				update_option( "product_cat_{$t_id}", $term_meta );
			}
		}

		/* end */
		/**
		 * Save settings.
		 */
		public static function save_settings() {
			woocommerce_update_options( self::get_settings() );
		}

		/**
		 * Add custom admin product tab.
		 *
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_add_custom_admin_product_tab() {
			?>
		<li class="referral_tab"><a href="#referral_tab_data">
			<?php
			esc_html_e( 'Multilevel Referral', 'multilevel-referral-plugin-for-woocommerce' );
			?>
			</a></li>
			<?php
		}

		/**
		 * Add custom general fields.
		 *
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_add_custom_general_fields() {
			global $woocommerce, $post;
			$credit_type = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
			$type_html   = '';
			if ( 'percentage' === $credit_type ) {
				$type_html = ' (%)';
			}
			wp_nonce_field( 'woocommerce_multilevel_referral_credit_nonce_action', 'woocommerce_multilevel_referral_credit_nonce' );
			echo '<div class="options_group"><h4 style="padding-left:10px;">' . esc_html__( 'Multilevel Referral Plugin Settings', 'multilevel-referral-plugin-for-woocommerce' ) . '</h4>';
			woocommerce_wp_text_input(
				array(
					'id'          => 'woocommerce_multilevel_referral_credits',
					'label'       => __( 'Affiliate Credit', 'multilevel-referral-plugin-for-woocommerce' ) . $type_html,
					'placeholder' => get_option( 'woocommerce_multilevel_referral_store_credit' ),
					'desc_tip'    => true,
					'description' => __( '1. The defined credit points will be deposited in affiliate users account, when user purchase this product.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . __( '2. For more information about "How credit system works?" visit', 'multilevel-referral-plugin-for-woocommerce' ) . '<a href="http://referral.staging.prismitsystems.com/shop/" target="_blank">' . __( 'here', 'multilevel-referral-plugin-for-woocommerce' ) . '</a>',
				)
			);
			echo '</div>';
		}

		/**
		 * Custom tab.
		 *
		 * @param array $default_tabs Default tabs.
		 * @return array
		 */
		public static function woocommerce_multilevel_referral_custom_tab( $default_tabs ) {
			$default_tabs['woocommerce_multilevel_referral_tab'] = array(
				'label'    => __( 'Referral', 'multilevel-referral-plugin-for-woocommerce' ),
				'target'   => 'woocommerce_multilevel_referral_custom_tab_panel',
				'priority' => 60,
				'class'    => array(),
			);
			return $default_tabs;
		}

		/**
		 * Custom tab panel.
		 *
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_custom_tab_panel() {
			global $woocommerce, $post;
			$credit_type = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
			$type_html   = '';
			if ( 'percentage' === $credit_type ) {
				$type_html = ' (%)';
			}
			wp_nonce_field( 'woocommerce_multilevel_referral_credit_nonce_action', 'woocommerce_multilevel_referral_credit_nonce' );
			echo '<div id="woocommerce_multilevel_referral_custom_tab_panel" class="panel woocommerce_options_panel">
         <h4 style="padding-left:10px;">' . esc_html__( 'Multilevel Referral Plugin Settings', 'multilevel-referral-plugin-for-woocommerce' ) . '</h4>
         <div class="options_group">';
			woocommerce_wp_text_input(
				array(
					'id'          => 'woocommerce_multilevel_referral_credits',
					'label'       => __( 'Global Credit', 'multilevel-referral-plugin-for-woocommerce' ) . $type_html,
					'type'        => 'number',
					'style'       => 'width:50px;text-align:right;',
					'placeholder' => get_option( 'woocommerce_multilevel_referral_store_credit' ),
					'desc_tip'    => true,
					'description' => __( '1. The defined credit points will be deposited in affiliate users account, when user purchase this product.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . __( '2. For more information about "How credit system works?" visit', 'multilevel-referral-plugin-for-woocommerce' ) . ' <a href="http://referral.staging.prismitsystems.com/shop/" target="_blank">' . __( 'here', 'multilevel-referral-plugin-for-woocommerce' ) . '</a>',
				)
			);
			echo '</div>';
			$is_level_base_credit = get_option( 'woocommerce-multilevel-referral-levelbase-credit', 0 );
			if ( $is_level_base_credit ) {
				echo '<div class="options_group"><h4 style="padding-left:10px;">' . esc_html__( 'Distribution of commission/Credit for each level.', 'multilevel-referral-plugin-for-woocommerce' ) . '</h4>';
				$max_levels                = get_option( 'woocommerce-multilevel-referral-max-level', 1 );
				$max_level_credits         = get_option( 'woocommerce-multilevel-referral-level-credit', array() );
				$customer_credits          = get_option( 'woocommerce_multilevel_referral_level_c', 0 );
				$max_product_level_credits = get_post_meta( $post->ID, 'woocommerce-multilevel-referral-level-credit', true );
				$c_credits                 = get_post_meta( $post->ID, 'woocommerce_multilevel_referral_level_c', true );
				echo '<p class="form-field woocommerce-multilevel-referral-level-c_field">
		<label for="woocommerce_multilevel_referral_level_c">' . esc_html__( 'Customer', 'multilevel-referral-plugin-for-woocommerce' ) . esc_html( $type_html ) . '</label><input type="number" step="0.01" class="short" style="width:50px;text-align:right;" name="woocommerce_multilevel_referral_level_c" id="woocommerce_multilevel_referral_level_c" value="' . esc_html( $c_credits ) . '" placeholder="' . esc_html( $customer_credits ) . '"> </p>';
				for ( $i = 0; $i < $max_levels; $i++ ) {
					$level_value = ( isset( $max_product_level_credits[ $i ] ) && '' !== $max_product_level_credits[ $i ] ? $max_product_level_credits[ $i ] : '' );
					woocommerce_wp_text_input(
						array(
							'id'                => 'woocommerce-multilevel-referral-level-credit',
							'name'              => 'woocommerce-multilevel-referral-level-credit[]',
							'type'              => 'number',
							'style'             => 'width:50px;text-align:right;',
							'label'             => __( 'Referrer Level ', 'multilevel-referral-plugin-for-woocommerce' ) . ( $i + 1 ) . ' ' . $type_html,
							'placeholder'       => $max_level_credits[ $i ],
							'desc_tip'          => false,
							'value'             => $level_value,
							'custom_attributes' => array(
								'step' => '0.01',
							),
						)
					);
				}
				echo '</div>';
			}
			echo '</div>';
		}

		/**
		 * Save custom general fields.
		 *
		 * @param int $post_id Post ID.
		 * @return void
		 */
		public static function woocommerce_multilevel_referral_add_custom_general_fields_save( $post_id ) {
			if ( ! isset( $_POST['woocommerce_multilevel_referral_credit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_credit_nonce'] ) ), 'woocommerce_multilevel_referral_credit_nonce_action' ) ) {
				wp_die( 'Nonce verification failed!' );
			}
			$woocommerce_text_field = ( isset( $_POST['woocommerce_multilevel_referral_credits'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_credits'] ) ) : '' );
			if ( '' !== $woocommerce_text_field ) {
				$woocommerce_text_field = floatval( $woocommerce_text_field );
			}
			update_post_meta( $post_id, 'woocommerce_multilevel_referral_credits', $woocommerce_text_field );
			if ( isset( $_POST['woocommerce_multilevel_referral_level_c'] ) ) {
				update_post_meta( $post_id, 'woocommerce_multilevel_referral_level_c', sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_level_c'] ) ) );
			}
			if ( isset( $_POST['woocommerce-multilevel-referral-level-credit'] ) ) {
				update_post_meta( $post_id, 'woocommerce-multilevel-referral-level-credit', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-level-credit'] ) ) );
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

}