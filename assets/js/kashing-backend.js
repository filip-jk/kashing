( function($) {

    // Window resize actions

    $(document).ready(function() {
        'use strict';

        // Add Kashing Form button

        $( '#add-kashing-form' ).on( 'click', function() {

            $.ajax ({
                url: kashing_wp_object.wp_ajax_url,
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


        $(amountID).focusout(function() {

            var value = $( this ).val();

            value = value.replace(/[^\d,.]/g, ''); // Remove letters

            //so we can use commas too

            if ( value.indexOf( ',' ) !== -1 ) {

                if ( ( value.match( /,/g ) || [] ).length < 2 && value.indexOf('.') === -1) {

                    var pos = value.lastIndexOf( ',' );
                    value = value.substring( 0, pos ) + '.' + value.substring( pos + 1 );

                }

                value = parseFloat( value.replace( /,/g, '' ) );
            }


            var valueDots = value.split('.');
            valueDots = valueDots.filter(Number);

            if ( valueDots.length > 1) {

                value = valueDots[0] + "." + valueDots[1];

            }


            if ( value !== '' && Number(value) == value ) {

                value = Number( value );
                value = value.toFixed( 2 );

                //$( this ).val( value );
            }

            $(this).val( value );

        });

        // Required field left empty

        $( '.kashing-admin-form .required-field input, .kashing-admin-form .required-field textarea' ).on( 'focusout', function() {
            if ( $(this).val() == '' ) {
                $(this).closest('.kashing-input').append('<span class="kashing-error required-field">' + kashing_wp_object.msg_missing_field + '</span>');
                $(this).closest('.kashing-field').addClass('has-errors');
            } else {
                $(this).closest('.kashing-input').find('.kashing-error').remove();
                $(this).closest('.kashing-field').removeClass('has-errors');
                return;
            }
        });

    });

} ) ( jQuery );