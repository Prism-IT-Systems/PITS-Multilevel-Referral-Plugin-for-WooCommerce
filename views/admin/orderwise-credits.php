<?php
/**
 * Admin Orderwise Credits View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$woocommerce_multilevel_referral_page_title        = __( 'Orderwise credits', 'multilevel-referral-plugin-for-woocommerce' );
$woocommerce_multilevel_referral_order_credit_list = new WooCommerce_Multilevel_Referral_Order_Credit_List();
?>
	<form method="get" id="otherwise_credits">
		<input type="hidden" name="page" value="wc_referral" />
		<input type="hidden" name="tab" value="orderwise-credits" />
		<?php
		$woocommerce_multilevel_referral_order_credit_list->search_box( 'Search', 'search' );
		$woocommerce_multilevel_referral_order_credit_list->prepare_items();
		$woocommerce_multilevel_referral_order_credit_list->display();
		?>
	</form>
</div>
