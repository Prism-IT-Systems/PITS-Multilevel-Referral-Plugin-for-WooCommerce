<?php
/**
 * Admin Referral Users View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$woocommerce_multilevel_referral_page_title = __( 'Referral users', 'multilevel-referral-plugin-for-woocommerce' );
$woocommerce_multilevel_referral_user_list  = new WooCommerce_Multilevel_Referral_User_Table();
$woocommerce_multilevel_referral_url_data   = woocommerce_multilevel_referral_get_query_vars();
if ( isset( $woocommerce_multilevel_referral_url_data['uid'] ) && '' !== $woocommerce_multilevel_referral_url_data['uid'] ) {
	$woocommerce_multilevel_referral_client_name = get_user_meta( sanitize_text_field( wp_unslash( $woocommerce_multilevel_referral_url_data['uid'] ) ), 'first_name', true );
	$woocommerce_multilevel_referral_last_name   = get_user_meta( sanitize_text_field( wp_unslash( $woocommerce_multilevel_referral_url_data['uid'] ) ), 'last_name', true );
	if ( ! empty( $woocommerce_multilevel_referral_last_name ) ) {
		$woocommerce_multilevel_referral_client_name .= ' ' . $woocommerce_multilevel_referral_last_name;
	}
	echo '<div class="updated"><p>' . esc_html( $woocommerce_multilevel_referral_client_name ) . ' ' . esc_html_e( 'is successfully activated', 'multilevel-referral-plugin-for-woocommerce' ) . '</p></div>';
}
?>
	<form method="get" action="admin.php" id="referral_user_form">
		<input type="hidden" name="page" value="wc_referral" />
		<?php
		$woocommerce_multilevel_referral_user_list->prepare_items();
		$woocommerce_multilevel_referral_user_list->display();
		?>
	</form>
</div>
<div id="dialog_referral_user" title="List of referral users"></div>
