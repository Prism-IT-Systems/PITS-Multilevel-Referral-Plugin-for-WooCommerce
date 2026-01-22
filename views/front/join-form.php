<?php
/**
 * Front Join Form View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce_multilevel_referral_join_form">
<h2><?php echo esc_html_e( 'Join Referral Program', 'multilevel-referral-plugin-for-woocommerce' ); ?></h2>
<?php $woocommerce_multilevel_referral_data = $data; ?>
	<form action="" method="post">
		<p class="form-row form-row-wide">
		<label for="option_1"><input type="radio" id="option_1" name="join_referral_program" <?php echo '1' === $woocommerce_multilevel_referral_data['join_referral_program'] ? 'checked' : ''; ?> value="1" /> <?php echo esc_html_e( 'I have the referral code and want to join referral program.', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
		<label for="option_2"><input type="radio" id="option_2" name="join_referral_program" <?php echo '2' === $woocommerce_multilevel_referral_data['join_referral_program'] ? 'checked' : ''; ?> value="2" /> <?php echo esc_html_e( 'I don\'t have referral code or I lost it. But I wish to join referral program.', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
		</p>
		<?php
		if ( isset( $_COOKIE['woocommerce_multilevel_referral_code'] ) ) {
			$woocommerce_multilevel_referral_data['referral_code'] = sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_multilevel_referral_code'] ) );
		}
		?>
		<p class="referral_code_panel form-row hide">
			<label for="referral_code"><?php echo esc_html( apply_filters( 'woocommerce_multilevel_referral_reg_field_referral_field_name_change', esc_html_e( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ) ) ); ?> <span class="required">*</span></label>
			<input type="text"  class="input-text"  name="referral_code" id="referral_code" value="<?php echo esc_attr( $woocommerce_multilevel_referral_data['referral_code'] ); ?>" />
			<small><?php echo esc_html_e( '&nbsp;', 'multilevel-referral-plugin-for-woocommerce' ); ?></small>
		</p>
		<p class="referral_terms_conditions form-row form-row-wide hide">
		<input type="checkbox" <?php echo isset( $woocommerce_multilevel_referral_data['termsandconditions'] ) && $woocommerce_multilevel_referral_data['termsandconditions'] ? 'checked' : ''; ?> name="termsandconditions" id="termsandconditions" value="1" /> <label for="termsandconditions"><?php esc_html_e( 'I\'ve read and agree to the referral program', 'multilevel-referral-plugin-for-woocommerce' ); ?> <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_multilevel_referral_terms_and_conditions', 0 ) ) ); ?>" target="_blank">
		<?php echo esc_html_e( 'terms and conditions', 'multilevel-referral-plugin-for-woocommerce' ); ?></a></label>
		</p>
		<p class="form-row form-row-wide">
			<input type="submit" class="button" name="add_new_referral_user" value="<?php echo esc_html_e( 'Join', 'multilevel-referral-plugin-for-woocommerce' ); ?>">
			<?php wp_nonce_field( 'referral_program', 'referral_registration_validation_nonce' ); ?>
			<input type="hidden" name="action" value="join_referreal_program">
		</p>
	</form>
</div>
