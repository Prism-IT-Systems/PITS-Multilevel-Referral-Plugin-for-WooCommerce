<?php
/**
 * Front My Account Referral Amounts View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="referral_program_details">
	<div class="referral_program_overview second_section">
		<div class="referral_program_stats total_earn_credit">
			<span class="total_credit_icon"></span>
			<span><?php esc_html_e( 'Total Credits Earned', 'multilevel-referral-plugin-for-woocommerce' ); ?></span>
			<span class="show_output"><?php echo esc_html( number_format( $data['total_earn_point'], 2 ) ); ?></span>
		</div>
		<div class="referral_program_stats total_avilable_credit">
			<span class="total_credit_icon"></span>
			<span><?php echo esc_attr( apply_filters( 'woocommerce_multilevel_referral_total_credits_available', __( 'Total Credits Available', 'multilevel-referral-plugin-for-woocommerce' ) ) ); ?></span>
			<span class="show_output"><?php echo esc_html( apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', number_format( $data['total_points'], 2 ) ) ); ?></span>
		</div>
		<div class="referral_program_stats">
			<span class="total_credit_icon total_withdraw_credit"></span>
			<span><?php echo esc_html_e( 'Total Withdrawn', 'multilevel-referral-plugin-for-woocommerce' ); ?></span>
			<span class="show_output"><?php echo esc_html( number_format( $data['total_withdraw'], 2 ) ); ?></span>
		</div>
	</div>
	<div class="referral_program_overview referral_top_section">
		<div class="referral_program_stats">
			<span class="referral_icon"></span>
			<span><?php esc_html_e( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ); ?></span>
			<span class="show_output"><?php echo esc_attr( $data['referral_code'] ); ?></span>
		</div>
		<div class="referral_program_stats total_avilable_credit">
			<span class="total_credit_icon"></span>
			<span><?php echo esc_attr( apply_filters( 'woocommerce_multilevel_referral_total_credits_available', esc_html_e( 'Total Credits Available', 'multilevel-referral-plugin-for-woocommerce' ) ) ); ?></span>
			<span class="show_output"><?php echo esc_html( apply_filters( 'woocommerce_multilevel_referral_total_credits_amount', number_format( $data['total_points'], 2 ) ) ); ?></span>
		</div>
		<div class="referral_program_stats">
			<span class="total_referral"></span>
			<span><?php esc_html_e( 'Total Referrals', 'multilevel-referral-plugin-for-woocommerce' ); ?></span>
			<span class="show_output"><?php echo esc_attr( $data['total_followers'] ); ?></span>
		</div>
	</div>
	<div class="referral_program_sections" style="padding-top: 30px;">
		<div class="referral_program_content">
			<?php echo wp_kses_post( $data['content'] ); ?>
		</div>
	</div>
</div>
