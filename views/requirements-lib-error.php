<?php
/**
 * Requirements Library Error View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="error">
	<p><strong><?php echo esc_html( WOOCOMMERCE_MULTILEVEL_REFERRAL_NAME ); ?></strong> <?php echo esc_html_e( 'Warning: Please enable the GD library to activate social sharing functionality.', 'multilevel-referral-plugin-for-woocommerce' ); ?></p>
</div>
