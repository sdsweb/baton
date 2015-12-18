( function ( $ ) {
	"use strict";

	$( function() {
		/**
		 * Navigation Tabs
		 */
		$( '.sds-theme-options-tab-wrap a' ).on( 'click', function ( event ) {
			var self = $( this ),
				tab_id_prefix = self.attr( 'href' );

			// Prevent default
			event.preventDefault();

			// Remove active classes
			$( '.sds-theme-options-tab-content' ).removeClass( 'sds-theme-options-tab-content-active' );
			$( '.sds-theme-options-tab' ).removeClass( 'nav-tab-active' );

			// Activate new tab
			$( tab_id_prefix + '-tab-content' ).addClass( 'sds-theme-options-tab-content-active' );
			self.addClass( 'nav-tab-active' );
		} );
	} );
}( jQuery ) );