( function ( $ ) {
	'use strict';

	var platformMeta = window.productUpdaterPlatformMeta || {};

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
				$cell.find( '.genesis-product-updater-compat-label' ).text( supports && labelFn ? labelFn( field ) : '' );
			}
		} );
	}

	function updateRowFields( $row ) {
		var slug = $( '#product_updater_platform' ).val();
		var meta = platformMeta[ slug ];
		var supportsCompat = ! slug || ! meta || false !== meta.supportsCompat;
		var supportsMobile = !! slug && !! meta && !! meta.supportsMobile;

		toggleFieldGroup( $row, 'genesis-product-updater-compat-cell', 'genesis-product-updater-compat-na', supportsCompat, function ( field ) {
			return slug ? compatLabel( field, meta ) : '';
		} );
		toggleFieldGroup( $row, 'genesis-product-updater-mobile-cell', 'genesis-product-updater-mobile-na', supportsMobile, null );
		$row.find( '.genesis-product-updater-changelog-url' ).toggle( 'custom' === $row.find( '.genesis-product-updater-changelog-mode' ).val() );

		var visibleFields = {
			joomla: [ 'version', 'target_version', 'tag', 'is_current', 'download_url', 'sha512', 'info_url', 'info_title', 'changelog_mode', 'changelog_url', 'release_date', 'requires', 'tested', 'requires_php' ],
			wordpress: [ 'version', 'tag', 'is_current', 'download_url', 'info_url', 'changelog_mode', 'changelog_url', 'release_date', 'requires', 'tested', 'requires_php' ],
			mobile: [ 'version', 'is_current', 'changelog_mode', 'changelog_url', 'build_number', 'url_ios', 'url_android', 'force_update', 'release_notes' ],
			fab: [ 'version', 'is_current', 'changelog_mode', 'changelog_url', 'url_fab', 'release_notes' ]
		};
		$( '.genesis-product-updater-rows-table thead th[data-field]' ).each( function () {
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
		var customChangelog = 'custom' === $row.find( '.genesis-product-updater-changelog-mode' ).val();
		$row.find( '.genesis-product-updater-changelog-url' ).toggle( customChangelog ).prop( 'disabled', ! customChangelog );
	}

	$( function () {
		var $body = $( '#genesis-product-updater-rows-body' );
		$( '.genesis-product-updater-select-image' ).on( 'click', function () {
			var frame = wp.media( { title: 'Select product preview image', multiple: false, library: { type: 'image' } } );
			frame.on( 'select', function () { $( '#product_updater_preview_image' ).val( frame.state().get( 'selection' ).first().toJSON().url ); } );
			frame.open();
		} );

		$body.find( '.genesis-product-updater-row' ).each( function () {
			updateRowFields( $( this ) );
		} );

		function updateProductTypes() {
			var platform = $( '#product_updater_platform' ).val();
			var $type = $( '#product_updater_type' );
			$type.find( 'option[data-platform]' ).each( function () {
				$( this ).prop( 'hidden', $( this ).data( 'platform' ) !== platform && 'all' !== $( this ).data( 'platform' ) );
			} );
			if ( $type.find( 'option:selected' ).prop( 'hidden' ) ) {
				$type.val( $type.find( 'option[data-platform="' + platform + '"]' ).first().val() );
			}
			$body.find( '.genesis-product-updater-row' ).each( function () { updateRowFields( $( this ) ); } );
		}
		updateProductTypes();
		$( '#product_updater_platform' ).on( 'change', updateProductTypes );

		$( '#genesis-product-updater-add-row' ).on( 'click', function () {
			var index = $body.find( '.genesis-product-updater-row' ).length;
			var template = $( '#genesis-product-updater-row-template' ).html().replace( /__INDEX__/g, index );
			var $row = $( template ).appendTo( $body );
			updateRowFields( $row );
		} );

		$body.on( 'change', '.genesis-product-updater-changelog-mode', function () {
			updateRowFields( $( this ).closest( 'tr' ) );
		} );

		$body.on( 'click', '.genesis-product-updater-remove-row', function () {
			var $rows = $body.find( '.genesis-product-updater-row' );
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
