/**
 * WooCommerce Multilevel Referral Admin JavaScript
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

jQuery( document ).ready(
	function () {
		var currentUserTotalReferral = 0;
		var currentListUser;
		var refCount   = 0;
		var arrUserIds = new Array();
		jQuery( 'input[name="search_by_join_sdate"]' ).mask( '0000-00-00' );
		jQuery( 'input[name="search_by_join_edate"]' ).mask( '0000-00-00' );
		jQuery( 'input[name="search_start_date"]' ).mask( '0000-00-00' );
		jQuery( 'input[name="search_end_date"]' ).mask( '0000-00-00' );
		jQuery( '.toplevel_page_wc_referral .woocommerce_multilevel_referral_referrals_count' ).each(
			function (i) {
				var element  = jQuery( this );
				var userId   = element.data( 'id' );
				var userName = element.data( 'name' );
				// fnSendAjaxRequest($element,'getReferralsDetails',$userId);.

				jQuery.get(
					'?getReferralsCount=' + userId,
					function (count) {
						if (isNaN( count )) {
								count = '<font color="red" ><strong>?</strong></font>';
						}
						element.html( count );
						if ( ! isNaN( count ) && count > 0) {
							jQuery( '#user-' + userId + ' .woocommerce_multilevel_referral_view' ).html( '<a href="#" data-name="' + userName + '" class="view_hierarchie" data-total="' + count + '" data-id="' + userId + '">View hierarchy</a>' );
						} else {
							jQuery( '#user-' + userId + ' .woocommerce_multilevel_referral_view' ).html( '' );
						}

					}
				);
			}
		);

		if ( jQuery( '#dropdown_referral_code' ).length ) {
			jQuery( '#dropdown_referral_code' ).select2(
				{
					allowClear: true
				}
			);
		}

		jQuery( '.woocommerce-multilevel-referral-email-template a.mdl-tabs__tab' ).click(
			function (e) {
				e.preventDefault();
				jQuery( '.mdl-tabs__panel, .mdl-tabs__tab' ).removeClass( 'is-active' );
				jQuery( this ).addClass( 'is-active' );
				jQuery( jQuery( this ).attr( 'href' ) ).addClass( 'is-active' );
			}
		);
		jQuery( '#woocommerce-multilevel-referral-customer-based-bonus' ).click(
			function (e) {
				jQuery( '.bonus_for_all' ).toggleClass( 'woocommerce-multilevel-referral-hide' );
				jQuery( '.bonus_for_customer' ).toggleClass( 'woocommerce-multilevel-referral-hide' );
			}
		);
		// fnMainElements();.
		function fnMainElements($index){
			$total = jQuery( '.toplevel_page_wc_referral .woocommerce_multilevel_referral_referrals_count' ).length;
			if ($index == '' || typeof $index === "undefined") {
				$index = 0;
			}
			if ($index >= 0 && $index < $total) {
				$element = jQuery( '.toplevel_page_wc_referral .woocommerce_multilevel_referral_referrals_count' ).eq( $index );
				console.log( 'fnMainElements=>' + $element.data( 'id' ) );
				fnNewAjaxCaller( $element,$element.data( 'id' ),++$index );
				// fnMainElements(++$index);.
			}
		}
		function fnNewAjaxCaller(element,userId,index){
			jQuery.ajax(
				{
					url: "?t=" + Math.random(),
					type: 'POST',
					data: {
						action: 'getReferralsDetails', userId: userId
					},
					success: function (response) {
						var arrObj = JSON.parse( response );
						if (arrObj.result == 'success') {
							if (arrObj.data != 0) {
								var count = jQuery( arrObj.data ).length;
								refCount += count;
								if (count > 0) {
									jQuery.each(
										arrObj.data,
										function (key,valueObj) {
											if (jQuery.inArray( valueObj.user_id,arrUserIds ) < 0) {
												arrUserIds.push( valueObj.user_id );
											}
										}
									);
								}
							}
						}
						/*console.log("fnNewAjaxCaller="+userId);
						console.log(arrUserIds); */
						if (arrUserIds.length > 0) {
							fnNewAjaxCaller( element, arrUserIds.pop(),index );
						} else {
							element.html( refCount );
							parentId = element.data( 'id' );
							if ( ! isNaN( refCount ) && refCount > 0) {
								jQuery( '#user-' + parentId + ' .woocommerce_multilevel_referral_view' ).html( '<a href="#" data-name="' + element.data( 'name' ) + '" class="view_hierarchie" data-total="' + refCount + '" data-id="' + parentId + '">View hierarchy</a>' );
							} else {
								jQuery( '#user-' + parentId + ' .woocommerce_multilevel_referral_view' ).html( '' );
							}
							refCount = 0;
							fnMainElements( index );
						}
					},
					error: function (error) {

					},
				}
			);

		}
		jQuery( '.woocommerce_multilevel_referral_view' ).on(
			'click',
			'a.view_hierarchie',
			function (e) {
				e.preventDefault();
				currentListUser = jQuery( this );
				var nonce       = woocommerce_multilevel_referral_ajax_admin.nonce;
				jQuery.get(
					'?load_referral_user_list=' + jQuery( this ).data( 'id' ) + '&_wpnonce=' + nonce,
					function (data) {
						jQuery( '#dialog_referral_user' ).html( data );
						currentUserTotalReferral = jQuery( currentListUser ).data( 'total' );
						currentUserName          = jQuery( currentListUser ).data( 'name' );
						jQuery( '#dialog_referral_user' ).dialog(
							{
								title: 'List of ' + currentUserName + ' Referrals (' + currentUserTotalReferral + ')',
								modal: true,
								resizable: false,
								width: 350,
								height: 400,
								closeText: "",
								open: function ( event, ui ) {
									jQuery( '#referral_user_form .wp-list-table' ).css( 'width','calc(100% - 350px)' );
									jQuery( 'body.toplevel_page_wc_referral .ui-dialog' ).css( 'top', jQuery( currentListUser ).position().top );
								},
								close: function () {
									jQuery( '#referral_user_form .wp-list-table' ).css( 'width','100%' );
								}
							}
						);
					}
				);
				return false;
			}
		);
		jQuery( '#dialog_referral_user' ).on(
			'click',
			'.get_referral_user',
			function (e) {
				e.preventDefault();
				jQuery( '#dialog_referral_user' ).addClass( 'loading' );
				currentListObj = jQuery( this ).parent( 'div' ).parent( 'li' );
				var nonce       = woocommerce_multilevel_referral_ajax_admin.nonce;
				fetchRecords   = jQuery( currentListObj ).data( 'get' );
				if (fetchRecords) {
					jQuery( '#dialog_referral_user' ).removeClass( 'loading' );
					jQuery( currentListObj ).find( 'ul' ).first().toggle( 'slow' );
				} else {
					jQuery.get(
						'?load_referral_user_list=' + jQuery( currentListObj ).data( 'id' ) + '&_wpnonce=' + nonce,
						function (data) {
							jQuery( currentListObj ).append( data );
							jQuery( currentListObj ).data( 'get', 1 );
							jQuery( '#dialog_referral_user' ).removeClass( 'loading' );
						}
					);
				}
				return false;
			}
		);
		jQuery( '.active_referral_user' ).click(
			function (e) {
				e.preventDefault();
				currentListObj = jQuery( this ).parent( 'div' ).parent( 'li' );
				if (confirm( 'Are sure want to active this user?' )) {
					jQuery( '.wrap' ).addClass( 'loading' );
					jQuery.get(
						'?active_referral_user=' + jQuery( this ).data( 'id' ),
						function (data) {
							window.location.href = data;
						}
					)
				}
			}
		);
		jQuery( '#dialog_referral_user' ).on(
			'click',
			'.remove_referral_user',
			function (e) {
				e.preventDefault();
				if (confirm( 'Are sure want to remove?' )) {

					currentListObj = jQuery( this ).parent( 'div' ).parent( 'li' );

					jQuery( '#dialog_referral_user' ).addClass( 'loading' );

					jQuery.get(
						'?remove_referral_user=' + jQuery( currentListObj ).data( 'id' ),
						function (data) {
							currentUserName = jQuery( currentListUser ).data( 'name' );
							if (jQuery( currentListObj ).parents( 'li' ).size() > 0) {
								currentListObj = jQuery( currentListObj ).parents( 'li' ).first();
								jQuery( currentListObj ).find( 'ul' ).remove()
								jQuery( currentListObj ).append( data );

								count = parseInt( jQuery( currentListObj ).find( '.count' ).first().html() );
								if (count > 0) {
									jQuery( currentListObj ).find( '.count' ).first().html( count - 1 );
								}

								jQuery( currentListObj ).parents( 'li' ).each(
									function () {
										count = parseInt( jQuery( this ).find( '.count' ).first().html() );
										if (count > 0) {
											jQuery( this ).find( '.count' ).first().html( count - 1 );
										}
									}
								);
							} else {
								jQuery( '#dialog_referral_user' ).html( data );
							}
							if ( currentUserTotalReferral > 0 ) {
								currentUserTotalReferral = currentUserTotalReferral - 1
								jQuery( '.ui-dialog-title' ).html( 'List of ' + currentUserName + ' Referrals (' + currentUserTotalReferral + ')' );
								jQuery( currentListUser ).parents( 'tr' ).find( 'td.no_of_followers' ).html( currentUserTotalReferral );
							}

							jQuery( '#dialog_referral_user' ).removeClass( 'loading' );
						}
					);
				}
				return false;
			}
		);
		jQuery( '#referral_user_form #reset_button' ).click(
			function () {
				jQuery( '#referral_user_form input[type=text]' ).val( '' );
				jQuery( '#referral_user_form' ).submit();
			}
		);
		jQuery( '#form_widthdraw_filter #reset_button_withdraw' ).click(
			function () {
				jQuery( '#form_widthdraw_filter input[type=text]' ).val( '' );
				jQuery( '#form_widthdraw_filter' ).submit();
			}
		);
		jQuery( 'input[type=radio][name=woocommerce-multilevel-referral-levelbase-credit]' ).change(
			function () {
				var offertype = jQuery( this ).parents( 'table' ).find( 'tr.woocommerce-multilevel-referral-optional-bouns select[name="woocommerce_multilevel_referral_bouns_offere_type"] option:selected' ).val();
				if (this.value == 1) {

					if ( offertype == 'woocommerce_multilevel_referral_order' ) {
						jQuery( '.woocommerce-multilevel-referral-optional' ).removeAttr( 'style' );
						jQuery( '.woocommerce-multilevel-referral-optional' ).addClass( 'woocommerce-multilevel-referral-hide' );
						jQuery( '.woocommerce-multilevel-referral-optional-bouns' ).show();
						jQuery( '.woocommerce-multilevel-referral-user-order-main' ).removeClass( 'woocommerce-multilevel-referral-hide' );
					} else if ( offertype == 'woocommerce_multilevel_referral_user' ) {
						jQuery( '.woocommerce-multilevel-referral-optional' ).removeClass( 'woocommerce-multilevel-referral-hide' );
						jQuery( '.woocommerce-multilevel-referral-optional-bouns' ).show();
						jQuery( '.woocommerce-multilevel-referral-user-order-main' ).addClass( 'woocommerce-multilevel-referral-hide' );
					} else {
						jQuery( '.woocommerce-multilevel-referral-optional-bouns' ).hide();
						jQuery( '.woocommerce-multilevel-referral-user-order-main' ).addClass( 'woocommerce-multilevel-referral-hide' );
						jQuery( '.woocommerce-multilevel-referral-optional' ).removeClass( 'woocommerce-multilevel-referral-hide' );
					}

					$totalLevels = jQuery( 'table.woocommerce-multilevel-referral-level-table .woocommerce-multilevel-referral-level' ).length;
					if (($totalLevels) > 1) {
						jQuery( '.woocommerce-multilevel-referral-buttons #woocommerce-multilevel-referral-delete-last' ).show();
					} else {
						jQuery( '.woocommerce-multilevel-referral-buttons #woocommerce-multilevel-referral-delete-last' ).hide();
					}
				} else {
					jQuery( '.woocommerce-multilevel-referral-optional-bouns' ).hide();
					jQuery( '.woocommerce-multilevel-referral-optional' ).addClass( 'woocommerce-multilevel-referral-hide' );
					jQuery( '.woocommerce-multilevel-referral-user-order-main' ).addClass( 'woocommerce-multilevel-referral-hide' );
					jQuery( '.woocommerce-multilevel-referral-buttons #woocommerce-multilevel-referral-delete-last' ).hide();
				}
			}
		);
		jQuery( '#woocommerce-multilevel-referral-add-more' ).click(
			function () {
				$totalLevels = jQuery( 'table.woocommerce-multilevel-referral-level-table .woocommerce-multilevel-referral-level' ).length;
				$row         = jQuery( 'table.woocommerce-multilevel-referral-level-table .woocommerce-multilevel-referral-level[data-level=1]' ).clone();
				$row.attr( 'data-level',$totalLevels + 1 );
				$row.find( 'label' ).attr( 'for','woocommerce-multilevel-referral-level-' + ($totalLevels + 1) );
				$row.find( 'input[type=number]' ).attr( 'id','woocommerce-multilevel-referral-level-' + ($totalLevels + 1) ).val( 0 );
				$row.find( 'label span' ).html( $totalLevels + 1 );
				jQuery( 'table.woocommerce-multilevel-referral-level-table > tbody' ).append( $row );
				jQuery( '#woocommerce-multilevel-referral-max-level' ).val( $totalLevels + 1 );
				if (($totalLevels + 1) > 1) {
					jQuery( '.woocommerce-multilevel-referral-buttons #woocommerce-multilevel-referral-delete-last' ).show();
				} else {
					jQuery( '.woocommerce-multilevel-referral-buttons #woocommerce-multilevel-referral-delete-last' ).hide();
				}
			}
		);
		jQuery( '.woocommerce-multilevel-referral-buttons #woocommerce-multilevel-referral-delete-last' ).click(
			function () {
				$totalLevels = jQuery( 'table .woocommerce-multilevel-referral-level' ).length;
				if ($totalLevels > 1) {
					jQuery( 'table.woocommerce-multilevel-referral-level-table tr:last' ).remove();
					$totalLevels--;
					jQuery( '#woocommerce-multilevel-referral-max-level' ).val( $totalLevels );
				}
				if ($totalLevels < 2) {
					jQuery( '.woocommerce-multilevel-referral-buttons #woocommerce-multilevel-referral-delete-last' ).hide();
				}
			}
		);
		function scrollHorizontally(e) {
			e         = window.event || e;
			var delta = Math.max( -1, Math.min( 1, (e.wheelDelta || -e.detail) ) );
			document.getElementById( 'woocommerce_multilevel_referral_header_tabs' ).scrollLeft -= (delta * 40); // Multiplied by 40.
			e.preventDefault();
		}
		if ( jQuery( '#woocommerce_multilevel_referral_header_tabs' ).length ) {
			if (document.getElementById( 'woocommerce_multilevel_referral_header_tabs' ).addEventListener) {
				// IE9, Chrome, Safari, Opera.
				document.getElementById( 'woocommerce_multilevel_referral_header_tabs' ).addEventListener( "mousewheel", scrollHorizontally, false );
				// Firefox.
				document.getElementById( 'woocommerce_multilevel_referral_header_tabs' ).addEventListener( "DOMMouseScroll", scrollHorizontally, false );
			} else {
				// IE 6/7/8.
				document.getElementById( 'woocommerce_multilevel_referral_header_tabs' ).attachEvent( "onmousewheel", scrollHorizontally );
			}
		}
		jQuery( '#woocommerce_multilevel_referral_auto_register' ).change(
			function () {
				woocommerce_multilevel_referral_manage_referral_code_field();
			}
		);
		woocommerce_multilevel_referral_manage_referral_code_field();
	}
);


function woocommerce_multilevel_referral_manage_referral_code_field(){
	if ( jQuery( '#woocommerce_multilevel_referral_auto_register' ).val() == 'yes' ) {
		jQuery( '#woocommerce_multilevel_referral_required_referral' ).parents( 'tr' ).show();
	} else {
		jQuery( '#woocommerce_multilevel_referral_required_referral' ).parents( 'tr' ).hide();
	}
}
jQuery( document ).ready(
	function () {
		jQuery( '[name="woocommerce_multilevel_referral_bouns_offere_type"] option:selected' ).each(
			function ( index, select ) {
				var offertype = jQuery( select ).val();
				if (offertype == 'woocommerce_multilevel_referral_user') {
					jQuery( '.woocommerce-multilevel-referral-optional' ).removeClass( 'woocommerce-multilevel-referral-hide' );
					jQuery( '.woocommerce-multilevel-referral-user-order-main' ).addClass( 'woocommerce-multilevel-referral-hide' );
				} else if ( offertype == 'woocommerce_multilevel_referral_order') {
					jQuery( '.woocommerce-multilevel-referral-optional' ).addClass( 'woocommerce-multilevel-referral-hide' );
					jQuery( '.woocommerce-multilevel-referral-user-order-main' ).removeClass( 'woocommerce-multilevel-referral-hide' );
				} else {
					jQuery( '.woocommerce-multilevel-referral-optional' ).removeClass( 'woocommerce-multilevel-referral-hide' );
					jQuery( '.woocommerce-multilevel-referral-user-order-main' ).addClass( 'woocommerce-multilevel-referral-hide' );
				}
			}
		);
	}
);

var a_num = ['','First ','Second ','Third ','Fourth ', 'Fifth ','Sixth ','Seventh ','Eighth ','Ninth ','Tenth ','Eleventh ','Twelfth ','Thirteenth ','Fourteenth ','Fifteenth ','Sixteenth ','Seventeenth ','Eighteenth ','Nineteenth '];
var b_num = ['', '', 'Twentieth','Thirtieth','Fortieth','Fiftieth', 'Sixtieth','Seventieth','Eightieth','Ninetieth'];
function woocommerceMultilevelReferralInWords(num) {
	if ((num = num.toString()).length > 9) {
		return 'overflow';
	}
	n = ('000000000' + num).substr( -9 ).match( /^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/ );
	if ( ! n) {
		return;
	} var str = '';
	str      += (n[1] != 0) ? (a_num[Number( n[1] )] || b_num[n[1][0]] + ' ' + a_num[n[1][1]]) + 'crore ' : '';
	str      += (n[2] != 0) ? (a_num[Number( n[2] )] || b_num[n[2][0]] + ' ' + a_num[n[2][1]]) + 'lakh ' : '';
	str      += (n[3] != 0) ? (a_num[Number( n[3] )] || b_num[n[3][0]] + ' ' + a_num[n[3][1]]) + 'thousand ' : '';
	str      += (n[4] != 0) ? (a_num[Number( n[4] )] || b_num[n[4][0]] + ' ' + a_num[n[4][1]]) + 'hundred ' : '';
	str      += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a_num[Number( n[5] )] || b_num[n[5][0]] + ' ' + a_num[n[5][1]]) + '' : '';
	return str;
}

jQuery( '.woocommerce-multilevel-referral-buttons' ).on(
	'click',
	'#woocommerce-multilevel-referral-order-add-more',
	function () {
		var attrid = jQuery( this ).parents( 'div.woocommerce-multilevel-referral-user-order-wrap' ).find( 'table tbody tr:last' ).attr( 'data-order_level' );
		var id     = parseInt( attrid ) + parseInt( 1 );
		jQuery( this ).parents( 'div.woocommerce-multilevel-referral-user-order-wrap' ).find( 'table tbody' ).append( '<tr valign="top" data-order_level="' + id + '" class="woocommerce-multilevel-referral-optional woocommerce-multilevel-referral-level "><th scope="row" class="titledesc"><label for="woocommerce-multilevel-referral-level-' + id + '">Order <span>' + id + '</span></label></th><td class="forminp"><input type="number" max="10000" step="0.01" min="0" name="woocommerce_multilevel_referral_order_level_credit[]" id="woocommerce-multilevel-referral-level-' + id + '" class="form-field" value="0"><span class="woocommerce-multilevel-referral-hide"> % </span></td></tr>' );
	}
);

jQuery( '.woocommerce-multilevel-referral-buttons' ).on(
	'click',
	'#woocommerce-multilevel-referral-order-delete-last',
	function () {
		var rowlength = jQuery( this ).parents( 'div.woocommerce-multilevel-referral-user-order-wrap' ).find( 'table tbody tr' ).length;
		if (rowlength == 1) {
			return false;
		} else {
			jQuery( this ).parents( 'div.woocommerce-multilevel-referral-user-order-wrap' ).find( 'table tbody tr:last' ).remove();
		}
	}
);

jQuery( 'body' ).on(
	'change',
	'[name="woocommerce_multilevel_referral_bouns_offere_type"]',
	function () {
		var offertype = jQuery( this ).val();
		if (offertype == 'woocommerce_multilevel_referral_user') {
			jQuery( '.woocommerce-multilevel-referral-optional' ).removeClass( 'woocommerce-multilevel-referral-hide' );
			jQuery( '.woocommerce-multilevel-referral-user-order-main' ).addClass( 'woocommerce-multilevel-referral-hide' );
		} else if ( offertype == 'woocommerce_multilevel_referral_order') {
			jQuery( '.woocommerce-multilevel-referral-optional' ).addClass( 'woocommerce-multilevel-referral-hide' );
			jQuery( '.woocommerce-multilevel-referral-user-order-main' ).removeClass( 'woocommerce-multilevel-referral-hide' );
		} else {
			jQuery( '.woocommerce-multilevel-referral-optional' ).removeClass( 'woocommerce-multilevel-referral-hide' );
			jQuery( '.woocommerce-multilevel-referral-user-order-main' ).addClass( 'woocommerce-multilevel-referral-hide' );
		}
	}
);