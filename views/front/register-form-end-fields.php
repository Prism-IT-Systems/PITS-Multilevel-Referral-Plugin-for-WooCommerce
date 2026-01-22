<?php
/**
 * Front Register Form End Fields View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_nonce_field( 'referral_program', 'referral_registration_validation_nonce' );
$woocommerce_multilevel_referral_data                    = $data;
$woocommerce_multilevel_referral_auto_join               = get_option( 'woocommerce_multilevel_referral_auto_register', 'no' );
$woocommerce_multilevel_referral_required_referral_field = 'no';
$woocommerce_multilevel_referral_readonly                = '';
if ( isset( $woocommerce_multilevel_referral_data['referral_code'] ) && '' !== $woocommerce_multilevel_referral_data['referral_code'] ) {
	$woocommerce_multilevel_referral_nonce = ( isset( $_REQUEST['woocommerce-register-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-register-nonce'] ) ) : '' );
	if ( wp_verify_nonce( $woocommerce_multilevel_referral_nonce, 'woocommerce-register' ) ) {
		if ( isset( $_POST['wrong_referral_code'] ) && 'yes' === $_POST['wrong_referral_code'] ) {
			$woocommerce_multilevel_referral_readonly = '';
		} else {
			$woocommerce_multilevel_referral_readonly = 'readonly="readonly"';
		}
	}
}
if ( 'yes' === $woocommerce_multilevel_referral_auto_join ) {
	?>
		<p class="referral_code_panel form-row form-row-wide">
			<input type="hidden" name="join_referral_program" value="1">
			<input type="hidden" name="termsandconditions" value="1">
		<label for="referral_code">
		<?php
		echo esc_html( apply_filters( 'woocommerce_multilevel_referral_reg_field_referral_field_name_change', __( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ) ) ) . ( ( 'yes' === $woocommerce_multilevel_referral_required_referral_field ? ' <span class="required">*</span>' : '' ) );
		?>
	</label>
		<input type="text" 
		<?php
		echo esc_attr( $woocommerce_multilevel_referral_readonly );
		?>
	placeholder="
	<?php
	echo ( 'yes' === $woocommerce_multilevel_referral_required_referral_field ? esc_html_e( 'Enter referral code', 'multilevel-referral-plugin-for-woocommerce' ) : esc_html_e( 'Add referral code if you have', 'multilevel-referral-plugin-for-woocommerce' ) );
	?>
	"  class="input-text" 
	<?php
	( 'yes' === $woocommerce_multilevel_referral_required_referral_field ? 'required' : '' );
	?>
	name="referral_code" id="referral_code" value="
	<?php
	echo ( isset( $data['referral_code'] ) ? esc_attr( $data['referral_code'] ) : '' );
	?>
	" />
		</p>
		<p class="referral_terms_conditions form-row form-row-wide">
		<small><i>
		<?php
		echo esc_html_e( 'By registering here, you agree to the', 'multilevel-referral-plugin-for-woocommerce' );
		?>
	<a href="
	<?php
	echo esc_url( get_permalink( get_option( 'woocommerce_multilevel_referral_terms_and_conditions', 0 ) ) );
	?>
	" target="_blank">
	<?php
	echo esc_html_e( ' Terms and conditions.', 'multilevel-referral-plugin-for-woocommerce' );
	?>
	</a> </i></small>
		</p>
	<?php
} else {
	?>
		<p class="form-row form-row-wide">
		<label for="option_1"><input type="radio" id="option_1" name="join_referral_program"  
		<?php
		echo ( isset( $data['join_referral_program'] ) && ( 0 === $data['join_referral_program'] || '1' === $data['join_referral_program'] ) ? 'checked' : '' );
		?>
	value="1" /> 
	<?php
	echo esc_html_e( 'I have the referral code and want to join referral program.', 'multilevel-referral-plugin-for-woocommerce' );
	?>
	</label>
		<label for="option_2"><input type="radio" id="option_2" name="join_referral_program" 
		<?php
		echo ( isset( $data['join_referral_program'] ) && '2' === $data['join_referral_program'] ? 'checked' : '' );
		?>
	value="2" /> 
	<?php
	echo esc_html_e( 'I don\'t have referral code but i wish to join referral program.', 'multilevel-referral-plugin-for-woocommerce' );
	?>
	</label>
		<label for="option_3"><input type="radio" id="option_3" name="join_referral_program" 
		<?php
		echo ( isset( $data['join_referral_program'] ) && '3' === $data['join_referral_program'] ? 'checked' : '' );
		?>
	value="3"  /> 
	<?php
	echo esc_html_e( 'No, I don\'t want to be a part of referral program at this time.', 'multilevel-referral-plugin-for-woocommerce' );
	?>
	</label>
		</p>
		<p class="referral_terms_conditions form-row form-row-wide">
			<label for="termsandconditions"><input type="checkbox" 
			<?php
			echo ( isset( $data['termsandconditions'] ) && $data['termsandconditions'] ? 'checked' : '' );
			?>
	name="termsandconditions" id="termsandconditions" value="1" /> 
	<?php
	esc_html_e( 'I\'ve read and agree to the referral program', 'multilevel-referral-plugin-for-woocommerce' );
	?>
	<a href="
	<?php
	echo esc_url( get_permalink( get_option( 'woocommerce_multilevel_referral_terms_and_conditions', 0 ) ) );
	?>
	" target="_blank">
		<?php
		esc_html_e( 'terms & conditions', 'multilevel-referral-plugin-for-woocommerce' );
		?>
	</a></label>
		</p>
		<p class="referral_code_panel form-row form-row-wide">
			<label for="referral_code">
			<?php
			echo esc_html( apply_filters( 'woocommerce_multilevel_referral_reg_field_referral_field_name_change', __( 'Referral Code', 'multilevel-referral-plugin-for-woocommerce' ) ) );
			?>
	<span class="required">*</span></label>
			<input type="text" 
			<?php
			echo esc_attr( $woocommerce_multilevel_referral_readonly );
			?>
	class="input-text" name="referral_code" id="referral_code" value="
	<?php
	echo ( isset( $woocommerce_multilevel_referral_data['referral_code'] ) ? esc_attr( $woocommerce_multilevel_referral_data['referral_code'] ) : '' );
	?>
	" />
			<small>
			<?php
			echo esc_html_e( '&nbsp;', 'multilevel-referral-plugin-for-woocommerce' );
			?>
	</small>
		</p>
	<?php
}