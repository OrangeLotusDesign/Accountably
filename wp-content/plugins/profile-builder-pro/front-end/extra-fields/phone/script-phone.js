jQuery( function( $ ) {
    $( ".extra_field_phone, .custom_field_phone" ).each( function() {
        var wppb_mask_data = $( this ).attr( 'data-phone-format' );
        var wppb_mask = '';

        $.each( JSON.parse( wppb_mask_data ).phone_data, function( key, value ) {
            if( value == '#' ) {
                value = '9';
            }
            wppb_mask += value;
        } );

        $( this ).inputmask( wppb_mask );
    } );
} );