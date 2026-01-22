<?php
/**
 * Admin Credit Logs View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$woocommerce_multilevel_referral_page_title      = __( 'Credit logs', 'multilevel-referral-plugin-for-woocommerce' );
$woocommerce_multilevel_referral_credit_log_list = new WooCommerce_Multilevel_Referral_Credit_Log();
?>
	<form method="get" id="point_logs_id">
		<input type="hidden" name="page" value="wc_referral" />
		<input type="hidden" name="tab" value="credit-logs" />
		<?php
		$woocommerce_multilevel_referral_credit_log_list->search_box( 'Search', 'search' );
		$woocommerce_multilevel_referral_credit_log_list->prepare_items();
		$woocommerce_multilevel_referral_credit_log_list->display();
		?>
	</form>
</div>
