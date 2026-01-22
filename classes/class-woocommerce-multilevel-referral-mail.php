<?php
/**
 * Referral Mail Class
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Mail' ) ) {
	/**
	 * Mail controller class
	 */
	class WooCommerce_Multilevel_Referral_Mail extends WC_Email {
		/**
		 * Email address.
		 *
		 * @var string
		 */
		public $email;
		/**
		 * User ID.
		 *
		 * @var int
		 */
		public $user_id;
		/**
		 * First name.
		 *
		 * @var string
		 */
		public $first_name;
		/**
		 * Last name.
		 *
		 * @var string
		 */
		public $last_name;
		/**
		 * Referral code.
		 *
		 * @var string
		 */
		public $referral_code;
		/**
		 * Template name.
		 *
		 * @var string
		 */
		public $template;
		/**
		 * Available credits.
		 *
		 * @var string
		 */
		public $available_credits;
		/**
		 * Expire date.
		 *
		 * @var string
		 */
		public $expire_date;
		/**
		 * Validity period.
		 *
		 * @var string
		 */
		public $validity_period;
		/**
		 * Today date.
		 *
		 * @var string
		 */
		public $today_date;
		/**
		 * Expire month.
		 *
		 * @var string
		 */
		public $expire_month;
		/**
		 * Expire credits.
		 *
		 * @var string
		 */
		public $expire_credits;
		/**
		 * Constructor.
		 */
		public function __construct() {
			// set ID, this simply needs to be a unique name.
			$this->id = 'wc_referral_program';
			// this is the title in WooCommerce Email settings.
			$this->title = 'Referral Program';
			// this is the description in WooCommerce email settings.
			$this->description = 'Sent email notification on joining Referral Program.';
			// these are the default heading and subject lines that can be overridden using the settings.
			$this->heading = 'Referral Program Team';
			$this->subject = 'Referral Program Team';
			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();
		}
		/**
		 * Trigger email.
		 *
		 * @param string $email Email address.
		 * @param string $first_name First name.
		 * @param string $last_name Last name.
		 * @param string $referral_code Referral code.
		 * @param string $template Template name.
		 * @param int    $user_id User ID.
		 */
		public function trigger( $email, $first_name, $last_name, $referral_code, $template, $user_id ) {
			$this->recipient     = $email;
			$this->user_id       = $user_id;
			$this->first_name    = ucfirst( $first_name );
			$this->last_name     = ucfirst( $last_name );
			$this->template      = $template;
			$this->referral_code = $referral_code;
			if ( ! $this->get_recipient() ) {
				return;
			}
			// woohoo, send the email!
			$this->send( $email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		/**
		 * Send reminder email.
		 *
		 * @param string $email Email address.
		 * @param string $first_name First name.
		 * @param string $last_name Last name.
		 * @param string $available_credits Available credits.
		 * @param string $expire_date Expire date.
		 * @param string $validity_period Validity period.
		 * @param string $today_date Today date.
		 * @param string $expire_month Expire month.
		 * @param string $expire_credits Expire credits.
		 */
		public function reminder( $email, $first_name, $last_name, $available_credits, $expire_date, $validity_period, $today_date, $expire_month, $expire_credits ) {
			$this->available_credits = $available_credits;
			$this->expire_date       = $expire_date;
			$this->validity_period   = $validity_period;
			$this->today_date        = $today_date;
			$this->expire_month      = $expire_month;
			$this->expire_credits    = $expire_credits;
			$this->recipient         = $email;
			$this->first_name        = ucfirst( $first_name );
			$this->last_name         = ucfirst( $last_name );
			$this->template          = 'expire_notification';
			if ( ! $this->get_recipient() ) {
				return;
			}
			// woohoo, send the email!
			$this->send( $email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		/**
		 * Get HTML content.
		 *
		 * @return string HTML content.
		 */
		public function get_content_html() {
			ob_start();
			$email_heading = $this->get_heading();
			$email         = $this;
			do_action( 'woocommerce_email_header', $email_heading, $email );
			echo wp_kses( $this->get_template_content( $this->template ), woocommerce_multilevel_referral_get_wp_fs_allow_html() );
			do_action( 'woocommerce_email_footer', $email );
			return ob_get_clean();
		}
		/**
		 * Get email heading.
		 *
		 * @return string Email heading.
		 */
		public function get_heading() {
			global $woocommerce_multilevel_referral_customer_id;
			$woocommerce_multilevel_referral_customer_id = $this->user_id;
			$arg          = array(
				'{first_name}',
				'{last_name}',
				'{referral_code}',
				'{available_credits}',
				'{expire_date}',
				'{validity_period}',
				'{today_date}',
				'{expire_month}',
				'{expire_credits}',
			);
			$replace_with = array(
				$this->first_name,
				$this->last_name,
				$this->referral_code,
				$this->available_credits,
				$this->expire_date,
				$this->validity_period,
				$this->today_date,
				$this->expire_month,
				$this->expire_credits,
			);
			return do_shortcode( stripslashes( str_replace( $arg, $replace_with, get_option( $this->template . '_heading', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) ) ) );
		}
		/**
		 * Get email subject.
		 *
		 * @return string Email subject.
		 */
		public function get_subject() {
			global $woocommerce_multilevel_referral_customer_id;
			$woocommerce_multilevel_referral_customer_id = $this->user_id;
			$arg          = array(
				'{first_name}',
				'{last_name}',
				'{referral_code}',
				'{available_credits}',
				'{expire_date}',
				'{validity_period}',
				'{today_date}',
				'{expire_month}',
				'{expire_credits}',
			);
			$replace_with = array(
				$this->first_name,
				$this->last_name,
				$this->referral_code,
				$this->available_credits,
				$this->expire_date,
				$this->validity_period,
				$this->today_date,
				$this->expire_month,
				$this->expire_credits,
			);
			return do_shortcode( stripslashes( str_replace( $arg, $replace_with, get_option( $this->template . '_subject', __( 'Referral Program Team', 'multilevel-referral-plugin-for-woocommerce' ) ) ) ) );
		}
		/**
		 * Get template content.
		 *
		 * @param string $template Template name.
		 *
		 * @return string Template content.
		 */
		public function get_template_content( $template ) {
			global $woocommerce_multilevel_referral_customer_id;
			$woocommerce_multilevel_referral_customer_id = $this->user_id;
			$arg          = array(
				'{first_name}',
				'{last_name}',
				'{referral_code}',
				'{available_credits}',
				'{expire_date}',
				'{validity_period}',
				'{today_date}',
				'{expire_month}',
				'{expire_credits}',
			);
			$replace_with = array(
				$this->first_name,
				$this->last_name,
				$this->referral_code,
				$this->available_credits,
				$this->expire_date,
				$this->validity_period,
				$this->today_date,
				$this->expire_month,
				$this->expire_credits,
			);
			return wpautop( do_shortcode( stripslashes( str_replace( $arg, $replace_with, get_option( $template . '_template', '' ) ) ) ) );
		}
		/**
		 * Get plain text content.
		 *
		 * @return string Plain text content.
		 */
		public function get_content_plain() {
			ob_start();
			$email_heading = $this->get_heading();
			$email         = $this->get_recipient();
			echo esc_html( $this->get_template_content( $this->template ) );
			return ob_get_clean();
		}
		/**
		 * Initialize form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => 'Enable/Disable',
					'type'    => 'checkbox',
					'label'   => 'Enable this email notification',
					'default' => 'yes',
				),
				'subject'    => array(
					'title'       => 'Subject',
					'type'        => 'text',
					'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
					'placeholder' => '',
					'default'     => '',
				),
				'heading'    => array(
					'title'       => 'Email Heading',
					'type'        => 'text',
					// translators: %s is the heading.
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'multilevel-referral-plugin-for-woocommerce' ), $this->heading ),
					'placeholder' => '',
					'default'     => '',
				),
				'email_type' => array(
					'title'       => 'Email type',
					'type'        => 'select',
					'description' => 'Choose which format of email to send.',
					'default'     => 'html',
					'class'       => 'email_type',
					'options'     => array(
						'plain'     => __( 'Plain text', 'multilevel-referral-plugin-for-woocommerce' ),
						'html'      => __( 'HTML', 'multilevel-referral-plugin-for-woocommerce' ),
						'multipart' => __( 'Multipart', 'multilevel-referral-plugin-for-woocommerce' ),
					),
				),
			);
		}
	} // end WooCommerce_Multilevel_Referral_Mail
}
