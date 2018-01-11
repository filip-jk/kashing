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
                    action: 'call_post_types_ajax'
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

    });

} ) ( jQuery );