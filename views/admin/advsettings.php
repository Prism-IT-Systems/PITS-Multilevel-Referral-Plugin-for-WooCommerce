<?php
/**
 * Admin Advanced Settings View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$woocommerce_multilevel_referral_tabnonce = isset( $_POST['_wptabnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wptabnonce'] ) ) : '';
if ( wp_verify_nonce( $woocommerce_multilevel_referral_tabnonce, 'woocommerce_multilevel_referral_tab_action' ) ) {
	if ( isset( $_POST['save'] ) ) {
		if ( isset( $_POST['woocommerce-multilevel-referral-levelbase-credit'] ) ) {
			update_option( 'woocommerce-multilevel-referral-levelbase-credit', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-levelbase-credit'] ) ) );
		}
		if ( isset( $_POST['woocommerce-multilevel-referral-max-level'] ) ) {
			update_option( 'woocommerce-multilevel-referral-max-level', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-max-level'] ) ) );
		}
		if ( isset( $_POST['woocommerce-multilevel-referral-customer-based-bonus'] ) ) {
			update_option( 'woocommerce_multilevel_referral_customer_based_bonus', 1 );
		} else {
			update_option( 'woocommerce_multilevel_referral_customer_based_bonus', 0 );
		}
		if ( isset( $_POST['woocommerce-multilevel-referral-level-c'] ) ) {
			update_option( 'woocommerce-multilevel-referral-level-c', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-level-c'] ) ) );
		}
		if ( isset( $_POST['woocommerce-multilevel-referral-level-credit'] ) ) {
			update_option( 'woocommerce-multilevel-referral-level-credit', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocommerce-multilevel-referral-level-credit'] ) ) );
		}
		if ( isset( $_POST['woocommerce-multilevel-referral-earning-method'] ) ) {
			update_option( 'woocommerce-multilevel-referral-earning-method', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-earning-method'] ) ) );
		}
		if ( isset( $_POST['woocommerce-multilevel-referral-customer-based-bonus'] ) && isset( $_POST['woocommerce-multilevel-referral-level-c-without-link'] ) ) {
			update_option( 'woocommerce-multilevel-referral-level-c-new', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-level-c-without-link'] ) ) );
		}
		if ( isset( $_POST['woocommerce-multilevel-referral-customer-based-bonus'] ) && isset( $_POST['woocommerce-multilevel-referral-level-c-with-link'] ) ) {
			update_option( 'woocommerce-multilevel-referral-level-c', sanitize_text_field( wp_unslash( $_POST['woocommerce-multilevel-referral-level-c-with-link'] ) ) );
		}
		if ( isset( $_POST['woocommerce_multilevel_referral_customer_order_based_bonus'] ) ) {
			update_option( 'woocommerce_multilevel_referral_customer_order_based_bonus', 1 );
		} else {
			update_option( 'woocommerce_multilevel_referral_customer_order_based_bonus', 0 );
		}
		if ( isset( $_POST['woocommerce_multilevel_referral_order_level_credit'] ) ) {
			update_option( 'woocommerce_multilevel_referral_order_level_credit', sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_order_level_credit'] ) ) );
		}
		if ( isset( $_POST['woocommerce_multilevel_referral_bouns_offere_type'] ) ) {
			update_option( 'woocommerce_multilevel_referral_bouns_offere_type', sanitize_text_field( wp_unslash( $_POST['woocommerce_multilevel_referral_bouns_offere_type'] ) ) );
		}
	}
}
$woocommerce_multilevel_referral_getcustorder         = get_option( 'woocommerce_multilevel_referral_customer_order_based_bonus', 0 );
$woocommerce_multilevel_referral_getorderlevel        = get_option( 'woocommerce_multilevel_referral_order_level_credit', array() );
$woocommerce_multilevel_referral_getbounsoffer        = get_option( 'woocommerce_multilevel_referral_bouns_offere_type' );
$woocommerce_multilevel_referral_is_level_base_credit = get_option( 'woocommerce-multilevel-referral-levelbase-credit', 0 );
$woocommerce_multilevel_referral_earning_method       = get_option( 'woocommerce-multilevel-referral-earning-method', 'product' );
$woocommerce_multilevel_referral_max_levels           = get_option( 'woocommerce-multilevel-referral-max-level', 1 );
$woocommerce_multilevel_referral_max_level_credits    = get_option( 'woocommerce-multilevel-referral-level-credit', array() );
$woocommerce_multilevel_referral_customer_credits     = get_option( 'woocommerce-multilevel-referral-level-c', 0 );
$woocommerce_multilevel_referral_customer_credits_new = get_option( 'woocommerce-multilevel-referral-level-c-new', 0 );
$woocommerce_multilevel_referral_global_store_credit  = get_option( 'woocommerce_multilevel_referral_store_credit', 0 );
$woocommerce_multilevel_referral_customer_bonus       = get_option( 'woocommerce_multilevel_referral_customer_based_bonus', 0 );
$woocommerce_multilevel_referral_credit_type          = get_option( 'woocommerce_multilevel_referral_credit_type', 'percentage' );
$woocommerce_multilevel_referral_class                = 'woocommerce-multilevel-referral-hide';
if ( $woocommerce_multilevel_referral_is_level_base_credit ) {
	$woocommerce_multilevel_referral_class = '';
}
if ( ! $woocommerce_multilevel_referral_customer_credits ) {
	$woocommerce_multilevel_referral_customer_credits = 0;
}
if ( ! $woocommerce_multilevel_referral_customer_credits_new ) {
	$woocommerce_multilevel_referral_customer_credits_new = 0;
}
if ( ! $woocommerce_multilevel_referral_global_store_credit ) {
	$woocommerce_multilevel_referral_global_store_credit = 10;
}
$woocommerce_multilevel_referral_credit_type_class = '';
$woocommerce_multilevel_referral_max_input_value   = 100;
if ( 'fixed' === $woocommerce_multilevel_referral_credit_type ) {
	$woocommerce_multilevel_referral_credit_type_class = 'woocommerce-multilevel-referral-hide';
	$woocommerce_multilevel_referral_max_input_value   = 10000;
}
?>
<div class="woocommerce-multilevel-referral-advsettings">
	<form method="post" action="">
		<?php wp_nonce_field( 'woocommerce_multilevel_referral_tab_action', '_wptabnonce' ); ?>
		<h2><?php echo esc_html_e( 'Level based credit system', 'multilevel-referral-plugin-for-woocommerce' ); ?></h2>
		<p><?php echo esc_html_e( 'The level based credit percentage will be applied on product price for each level affiliate users.', 'multilevel-referral-plugin-for-woocommerce' ); ?></p>
		<table class="form-table woocommerce-multilevel-referral-level-table">
			<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc"><label><?php echo esc_html_e( 'Enable', 'multilevel-referral-plugin-for-woocommerce' ); ?> / <?php echo esc_html_e( 'Disable', 'multilevel-referral-plugin-for-woocommerce' ); ?></label></th>
					<td class="forminp">
						<fieldset>
							<label for="woocommerce-multilevel-referral-levelbase-credit-disable">
							<?php
							$woocommerce_multilevel_referral_d_checked = '';
							$woocommerce_multilevel_referral_e_checked = '';
							if ( $woocommerce_multilevel_referral_is_level_base_credit ) {
								$woocommerce_multilevel_referral_e_checked = 'checked="checked"';
							} else {
								$woocommerce_multilevel_referral_d_checked = 'checked="checked"';
							}
							?>
								<input type="radio" id="woocommerce-multilevel-referral-levelbase-credit-disable" name="woocommerce-multilevel-referral-levelbase-credit" value="0" <?php echo esc_attr( $woocommerce_multilevel_referral_d_checked ); ?>><?php echo esc_html_e( 'Disable level based credit system.', 'multilevel-referral-plugin-for-woocommerce' ); ?>
							</label>
						</fieldset>
						<fieldset>
							<label for="woocommerce-multilevel-referral-levelbase-credit-enable">
								<input type="radio" <?php echo esc_attr( $woocommerce_multilevel_referral_e_checked ); ?> id="woocommerce-multilevel-referral-levelbase-credit-enable" name="woocommerce-multilevel-referral-levelbase-credit" value="1"><?php echo esc_html_e( 'Enable level based credit system.', 'multilevel-referral-plugin-for-woocommerce' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top" class="woocommerce-multilevel-referral-optional-bouns">
					<th scope="row" class="" ><label for="woocommerce-multilevel-referral-max-level"><?php echo esc_html_e( 'Select Bonus Offers Type', 'multilevel-referral-plugin-for-woocommerce' ); ?> </label></th>
					<td class="forminp">
						<select class="woocommerce-multilevel-referral-bouns-offere-type" name="woocommerce_multilevel_referral_bouns_offere_type">
							<option value="woocommerce_multilevel_referral_user"
							<?php
							if ( 'woocommerce_multilevel_referral_user' === $woocommerce_multilevel_referral_getbounsoffer ) {
								echo 'selected';}
							?>
							><?php esc_html_e( 'User', 'multilevel-referral-plugin-for-woocommerce' ); ?></option>
							<option value="woocommerce_multilevel_referral_order"
							<?php
							if ( 'woocommerce_multilevel_referral_order' === $woocommerce_multilevel_referral_getbounsoffer ) {
								echo 'selected';}
							?>
							><?php esc_html_e( 'Order', 'multilevel-referral-plugin-for-woocommerce' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top" class="woocommerce-multilevel-referral-optional <?php echo esc_attr( $woocommerce_multilevel_referral_class ); ?>">
					<th scope="row" class="titledesc" ><label for="woocommerce-multilevel-referral-max-level"><?php echo esc_html_e( 'Maximum number of levels', 'multilevel-referral-plugin-for-woocommerce' ); ?> </label></th>
					<td class="forminp">
						<input type="number" readonly="readonly" name="woocommerce-multilevel-referral-max-level" id="woocommerce-multilevel-referral-max-level" class="form-field" min="1" value="<?php echo esc_attr( $woocommerce_multilevel_referral_max_levels ); ?>">
					</td>
				</tr>
		<?php
		/*
				<!--tr valign="top" class="woocommerce-multilevel-referral-optional <?php echo esc_attr($woocommerce_multilevel_referral_class);?>">
					<th scope="row" class="titledesc" ><label for="woocommerce-multilevel-referral-max-referrals"><?php echo esc_html_e('Maximum number of referrals on each level','multilevel-referral-plugin-for-woocommerce'); ?> </label></th>
					<td class="forminp">
						<input type="number"  name="woocommerce-multilevel-referral-max-referrals" id="woocommerce-multilevel-referral-max-referrals" class="form-field" placeholder="0" min="0" value="<?php echo esc_attr($maxReferrals);?>">
						<p class="description"><?php echo esc_html_e('Input "0 (ZERO)" for no limitations, 2 for Binary and 3 for turnary Tree','multilevel-referral-plugin-for-woocommerce'); ?></p>
					</td>
				</tr-->
		*/
		?>
				<tr valign="top" class="woocommerce-multilevel-referral-optional woocommerce_multilevel_referral_advsetting_credit  <?php echo esc_attr( "$woocommerce_multilevel_referral_credit_type_class $woocommerce_multilevel_referral_class" ); ?>">
					<th scope="row" class="titledesc"><label><?php echo esc_html_e( 'Credit / Commission Earning Method options', 'multilevel-referral-plugin-for-woocommerce' ); ?> </label></th>
					<td class="forminp">
						<fieldset>
							<label for="woocommerce-multilevel-referral-product-base">
							<?php
							$woocommerce_multilevel_referral_p_checked       = '';
							$woocommerce_multilevel_referral_c_checked       = '';
							$woocommerce_multilevel_referral_discount_amount = 0;
							if ( 'product' === $woocommerce_multilevel_referral_earning_method ) {
								$woocommerce_multilevel_referral_p_checked = 'checked="checked"';
							} elseif ( 'commission' === $woocommerce_multilevel_referral_earning_method ) {
								$woocommerce_multilevel_referral_c_checked = 'checked="checked"';
							}
							$woocommerce_multilevel_referral_product_price = 3900;
							?>
								<input type="radio" id="woocommerce-multilevel-referral-product-base" name="woocommerce-multilevel-referral-earning-method" value="product" <?php echo esc_attr( $woocommerce_multilevel_referral_p_checked ); ?>><?php echo esc_html_e( 'Product Price.', 'multilevel-referral-plugin-for-woocommerce' ); ?>
								<p class="description">
								<?php
								echo esc_html_e( 'With this method, The Referral Plugin will make use of the direct product price to calculate commission for each level.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . esc_html_e( 'e.g. Suppose Product "A" is priced at ', 'multilevel-referral-plugin-for-woocommerce' );
								echo wp_kses( wc_price( $woocommerce_multilevel_referral_product_price ), woocommerce_multilevel_referral_get_wp_fs_allow_html( array( 'span', 'bdi' ) ) );
								echo esc_html_e( ' then, referrals of each level will receive commission/credits as summarised below:', 'multilevel-referral-plugin-for-woocommerce' ) . '</p><ul>';
								echo '<li>';
								echo esc_html_e( 'Customer', 'multilevel-referral-plugin-for-woocommerce' ) . ' -  : ' . esc_attr( $woocommerce_multilevel_referral_product_price ) . ' * ' . esc_attr( $woocommerce_multilevel_referral_customer_credits ) . '% = ' . wp_kses( wc_price( ( $woocommerce_multilevel_referral_product_price * $woocommerce_multilevel_referral_customer_credits ) / 100 ), woocommerce_multilevel_referral_get_wp_fs_allow_html( array( 'span', 'bdi' ) ) );
								echo '</li>';
								if ( ! $woocommerce_multilevel_referral_discount_amount && $woocommerce_multilevel_referral_p_checked ) {
									$woocommerce_multilevel_referral_discount_amount = wc_price( ( $woocommerce_multilevel_referral_product_price * $woocommerce_multilevel_referral_customer_credits ) / 100 );
								}
								for ( $woocommerce_multilevel_referral_i = 0;$woocommerce_multilevel_referral_i < $woocommerce_multilevel_referral_max_levels;$woocommerce_multilevel_referral_i++ ) {
									if ( isset( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) && ! empty( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) ) :
										echo '<li>';
										echo esc_html_e( 'Referrer Level', 'multilevel-referral-plugin-for-woocommerce' ) . ' - ' . esc_attr( ( $woocommerce_multilevel_referral_i + 1 ) ) . ' : ' . esc_attr( $woocommerce_multilevel_referral_product_price ) . ' * ' . esc_attr( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) . '% = ' . wp_kses( wc_price( ( $woocommerce_multilevel_referral_product_price * $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) / 100 ), woocommerce_multilevel_referral_get_wp_fs_allow_html( array( 'span', 'bdi' ) ) );
										echo '</li>';
									endif;
								}
								?>
								</ul>
							</label>
						</fieldset>
						<fieldset>
							<label for="woocommerce-multilevel-referral-commission-base">
								<input type="radio" <?php echo esc_attr( $woocommerce_multilevel_referral_c_checked ); ?> id="woocommerce-multilevel-referral-commission-base" name="woocommerce-multilevel-referral-earning-method" value="commission"><?php echo esc_html_e( 'Commission/Credit', 'multilevel-referral-plugin-for-woocommerce' ); ?>
								<p class="description">
								<?php
								echo esc_html_e( 'This method is more lucid and is widely used and supported. Here The Referral Plugin will first calculate the commission/credit in accordance to the globally set percentage, and, that percentage will then be used to calculate the commission/credit for each of the levels.', 'multilevel-referral-plugin-for-woocommerce' ) . '<br>' . esc_html_e( 'e.g. Suppose Product "A" is priced at ', 'multilevel-referral-plugin-for-woocommerce' ) . wp_kses( wc_price( $woocommerce_multilevel_referral_product_price ), woocommerce_multilevel_referral_get_wp_fs_allow_html( array( 'span', 'bdi' ) ) ) . esc_html_e( ' and the global credit/commission percentage is set to ', 'multilevel-referral-plugin-for-woocommerce' ) . esc_attr( $woocommerce_multilevel_referral_global_store_credit ) . esc_html_e( '% then, the total commission/credit on this product would sum up to ', 'multilevel-referral-plugin-for-woocommerce' ) . wp_kses( wc_price( ( $woocommerce_multilevel_referral_product_price * $woocommerce_multilevel_referral_global_store_credit ) / 100 ), woocommerce_multilevel_referral_get_wp_fs_allow_html( array( 'span', 'bdi' ) ) ) . esc_html_e( '. Referrals on each levels will receive the commission/credits as summarised below', 'multilevel-referral-plugin-for-woocommerce' ) . '</p><ul>';
								$woocommerce_multilevel_referral_commission = ( ( $woocommerce_multilevel_referral_product_price * $woocommerce_multilevel_referral_global_store_credit ) / 100 );
								echo '<li>';
								echo esc_html_e( 'Customer', 'multilevel-referral-plugin-for-woocommerce' ) . ' -  : ' . esc_attr( $woocommerce_multilevel_referral_commission ) . ' * ' . esc_attr( $woocommerce_multilevel_referral_customer_credits ) . '% = ' . wp_kses( wc_price( ( $woocommerce_multilevel_referral_commission * $woocommerce_multilevel_referral_customer_credits ) / 100 ), woocommerce_multilevel_referral_get_wp_fs_allow_html( array( 'span', 'bdi' ) ) );
								echo '</li>';
								if ( ! $woocommerce_multilevel_referral_discount_amount && $woocommerce_multilevel_referral_c_checked ) {
									$woocommerce_multilevel_referral_discount_amount = wc_price( ( $woocommerce_multilevel_referral_commission * $woocommerce_multilevel_referral_customer_credits ) / 100 );
								}
								for ( $woocommerce_multilevel_referral_i = 0;$woocommerce_multilevel_referral_i < $woocommerce_multilevel_referral_max_levels;$woocommerce_multilevel_referral_i++ ) {
									if ( isset( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) && ! empty( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) ) :
										echo '<li>';
										echo esc_html_e( 'Referrer Level', 'multilevel-referral-plugin-for-woocommerce' ) . ' - ' . esc_attr( ( $woocommerce_multilevel_referral_i + 1 ) ) . ' : ' . esc_attr( $woocommerce_multilevel_referral_commission ) . ' * ' . esc_attr( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) . '% = ' . wp_kses( wc_price( ( $woocommerce_multilevel_referral_commission * $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) / 100 ), woocommerce_multilevel_referral_get_wp_fs_allow_html( array( 'span', 'bdi' ) ) );
										echo '</li>';
									endif;
								}
								?>
								</ul>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top" class="woocommerce-multilevel-referral-optional <?php echo esc_attr( $woocommerce_multilevel_referral_class ); ?>">
					<th scope="row" class="titledesc" ><label for="woocommerce-multilevel-referral-customer-based-bonus"><?php echo esc_html_e( 'Customer Based Bonus Offers', 'multilevel-referral-plugin-for-woocommerce' ); ?></label></th>
					<td>
						<input type="checkbox" name="woocommerce-multilevel-referral-customer-based-bonus" id="woocommerce-multilevel-referral-customer-based-bonus" <?php echo $woocommerce_multilevel_referral_customer_bonus ? 'checked' : ''; ?> />
					</td>
				</tr>
				<tr valign="top" class="woocommerce-multilevel-referral-optional <?php echo esc_attr( $woocommerce_multilevel_referral_class ); ?>">
						<th scope="row" class="titledesc" ><label for="woocommerce-multilevel-referral-level-c"><?php echo esc_html_e( 'Customer', 'multilevel-referral-plugin-for-woocommerce' ); ?></label></th>
						<td class="forminp">
							<div class="bonus_for_all <?php echo $woocommerce_multilevel_referral_customer_bonus ? 'woocommerce-multilevel-referral-hide' : ''; ?>">
								<input type="number" max="<?php echo esc_attr( $woocommerce_multilevel_referral_max_input_value ); ?>" step="0.01" min="0" name="woocommerce-multilevel-referral-level-c" id="woocommerce-multilevel-referral-level-c" class="form-field" value="<?php echo esc_attr( $woocommerce_multilevel_referral_customer_credits ); ?>"><span class="<?php echo esc_attr( $woocommerce_multilevel_referral_credit_type_class ); ?>"> % </span>
							</div>
							<table class="form-table woocommerce-multilevel-referral-customer-bonus-table bonus_for_customer <?php echo $woocommerce_multilevel_referral_customer_bonus ? '' : 'woocommerce-multilevel-referral-hide'; ?>">
								<tr>
									<th>
										<label><?php esc_html_e( 'Bonus for Customer with Ref-link', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
									</th>
									<th>
										<label><?php esc_html_e( 'Bonus for Customer without Ref-link', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
									</th>
								</tr>
								<tr>
									<td>
										<input type="number" max="<?php echo esc_attr( $woocommerce_multilevel_referral_max_input_value ); ?>" step="0.01" min="0" name="woocommerce-multilevel-referral-level-c-with-link" id="woocommerce-multilevel-referral-level-c-with-link" class="form-field" value="<?php echo esc_attr( $woocommerce_multilevel_referral_customer_credits ); ?>"><span class="<?php echo esc_attr( $woocommerce_multilevel_referral_credit_type_class ); ?>"> % </span>
									</td>
									<td>
										<input type="number" max="<?php echo esc_attr( $woocommerce_multilevel_referral_max_input_value ); ?>" step="0.01" min="0" name="woocommerce-multilevel-referral-level-c-without-link" id="woocommerce-multilevel-referral-level-c-without-link" class="form-field" value="<?php echo esc_attr( $woocommerce_multilevel_referral_customer_credits_new ); ?>"><span class="<?php echo esc_attr( $woocommerce_multilevel_referral_credit_type_class ); ?>"> % </span>
									</td>
								</tr>
							</table>
							<?php do_action( 'woocommerce_multilevel_referral_customer_announcement', $woocommerce_multilevel_referral_discount_amount, $woocommerce_multilevel_referral_product_price ); ?>
						</td>
					</tr>
				<?php for ( $woocommerce_multilevel_referral_i = 0;$woocommerce_multilevel_referral_i < $woocommerce_multilevel_referral_max_levels;$woocommerce_multilevel_referral_i++ ) { ?>
					<tr valign="top" data-level="<?php echo esc_attr( $woocommerce_multilevel_referral_i + 1 ); ?>" class="woocommerce-multilevel-referral-optional woocommerce-multilevel-referral-level <?php echo esc_attr( $woocommerce_multilevel_referral_class ); ?>">
						<th scope="row" class="titledesc" ><label for="woocommerce-multilevel-referral-level-<?php echo esc_attr( $woocommerce_multilevel_referral_i + 1 ); ?>"><?php echo esc_html_e( 'Referrer Level - ', 'multilevel-referral-plugin-for-woocommerce' ); ?><span><?php echo esc_attr( $woocommerce_multilevel_referral_i + 1 ); ?></span></label></th>
						<td class="forminp">
							<input type="number" max="<?php echo esc_attr( $woocommerce_multilevel_referral_max_input_value ); ?>" step="0.01" min="0" name="woocommerce-multilevel-referral-level-credit[]" id="woocommerce-multilevel-referral-level-<?php echo esc_attr( $woocommerce_multilevel_referral_i + 1 ); ?>" class="form-field" value="<?php echo isset( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) ? esc_attr( $woocommerce_multilevel_referral_max_level_credits[ $woocommerce_multilevel_referral_i ] ) : ''; ?>"><span class="<?php echo esc_attr( $woocommerce_multilevel_referral_credit_type_class ); ?>"> % </span>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<div class="woocommerce-multilevel-referral-buttons">
			<span class="woocommerce-multilevel-referral-optional">
				<button id="woocommerce-multilevel-referral-add-more" type="button" class="woocommerce-multilevel-referral-optional button-primary <?php echo esc_attr( $woocommerce_multilevel_referral_class ); ?>"><?php echo esc_html_e( 'Add Level', 'multilevel-referral-plugin-for-woocommerce' ); ?></button>
			</span>
			<span class="woocommerce-multilevel-referral-optional">
			<?php
			if ( $woocommerce_multilevel_referral_max_levels > 1 ) {
				echo '<button id="woocommerce-multilevel-referral-delete-last" type="button" class="woocommerce-multilevel-referral-optional button-primary ' . esc_attr( $woocommerce_multilevel_referral_class ) . '">';
				echo esc_html_e( 'Delete Last Level', 'multilevel-referral-plugin-for-woocommerce' );
				echo '</button>';
			} else {
				echo '<button style="display:none;" id="woocommerce-multilevel-referral-delete-last" type="button" class="woocommerce-multilevel-referral-optional hide button-primary">';
				echo esc_html_e( 'Delete Last Level', 'multilevel-referral-plugin-for-woocommerce' );
				echo '</button>';
			}
			?>
			</span>
		</div>
		<div class="woocommerce-multilevel-referral-user-order-main">
			<div class="woocommerce-multilevel-referral-user-order-wrap">
				<table class="form-table woocommerce-multilevel-referral-order-level-table">
					<tbody>
						<?php
						if ( is_array( $woocommerce_multilevel_referral_getorderlevel ) && ! empty( $woocommerce_multilevel_referral_getorderlevel ) ) {
							$woocommerce_multilevel_referral_count = 1;
							foreach ( $woocommerce_multilevel_referral_getorderlevel as $woocommerce_multilevel_referral_key => $woocommerce_multilevel_referral_level ) {
								?>
								<tr valign="top" data-order_level="<?php echo esc_attr( $woocommerce_multilevel_referral_key + 1 ); ?>" class=" woocommerce-multilevel-referral-level ">
									<th scope="row" class="titledesc">
										<label for="woocommerce-multilevel-referral-level-<?php echo esc_attr( $woocommerce_multilevel_referral_key ); ?>">
											<?php esc_html_e( 'Order', 'multilevel-referral-plugin-for-woocommerce' ); ?> <span><?php echo esc_attr( $woocommerce_multilevel_referral_count++ ); ?></span>
										</label>
									</th>
									<td class="forminp">
										<input type="number" max="10000" step="0.01" min="0" name="woocommerce_multilevel_referral_order_level_credit[]" id="woocommerce-multilevel-referral-level-1" class="form-field" value="<?php echo esc_attr( $woocommerce_multilevel_referral_level ); ?>"><span class="woocommerce-multilevel-referral-hide"> % </span>
									</td>
								</tr>
								<?php
							}
						} else {
							?>
								<tr valign="top" data-order_level="1" class=" woocommerce-multilevel-referral-level ">
									<th scope="row" class="titledesc">
										<label for="woocommerce-multilevel-referral-level-1">
											<?php esc_html_e( ' - Order', 'multilevel-referral-plugin-for-woocommerce' ); ?> <span>1</span>
										</label>
									</th>
									<td class="forminp">
										<input type="number" max="10000" step="0.01" min="0" name="woocommerce_multilevel_referral_order_level_credit[]" id="woocommerce-multilevel-referral-level-1" class="form-field" value="0"><span class="woocommerce-multilevel-referral-hide"> % </span>
									</td>
								</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<div class="woocommerce-multilevel-referral-buttons">
					<span>
						<button id="woocommerce-multilevel-referral-order-add-more" type="button" class="button-primary "><?php echo esc_html_e( 'Add Level', 'multilevel-referral-plugin-for-woocommerce' ); ?></button>
					</span>
					<span>
						<button id="woocommerce-multilevel-referral-order-delete-last" type="button" class="button-primary"><?php esc_html_e( 'Delete Last Level', 'multilevel-referral-plugin-for-woocommerce' ); ?></button>
					</span>
				</div>
			</div>
		</div>
		<?php
			do_action( 'woocommerce_multilevel_referral_additional_commission_settings' );
		?>
		<div class="woocommerce-multilevel-referral-buttons">
			<span>
				<button name="save" class="button-primary" type="submit" value="<?php echo esc_html_e( 'Save changes', 'multilevel-referral-plugin-for-woocommerce' ); ?>"><?php echo esc_html_e( 'Save changes', 'multilevel-referral-plugin-for-woocommerce' ); ?></button>
			</span>
		</div>
	</form>
</div>
