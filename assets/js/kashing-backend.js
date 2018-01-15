( function($) {

    // Window resize actions

    $(document).ready(function() {
        'use strict';

        // Add Kashing Form button

        $( '#add-kashing-form' ).on( 'click', function() {

            $.ajax ({
                url: post_types_wp_object.wp_ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'call_get_kashing_forms'
                },
                success: function ( resp ) {

                    if ( resp.success ) {

                        var response = JSON.parse( resp.data );

                        tinymce.activeEditor.windowManager.open( {

                            title: 'Add Kashing Form:',
                            width: 320,
                            height: 240,
                            body: [{
                                type: 'listbox',
                                name: 'title',
                                label: 'Title',
                                values : response.data
                            },],
                            onsubmit: function( e ) {

                                tinymce.activeEditor.insertContent( '[kashing_form form_id="' + e.data.title + '"]' );
                            }

                        } );

                    } else {

                        alert ( 'There is no payment form.' ) ;
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {

                    alert ('Request failed: ') ;
                },
            }) ;

        });

        // Metabox additional field validation

        var amountID = '#' + 'ksng-amount';


        $(amountID).attr( 'pattern', '[0-9]+([,\.][0-9]+)?' );

        $(amountID).focusout(function() {

            var value = $( this ).val();

            //so we can use commas too

            if ( value.indexOf( ',' ) !== -1 ) {

                if ( ( value.match( /,/g ) || [] ).length < 2 && value.indexOf('.') === -1) {

                    var pos = value.lastIndexOf( ',' );
                    value = value.substring( 0, pos ) + '.' + value.substring( pos + 1 );

                }

                value = parseFloat( value.replace( /,/g, '' ) );
            }


            if ( value !== '' && Number(value) == value ) {

                value = Number( value );
                value = value.toFixed( 2 );

                $( this ).val( value );
            }

        });

    });

} ) ( jQuery );