<?php
/**
 * Plugin Functions
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'woocommerce_multilevel_referral_remove_wc_currency_symbols' ) ) {
	/**
	 * Remove WooCommerce currency symbols.
	 *
	 * @return string Empty string.
	 */
	function woocommerce_multilevel_referral_remove_wc_currency_symbols() {
		return '';
	}
}
/**
 * Get query vars from URL.
 *
 * @return array Query vars.
 */
function woocommerce_multilevel_referral_get_query_vars() {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$query_string = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '';
	parse_str( $query_string, $qryvars );
	return $qryvars;
}
if ( ! function_exists( 'woocommerce_multilevel_referral_get_wp_fs_allow_html' ) ) {
	/**
	 * Get allowed HTML tags for wp_kses.
	 *
	 * @param array|null $tags Specific tags to return.
	 *
	 * @return array Allowed HTML tags.
	 */
	function woocommerce_multilevel_referral_get_wp_fs_allow_html( $tags = null ) {
		$allowed_html = array(
			'form'     => array(
				'action'  => array(),
				'method'  => array(),
				'enctype' => array(),
				'class'   => array(),
				'id'      => array(),
				'name'    => array(),
				'target'  => array(),
			),
			'input'    => array(
				'type'             => array(),
				'name'             => array(),
				'value'            => array(),
				'placeholder'      => array(),
				'id'               => array(),
				'class'            => array(),
				'checked'          => array(),
				'disabled'         => array(),
				'readonly'         => array(),
				'maxlength'        => array(),
				'min'              => array(),
				'max'              => array(),
				'step'             => array(),
				'autocomplete'     => array(),
				'size'             => array(),
				'aria-describedby' => array(),
			),
			'textarea' => array(
				'name'         => array(),
				'cols'         => array(),
				'rows'         => array(),
				'id'           => array(),
				'class'        => array(),
				'placeholder'  => array(),
				'readonly'     => array(),
				'disabled'     => array(),
				'autocomplete' => array(),
				'style'        => array(),
			),
			'select'   => array(
				'name'        => array(),
				'id'          => array(),
				'class'       => array(),
				'multiple'    => array(),
				'required'    => array(),
				'disabled'    => array(),
				'data-url'    => array(),
				'data_url'    => array(),
				'data-loader' => array(),
			),
			'option'   => array(
				'value'         => array(),
				'selected'      => array(),
				'data-code'     => array(),
				'data-url'      => array(),
				'data-attachid' => array(),
				'data-title'    => array(),
				'data-desc'     => array(),
				'data-image'    => array(),
			),
			'button'   => array(
				'style'             => array(),
				'type'              => array(),
				'name'              => array(),
				'value'             => array(),
				'id'                => array(),
				'class'             => array(),
				'aria-pressed'      => array(),
				'data-wp-editor-id' => array(),
				'data-editor'       => array(),
				'data-page'         => array(),
			),
			'label'    => array(
				'for'   => array(),
				'class' => array(),
			),
			'fieldset' => array(
				'class' => array(),
				'id'    => array(),
			),
			'legend'   => array(),
			'br'       => array(
				'class' => array(),
			),
			'strong'   => array(),
			'small'    => array(),
			'em'       => array(),
			'ul'       => array(),
			'ol'       => array(),
			'li'       => array(
				'class'   => array(),
				'data-id' => array(),
			),
			'h1'       => array( 'class' => array() ),
			'h2'       => array( 'class' => array() ),
			'h3'       => array( 'class' => array() ),
			'h4'       => array( 'class' => array() ),
			'h5'       => array( 'class' => array() ),
			'h6'       => array( 'class' => array() ),
			'code'     => array(),
			'pre'      => array(),
			'i'        => array( 'class' => array() ),
			'div'      => array(
				'class'            => array(),
				'id'               => array(),
				'style'            => array(),
				'data-name'        => array(),
				'data-id'          => array(),
				'data-url'         => array(),
				'data-title'       => array(),
				'data-image'       => array(),
				'data-description' => array(),
			),
			'span'     => array(
				'class'       => array(),
				'id'          => array(),
				'style'       => array(),
				'aria-hidden' => array(),
			),
			'p'        => array(
				'class' => array(),
				'id'    => array(),
				'style' => array(),
			),
			'bdi'      => array(),
			'a'        => array(
				'href'             => array(),
				'title'            => array(),
				'target'           => array(),
				'class'            => array(),
				'rel'              => array(),
				'data-content'     => array(),
				'data-account'     => array(),
				'data-ru'          => array(),
				'data-share'       => array(),
				'data-count'       => array(),
				'data-url'         => array(),
				'data-title'       => array(),
				'data-image'       => array(),
				'data-description' => array(),
				'data-finder'      => array(),
				'data-page'        => array(),
			),
			'img'      => array(
				'src'    => array(),
				'alt'    => array(),
				'width'  => array(),
				'height' => array(),
				'style'  => array(),
				'class'  => array(),
			),
			'table'    => array(
				'class'       => array(),
				'id'          => array(),
				'style'       => array(),
				'border'      => array(),
				'cellpadding' => array(),
				'cellspacing' => array(),
			),
			'thead'    => array(
				'class' => array(),
				'id'    => array(),
			),
			'tbody'    => array(
				'class'         => array(),
				'id'            => array(),
				'data-wp-lists' => array(),
			),
			'tfoot'    => array(
				'class' => array(),
				'id'    => array(),
			),
			'tr'       => array(
				'class'            => array(),
				'id'               => array(),
				'style'            => array(),
				'valign'           => array(),
				'data-level'       => array(),
				'data-order_level' => array(),
			),
			'th'       => array(
				'class'   => array(),
				'id'      => array(),
				'scope'   => array(),
				'colspan' => array(),
				'rowspan' => array(),
				'style'   => array(),
				'align'   => array(),
			),
			'td'       => array(
				'class'        => array(),
				'data-colname' => array(),
				'id'           => array(),
				'colspan'      => array(),
				'rowspan'      => array(),
				'style'        => array(),
				'align'        => array(),
				'data-title'   => array(),
			),
			'link'     => array(
				'rel'   => array(),
				'href'  => array(),
				'id'    => array(),
				'media' => array(),
			),
			'style'    => array(),
			'abbr'     => array(
				'title' => array(),
			),
			'acronym'  => array(
				'title' => array(),
			),
		);
		// If specific tags are requested, filter the array.
		if ( is_array( $tags ) && ! empty( $tags ) ) {
			return array_intersect_key( $allowed_html, array_flip( $tags ) );
		}
		// If no specific tags, return the full array.
		return $allowed_html;
	}
}
