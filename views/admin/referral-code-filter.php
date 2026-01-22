<?php
/**
 * Admin Referral Code Filter View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<select name="referral_code[]" multiple="multiple" id="dropdown_referral_code" data-placeholder="<?php esc_attr_e( 'Filter by referral code', 'multilevel-referral-plugin-for-woocommerce' ); ?>" data-allow_clear="true" >
	<?php if ( is_array( $woocommerce_multilevel_referral_code_list ) ) : ?>
		<?php foreach ( $woocommerce_multilevel_referral_code_list as $woocommerce_multilevel_referral_code_obj ) : ?>
			<option <?php echo ( is_array( $woocommerce_multilevel_referral_get_referral_code ) && in_array( $woocommerce_multilevel_referral_code_obj->user_id, $woocommerce_multilevel_referral_get_referral_code, true ) ? 'selected' : '' ); ?>  value="<?php echo esc_attr( $woocommerce_multilevel_referral_code_obj->user_id ); ?>"><?php echo esc_attr( $woocommerce_multilevel_referral_code_obj->referral_code ); ?></option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>
