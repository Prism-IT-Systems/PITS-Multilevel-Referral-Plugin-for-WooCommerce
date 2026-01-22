<?php
/**
 * Admin Withdraw History View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$woocommerce_multilevel_referral_page_title            = __( 'Withdrawn History', 'multilevel-referral-plugin-for-woocommerce' );
$woocommerce_multilevel_referral_withdraw_history_list = new WooCommerce_Multilevel_Referral_Withdraw_History();
$woocommerce_multilevel_referral_url_data              = woocommerce_multilevel_referral_get_query_vars();
?>
<?php if ( isset( $woocommerce_multilevel_referral_url_data['action'] ) && 'delete' === $woocommerce_multilevel_referral_url_data['action'] ) { ?>
<div class="notice notice-success is-dismissible">
	<p><strong>Withdraw transaction deleted.</strong></p>
</div>
<?php } ?>
	<form method="get" id="form_widthdraw_filter">
		<div class="tablenav top">
			<div class="alignleft actions">
				<input type="hidden" name="page" value="wc_referral">
				<input type="hidden" name="tab" value="withdraw-history">
			<?php
			echo '<input type="text" name="search_by_name" placeholder="' . esc_html_e( 'Search By Name', 'multilevel-referral-plugin-for-woocommerce' ) . '" value="' . ( isset( $woocommerce_multilevel_referral_url_data['search_by_name'] ) ? esc_attr( sanitize_text_field( wp_unslash( $woocommerce_multilevel_referral_url_data['search_by_name'] ) ) ) : '' ) . '" />';
			echo '<input type="text" name="search_by_mobile" class="search_email" placeholder="' . esc_html_e( 'Search By Mobile Number', 'multilevel-referral-plugin-for-woocommerce' ) . '" value="' . ( isset( $woocommerce_multilevel_referral_url_data['search_by_mobile'] ) ? esc_attr( sanitize_text_field( wp_unslash( $woocommerce_multilevel_referral_url_data['search_by_mobile'] ) ) ) : '' ) . '" />';
			echo '<lable>' . esc_html_e( 'Date Range', 'multilevel-referral-plugin-for-woocommerce' ) . ' :</lable>';
			echo '<input type="text" name="search_start_date" placeholder="YYYY-MM-DD" value="' . ( isset( $woocommerce_multilevel_referral_url_data['search_start_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $woocommerce_multilevel_referral_url_data['search_start_date'] ) ) ) : '' ) . '" />';
			echo '<input type="text" name="search_end_date" placeholder="YYYY-MM-DD" value="' . ( isset( $woocommerce_multilevel_referral_url_data['search_end_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $woocommerce_multilevel_referral_url_data['search_end_date'] ) ) ) : '' ) . '" />';
			submit_button( esc_html_e( 'Apply', 'multilevel-referral-plugin-for-woocommerce' ), 'action', '', false, array( 'id' => 'doaction' ) );
			echo '<input type="button" value="' . esc_html_e( 'Reset', 'multilevel-referral-plugin-for-woocommerce' ) . '" class="button action" id="reset_button_withdraw"><br />';
			?>
			</div>
		</div>
	</form>
	<form method="get" id="form_widthdraw_table">
		<input type="hidden" name="page" value="wc_referral">
		<input type="hidden" name="tab" value="withdraw-history">
		<?php
		$woocommerce_multilevel_referral_withdraw_history_list->prepare_items();
		$woocommerce_multilevel_referral_withdraw_history_list->display();
		?>
	</form>
</div>
