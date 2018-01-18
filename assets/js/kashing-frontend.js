( function($) {

    // Window resize actions

    $(document).ready(function() {
        'use strict';


        // New CALL

        $( '#kashing-formX' ).submit( function( event ) {

            event.preventDefault(); // Prevent the default form submit.

            // Serialize the form data

            var ajax_form_data = $( "#kashing-form" ).serialize();

            // Add the ajax check, X-Requested-With is not always reliable

            ajax_form_data = ajax_form_data + '&ajaxrequest=true';

            // Make an AJAX call

            $.ajax({
                url:    kashing_wp_object.wp_ajax_url, // admin-ajax.php address
                type:   'POST',
                data:   ajax_form_data,
                success: function( response ) {
                    if ( response.success ) { // The API Call was successful

                        //validServerData();

                        console.log( response );
                        if ( response.data.redirect_url != '' ) {
                            //window.location.href = response.data.redirect_url;
                        }
                    } else { // The API Call went wrong
                        alert( 'ERROR' );

                         //dla odpowiedzi typu {success: false, data: ["firstname", "lastname"]}
                        // for (var name in resp['data']) {

                        //         invalidData( resp['data'][name] );

                        //     }

                        // invalidServerData( resp );

                        console.log( response );
                    }
                }
            }).fail( function() {
                console.log( "<h2>Something went wrong with the entire AJAX call.</h2><br>" );
            });

        });

    });

    function validateKashingForm() {


        var kashingFormParameters = {
                'firstname': {
                    'data-validation': {
                        "required": true,
                        "minlength": 1
                    }
                },
            'lastname': {
                    'data-validation': {
                        "required": true,
                        "minlength": 1
                    }
                },
            'address1': {
                    'data-validation': {
                        "required": true,
                        "minlength": 1
                    }
                },
            'address2': {
                    'data-validation': {
                        "required": false
                    }
                },
            'city': {
                    'data-validation': {
                        "required": true,
                        "minlength": 1
                    }
                },
            'postcode': {
                    'data-validation': {
                       "required": true,
                        "minlength": 1
                    }
                },
            'email': {
                    'data-validation': {
                        "required": true,
                        "minlength": 1,
                        'type': 'email'
                    }
                },
            'phone': {
                    'data-validation': {
                        "required": false
                    }
                }
        };


            var inputFields = $( '.input-holder > input' ).serializeArray();

            var errorFree = true;

            for ( var input in inputFields ) {

                var fieldName = inputFields[ input ][ 'name' ];

                var formData = kashingFormParameters[fieldName];

                //if not in kashingFormParameters
                if(formData == undefined) {

                    invalidData( fieldName );
                    errorFree = false;

                    continue;
                }

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
     *  Invalid data on a server side.
     */

    function invalidServerData( errorData ) {


        var parent = $( '.kashing-form' );

        var error = $( '.kashing-form > .server-error');

        if ( error.length == 0 ) {

            parent.prepend( "<div class=\"server-error invalid-field\">" +  JSON.stringify(errorData) + "</div>" );

        } else {
            //already marked as invalid
        }

    }

    /**
     *  Invalid data on a server side.
     */

    function validServerData() {

        var error = $( '.kashing-form > .server-error');

        if ( error.length > 0 ) {

            error.remove();

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

            if (paramValue) {

                if ( value ) {

                    return true;

                } else {

                    return false;
                }

            } else {

                return true;

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

        } else if ( paramValue === 'email' ) {

            var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            return regex.test(value);

        } else {

            return false;
        }
        
    };

} ) ( jQuery );