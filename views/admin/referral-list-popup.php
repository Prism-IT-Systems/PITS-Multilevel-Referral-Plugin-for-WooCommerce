<?php
/**
 * Admin Referral List Popup View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! $woocommerce_multilevel_referral_referral_users ) {
	return;
}
$woocommerce_multilevel_referral_count = 1;
echo '<ul>';
foreach ( $woocommerce_multilevel_referral_referral_users as $woocommerce_multilevel_referral_user ) {
	$woocommerce_multilevel_referral_extend_list = '<li data-id="%d"><div><span class="%s">%s %s</span> (<span class="count">%d</span>)<a href="#" class="remove_referral_user">Remove</a></div></li>';
	if ( $woocommerce_multilevel_referral_user->followers ) {
		$woocommerce_multilevel_referral_extend_list = '<li data-get="0" data-id="%d"><div><a class="get_referral_user" href="#"><span class="%s">%s %s</span> (<span class="count">%d</span>)</a><a href="#" class="remove_referral_user">Remove</a></div></li>';
	}
	$woocommerce_multilevel_referral_inactive_class = '';
	if ( ! $woocommerce_multilevel_referral_user->active ) {
		$woocommerce_multilevel_referral_inactive_class = 'in_active';
	}
	if ( empty( $woocommerce_multilevel_referral_user->first_name ) && empty( $woocommerce_multilevel_referral_user->last_name ) ) {
		$woocommerce_multilevel_referral_number = get_user_meta( $woocommerce_multilevel_referral_user->user_id, 'billing_phone', true );
		echo wp_kses_post( sprintf( $woocommerce_multilevel_referral_extend_list, esc_attr( $woocommerce_multilevel_referral_user->user_id ), esc_attr( $woocommerce_multilevel_referral_inactive_class ), esc_html( $woocommerce_multilevel_referral_number ), esc_html( '' ), esc_html( $woocommerce_multilevel_referral_user->followers ) ) );
	} else {
		echo wp_kses_post( sprintf( $woocommerce_multilevel_referral_extend_list, esc_attr( $woocommerce_multilevel_referral_user->user_id ), esc_attr( $woocommerce_multilevel_referral_inactive_class ), esc_html( $woocommerce_multilevel_referral_user->first_name ), esc_html( $woocommerce_multilevel_referral_user->last_name ), esc_html( $woocommerce_multilevel_referral_user->followers ) ) );
	}
}
echo '</ul>';
