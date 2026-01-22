<?php
/**
 * Admin Referral Header View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html_e( 'Multilevel Referral Plugin for WooCommerce', 'multilevel-referral-plugin-for-woocommerce' ); ?></h1>
	<div id="referral_program_statistics">
		<div class="total_users_panel">
			<div class="icon">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<div class="number"><?php echo esc_html( $woocommerce_multilevel_referral_data['total_users'] ); ?></div>
			<div class="text"><?php echo esc_html_e( 'Total Users', 'multilevel-referral-plugin-for-woocommerce' ); ?></div>
		</div>
		<div class="total_referral_panel">
			<div class="icon">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="number"><?php echo esc_html( $woocommerce_multilevel_referral_data['total_referrals'] ); ?></div>
			<div class="text"><?php echo esc_html_e( 'Referrals', 'multilevel-referral-plugin-for-woocommerce' ); ?></div>
		</div>
		<div class="total_earn_panel">
			<div class="icon">
				<span class="dashicons dashicons-download"></span>
			</div>
			<div class="number"><?php echo esc_html( $woocommerce_multilevel_referral_data['total_credites'] ); ?></div>
			<div class="text"><?php echo esc_html_e( 'Earned Credits', 'multilevel-referral-plugin-for-woocommerce' ); ?></div>
		</div>
		<div class="total_redeem_panel">
			<div class="icon">
				<span class="dashicons dashicons-upload"></span>
			</div>
			<div class="number"><?php echo esc_html( $woocommerce_multilevel_referral_data['total_redeems'] ); ?></div>
			<div class="text"><?php echo esc_html_e( 'Redeemed Credits', 'multilevel-referral-plugin-for-woocommerce' ); ?></div>
		</div>
	</div>
<?php do_action( 'woocommerce_multilevel_referral_notices' ); ?>
<div class="woocommerce_multilevel_referral_header_tabs" id="woocommerce_multilevel_referral_header_tabs">
	<div class="scroller scroller-left"><span class="dashicons dashicons-arrow-left-alt2"></span></div>
		<div class="scroller scroller-right"><span class="dashicons dashicons-arrow-right-alt2"></span></div>
<h2 class="nav-tab-wrapper">
	<?php $woocommerce_multilevel_referral_url_data = woocommerce_multilevel_referral_get_query_vars(); ?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc_referral' ) ); ?>" title="<?php echo esc_html_e( 'Referral users', 'multilevel-referral-plugin-for-woocommerce' ); ?>" class="nav-tab <?php echo ! isset( $woocommerce_multilevel_referral_url_data['tab'] ) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Referral users', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc_referral&tab=orderwise-credits' ) ); ?>" title="<?php echo esc_html_e( 'Orderwise user credits', 'multilevel-referral-plugin-for-woocommerce' ); ?>" class="nav-tab <?php echo isset( $woocommerce_multilevel_referral_url_data['tab'] ) && 'orderwise-credits' === $woocommerce_multilevel_referral_url_data['tab'] ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Orderwise user credits', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc_referral&tab=credit-logs' ) ); ?>" title="<?php echo esc_html_e( 'Point logs', 'multilevel-referral-plugin-for-woocommerce' ); ?>" class="nav-tab <?php echo isset( $woocommerce_multilevel_referral_url_data['tab'] ) && 'credit-logs' === $woocommerce_multilevel_referral_url_data['tab'] ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Point logs', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	<!--a href="<?php echo esc_url( admin_url( 'admin.php?page=wc_referral&tab=withdraw-history' ) ); ?>" title="<?php echo esc_html_e( 'Withdraw History', 'multilevel-referral-plugin-for-woocommerce' ); ?>" class="nav-tab <?php echo isset( $woocommerce_multilevel_referral_url_data['tab'] ) && 'withdraw-history' === $woocommerce_multilevel_referral_url_data['tab'] ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Withdraw History', 'multilevel-referral-plugin-for-woocommerce' ); ?></a-->
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc_referral&tab=email-templates' ) ); ?>" title="<?php echo esc_html_e( 'Email templates', 'multilevel-referral-plugin-for-woocommerce' ); ?>" class="nav-tab <?php echo isset( $woocommerce_multilevel_referral_url_data['tab'] ) && 'email-templates' === $woocommerce_multilevel_referral_url_data['tab'] ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Email templates', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wc_ml_ref_banner&tab=banners' ) ); ?>" title="<?php echo esc_html_e( 'Banners', 'multilevel-referral-plugin-for-woocommerce' ); ?>" class="nav-tab <?php echo isset( $woocommerce_multilevel_referral_url_data['tab'] ) && 'banners' === $woocommerce_multilevel_referral_url_data['tab'] ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Banners', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	<?php do_action( 'woocommerce_multilevel_referral_header' ); ?>
	<a href="<?php echo isset( $woocommerce_multilevel_referral_data['advance_setting_link'] ) ? esc_url( $woocommerce_multilevel_referral_data['advance_setting_link'] ) : '#'; ?>" title="<?php echo esc_html_e( 'Advance Settings', 'multilevel-referral-plugin-for-woocommerce' ) . ( ! isset( $woocommerce_multilevel_referral_data['advance_setting_link'] ) ? ' (' . esc_html_e( 'Premium Feature', 'multilevel-referral-plugin-for-woocommerce' ) . ')' : '' ); ?>" class="nav-tab advance_setting_link <?php echo isset( $woocommerce_multilevel_referral_url_data['tab'] ) && 'advsettings' === $woocommerce_multilevel_referral_url_data['tab'] ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Advance Settings', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	<a href="<?php echo isset( $woocommerce_multilevel_referral_data['addons_link'] ) ? esc_url( $woocommerce_multilevel_referral_data['addons_link'] ) : '#'; ?>" title="<?php echo esc_html_e( 'Add-Ons', 'multilevel-referral-plugin-for-woocommerce' ) . ( ! isset( $woocommerce_multilevel_referral_data['addons_link'] ) ? ' (' . esc_html_e( 'Premium Feature', 'multilevel-referral-plugin-for-woocommerce' ) . ')' : '' ); ?>" class="nav-tab addons_link <?php echo isset( $woocommerce_multilevel_referral_url_data['tab'] ) && 'addons' === $woocommerce_multilevel_referral_url_data['tab'] ? 'nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Add-Ons', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
</h2>
</div>
