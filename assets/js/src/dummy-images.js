( function( $ ) {
	var loading = false;

	$( document ).ready( function() {
		show_listing( 1 );

	    $('.color-picker-field').wpColorPicker();

		$( '.more-button' ).on( 'click', function() {	
			show_listing( parseInt( $( this ).attr( 'data-page' ) ) + 1 );
		} );

		$( '.open-create-area' ).click( function() {
			$( '.create-area' ).toggle();
		} );

		$( '#create-dummy-form' ).submit( function( e ) {
			e.preventDefault();

			$.post( dummy_images.ajax_url, {
				'action': 'create_dummyimages',
				'data': $( '#create-dummy-form input' ).serialize(),
				'nonce': dummy_images.ajax_nonce,
			}, function( response ) {
				if ( response.success == true ) {
					show_listing( 1 );
					
					show_notice( 'success', response.data );

					$( '.create-area' ).css( 'display', 'none' );
					$( '#create-dummy-form' )[0].reset();

					toggle_button( true );
				} else {
					show_notice( 'error', response.data );
				}
			} );
		} );
	} );

	function show_notice( type, msg ) {
		$( '.notice-area' ).html( '<div class="notice notice-' + type + ' is-dismissible"><p>' + msg + '</p></div>' );
	}

	function show_listing( page ) {
		if ( loading ) return; loading = true;

		$.get( dummy_images.ajax_url, {
			'action': 'list_dummyimages',
			'page': page,
			'nonce': dummy_images.ajax_nonce,
		}, function( response ) {
			if ( response.success == true ) {
				$( '.more-button' ).attr( 'data-page', page );

				if ( page > 1 ) {
					$( '.dummy-images-listing' ).append( response.data.html );	
				} else {
					$( '.dummy-images-listing' ).html( response.data.html );	
				}
				
				if ( response.data.left <= 0 ) toggle_button( false );
			} else {
				$( '.dummy-images-listing' ).html( response.data.html );
				toggle_button( false );
			}

			loading = false;
		} );
	}

	function toggle_button( new_call ) {
		var button = $( '.more-button' );

		if ( new_call == true ) button.attr( 'data-page', 1 );

		button.toggle( new_call );
	}
} ) ( jQuery );