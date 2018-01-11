( function($) {

    // Window resize actions

    $(document).ready(function() {
        'use strict';

        $( '#kashing-pay' ).on( 'click', function() {

            if ( validateKashingForm() == false ) {
                return; // TO-DO
            }

            // Making an AJAX call to a PHP script:

            $.ajax ({
                url: kashing_wp_object.wp_ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'call_kashing_ajax', // Name of the PHP function assigned to WP Ajax
                    form_id: $( '#kashing-form-id' ).val(),
                    // ANY other properties of data are passed to your_function()
                    // in the PHP global $_REQUEST (or $_POST in this case)
                    firstname : $( '#kashing-firstname' ).val(),
                    lastname : $( '#kashing-lastname' ).val(),
                    address1 : $( '#kashing-address1' ).val(),
                    city : $( '#kashing-city' ).val(),
                    postcode : $( '#kashing-postcode' ).val(),
                    country : $( '#kashing-country' ).val()
                },
                success: function ( resp ) {

                    if ( resp.success ) {

                        // Parse the JSON response

                        var response = JSON.parse( resp.data );

                        console.log( response );

                        // Check response type and proceed accordingly

                        if ( response.responsecode == 4 && response.redirect != '' ) { // Redirect
                            //alert( 'Success! Redirection would go now.' );
                            window.location.href = response.redirect;
                        }

                    } else {
                        // this "error" case means the ajax call, itself, succeeded, but the function
                        // called returned an error condition
                        alert ( 'Error: ' ) ;
                        console.log( resp );
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    // this error case means that the ajax call, itself, failed, e.g., a syntax error
                    // in your_function()
                    alert ('Request failed: ') ;

                },
            }) ;

        });

    });

    function validateKashingForm() {
        return true;
    }

} ) ( jQuery );