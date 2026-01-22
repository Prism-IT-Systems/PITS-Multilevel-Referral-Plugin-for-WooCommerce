<?php
/**
 * Admin Addons View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php $woocommerce_multilevel_referral_addons = simplexml_load_file( WOOCOMMERCE_MULTILEVEL_REFERRAL_DIR . '/addons.xml' ); ?>
<div class="woocommerce-multilevel-referral-addons-panel">
	<h1><?php echo esc_html__( 'Implementing Inputs with Add-Ons for an ever evolving experience', 'multilevel-referral-plugin-for-woocommerce' ); ?></h1>
	<p><?php echo esc_html__( 'Recognising various popular requests, we steadily aim for new advancements at your disposal', 'multilevel-referral-plugin-for-woocommerce' ); ?></p>
	<?php foreach ( $woocommerce_multilevel_referral_addons as $woocommerce_multilevel_referral_addon ) : ?>
		<?php $woocommerce_multilevel_referral_plugin_file = $woocommerce_multilevel_referral_addon->slug . '/' . $woocommerce_multilevel_referral_addon->slug . '.php'; ?>
		<?php $woocommerce_multilevel_referral_licence = get_option( $woocommerce_multilevel_referral_addon->slug . '_activated' ); ?>
	<div class="woocommerce-multilevel-referral-addons-banner-block-item woocommerce-multilevel-referral-blue-gradient">
		<div class="woocommerce-multilevel-referral-addons-banner-block-item-icon">
			<div class="woocommerce-multilevel-referral-addons-banner-inner">
				<?php
				$woocommerce_multilevel_referral_img_url = WOOCOMMERCE_MULTILEVEL_REFERRAL_URL . '/images/add-ons/' . $woocommerce_multilevel_referral_addon->image_url;
				// phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				echo '<img class="woocommerce-multilevel-referral-addons-img" src="' . esc_url( $woocommerce_multilevel_referral_img_url ) . '">';
				?>
			</div>
			<?php if ( '' !== $woocommerce_multilevel_referral_addon->video_url ) : ?>
			<div>
				<a class="woocommerce-multilevel-referral-addons-button woocommerce-multilevel-referral-addons-watch" href=""><?php esc_html_e( 'Watch', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
			</div>
			<?php endif; ?>
		</div>
		<div class="woocommerce-multilevel-referral-addons-banner-block-item-content">
			<h3><?php echo esc_html( $woocommerce_multilevel_referral_addon->name ); ?></h3>
			<p><?php echo esc_html( $woocommerce_multilevel_referral_addon->description ); ?></p>
		</div>
		<div class="woocommerce-multilevel-referral-addon-inner">
			<div class="woocommerce-multilevel-referral-addon-price">
				<?php echo esc_html( $woocommerce_multilevel_referral_addon->price ); ?>
			</div>
			<div>
				<?php
				$woocommerce_multilevel_referral_filename = ABSPATH . 'wp-content/plugins/' . $woocommerce_multilevel_referral_plugin_file;
				if ( file_exists( $woocommerce_multilevel_referral_filename ) && ! is_plugin_active( $woocommerce_multilevel_referral_plugin_file ) ) {
					$woocommerce_multilevel_referral_plugin_slug        = trim( $woocommerce_multilevel_referral_addon->slug );
					$woocommerce_multilevel_referral_plugin_active_link = add_query_arg(
						array(
							'page'        => 'wc_referral',
							'tab'         => 'addons',
							'plugin'      => $woocommerce_multilevel_referral_plugin_file,
							'plugin_slug' => $woocommerce_multilevel_referral_plugin_slug,
							'woocommerce_multilevel_referral_addons_active_nonce' => wp_create_nonce( $woocommerce_multilevel_referral_plugin_slug ),
						),
						admin_url( 'admin.php' )
					);
					?>
					<a class="woocommerce-multilevel-referral-addons-button woocommerce-multilevel-referral-addons-button-activate"  href="<?php echo esc_url( $woocommerce_multilevel_referral_plugin_active_link ); ?>"><?php esc_html_e( 'Activate', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
					<?php
				} elseif ( is_plugin_active( $woocommerce_multilevel_referral_plugin_file ) ) {
					/*
					$licence_validation_remaining has added for future purpose. Once we complete licence validation functionality, we can remove this.
					*/
					?>
					<?php if ( ! $woocommerce_multilevel_referral_licence && isset( $woocommerce_multilevel_referral_licence_validation_remaining ) ) : ?>
					<form name="woocommerce-multilevel-referral-addons-form" method="post">
						<?php wp_nonce_field( $woocommerce_multilevel_referral_addon->slug, 'woocommerce_multilevel_referral_addons_nonce' ); ?>
						<h2><?php esc_html_e( 'Licence Key', 'multilevel-referral-plugin-for-woocommerce' ); ?>:</h2>
						<input type="text" name="woocommerce-multilevel-referral-addon-license-key">
						<input type="button" class="woocommerce-multilevel-referral-addons-button-activate" value="<?php esc_html_e( 'Verify', 'multilevel-referral-plugin-for-woocommerce' ); ?>">
					</form>
					<?php else : ?>
						<input type="button" class="woocommerce-multilevel-referral-addons-button-activate" value="<?php esc_html_e( 'Activated', 'multilevel-referral-plugin-for-woocommerce' ); ?>">
						<?php
					endif;
				} else {
					?>
				<a class="woocommerce-multilevel-referral-addons-button addon-buy-now" target="_blank" href="<?php echo esc_url( $woocommerce_multilevel_referral_addon->plugin_link ); ?>"><?php echo esc_html__( 'Buy Now', 'multilevel-referral-plugin-for-woocommerce' ); ?></a><br />
				<a class="woocommerce-multilevel-referral-addons-button addon-demo" target="_blank" href="<?php echo esc_url( $woocommerce_multilevel_referral_addon->demo_link ); ?>"><?php echo esc_html__( 'Demo', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>
