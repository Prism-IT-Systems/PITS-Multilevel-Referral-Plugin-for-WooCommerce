<?php
/**
 * Front My Account View
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$woocommerce_multilevel_referral_data = $data;
?>
<div class="referral_program_details">
	<p class="hide">
	<a href="#" class="button btn-invite-friends"><?php echo esc_html_e( 'Invite Friends', 'multilevel-referral-plugin-for-woocommerce' ); ?></a>
	</p>
	<div id="dialog-invitation-form">
		<h2><?php __( 'Invite your friends', 'multilevel-referral-plugin-for-woocommerce' ); ?></h2>
		<span><small><?php echo esc_html_e( 'You can earn more credits by inviting more people to join this referral program. You can add comma separated list of emails below ...', 'multilevel-referral-plugin-for-woocommerce' ); ?></small></span>
		<form method="post">
			<table class="shop_table shop_table_responsive">
				<tr>
					<td>
						<input type="text" name="emails"  class="input-text" value="<?php echo esc_attr( $data['emails'] ); ?>" placeholder="Ex. test@demo.com, test2@demo.com" />
					</td>
					<td width="30%">
						<input type="submit" class="button btn-send-invitation" value="<?php echo esc_html_e( 'Send Invitations', 'multilevel-referral-plugin-for-woocommerce' ); ?>" />
						<input type="hidden" name="action" value="send_invitations" />
					</td>
				</tr>
			</table>
		</form>
	</div>
	<h2><?php __( 'Referral Program Details', 'multilevel-referral-plugin-for-woocommerce' ); ?></h2>
	<table class="shop_table shop_table_responsive my_account_orders">
		<tr>
			<th><?php __( 'Your Referral Code', 'multilevel-referral-plugin-for-woocommerce' ); ?></th>
			<th><?php __( 'Store Credits', 'multilevel-referral-plugin-for-woocommerce' ); ?></th>
			<th><?php __( 'Total Followers', 'multilevel-referral-plugin-for-woocommerce' ); ?></th>
		</tr>
		<tr>
			<td><?php echo esc_attr( $data['referral_code'] ); ?></td>
			<td><?php echo esc_attr( wc_price( $data['total_points'] ) ); ?></td>
			<td><?php echo esc_attr( $data['total_followers'] ); ?></td>
		</tr>
	</table>
	<?php
	if ( count( $data['records'] ) > 0 ) {
		?>
	<table class="shop_table shop_table_responsive my_account_orders">
		<tr>
			<!--th><?php esc_attr_e( 'Order', 'multilevel-referral-plugin-for-woocommerce' ); ?></th-->
			<th><?php __( 'Date', 'multilevel-referral-plugin-for-woocommerce' ); ?></th>
			<th><?php __( 'Note', 'multilevel-referral-plugin-for-woocommerce' ); ?></th>
		</tr>
		<?php
		foreach ( $woocommerce_multilevel_referral_data['records'] as $woocommerce_multilevel_referral_row ) {
			$woocommerce_multilevel_referral_note  = '';
			$woocommerce_multilevel_referral_order = new WC_Order( $woocommerce_multilevel_referral_row['order_id'] );
			if ( $woocommerce_multilevel_referral_row['credits'] > 0 ) {
				$woocommerce_multilevel_referral_credits = wc_price( $woocommerce_multilevel_referral_row['credits'] );
				if ( $woocommerce_multilevel_referral_row['user_id'] === $woocommerce_multilevel_referral_order->user_id ) {
					if ( 'cancelled' === $woocommerce_multilevel_referral_order->get_status() || 'refunded' === $woocommerce_multilevel_referral_order->get_status() || 'failed' === $woocommerce_multilevel_referral_order->get_status() ) {
						// translators: %s is the credits and order id.
						$woocommerce_multilevel_referral_note = sprintf( __( '%1$s Store credit is refund for order %2$s.', 'multilevel-referral-plugin-for-woocommerce' ), $woocommerce_multilevel_referral_credits, '#' . $woocommerce_multilevel_referral_row['order_id'] );
					} else {
						// translators: %s is the credits and order id.
						$woocommerce_multilevel_referral_note = sprintf( __( '%1$s Store credit is earned from order %2$s.', 'multilevel-referral-plugin-for-woocommerce' ), $woocommerce_multilevel_referral_credits, '#' . $woocommerce_multilevel_referral_row['order_id'] );
					}
				} else {
					// translators: %s is the credits and user name.
					$woocommerce_multilevel_referral_note = sprintf( __( '%1$s Store credit is earned through referral user ( %2$s order %3$s )  ', 'multilevel-referral-plugin-for-woocommerce' ), $woocommerce_multilevel_referral_credits, get_user_meta( $woocommerce_multilevel_referral_order->user_id, 'first_name', true ) . ' ' . get_user_meta( $woocommerce_multilevel_referral_order->user_id, 'last_name', true ), '#' . $woocommerce_multilevel_referral_row['order_id'] );
				}
			}
			if ( $woocommerce_multilevel_referral_row['redeems'] > 0 ) {
				$woocommerce_multilevel_referral_redeems = wc_price( $woocommerce_multilevel_referral_row['redeems'] );
				if ( 'cancelled' === $woocommerce_multilevel_referral_order->get_status() || 'refunded' === $woocommerce_multilevel_referral_order->get_status() || 'failed' === $woocommerce_multilevel_referral_order->get_status() ) {
					// translators: %s is the redeems and order id.
					$woocommerce_multilevel_referral_note = sprintf( __( '%1$s Store credit is refund for order %2$s.', 'multilevel-referral-plugin-for-woocommerce' ), $woocommerce_multilevel_referral_redeems, '#' . $woocommerce_multilevel_referral_row['order_id'] );
				} elseif ( $woocommerce_multilevel_referral_row['order_id'] ) {
						// translators: %s is the redeems and order id.
						$woocommerce_multilevel_referral_note = sprintf( __( '%1$s Store credit is used in order %2$s.', 'multilevel-referral-plugin-for-woocommerce' ), $woocommerce_multilevel_referral_redeems, '#' . $woocommerce_multilevel_referral_row['order_id'] );
				} else {
					// translators: %s is the redeems.
					$woocommerce_multilevel_referral_note = sprintf( __( '%s Store credit is expired.', 'multilevel-referral-plugin-for-woocommerce' ), $woocommerce_multilevel_referral_redeems );
				}
			}
			echo '
						<tr>
							<!--td><a htref="">#' . esc_attr( $woocommerce_multilevel_referral_row['order_id'] ) . '</a></td-->
							<td>' . esc_attr( date_i18n( 'M d, Y', strtotime( $woocommerce_multilevel_referral_row['date'] ) ) ) . '</td>
							<td>' . esc_html( $woocommerce_multilevel_referral_note ) . '</td>
						</tr>';
		}
		?>
	</table>
		<?php
	}
	?>
</div>
