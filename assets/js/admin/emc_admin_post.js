jQuery( function ( $ ) {
	
	/**
	 * Order Shipping Panel
	 */
	var wc_meta_boxes_order = {
				
		init: function() {
			$( 'a.edit_dimensions' ).click( this.edit_dimensions );
			$( 'a.edit_shipment_info' ).click( this.edit_shipment_info );
			$( '#_insurance' ).click( this.edit_insurance );
			this.edit_insurance();
			$( 'a.edit_item_international' ).click( this.edit_proforma );
		},
		
		edit_dimensions: function( e ) {
			e.preventDefault();
			$( this ).hide();
			$( this ).closest( '.order_shipping_column' ).find( 'div.dimensions' ).hide();
			$( this ).closest( '.order_shipping_column' ).find( 'div.edit_dimensions' ).show();
		},
		
		edit_shipment_info: function( e ) {
			e.preventDefault();
			$( this ).hide();
			$( this ).closest( '.order_shipping_column' ).find( 'div.shipment_info' ).hide();
			$( this ).closest( '.order_shipping_column' ).find( 'div.edit_shipment_info' ).show();
		},
		
		edit_proforma: function( e ) {
			e.preventDefault();
			id = $( this ).closest( 'div.proforma' ).attr('data-item');
			$( this ).hide();
			$( this ).closest( '.item' ).find( 'div.proforma_'+id ).hide();
			$( this ).closest( '.item' ).find( 'div.edit_proforma_'+id ).css({display: 'inline-block'});
		},
		
		edit_insurance: function() {
			if( $( '#_insurance' ).attr( "checked" ) == "checked" ) {
				$( '.edit_insurance' ).show();
				
				var current_rate = parseFloat($( '#current_rate' ).html());
				var insurance_rate = parseFloat($( '#insurance_rate_unformatted' ).html());		
				var sum = (current_rate + insurance_rate).toFixed(2);

				$.post(ajaxurl, { action: 'format_price', security: ajax_nonce, value: sum, tax:"notax" }, function (html) {
					$( "#display_rate" ).html( html );
				});
			}
			else{
				$( '.edit_insurance' ).hide();
				
				var current_rate = parseFloat($( '#current_rate' ).html());
				
				$.post(ajaxurl, { action: 'format_price', security: ajax_nonce, value: current_rate, tax:"notax" }, function (html) {
					$( "#display_rate" ).html( html );
				});
			}
		},
		
	}
	
	wc_meta_boxes_order.init();

});

function delay_do_label_request() {
	setTimeout("do_label_request()",10000);
}

function do_label_request() {

	if ( !jQuery("#emc_documents").hasClass("labels_available") ){
		// Call server for documents
		jQuery.post(ajaxurl, { action: 'check_labels', security: ajax_nonce, order_id: jQuery("#post_ID").val() }, function(data) {
			if(data) {
				if ( typeof data.label_url != 'undefined' ) {
					jQuery(".label_url a").attr("href", data.label_url );
					jQuery(".label_url").show();
				}
				if ( typeof data.remise != 'undefined' ) {
					jQuery(".remise a").attr("href", data.remise );
					jQuery(".remise").show();						
				}
				if ( typeof data.manifest != 'undefined' ) {
					jQuery(".manifest a").attr("href", data.manifest );
					jQuery(".manifest").show();						
				}
				if ( typeof data.connote != 'undefined' ) {
					jQuery(".connote a").attr("href", data.connote );
					jQuery(".connote").show();						
				}
				if ( typeof data.proforma != 'undefined' ) {
					jQuery(".proforma a").attr("href", data.proforma );
					jQuery(".proforma").show();						
				}
				if ( typeof data.b13a != 'undefined' ) {
					jQuery(".b13a a").attr("href", data.b13a );
					jQuery(".b13a").show();						
				}
				
				// no need to check again
				jQuery("#emc_documents").addClass("labels_available");
			}
			else{
				delay_do_label_request();
			}
		}, "json");
	}
}

jQuery(document).ready(function(){
	do_label_request();
});