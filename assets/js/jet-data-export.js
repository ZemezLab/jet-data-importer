( function( $, JetDataExport ) {

	"use strict";

	JetDataExport = {
		globalProgress: null,
		init: function(){
			$( '#jet-export' ).on( 'click', function( event ) {
				var $this   = $( this ),
					href    = $this.attr( 'href' );
				event.preventDefault();
				window.location = href + '&nonce=' + window.JetDataExportVars.nonce;
			});
		},
	};

	JetDataExport.init();

}( jQuery, window.JetDataExport ) );