jQuery( document ).ready( function( $ ) {
    $( '.custom_field_colorpicker' ).each( function() {
        var $delimiter = $(this).siblings( '.wppb-description-delimiter' );

        if( wppb_colorpicker_data.isFrontend == 1 ) {
            $( this ).iris( {
                target: $delimiter
            } );
        } else {
            $( this ).iris( {} );
        }
    } );

    $( document ).click( function( e ) {
        if( ! $( e.target ).is( ".custom_field_colorpicker, .iris-picker, .iris-picker-inner, .iris-slider, .iris-slider-offset, .iris-square-handle, .iris-square-value" ) ) {
            $( '.custom_field_colorpicker' ).iris( 'hide' );
        }
    } );

    $( '.custom_field_colorpicker' ).click( function( event ) {
        $( '.custom_field_colorpicker' ).iris( 'hide' );
        $( this ).iris( 'show' );
    } );
} );