<?php
/**
 * Front Register Form Start Fields View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="form-row form-row-first">
	<label for="reg_billing_first_name"><?php esc_html_e( 'First name', 'multilevel-referral-plugin-for-woocommerce' ); ?> <span class="required">*</span></label>
	<?php
		$woocommerce_multilevel_referral_nonce = isset( $_REQUEST['woocommerce-register-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-register-nonce'] ) ) : '';
	?>
	<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="
	<?php
	if ( wp_verify_nonce( $woocommerce_multilevel_referral_nonce, 'woocommerce-register' ) ) {
		$woocommerce_multilevel_referral_first_name_sanitized_value = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : '';
		if ( ! empty( $woocommerce_multilevel_referral_first_name_sanitized_value ) ) {
			echo esc_attr( $woocommerce_multilevel_referral_first_name_sanitized_value ); }
	}
	?>
	" />
</p>
<p class="form-row form-row-last">
	<label for="reg_billing_last_name"><?php esc_html_e( 'Last name', 'multilevel-referral-plugin-for-woocommerce' ); ?> <span class="required">*</span></label>
	<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="
	<?php
	if ( wp_verify_nonce( $woocommerce_multilevel_referral_nonce, 'woocommerce-register' ) ) {
		$woocommerce_multilevel_referral_last_name_sanitized_value = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : '';
		if ( ! empty( $woocommerce_multilevel_referral_last_name_sanitized_value ) ) {
			echo esc_attr( $woocommerce_multilevel_referral_last_name_sanitized_value ); }
	}
	?>
	" />
</p>
