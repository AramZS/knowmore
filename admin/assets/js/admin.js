( function ( $ ) {

	"use strict";

	$( function () {

		$( '#know_more_meta_box_url' ).on( 'change', function() {
			var url  = $( this ).val();

			$.ajax( {
				url     : ajaxurl,
				cache   : false,
				type    : 'get',
				datatype: 'json',
				data    : { action: 'handle_request', url: url },
				success : function( data ) {
					if ( data.success ) {
						$( '#know_more_error' ).hide();
						var html = '';
						html += '<label for="know_more_meta_box_headline">Headline</label>';
						html += '<input type="text" id="know_more_meta_box_headline" name="know_more_meta_box_headline" ';
						html += 'value="' + data.data.headline + '">';
						html += '<label for="know_more_meta_box_image">Image</label>';
						if ( data.data.image ) {
							html += '<input type="text" id="know_more_meta_box_image" name="know_more_meta_box_image" ';
							html += 'value="' + data.data.image + '">';
						}
						html += '<input type="hidden" id="know_more_meta_box_site" name="know_more_meta_box_site" ';
						html += 'value="' + data.data.site + '">';
						$( '#know_more_meta_box' ).append( html );
					} else {
						$( '#know_more_error' ).show();
					}
				}
			} );
			

		} );

		function get_url( url ) {
		}

	} );

}( jQuery ) );