<?php
/**
 * Admin Users View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
$woocommerce_multilevel_referral_tablename = $wpdb->prefix . 'referal_users';
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
$woocommerce_multilevel_referral_referral_users_result = $wpdb->get_results( "SELECT RU.user_id FROM {$wpdb->prefix}referal_users AS RU" );
// phpcs:enable
?>
<h3><?php esc_html_e( 'Referral Program Statistics', 'multilevel-referral-plugin-for-woocommerce' ); ?></h3>
<table class="form-table">
	<?php
		wp_nonce_field( 'woocommerce_multilevel_referral_userform_action', 'woocommerce_multilevel_referral_userform_nonce' );
	if ( ! isset( $user['join_date'] ) ) {
		?>
			<tr>
				<td>
					<input type="checkbox" name="add_user_to_referal" id="add_user_to_referal"  />
					<span class="description"><?php esc_html_e( 'Add user to referal user?', 'multilevel-referral-plugin-for-woocommerce' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="parent_userid"><?php esc_html_e( 'Select Parent User', 'multilevel-referral-plugin-for-woocommerce' ); ?></label></th>
				<td>
					<select name="parent_userid" id="parent_userid" >
						<option value="0">Select parent user</option>
					<?php
					foreach ( $woocommerce_multilevel_referral_referral_users_result as $woocommerce_multilevel_referral_user_row ) {
						$woocommerce_multilevel_referral_uid       = $woocommerce_multilevel_referral_user_row->user_id;
						$woocommerce_multilevel_referral_user_info = get_userdata( $woocommerce_multilevel_referral_uid );
						?>
									<option value="<?php echo esc_attr( $woocommerce_multilevel_referral_uid ); ?>"><?php echo esc_attr( $woocommerce_multilevel_referral_user_info->user_login ); ?></option>
							<?php
					}
					?>
					</select>
				</td>
			</tr>
			<?php
	}
	?>
	<tr>
		<th><label for="join_date"><?php esc_html_e( 'Join Date', 'multilevel-referral-plugin-for-woocommerce' ); ?></label></th>
		<td>
			<input type="text" name="join_date" id="join_date" disabled value="<?php echo isset( $user['join_date'] ) ? esc_attr( $user['join_date'] ) : ''; ?>" class="regular-text" /><br />
			<span class="description"><?php esc_html_e( 'Joining date of referral user', 'multilevel-referral-plugin-for-woocommerce' ); ?>.</span>
		</td>
	</tr>
	<tr>
		<th><label for="referal_benefits"><?php esc_html_e( 'Referral Discount', 'multilevel-referral-plugin-for-woocommerce' ); ?></label></th>
		<td>
			<input type="checkbox" name="referal_benefits" disabled id="referal_benefits" <?php echo isset( $user['referal_benefits'] ) ? esc_attr( $user['referal_benefits'] ) ? 'checked' : '' : ''; ?> /><br />
			<span class="description"><?php esc_html_e( 'Status of referral user for discount that taken or not?', 'multilevel-referral-plugin-for-woocommerce' ); ?>.</span>
		</td>
	</tr>
	<tr>
		<th><label for="referal_code"><?php esc_html_e( 'Referral code', 'multilevel-referral-plugin-for-woocommerce' ); ?></label></th>
		<td>
			<input type="text" name="referal_code" id="referal_code"
			<?php
			if ( ! current_user_can( 'manage_options' ) ) {
				?>
				disabled <?php } ?> value="<?php echo isset( $user['referral_code'] ) ? esc_attr( $user['referral_code'] ) : ''; ?>" class="regular-text" /><br />
			<span class="description"><?php esc_html_e( 'Auto generated referral code for referral users', 'multilevel-referral-plugin-for-woocommerce' ); ?>.</span>
		</td>
	</tr>
</table>
<h3><?php esc_html_e( 'Distribution of commission/Credit for each level.', 'multilevel-referral-plugin-for-woocommerce' ); ?></h3>
<?php
	global $woocommerce;
	$woocommerce_multilevel_referral_url_data    = woocommerce_multilevel_referral_get_query_vars();
	$woocommerce_multilevel_referral_user_id     = isset( $woocommerce_multilevel_referral_url_data['user_id'] ) ? sanitize_text_field( wp_unslash( $woocommerce_multilevel_referral_url_data['user_id'] ) ) : '';
	$woocommerce_multilevel_referral_credit_type = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
	$woocommerce_multilevel_referral_type_html   = '';
if ( 'percentage' === $woocommerce_multilevel_referral_credit_type ) {
	$woocommerce_multilevel_referral_type_html = ' (%)';
}
	$woocommerce_multilevel_referral_is_level_base_credit = get_option( 'woocommerce-multilevel-referral-levelbase-credit', 0 );
if ( $woocommerce_multilevel_referral_is_level_base_credit ) {
	$woocommerce_multilevel_referral_max_levels             = get_option( 'woocommerce-multilevel-referral-max-level', 1 );
	$woocommerce_multilevel_referral_max_level_credits      = get_option( 'woocommerce-multilevel-referral-level-credit', array() );
	$woocommerce_multilevel_referral_customer_credits       = get_option( 'woocommerce-multilevel-referral-level-c', 0 );
	$woocommerce_multilevel_referral_max_user_level_credits = get_user_meta( $woocommerce_multilevel_referral_user_id, 'woocommerce-multilevel-referral-level-credit', true );
	$woocommerce_multilevel_referral_user_level_enable      = get_user_meta( $woocommerce_multilevel_referral_user_id, 'woocommerce-multilevel-referral-user-level-enable', true );
	$woocommerce_multilevel_referral_c_credits              = get_user_meta( $woocommerce_multilevel_referral_user_id, 'woocommerce-multilevel-referral-level-c', true );
	$woocommerce_multilevel_referral_addattr                = '';
	if ( 'on' === $woocommerce_multilevel_referral_user_level_enable ) {
		$woocommerce_multilevel_referral_addattr = 'checked';
	}
	echo '<input name="woocommerce_multilevel_referral_enable_user_level" type="checkbox" ' . esc_attr( $woocommerce_multilevel_referral_addattr ) . ' id="woocommerce_multilevel_referral-enable-user-level" ><label for="woocommerce_multilevel_referral-enable-user-level">';
	esc_html_e( 'Enable User Credit', 'multilevel-referral-plugin-for-woocommerce' );
	echo '</label>';
	echo '<p class="form-field woocommerce-multilevel-referral-level-c_field">
		<label for="woocommerce-multilevel-referral-level-c">';
	esc_html_e( 'Customer', 'multilevel-referral-plugin-for-woocommerce' );
	echo esc_html( $woocommerce_multilevel_referral_type_html );
	echo '</label><input type="number" step="0.01" class="short" style="width:50px;text-align:right;" name="woocommerce-multilevel-referral-level-c" id="woocommerce-multilevel-referral-level-c" value="' . esc_attr( $woocommerce_multilevel_referral_c_credits ) . '" placeholder="' . esc_attr( $woocommerce_multilevel_referral_customer_credits ) . '"> </p>';
	for ( $woocommerce_multilevel_referral_i = 0;$woocommerce_multilevel_referral_i < $woocommerce_multilevel_referral_max_levels;$woocommerce_multilevel_referral_i++ ) {
		$woocommerce_multilevel_referral_level_value = ( isset( $woocommerce_multilevel_referral_max_user_level_credits[ $woocommerce_multilevel_referral_i ] ) && '' !== $woocommerce_multilevel_referral_max_user_level_credits[ $woocommerce_multilevel_referral_i ] ) ? $woocommerce_multilevel_referral_max_user_level_credits[ $woocommerce_multilevel_referral_i ] : '';
		woocommerce_wp_text_input(
			array(
				'id'                => 'woocommerce-multilevel-referral-level-credit',
				'name'              => 'woocommerce-multilevel-referral-level-credit[]',
				'type'              => 'number',
				'style'             => 'width:50px;text-align:right;',
				'label'             => esc_html__( 'Referrer Level ', 'multilevel-referral-plugin-for-woocommerce' ) . ( $woocommerce_multilevel_referral_i + 1 ) . ' ' . esc_html( $woocommerce_multilevel_referral_type_html ),
				'placeholder'       => isset( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) ? $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] : '',
				'desc_tip'          => false,
				'value'             => $woocommerce_multilevel_referral_level_value,
				'custom_attributes' => array( 'step' => '0.01' ),
			)
		);
	}
	echo '</div>';
}
?>
