<?php
/**
 * Admin Email Templates View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$woocommerce_multilevel_referral_page_title                   = __( 'Email templates', 'multilevel-referral-plugin-for-woocommerce' );
$woocommerce_multilevel_referral_joining_mail_template        = stripcslashes( get_option( 'joining_mail_template', '' ) );
$woocommerce_multilevel_referral_joining_mail_subject         = stripcslashes( get_option( 'joining_mail_subject', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) );
$woocommerce_multilevel_referral_joining_mail_heading         = stripcslashes( get_option( 'joining_mail_heading', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) );
$woocommerce_multilevel_referral_referral_user_template       = stripcslashes( get_option( 'referral_user_template', '' ) );
$woocommerce_multilevel_referral_referral_user_subject        = stripcslashes( get_option( 'referral_user_subject', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) );
$woocommerce_multilevel_referral_referral_user_heading        = stripcslashes( get_option( 'referral_user_heading', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) );
$woocommerce_multilevel_referral_expire_notification_template = stripcslashes( get_option( 'expire_notification_template', '' ) );
$woocommerce_multilevel_referral_expire_notification_subject  = stripcslashes( get_option( 'expire_notification_subject', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) );
$woocommerce_multilevel_referral_expire_notification_heading  = stripcslashes( get_option( 'expire_notification_heading', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) );
$woocommerce_multilevel_referral_settings                     = array(
	'editor_height' => 425,
	'textarea_rows' => 20,
);
?>
<form action="" method="post">
	<div class="woocommerce-multilevel-referral-email-template">
		<div class="mdl-tabs vertical-mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
		<div class="mdl-grid mdl-grid--no-spacing">
			<div class="mdl-cell mdl-cell--2-col">
				<div class="mdl-tabs__tab-bar"> <a href="#tab1-panel" class="mdl-tabs__tab is-active"> <span class="hollow-circle"></span> <?php esc_html_e( 'Joining mail for referral program', 'multilevel-referral-plugin-for-woocommerce' ); ?> </a> <a href="#tab2-panel" class="mdl-tabs__tab"> <span class="hollow-circle"></span> <?php esc_html_e( 'Invitation mail for Referral users', 'multilevel-referral-plugin-for-woocommerce' ); ?> </a> <a href="#tab3-panel" class="mdl-tabs__tab"> <span class="hollow-circle"></span> <?php esc_html_e( 'Expire credit notification', 'multilevel-referral-plugin-for-woocommerce' ); ?> </a>
					<?php do_action( 'woocommerce_multilevel_referral_mail_menu_header' ); ?>
				</div>
			</div>
			<?php wp_nonce_field( 'woocommerce_multilevel_referral_template_action', 'woocommerce_multilevel_referral_template_nonce' ); ?>
			<div class="mdl-cell mdl-cell--10-col">
				<div class="mdl-tabs__panel is-active" id="tab1-panel">
					<div class="cell-50 cell-0">
					<label for="joining_mail_subject"><?php esc_html_e( 'Joining mail Subject', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
					<div><input placeholder="<?php esc_html_e( 'Joining mail Subject', 'multilevel-referral-plugin-for-woocommerce' ); ?>" type="text" class="form-field" name="joining_mail_subject" id="joining_mail_subject" value="<?php echo esc_attr( $woocommerce_multilevel_referral_joining_mail_subject ); ?>"></div>
					</div>
					<div class="cell-50 cell-1">
					<label for="joining_mail_heading"><?php esc_html_e( 'Joining mail Heading', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
					<div><input placeholder="<?php esc_html_e( 'Joining mail Heading', 'multilevel-referral-plugin-for-woocommerce' ); ?>" type="text" class="form-field" name="joining_mail_heading" id="joining_mail_heading" value="<?php echo esc_attr( $woocommerce_multilevel_referral_joining_mail_heading ); ?>"></div>
					</div>
					<?php echo esc_html( wp_editor( $woocommerce_multilevel_referral_joining_mail_template, 'joining_mail_template', $woocommerce_multilevel_referral_settings ) ); ?> <small><?php esc_html_e( 'You can use{referral_code}to replace respective referral code.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( 'You can use{first_name}to replace respective user name.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( 'You can use{last_name}to replace respective user name.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small>
					<p> <input type="submit" class="button button-primary button-large" name="save_template" value="<?php esc_html_e( 'Save template', 'multilevel-referral-plugin-for-woocommerce' ); ?>"/> </p>
				</div>
				<div class="mdl-tabs__panel" id="tab2-panel">
					<div class="cell-50 cell-0">
					<label for="referral_user_subject"><?php esc_html_e( 'Referral User E-mail Subject', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
					<div><input placeholder="<?php esc_html_e( 'Referral User E-mail Subject', 'multilevel-referral-plugin-for-woocommerce' ); ?>" type="text" class="form-field" name="referral_user_subject" id="referral_user_subject" value="<?php echo esc_attr( $woocommerce_multilevel_referral_referral_user_subject ); ?>"></div>
					</div>
					<div class="cell-50 cell-1">
					<label for="referral_user_heading"><?php esc_html_e( 'Referral User E-mail Heading', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
					<div><input placeholder="<?php esc_html_e( 'Referral User E-mail Heading', 'multilevel-referral-plugin-for-woocommerce' ); ?>" type="text" class="form-field" name="referral_user_heading" id="referral_user_heading" value="<?php echo esc_attr( $woocommerce_multilevel_referral_referral_user_heading ); ?>"></div>
					</div>
					<?php echo esc_html( wp_editor( $woocommerce_multilevel_referral_referral_user_template, 'referral_user_template', $woocommerce_multilevel_referral_settings ) ); ?> <small><?php esc_html_e( 'You can use{referral_code}to replace respective referral code.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( 'You can use{first_name}to replace respective user name.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( 'You can use{last_name}to replace respective user name.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( 'You can use [referral_link text="Click here"] to replace respective user referral link.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small>
					<p> <input type="submit" class="button button-primary button-large" name="save_template" value="<?php esc_html_e( 'Save template', 'multilevel-referral-plugin-for-woocommerce' ); ?>"/> </p>
				</div>
				<div class="mdl-tabs__panel" id="tab3-panel">
					<div class="cell-50 cell-0">
					<label for="expire_notification_subject"><?php esc_html_e( 'Expire Notification E-mail Subject', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
					<div><input placeholder="<?php esc_html_e( ' Notification E-mail Subject', 'multilevel-referral-plugin-for-woocommerce' ); ?>" type="text" class="form-field" name="expire_notification_subject" id="expire_notification_subject" value="<?php echo esc_attr( $woocommerce_multilevel_referral_expire_notification_subject ); ?>"></div>
					</div>
					<div class="cell-50 cell-1">
					<label for="expire_notification_heading"><?php esc_html_e( 'Expire Notification E-mail Heading', 'multilevel-referral-plugin-for-woocommerce' ); ?></label>
					<div><input placeholder="<?php esc_html_e( ' Notification E-mail Heading', 'multilevel-referral-plugin-for-woocommerce' ); ?>" type="text" class="form-field" name="expire_notification_heading" id="expire_notification_heading" value="<?php echo esc_attr( $woocommerce_multilevel_referral_expire_notification_heading ); ?>"></div>
					</div>
					<?php echo esc_html( wp_editor( $woocommerce_multilevel_referral_expire_notification_template, 'expire_notification_template', $woocommerce_multilevel_referral_settings ) ); ?> <small><?php esc_html_e( '{available_credits}- Replace respective user credits.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( '{first_name}- Replace respective user name.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( '{last_name}- Replace respective user name.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( '{expire_date}- Replace respective expiry date of user credits.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( '{validity_period}- Replace respective store credit validity.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( '{today_date}- Replace respective current date.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( '{expire_month}- Replace respective credit expired month.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small><br/> <small><?php esc_html_e( '{expire_credits}- Replace respective expired credits.', 'multilevel-referral-plugin-for-woocommerce' ); ?></small>
					<p> <input type="submit" class="button button-primary button-large" name="save_template" value="<?php esc_html_e( 'Save template', 'multilevel-referral-plugin-for-woocommerce' ); ?>"/> </p>
				</div>
				<?php do_action( 'woocommerce_multilevel_referral_mail_menu_content', $woocommerce_multilevel_referral_settings ); ?>
			</div>
		</div>
		</div>
	</div>
</form>
