( function ( $ ) {
	'use strict';

	$( function () {
		var $body = $( '#genesis-updater-changelog-rows-body' );

		$( '#genesis-updater-changelog-add-row' ).on( 'click', function () {
			var index = $body.find( '.genesis-updater-row' ).length;
			var template = $( '#genesis-updater-changelog-row-template' ).html().replace( /__INDEX__/g, index );
			$body.append( template );
		} );

		$body.on( 'click', '.genesis-updater-remove-row', function () {
			var $rows = $body.find( '.genesis-updater-row' );
			if ( $rows.length <= 1 ) {
				$( this ).closest( 'tr' ).find( 'input[type=text], textarea' ).val( '' );
				return;
			}
			$( this ).closest( 'tr' ).remove();
		} );
	} );
} )( jQuery );
