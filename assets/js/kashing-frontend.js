( function($) {

    // Window resize actions

    $(document).ready(function() {
        'use strict';


        $( '#kashing-pay' ).on( 'click', function() {

            if ( validateKashingForm() == false ) {

                return;
            } else {

                // Making an AJAX call to a PHP script:

                $.ajax({
                    url: kashing_wp_object.wp_ajax_url, // wp-ajax.php
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
                        country : $( '#kashing-country' ).val(),
                        phone: $( '#kashing-phone' ).val(),
                        email: $( '#kashing-email' ).val(),
                        address2: $( '#kashing-email' ).val()
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
                });

            }



        });

    });

    function validateKashingForm() {


        var kashingFormParameters = [
            {
                'name': 'firstname',
                'data-validation' : {
                    "required" : true,
                    "minlength" : 1
                }},
            {
                'name': 'lastname',
                'data-validation' : {
                    "required" : true,
                    "minlength" : 1
                }},
            {
                'name': 'address1',
                'data-validation' : {
                    "required" : true,
                    "minlength" : 1
                }},
            {
                'name': 'city',
                'data-validation' : {
                    "required" : true,
                    "minlength" : 1
                }},
            {
                'name': 'postcode',
                'data-validation' : {
                    "required" : true,
                    "minlength" : 1,
                    "type" : "postcode"
                }}

        ];

            //var form_data = $( '#kashing-form-id' ).serializeArray();

            //$( '.error' ).remove();
            var errorFree = true;

            //for ( var input in form_data ) {
            for ( var input in kashingFormParameters ) {

                // var fieldName = form_data[ input ][ 'name' ];
                // var fieldValue = form_data[ input ][ 'value' ];
                // var selector = 'input[name="' + fieldName + '"]';
                // var valAttributes = $( selector ).attr( 'data-validation' );
                var formData = kashingFormParameters[input];
                var fieldName = formData[ 'name' ];
                var valAttributes = formData[ 'data-validation' ];
                var selector = 'input[name="' + fieldName + '"]';
                var fieldValue = $( selector ).attr( 'value' );

                if ( typeof valAttributes !== 'undefined' ) {

                    //var attributes = JSON.parse( valAttributes );
                    var attributes = valAttributes;

                    var isDataValid = true;

                    for ( var attr in attributes ) {

                        if ( !validationFunctions[attr]( fieldValue, attributes[attr] ) ) {

                            isDataValid = false;
                            errorFree = false;
                            break;
                        }
                    }

                    if ( isDataValid ) {

                        validData( fieldName );

                    } else {

                        invalidData( fieldName );
                    }


                } else {

                    //if no data-validation attributes
                }

            }

            if ( errorFree ) {

                return true;

            } else {

                return false
            }

    }

    /**
     *  Mark invalid field in kashing form.
     */

    function invalidData( fieldName ) {

        var selector = 'input[name="' + fieldName + '"]';
        var message = 'Invalid field';

        var parent = $( selector ).closest( '.input-holder' );
        var error = parent.find( '.error' );


        if ( error.length == 0 ) {

            parent.addClass( 'invalid-field' );
            parent.append( "<span class=\"error\">" +  message + "</span>" );

        } else {
            //already marked as invalid
        }

    }

    /**
     *  Remove invalid field formatting.
     */

    function validData( fieldName ) {

        var selector = 'input[name="' + fieldName + '"]';
        var parent = $( selector ).closest( '.input-holder' );

        parent.find( '.error' ).remove();
        parent.removeClass( 'invalid-field' );
    }

    /**
     *  Validation function.
     */

    var validationFunctions = [];

    validationFunctions['required'] = function (value, paramValue) {

        if ( typeof(paramValue) === 'boolean' ) {

            if ( value ) {

                return true;

            } else {

                return false;
            }
        } else {

            return false;
        }
    };

    /**
     *  Validation function.
     */

    validationFunctions['minlength'] = function (value, paramValue) {

        if ( paramValue >>> 0 === parseFloat(paramValue) ) { //if positive integer

            if (value.length >= paramValue) {

                return true;

            } else {

                return false;
            }
        } else {

            return false;
        }
    };

    /**
     *  Validation function.
     */

    validationFunctions['type'] = function (value, paramValue) {

        if (paramValue === 'postcode') {

            //value = value.replace(/\s/g, "");

            var regex = '';
            var country = $( '#kashing-country' ).val();


            if( country === 'US' ) {

                regex = /^\d{5}(?:[-\s]\d{4})?$/;

            } else if ( country === 'UK' ){

                regex = /^(([gG][iI][rR] {0,}0[aA]{2})|((([a-pr-uwyzA-PR-UWYZ][a-hk-yA-HK-Y]?[0-9][0-9]?)|(([a-pr-uwyzA-PR-UWYZ][0-9][a-hjkstuwA-HJKSTUW])|([a-pr-uwyzA-PR-UWYZ][a-hk-yA-HK-Y][0-9][abehmnprv-yABEHMNPRV-Y]))) {0,}[0-9][abd-hjlnp-uw-zABD-HJLNP-UW-Z]{2}))$/i;

            } else {
                return false;
            }


            return regex.test(value);

        } else {

            return false;
        }
        
    };

} ) ( jQuery );