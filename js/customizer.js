/**
 * Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Customizer preview reload changes asynchronously.
 */
( function( $ ) {

	// Background color.
	wp.customize( 'pt_randombtn_background', function( value ) {
		value.bind( function( to ) {
			if ( to ) {
				$( 'a.random-button' ).css( {
					'background': to,
				} );
			}
		} );
	} );

	// Font color.
	wp.customize( 'pt_randombtn_color', function( value ) {
		value.bind( function( to ) {
			if ( to ) {
				$( 'a.random-button' ).css( {
					'color': to,
				} );
			}
		} );
	} );


} )( jQuery );