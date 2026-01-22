<?php
/**
 * Front Store Credits Notice View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="store_credit_notice">
	<h3>
		<?php
		// translators: %s is the store credit.
		$woocommerce_multilevel_referral_store_credit = floatval( $data['store_credit'] );
		$woocommerce_multilevel_referral_store_credit = number_format( $woocommerce_multilevel_referral_store_credit, 2 );
		echo esc_html_e( 'You have ', 'multilevel-referral-plugin-for-woocommerce' ) . esc_attr( $woocommerce_multilevel_referral_store_credit );
		esc_html_e( ' store credits.', 'multilevel-referral-plugin-for-woocommerce' );
		?>
			<a href="#"><?php echo esc_html_e( 'Redeem now.', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	</h3>
	<form method="post" action="<?php echo esc_url( get_the_permalink() ); ?>">
	<?php
		$woocommerce_multilevel_referral_applied_credit_amount = floatval( $data['applied_credit_amount'] );
		$woocommerce_multilevel_referral_applied_credit_amount = number_format( $woocommerce_multilevel_referral_applied_credit_amount, 2 );
	?>
	<input type="text" value="<?php echo esc_attr( $woocommerce_multilevel_referral_applied_credit_amount ); ?>" name="applied_credit_amount" />
		<input type="hidden" name="action" value="apply_store_credit" />
		<input type="hidden" name="_nonce" value="<?php echo esc_attr( $data['nonce'] ); ?>" />
		<input type="submit" value="<?php echo esc_html_e( 'Apply', 'multilevel-referral-plugin-for-woocommerce' ); ?>" /><br />
		<div class="notice">
			<small>
			<?php
			// translators: %s is the max use credit.
			$woocommerce_multilevel_referral_max_use_credit = floatval( $data['max_use_credit'] );
			$woocommerce_multilevel_referral_max_use_credit = number_format( $woocommerce_multilevel_referral_max_use_credit, 2 );
			echo esc_html_e( 'You can use max ', 'multilevel-referral-plugin-for-woocommerce' ) . esc_attr( $woocommerce_multilevel_referral_max_use_credit );
			echo esc_html_e( ' as store credit.', 'multilevel-referral-plugin-for-woocommerce' ) . esc_attr( $data['notice'] );
			?>
			</small>
		</div>
	</form>
</div>
