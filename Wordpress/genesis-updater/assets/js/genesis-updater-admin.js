( function ( $ ) {
	'use strict';

	var platformMeta = window.genesisUpdaterPlatformMeta || {};

	function compatLabel( field, meta ) {
		if ( 'requires_php' === field ) {
			return 'Requires PHP';
		}
		var platformLabel = meta && meta.label ? meta.label : 'Platform';
		return ( 'requires' === field ? 'Requires ' : 'Tested up to ' ) + platformLabel + ' Version';
	}

	function toggleFieldGroup( $row, cellClass, naClass, supports, labelFn ) {
		$row.find( '.' + cellClass ).each( function () {
			var $cell = $( this );
			var field = $cell.data( 'field' );

			$cell.find( 'input' ).toggle( supports );
			$cell.find( '.' + naClass ).toggle( ! supports );

			if ( labelFn ) {
				$cell.find( '.genesis-updater-compat-label' ).text( supports && labelFn ? labelFn( field ) : '' );
			}
		} );
	}

	function updateRowFields( $row ) {
		var slug = $( '#genesis_updater_platform' ).val();
		var meta = platformMeta[ slug ];
		var supportsCompat = ! slug || ! meta || false !== meta.supportsCompat;
		var supportsMobile = !! slug && !! meta && !! meta.supportsMobile;

		toggleFieldGroup( $row, 'genesis-updater-compat-cell', 'genesis-updater-compat-na', supportsCompat, function ( field ) {
			return slug ? compatLabel( field, meta ) : '';
		} );
		toggleFieldGroup( $row, 'genesis-updater-mobile-cell', 'genesis-updater-mobile-na', supportsMobile, null );
		$row.find( '.genesis-updater-changelog-url' ).toggle( 'custom' === $row.find( '.genesis-updater-changelog-mode' ).val() );

		var visibleFields = {
			joomla: [ 'version', 'target_version', 'tag', 'is_current', 'download_url', 'sha512', 'info_url', 'info_title', 'changelog_mode', 'changelog_url', 'release_date', 'requires', 'tested', 'requires_php' ],
			wordpress: [ 'version', 'tag', 'is_current', 'download_url', 'info_url', 'changelog_mode', 'changelog_url', 'release_date', 'requires', 'tested', 'requires_php' ],
			mobile: [ 'version', 'is_current', 'changelog_mode', 'changelog_url', 'build_number', 'url_ios', 'url_android', 'force_update', 'release_notes' ],
			fab: [ 'version', 'is_current', 'changelog_mode', 'changelog_url', 'url_fab', 'release_notes' ]
		};
		$( '.genesis-updater-rows-table thead th[data-field]' ).each( function () {
			var field = $( this ).data( 'field' );
			$( this ).toggle( -1 !== ( visibleFields[ slug ] || [] ).indexOf( field ) );
		} );
		$row.children( 'td' ).each( function () {
			var $cell = $( this );
			var name = $cell.find( '[name]' ).first().attr( 'name' ) || '';
			var match = name.match( /\[([^\]]+)\]$/ );
			if ( match ) {
				var visible = -1 !== ( visibleFields[ slug ] || [] ).indexOf( match[ 1 ] );
				$cell.toggle( visible );
				$cell.find( ':input' ).prop( 'disabled', ! visible );
			}
		} );
		var customChangelog = 'custom' === $row.find( '.genesis-updater-changelog-mode' ).val();
		$row.find( '.genesis-updater-changelog-url' ).toggle( customChangelog ).prop( 'disabled', ! customChangelog );
	}

	$( function () {
		var $body = $( '#genesis-updater-rows-body' );
		$( '.genesis-updater-select-image' ).on( 'click', function () {
			var frame = wp.media( { title: 'Select product preview image', multiple: false, library: { type: 'image' } } );
			frame.on( 'select', function () { $( '#genesis_updater_preview_image' ).val( frame.state().get( 'selection' ).first().toJSON().url ); } );
			frame.open();
		} );

		$body.find( '.genesis-updater-row' ).each( function () {
			updateRowFields( $( this ) );
		} );

		function updateProductTypes() {
			var platform = $( '#genesis_updater_platform' ).val();
			var $type = $( '#genesis_updater_type' );
			$type.find( 'option[data-platform]' ).each( function () {
				$( this ).prop( 'hidden', $( this ).data( 'platform' ) !== platform && 'all' !== $( this ).data( 'platform' ) );
			} );
			if ( $type.find( 'option:selected' ).prop( 'hidden' ) ) {
				$type.val( $type.find( 'option[data-platform="' + platform + '"]' ).first().val() );
			}
			$body.find( '.genesis-updater-row' ).each( function () { updateRowFields( $( this ) ); } );
		}
		updateProductTypes();
		$( '#genesis_updater_platform' ).on( 'change', updateProductTypes );

		$( '#genesis-updater-add-row' ).on( 'click', function () {
			var index = $body.find( '.genesis-updater-row' ).length;
			var template = $( '#genesis-updater-row-template' ).html().replace( /__INDEX__/g, index );
			var $row = $( template ).appendTo( $body );
			updateRowFields( $row );
		} );

		$body.on( 'change', '.genesis-updater-changelog-mode', function () {
			updateRowFields( $( this ).closest( 'tr' ) );
		} );

		$body.on( 'click', '.genesis-updater-remove-row', function () {
			var $rows = $body.find( '.genesis-updater-row' );
			if ( $rows.length <= 1 ) {
				var $row = $( this ).closest( 'tr' );
				$row.find( 'input[type=text], input[type=url]' ).val( '' );
				updateRowFields( $row );
				return;
			}
			$( this ).closest( 'tr' ).remove();
		} );
	} );
} )( jQuery );
