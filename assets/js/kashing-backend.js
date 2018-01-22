( function($) {

    // Window resize actions

    $(document).ready(function() {
        'use strict';

        // Add Kashing Form button

        $( '#add-kashing-form' ).on( 'click', function() {
            $.ajax({
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
                            title: kashing_wp_object.text_add_kashing_form,
                            width: 400,
                            height: 150,
                            id: 'kashing-popup-window',
                            body: [
                                {
                                    type: 'listbox',
                                    name: 'title',
                                    label: kashing_wp_object.text_form_name + ':',
                                    values : response.data
                                },
                                {
                                    type: 'container',
                                    name: 'container',
                                    label: '',
                                    layout: 'grid',
                                    html : kashing_wp_object.msg_new_forms_how_to
                                }
                            ],
                            onsubmit: function( e ) {
                                tinymce.activeEditor.insertContent( '[kashing_form form_id="' + e.data.title + '"]' );
                            }
                        });
                    } else {
                        // No payment forms found
                        tinymce.activeEditor.windowManager.open({
                            title: kashing_wp_object.text_add_kashing_form,
                            width: 400,
                            height: 190,
                            id: 'kashing-popup-window',
                            body: [{
                                type   : 'container',
                                name   : 'container',
                                id : 'kashing-no-forms-notice',
                                label : '',
                                layout: 'grid',
                                html   : kashing_wp_object.msg_no_forms
                            }]
                        });
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log( 'There was an error during the AJAX call to WordPress Ajax: ' + xhr );
                },
            }) ;

        });

        // Metabox additional field validation

        $( '#ksng-amount' ).focusout(function() {

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