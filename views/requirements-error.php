<?php
/**
 * Requirements Error View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="error">
	<p><?php echo esc_html( WOOCOMMERCE_MULTILEVEL_REFERRAL_NAME ); ?> error: Your environment doesn't meet all of the system requirements listed below.</p>
	<ul class="ul-disc">
		<li>
			<strong>PHP <?php echo esc_html( WOOCOMMERCE_MULTILEVEL_REFERRAL_REQUIRED_PHP_VERSION ); ?>+</strong>
			<em>(You're running version <?php echo esc_html( PHP_VERSION ); ?>)</em>
		</li>
		<li>
			<strong>WordPress <?php echo esc_html( WOOCOMMERCE_MULTILEVEL_REFERRAL_REQUIRED_WP_VERSION ); ?>+</strong>
			<em>(You're running version <?php echo esc_html( $wp_version ); ?>)</em>
		</li>
		<?php
		echo ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) ? '<li><strong>WooCommerce Plugin</strong> needs to be activate.</em></li>' : '';
		?>
	</ul>
	<p>If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.</p>
</div>
