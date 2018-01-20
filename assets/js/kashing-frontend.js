( function($) {

    /**
     *  Handling the Kashing Form with JavaScript.
     */

    $(document).ready(function() {
        'use strict';

        // Take over the form submission

        var $kashingForm = $( '.kashing-form' );

        $kashingForm.submit( function( event ) {

            var validated = false;

            // Validate the entire form

            if ( validateKashingForm() === false ) {
                event.preventDefault(); // Prevent the default form submit.
            } else { // Form okay, proceed with submission.
                return true;
            }

        });

        On field focusout

        $( '.kashing-form input' ).on( 'focusout', function() {
            validateFormField( $(this).attr('name'), $(this).val() ); // Validate a field that was just focused out by the user
        });

    });

    /**
     *  Entire form validation (2nd layer, 1st one being the browser itself)
     */

    var kashingFormParameters = {
        'firstname': {
            'data-validation': {
                "required": true
            }
        },
        'lastname': {
            'data-validation': {
                "required": true
            }
        },
        'address1': {
            'data-validation': {
                "required": true
            }
        },
        'address2': {
            'data-validation': {
                "required": false
            }
        },
        'city': {
            'data-validation': {
                "required": true
            }
        },
        'postcode': {
            'data-validation': {
                "required": true
            }
        },
        'email': {
            'data-validation': {
                "required": false,
                "type": 'email'
            }
        },
        'phone': {
            'data-validation': {
                "required": false
            }
        }
    };

    /**
     *  Entire form validation (2nd layer, 1st one being the browser itself)
     */

    function validateKashingForm() {

        var inputFields = $( '.input-holder > input' ).serializeArray();
        var errorFree = true;
        var errorType = '';

        for ( var input in inputFields ) {

            var fieldName = inputFields[ input ][ 'name' ];
            var fieldValue = $( 'input[name="' + fieldName + '"]' ).attr( 'value' );
            var formData = kashingFormParameters[fieldName];

            // if not in kashingFormParameters

            if ( formData == undefined ) {
                invalidData( fieldName );
                errorFree = false;
                continue;
            }

            if ( validateFormField( fieldName, fieldValue ) === false ) {
                errorFree = false;
            }

        }

        return errorFree; // true or false
    }

    /**
     *  Single field validation.
     */

    function validateFormField( fieldName, fieldValue ) {

        var formData = kashingFormParameters[fieldName];
        var valAttributes = formData[ 'data-validation' ];
        var errorType;

        if ( typeof valAttributes !== 'undefined' ) {

            var attributes = valAttributes;
            var isDataValid = true;

            for ( var attr in attributes ) {
                if ( !validationFunctions[attr]( fieldValue, attributes[attr] ) ) {
                    isDataValid = false;
                    errorType = attributes[attr]; // Store the type of the error
                    break;
                }
            }

            if ( isDataValid ) {
                validData( fieldName );
                return true;
            } else {
                invalidData( fieldName, errorType );
                return false;
            }

        }

        return true;

    }

    /**
     *  Mark invalid field in kashing form.
     */

    function invalidData( fieldName, errorType ) {
        var selector = 'input[name="' + fieldName + '"]';

        var parent = $( selector ).closest( '.input-holder' );
        var error = parent.find( '.kashing-form-error-msg' );

        if ( error.length == 0 ) {

            var message; // The error message based on errorType

            if ( errorType === 'email' ) {
                message = kashing_wp_object.msg_invalid_email;
            } else {
                message = kashing_wp_object.msg_missing_field;
            }

            parent.addClass( 'validation-error' );
            parent.append( '<span class="kashing-form-error-msg">' +  message + "</span>" );
        }
    }

    /**
     *  Remove invalid field formatting.
     */

    function validData( fieldName ) {
        var selector = 'input[name="' + fieldName + '"]';
        var parent = $( selector ).closest( '.input-holder' );

        parent.find( '.kashing-form-error-msg' ).remove();
        parent.removeClass( 'validation-error' );
    }

    /**
     *  Validation function for the field being required.
     */

    var validationFunctions = [];

    validationFunctions['required'] = function (value, paramValue) {
        if ( paramValue == false ) return true;
        if ( typeof(paramValue) === 'boolean' ) {
            if ( paramValue ) {
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
     *  Validation function for a minimum number of characters in the field.
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
     *  Validation function for field types (i.e. email)
     */

    validationFunctions['type'] = function ( value, paramValue ) {
        if ( paramValue === 'email' ) {
            if ( value != '' ) { // Do not make a check if no value provided
                var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regex.test( value );
            } else {
                return true;
            }
        } else {
            return false;
        }
    };

} ) ( jQuery );