( function($) {


$( document ).ready(function() {

    //id set permanent
    var amountID = '#' + 'ksng-amount';


    $(amountID).attr( 'pattern', '[0-9]+([,\.][0-9]+)?' );

    $(amountID).focusout(function() {

        var value = $( this ).val();

        //so we can use commas too

        if ( value.indexOf(',') !== -1) {

            if ( ( value.match( /,/g ) || [] ).length < 2 && value.indexOf('.') === -1) {

                var pos = value.lastIndexOf( ',' );
                value = value.substring( 0, pos ) + '.' + value.substring( pos + 1 );

            }

            value = parseFloat(value.replace(/,/g, ''));
        }


        if( value !== '' && Number(value) == value ) {

            value = Number( value );
            value = value.toFixed( 2 );

            $( this ).val( value );
        }

    });

    //replace commas with dots real time
    // $(amountID).on('input', function() {
    //
    //     var value = $( amountID ).val();
    //
    //     value = value.replace(/,/g, '.')
    //
    //     $( this ).val(value);
    //
    // });


});


} ) ( jQuery );