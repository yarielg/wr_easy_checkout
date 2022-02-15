/* global wc_checkout_params */
jQuery( function( $ ) {

    toastr.options = {
        "closeButton": true,
        "newestOnTop": false,
        "progressBar": true,
        "escapeHtml": false,
        "timeOut": "10000",
        "positionClass": "toast-bottom-center",
        "preventDuplicates": true,
        "enableHtml": true,
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    // wc_checkout_params is required to continue, ensure the object exists
    if ( typeof wc_checkout_params === 'undefined' ) {
        return false;
    }

    $.blockUI.defaults.overlayCSS.cursor = 'default';

    var $mask = $('.wrech-mask-container');
    $mask.css('display','none');

    var wc_checkout_form = {
        updateTimer: false,
        dirtyInput: false,
        selectedPaymentMethod: false,
        xhr: false,
        $order_review: $( '#order_review' ),
        $checkout_form: $( 'form.wrech-checkout' ),
        init: function() {

            $( document.body ).on( 'update_checkout', this.update_checkout );
            $( document.body ).on( 'init_checkout', this.init_checkout );

            // Payment methods
            this.$checkout_form.on( 'click', 'input[name="payment_method"]', this.payment_method_selected );

            if ( $( document.body ).hasClass( 'woocommerce-order-pay' ) ) {
                this.$order_review.on( 'click', 'input[name="payment_method"]', this.payment_method_selected );
                this.$order_review.on( 'submit', this.submitOrder );
                this.$order_review.attr( 'novalidate', 'novalidate' );
            }

            // Prevent HTML5 validation which can conflict.
            this.$checkout_form.attr( 'novalidate', 'novalidate' );

            // Form submission
            this.$checkout_form.on( 'submit', this.submit );

            // Inline validation
            this.$checkout_form.on( 'input validate change', '.input-text, select, input:checkbox', this.validate_field );

            // Manual trigger
            this.$checkout_form.on( 'update', this.trigger_update_checkout );

            // Inputs/selects which update totals
            this.$checkout_form.on( 'change', 'select.shipping_method, input[name^="shipping_method"], #ship-to-different-address input, .update_totals_on_change select, .update_totals_on_change input[type="radio"], .update_totals_on_change input[type="checkbox"]', this.trigger_update_checkout );
            this.$checkout_form.on( 'change', '.address-field select', this.input_changed );
            this.$checkout_form.on( 'change', '.address-field input.input-text, .update_totals_on_change input.input-text', this.maybe_input_changed );
            this.$checkout_form.on( 'keydown', '.address-field input.input-text, .update_totals_on_change input.input-text', this.queue_update_checkout );
            // Address fields
            this.$checkout_form.on( 'change', '#ship-to-different-address input', this.ship_to_different_address );

            // Trigger events
            this.$checkout_form.find( '#ship-to-different-address input' ).trigger( 'change' );
            this.init_payment_methods();

            // Update on page load
            if ( parseInt(wc_checkout_params.is_checkout) == 1 ) {
                $( document.body ).trigger( 'init_checkout' );
            }
            if ( wc_checkout_params.option_guest_checkout === 'yes' ) {
                $( 'input#createaccount' ).on( 'change', this.toggle_create_account ).trigger( 'change' );
            }

            $('#billing_country').select2().on('select2:close', function (e) {
                const evt = "scroll.select2";
                $(e.target).parents().off(evt);
                $(window).off(evt);
            });

            $('#shipping_country').select2().on('select2:close', function (e) {
                const evt = "scroll.select2";
                $(e.target).parents().off(evt);
                $(window).off(evt);
            });
;
            $('#billing_state').select2().on('select2:close', function (e) {
                const evt = "scroll.select2";
                $(e.target).parents().off(evt);
                $(window).off(evt);
            });
;
            $('#shipping_state').select2().on('select2:close', function (e) {
                const evt = "scroll.select2";
                $(e.target).parents().off(evt);
                $(window).off(evt);
            });
;
        },
        init_payment_methods: function() {
            var $payment_methods = $( '.woocommerce-checkout' ).find( 'input[name="payment_method"]' );

            // If there is one method, we can hide the radio input
            if ( 1 === $payment_methods.length ) {
                $payment_methods.eq(0).hide();
            }

            // If there was a previously selected method, check that one.
            if ( wc_checkout_form.selectedPaymentMethod ) {
                $( '#' + wc_checkout_form.selectedPaymentMethod ).prop( 'checked', true );
            }

            // If there are none selected, select the first.
            if ( 0 === $payment_methods.filter( ':checked' ).length ) {
                $payment_methods.eq(0).prop( 'checked', true );
            }

            // Get name of new selected method.
            var checkedPaymentMethod = $payment_methods.filter( ':checked' ).eq(0).prop( 'id' );

            if ( $payment_methods.length > 1 ) {
                // Hide open descriptions.
                $( 'div.payment_box:not(".' + checkedPaymentMethod + '")' ).filter( ':visible' ).slideUp( 0 );
            }

            // Trigger click event for selected method
            $payment_methods.filter( ':checked' ).eq(0).trigger( 'click' );
        },
        get_payment_method: function() {
            return wc_checkout_form.$checkout_form.find( 'input[name="payment_method"]:checked' ).val();
        },
        payment_method_selected: function( e ) {
            e.stopPropagation();

            if ( $( '.payment_methods input.input-radio' ).length > 1 ) {
                var target_payment_box = $( 'div.payment_box.' + $( this ).attr( 'ID' ) ),
                    is_checked         = $( this ).is( ':checked' );

                if ( is_checked && ! target_payment_box.is( ':visible' ) ) {
                    $( 'div.payment_box' ).filter( ':visible' ).slideUp( 230 );

                    if ( is_checked ) {
                        target_payment_box.slideDown( 230 );
                    }
                }
            } else {
                $( 'div.payment_box' ).show();
            }

            if ( $( this ).data( 'order_button_text' ) ) {
                $( '#place_order' ).text( $( this ).data( 'order_button_text' ) );
            } else {
                $( '#place_order' ).text( $( '#place_order' ).data( 'value' ) );
            }

            var selectedPaymentMethod = $( '.woocommerce-checkout input[name="payment_method"]:checked' ).attr( 'id' );

            if ( selectedPaymentMethod !== wc_checkout_form.selectedPaymentMethod ) {
                $( document.body ).trigger( 'payment_method_selected' );
            }

            wc_checkout_form.selectedPaymentMethod = selectedPaymentMethod;
        },
        toggle_create_account: function() {
            $( 'div.create-account' ).hide();

            if ( $( this ).is( ':checked' ) ) {
                // Ensure password is not pre-populated.
                $( '#account_password' ).val( '' ).trigger( 'change' );
                $( 'div.create-account' ).slideDown();
            }
        },
        init_checkout: function() {
            $( document.body ).trigger( 'update_checkout' );
        },
        maybe_input_changed: function( e ) {
            if ( wc_checkout_form.dirtyInput ) {
                wc_checkout_form.input_changed( e );
            }
        },
        input_changed: function( e ) {
            wc_checkout_form.dirtyInput = e.target;
            wc_checkout_form.maybe_update_checkout();
        },
        queue_update_checkout: function( e ) {
            var code = e.keyCode || e.which || 0;

            if ( code === 9 ) {
                return true;
            }

            wc_checkout_form.dirtyInput = this;
            wc_checkout_form.reset_update_checkout_timer();
            wc_checkout_form.updateTimer = setTimeout( wc_checkout_form.maybe_update_checkout, '1000' );
        },
        trigger_update_checkout: function() {
            wc_checkout_form.reset_update_checkout_timer();
            wc_checkout_form.dirtyInput = false;
            $( document.body ).trigger( 'update_checkout' );
        },
        maybe_update_checkout: function() {
            var update_totals = true;

            if ( $( wc_checkout_form.dirtyInput ).length ) {
                var $required_inputs = $( wc_checkout_form.dirtyInput ).closest( 'div' ).find( '.address-field.validate-required' );

                if ( $required_inputs.length ) {
                    $required_inputs.each( function() {
                        if ( $( this ).find( 'input.input-text' ).val() === '' ) {
                            update_totals = false;
                        }
                    });
                }
            }
            if ( update_totals ) {
                wc_checkout_form.trigger_update_checkout();
            }
        },
        ship_to_different_address: function() {
            $( 'div.shipping_address' ).hide();
            if ( $( this ).is( ':checked' ) ) {
                $( 'div.shipping_address' ).slideDown();
            }
        },
        reset_update_checkout_timer: function() {
            clearTimeout( wc_checkout_form.updateTimer );
        },
        is_valid_json: function( raw_json ) {
            try {
                var json = JSON.parse( raw_json );

                return ( json && 'object' === typeof json );
            } catch ( e ) {
                return false;
            }
        },
        validate_field: function( e ) {
            var $this             = $( this ),
                $parent           = $this.closest( '.form-row' ),
                validated         = true,
                validate_required = $parent.is( '.validate-required' ),
                validate_email    = $parent.is( '.validate-email' ),
                validate_phone    = $parent.is( '.validate-phone' ),
                pattern           = '',
                event_type        = e.type;

            if ( 'input' === event_type ) {
                $parent.removeClass( 'woocommerce-invalid woocommerce-invalid-required-field woocommerce-invalid-email woocommerce-invalid-phone woocommerce-validated' ); // eslint-disable-line max-len
            }

            if ( 'validate' === event_type || 'change' === event_type ) {

                if ( validate_required ) {
                    if ( 'checkbox' === $this.attr( 'type' ) && ! $this.is( ':checked' ) ) {
                        $parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
                        validated = false;
                    } else if ( $this.val() === '' ) {
                        $parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
                        validated = false;
                    }
                }

                if ( validate_email ) {
                    if ( $this.val() ) {
                        /* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
                        pattern = new RegExp( /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[0-9a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i ); // eslint-disable-line max-len

                        if ( ! pattern.test( $this.val() ) ) {
                            $parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-email woocommerce-invalid-phone' ); // eslint-disable-line max-len
                            validated = false;
                        }
                    }
                }

                if ( validate_phone ) {
                    pattern = new RegExp( /[\s\#0-9_\-\+\/\(\)\.]/g );

                    if ( 0 < $this.val().replace( pattern, '' ).length ) {
                        $parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-phone' );
                        validated = false;
                    }
                }

                if ( validated ) {
                    $parent.removeClass( 'woocommerce-invalid woocommerce-invalid-required-field woocommerce-invalid-email woocommerce-invalid-phone' ).addClass( 'woocommerce-validated' ); // eslint-disable-line max-len
                }
            }
        },
        update_checkout: function( event, args ) {
            // Small timeout to prevent multiple requests when several fields update at the same time
            wc_checkout_form.reset_update_checkout_timer();
            wc_checkout_form.updateTimer = setTimeout( wc_checkout_form.update_checkout_action, '5', args );
        },
        update_checkout_action: function( args ) {
            if ( wc_checkout_form.xhr ) {
                wc_checkout_form.xhr.abort();
            }

            if ( $( 'form.wrech-checkout' ).length === 0 ) {
                return;
            }

            args = typeof args !== 'undefined' ? args : {
                update_shipping_method: true
            };

            var country			 = $( '#billing_country' ).val(),
                state			 = $( '#billing_state' ).val(),
                postcode		 = $( ':input#billing_postcode' ).val(),
                city			 = $( '#billing_city' ).val(),
                address			 = $( ':input#billing_address_1' ).val(),
                address_2		 = $( ':input#billing_address_2' ).val(),
                s_country		 = country,
                s_state			 = state,
                s_postcode		 = postcode,
                s_city			 = city,
                s_address		 = address,
                s_address_2		 = address_2,
                $required_inputs = $( wc_checkout_form.$checkout_form ).find( '.address-field.validate-required:visible' ),
                has_full_address = true;

            if ( $required_inputs.length ) {
                $required_inputs.each( function() {
                    if ( $( this ).find( ':input' ).val() === '' ) {
                        has_full_address = false;
                    }
                });
            }

            if ( $( '#ship-to-different-address' ).find( 'input' ).is( ':checked' ) ) {
                s_country		 = $( '#shipping_country' ).val();
                s_state			 = $( '#shipping_state' ).val();
                s_postcode		 = $( ':input#shipping_postcode' ).val();
                s_city			 = $( '#shipping_city' ).val();
                s_address		 = $( ':input#shipping_address_1' ).val();
                s_address_2		 = $( ':input#shipping_address_2' ).val();
            }

            var data = {
                security        : wc_checkout_params.update_order_review_nonce,
                payment_method  : wc_checkout_form.get_payment_method(),
                country         : country,
                state           : state,
                postcode        : postcode,
                city            : city,
                address         : address,
                address_2       : address_2,
                s_country       : s_country,
                s_state         : s_state,
                s_postcode      : s_postcode,
                s_city          : s_city,
                s_address       : s_address,
                s_address_2     : s_address_2,
                has_full_address: has_full_address,
                post_data       : $( 'form.wrech-checkout' ).serialize(),
                easy_checkout: true
            };

            if ( false !== args.update_shipping_method ) {
                var shipping_methods = {};

                // eslint-disable-next-line max-len
                $( 'select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]' ).each( function() {
                    shipping_methods[ $( this ).data( 'index' ) ] = $( this ).val();
                } );

                data.shipping_method = shipping_methods;

                //data.action = 'wrech_update_order_review';
            }


            wc_checkout_form.xhr = $.ajax({
                type:		'POST',
                url:		wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'update_order_review' ),
                data:		data,
                success:	function( data ) {

                    // Reload the page if requested
                    if ( data && true === data.reload ) {
                        window.location.reload();
                        return;
                    }

                    if(data.success == false){
                        toastr.error(data.msg,'');
                    }

                    // Remove any notices added previously
                    $( '.woocommerce-NoticeGroup-updateOrderReview' ).remove();

                    var termsCheckBoxChecked = $( '#terms' ).prop( 'checked' );

                    // Save payment details to a temporary object
                    var paymentDetails = {};
                    $( '.payment_box :input' ).each( function() {
                        var ID = $( this ).attr( 'id' );

                        if ( ID ) {
                            if ( $.inArray( $( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                                paymentDetails[ ID ] = $( this ).prop( 'checked' );
                            } else {
                                paymentDetails[ ID ] = $( this ).val();
                            }
                        }
                    });

                    // Always update the fragments
                    if ( data && data.fragments ) {
                        $.each( data.fragments, function ( key, value ) {
                            if ( ! wc_checkout_form.fragments || wc_checkout_form.fragments[ key ] !== value ) {
                                $( key ).replaceWith( value );
                            }
                            $( key ).unblock();
                        } );
                        wc_checkout_form.fragments = data.fragments;
                    }

                    // Recheck the terms and conditions box, if needed
                    if ( termsCheckBoxChecked ) {
                        $( '#terms' ).prop( 'checked', true );
                    }

                    // Fill in the payment details if possible without overwriting data if set.
                    if ( ! $.isEmptyObject( paymentDetails ) ) {
                        $( '.payment_box :input' ).each( function() {
                            var ID = $( this ).attr( 'id' );
                            if ( ID ) {
                                if ( $.inArray( $( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                                    $( this ).prop( 'checked', paymentDetails[ ID ] ).trigger( 'change' );
                                } else if ( $.inArray( $( this ).attr( 'type' ), [ 'select' ] ) !== -1 ) {
                                    $( this ).val( paymentDetails[ ID ] ).trigger( 'change' );
                                } else if ( null !== $( this ).val() && 0 === $( this ).val().length ) {
                                    $( this ).val( paymentDetails[ ID ] ).trigger( 'change' );
                                }
                            }
                        });
                    }

                    // Check for error
                    if ( data && 'error' === data.type ) {

                        var $form = $( 'form.wrech-checkout' );

                        // Remove notices from all sources
                        $( '.woocommerce-error, .woocommerce-message' ).remove();

                        toastr.error(data.errors[0] , '')

                        // Lose focus for all fields
                        $form.find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).trigger( 'blur' );

                        //wc_checkout_form.scroll_to_notices();
                    }

                    // Re-init methods
                    wc_checkout_form.init_payment_methods();

                    // Fire updated_checkout event.
                    $( document.body ).trigger( 'updated_checkout', [ data ] );
                },
                beforeSend: function () {
                    $mask.css('display','block');
                },
                complete: function () {
                    $mask.css('display','none');
                },


            });
        },
        handleUnloadEvent: function( e ) {
            // Modern browsers have their own standard generic messages that they will display.
            // Confirm, alert, prompt or custom message are not allowed during the unload event
            // Browsers will display their own standard messages

            // Check if the browser is Internet Explorer
            if((navigator.userAgent.indexOf('MSIE') !== -1 ) || (!!document.documentMode)) {
                // IE handles unload events differently than modern browsers
                e.preventDefault();
                return undefined;
            }

            return true;
        },
        attachUnloadEventsOnSubmit: function() {
            $( window ).on('beforeunload', this.handleUnloadEvent);
        },
        detachUnloadEventsOnSubmit: function() {
            $( window ).off('beforeunload', this.handleUnloadEvent);
        },
        blockOnSubmit: function( $form ) {
            var isBlocked = $form.data( 'blockUI.isBlocked' );

            if ( 1 !== isBlocked ) {
                $form.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }
        },
        submitOrder: function() {
            wc_checkout_form.blockOnSubmit( $( this ) );
        },
        submit: function(e) {
            wc_checkout_form.reset_update_checkout_timer();
            var $form = $( this );

            e.preventDefault();

            if ( $form.is( '.processing' ) ) {
                return false;
            }

            // Trigger a handler to let gateways manipulate the checkout if needed
            // eslint-disable-next-line max-len
            if ( $form.triggerHandler( 'checkout_place_order' ) !== false && $form.triggerHandler( 'checkout_place_order_' + wc_checkout_form.get_payment_method() ) !== false ) {

                $form.addClass( 'processing' );

                wc_checkout_form.blockOnSubmit( $form );

                // Attach event to block reloading the page when the form has been submitted
                wc_checkout_form.attachUnloadEventsOnSubmit();

                // ajaxSetup is global, but we use it to ensure JSON is valid once returned.
                $.ajaxSetup( {
                    dataFilter: function( raw_response, dataType ) {
                        // We only want to work with JSON
                        if ( 'json' !== dataType ) {
                            return raw_response;
                        }

                        if ( wc_checkout_form.is_valid_json( raw_response ) ) {

                            return raw_response;
                        } else {
                            // Attempt to fix the malformed JSON
                            var maybe_valid_json = raw_response.match( /{"result.*}/ );

                            if ( null === maybe_valid_json ) {
                                console.log( 'Unable to fix malformed JSON' );
                            } else if ( wc_checkout_form.is_valid_json( maybe_valid_json[0] ) ) {
                                console.log( 'Fixed malformed JSON. Original:' );
                                raw_response = maybe_valid_json[0];
                            } else {
                                toastr.error( 'Unable to fix malformed JSON', '' );
                            }
                        }

                        return raw_response;
                    }
                } );


                $.ajax({
                    type:		'POST',
                    url:		wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'checkout' ),
                    data:		$form.serialize(),
                    dataType:   'json',
                    success:	function( result ) {
                        // Detach the unload handler that prevents a reload / redirect
                        wc_checkout_form.detachUnloadEventsOnSubmit();


                        try {
                            if ( 'success' === result.result && $form.triggerHandler( 'checkout_place_order_success', result ) !== false ) {
                                if ( -1 === result.redirect.indexOf( 'https://' ) || -1 === result.redirect.indexOf( 'http://' ) ) {
                                    window.location = result.redirect;
                                } else {
                                    window.location = decodeURI( result.redirect );
                                }
                            } else if ( 'failure' === result.result ) {
                                var messages = JSON.parse(result.messages);
                                toastr.error(messages.errors[0] , '');
                                wrech_modal.show_info();
                                throw 'Invalid Info';
                            } else {
                                throw 'Invalid response';
                            }

                        } catch( err ) {
                            // Reload page
                            if ( true === result.reload ) {
                                window.location.reload();
                                return;
                            }

                            // Trigger update in case we need a fresh nonce
                            if ( true === result.refresh ) {
                                $( document.body ).trigger( 'update_checkout' );
                            }

                            // Add new errors
                            if ( result.messages ) {

                                wc_checkout_form.submit_error( result.messages );
                            } else {
                                wc_checkout_form.submit_error( '<div class="woocommerce-error pepe">' + wc_checkout_params.i18n_checkout_error + '</div>' ); // eslint-disable-line max-len
                            }
                        }
                    },
                    error:	function( jqXHR, textStatus, errorThrown ) {
                        // Detach the unload handler that prevents a reload / redirect
                        wc_checkout_form.detachUnloadEventsOnSubmit();

                        wc_checkout_form.submit_error( '<div class="woocommerce-error tete">' + errorThrown + '</div>' );
                    }
                });
            }

            return false;
        },
        submit_error: function( error_message ) {
            $( '.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove();
            wc_checkout_form.$checkout_form.prepend( '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>' ); // eslint-disable-line max-len
            wc_checkout_form.$checkout_form.removeClass( 'processing' ).unblock();
            wc_checkout_form.$checkout_form.find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).trigger( 'blur' );
            wc_checkout_form.scroll_to_notices();
            $( document.body ).trigger( 'checkout_error' , [ error_message ] );
        },
        scroll_to_notices: function() {
            var scrollElement           = $( '.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout' );

            if ( ! scrollElement.length ) {
                scrollElement = $( '.form.wrech-checkout' );
            }
            $.scroll_to_notices( scrollElement );
        }
    };

    var wc_checkout_coupons = {
        init: function() {
          //  $( document.body ).on( 'click', 'a.showcoupon', this.show_coupon_form );
            $( document.body ).on( 'click', '.woocommerce-remove-coupon', this.remove_coupon );
            $( 'form.wrech-coupon-form' ).on( 'submit', this.submit );
        },
        show_coupon_form: function() {
            $( '.checkout_coupon' ).slideToggle( 400, function() {
                $( '.checkout_coupon' ).find( ':input:eq(0)' ).trigger( 'focus' );
            });
            return false;
        },
        submit: function(e) {

            var $form = $( this );

            if ( $form.is( '.processing' ) ) {
                return false;
            }

            var data = {
                security:		wc_checkout_params.apply_coupon_nonce,
                coupon_code:	$form.find( 'input[name="coupon_code"]' ).val(),
                easy_checkout: true
            };

            $.ajax({
                type:		'POST',
                url:		wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'apply_coupon' ),
                data:		data,
                success:	function( code ) {
                    $( '.woocommerce-error, .woocommerce-message' ).remove();
                    //$form.removeClass( 'processing' ).unblock();
                    $mask.css('display','none');

                    if(code.type === 'error'){
                        toastr.error(code.errors[0] , 'Error!');
                    }

                    if ( code.type === 'success' ) {

                        toastr.success(code.msg[0] , 'Success!');

                        $( document.body ).trigger( 'applied_coupon_in_checkout', [ data.coupon_code ] );
                        $( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );
                    }

                },
                beforeSend: function () {
                    $mask.css('display','block');
                },
                complete: function () {
                    $mask.css('display','none');
                },
                dataType: 'json'
            });

            return false;
        },
        remove_coupon: function( e ) {
            e.preventDefault();

            var container = $( this ).parents( '.woocommerce-checkout-review-order' ),
                coupon    = $( this ).data( 'coupon' );

            container.addClass( 'processing' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            var data = {
                security: wc_checkout_params.remove_coupon_nonce,
                coupon:   coupon,
                easy_checkout: true
            };

            $.ajax({
                type:    'POST',
                url:     wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_coupon' ),
                data:    data,
                success: function( code ) {
                    $( '.woocommerce-error, .woocommerce-message' ).remove();
                    container.removeClass( 'processing' ).unblock();

                    if ( code ) {
                        $( 'form.woocommerce-checkout' ).before( code );

                        $( document.body ).trigger( 'removed_coupon_in_checkout', [ data.coupon_code ] );
                        $( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );

                        // Remove coupon code from coupon field
                        $( 'form.checkout_coupon' ).find( 'input[name="coupon_code"]' ).val( '' );
                    }
                },
                error: function ( jqXHR ) {
                    console.log( jqXHR.responseText );
                },
                dataType: 'html'
            });
        }
    };

    var wc_checkout_login_form = {
        init: function() {
            $( document.body ).on( 'click', 'a.showlogin', this.show_login_form );
        },
        show_login_form: function() {
            $( 'form.login, form.woocommerce-form--login' ).slideToggle();
            return false;
        }
    };

    var wc_terms_toggle = {
        init: function() {
            $( document.body ).on( 'click', 'a.woocommerce-terms-and-conditions-link', this.toggle_terms );
        },

        toggle_terms: function() {
            if ( $( '.woocommerce-terms-and-conditions' ).length ) {
                $( '.woocommerce-terms-and-conditions' ).slideToggle( function() {
                    var link_toggle = $( '.woocommerce-terms-and-conditions-link' );

                    if ( $( '.woocommerce-terms-and-conditions' ).is( ':visible' ) ) {
                        link_toggle.addClass( 'woocommerce-terms-and-conditions-link--open' );
                        link_toggle.removeClass( 'woocommerce-terms-and-conditions-link--closed' );
                    } else {
                        link_toggle.removeClass( 'woocommerce-terms-and-conditions-link--open' );
                        link_toggle.addClass( 'woocommerce-terms-and-conditions-link--closed' );
                    }
                } );

                return false;
            }
        }
    };

    var wc_add_to_cart = {
        init:function(){
            $( document.body )
                .on( 'click', '.add_to_cart_button,.wrech_add_to_cart_button', this.add_to_cart )
                .on( 'click', '.single_add_to_cart_button', this.add_to_cart )
                .on( 'click', '.wrech_cart_item_remove', this.remove_cart_item)
                .on( 'change', '.wrech-quantity', this.update_cart_item_quantity)
                .on( 'click', '.wrech-quantity-increase', this.increment_quantity)
                .on( 'click', '.wrech-quantity-decrease', this.decrement_quantity);

        },
        remove_cart_item(e){

            $remove_link = $(this);
            if($remove_link.data('product_id') && $remove_link.data('cart_item_key')){
                e.preventDefault();

                var data = {
                    action: 'wrech_remove_item_cart',
                    product_id: $remove_link.data('product_id'),
                    cart_item_key: $remove_link.data('cart_item_key'),
                };

                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.ajax_url,
                    data: data,
                    success: function( response ) {
                        if ( ! response ) {
                            return;
                        }

                        wc_add_to_cart.refresh_cart_wrapper(response.html);
                        wc_checkout_form.trigger_update_checkout();

                        wc_add_to_cart.update_cart_count(response.data.count);

                        // Trigger event so themes can refresh other areas. such mini cart.
                        $(document.body).trigger("wc_fragment_refresh");
                        $( document.body ).trigger( 'removed_from_cart', [ response.data.fragments, response.data.cart_hash ] );

                    },
                    beforeSend: function () {
                        $mask.css('display','block');
                    },
                    complete: function () {
                        $mask.css('display','none');
                    },
                    error: function ( jqXHR ) {
                         toastr.error('The cart item was not removed', 'Error')
                    },
                    dataType: 'json'
                });
            }

        },
        refresh_cart_wrapper(html){
            $('.wrech-step-cart').empty();
            $('.wrech-step-cart').html(html);
        },
        add_to_cart(e){

            var $thisbutton = $(this);
            var data = {};

            if($thisbutton.hasClass('product_type_variable')){
                return true;
            }

            if($thisbutton.hasClass('single_add_to_cart_button')){
                if($thisbutton.attr('value')){
                    data['product_id'] = $thisbutton.attr('value');
                }else{
                    var product_id = $('input[name="product_id"]').val();
                    var variation_id = $('input[name="variation_id"]').val();

                    if(product_id !== undefined && variation_id !== undefined){
                        data['product_id'] = variation_id;
                        //data['variation_id'] = variation_id;
                    }else{
                        return true;
                    }
                }
            }

            //Check quantity
            if($qty = $('.quantity [name="quantity"]')){
                data['quantity'] = $qty.val();
            }

            if($thisbutton.hasClass('add_to_cart_button') || $thisbutton.hasClass('wrech_add_to_cart_button')){
               if(wc_checkout_params.cart_ajax_archive !== 'yes'){ return true; }
                data['product_id'] = $thisbutton.data('product_id');
                data['quantity'] = $thisbutton.data('quantity');
            }

            e.preventDefault();

            $thisbutton.removeClass( 'added' );
            $thisbutton.addClass( 'loading' );

            // Fetch changes that are directly added by calling $thisbutton.data( key, value )
            $.each( $thisbutton.data(), function( key, value ) {
                data[ key ] = value;
            });

            data['action'] = 'wrech_add_to_cart';

            //$(document.body).trigger("wc_fragment_refresh");
            //$( document.body ).trigger( 'adding_to_cart', [ $thisbutton, data ] );
            $.ajax({
                type: 'POST',
                url: wc_checkout_params.ajax_url,
                data: data,
                success: function( response ) {
                    if ( ! response ) {
                        return;
                    }

                    wc_add_to_cart.refresh_cart_wrapper(response.html);

                    wc_checkout_form.trigger_update_checkout();

                    wc_add_to_cart.update_cart_count(response.data.count);

                    // Redirect to cart option
                    if ( wc_checkout_params.cart_redirect_after_add === 'yes' ) {
                        window.location = wc_add_to_cart_params.cart_url;
                        return;
                    }

                    $thisbutton.removeClass( 'loading' );
                    $thisbutton.addClass( 'added' );
``
                    // Trigger event so themes can refresh other areas.
                    $( document.body ).trigger( 'added_to_cart', [ response.data.fragments, response.data.cart_hash, $thisbutton ] );

                },
                beforeSend: function () {
                    $mask.css('display','block');
                },
                complete: function () {
                    $mask.css('display','none');
                },
                dataType: 'json'
            });
        },
        update_cart_count(count){
            $('.wrech-cart-count').text(count);
        },
        update_cart_item_quantity(e){
            var $qty = $(this);
            wc_add_to_cart.change_quantity($qty);
        },
        increment_quantity(){
            var $qty = $(this).prev('.wrech-quantity');

            wc_add_to_cart.change_quantity($qty,1);
        },
        decrement_quantity(){
            var $qty = $(this).next('.wrech-quantity');

            wc_add_to_cart.change_quantity($qty,-1);
        },
        change_quantity($qty, incre_decre = 0){

            var data = {};

            data['action'] = 'wrech_update_quantity_item';
            data['cart_quantity'] = parseInt($qty.val()) + (1*incre_decre);
            data['cart_item_key'] = $qty.data('cart_item_key');

            // Trigger event.
           // $(document.body).trigger("wc_fragment_refresh");
            $.ajax({
                type: 'POST',
                url: wc_checkout_params.ajax_url,
                data: data,
                success: function( response ) {
                    if ( ! response ) {
                        return;
                    }

                    $qty.val(response.quantity);

                    wc_add_to_cart.refresh_cart_wrapper(response.html);

                    wc_checkout_form.trigger_update_checkout();

                    wc_add_to_cart.update_cart_count(response.data.count);

                    // Trigger event so themes can refresh other areas.
                    $( document.body ).trigger( 'added_to_cart', [ response.data.fragments, response.data.cart_hash, null ] );

                },
                beforeSend: function () {
                    $mask.css('display','block');
                },
                complete: function () {
                    $mask.css('display','none');
                },
                dataType: 'json'
            });
        }
    }

    var wrech_modal = {
        $checkout_modal: $('.wrech-modal'),
        $mask: $('.wrech-mask-container'),
        $close: $('.wrech-close-modal'),
        $open: $('.wrech-float-btn'),
        $step_cart: $('.wrech-step-cart'),
        $step_info: $('.wrech-step-info'),
        $step_payment: $('.wrech-step-payment'),
        $car_btn: $('.wrech-cart-btn'),
        $order_info_btn: $('.wrech-order-info-btn'),
        $payment_btn: $('.wrech-payment-btn'),
        init: function() {

            //defaults
            this.$close.on( 'click', this.hide_modal );
            this.$open.on( 'click', this.show_modal );

            //step logic
            this.$car_btn.on( 'click', this.show_cart );
            this.$order_info_btn.on( 'click', this.show_info );
            this.$payment_btn.on( 'click', this.show_payment );
            wrech_modal.initialStepsState();
            wrech_modal.$step_cart.show();

        },
        show_cart(){
            //wc_checkout_form.$checkout_form.removeClass('woocommerce-checkout');
            wrech_modal.initialStepsState();
            wrech_modal.$step_cart.show();

        },
        check_empty_cart(){
            if($('.wrech_mini_cart_item').length === 0){
                wrech_modal.initialStepsState();
                wrech_modal.$step_cart.show();
                toastr.error('There is not items on the cart' , '');
                return true;
            }
            return false;
        },
        show_info(){

            if(!wrech_modal.check_empty_cart()){
                wrech_modal.initialStepsState();
                wrech_modal.$step_info.show();
            }
        },
        show_payment(){

            if(!wrech_modal.check_empty_cart()){
                wrech_modal.initialStepsState();
                wrech_modal.$step_payment.show();
            }
        },
        hide_modal: function() {
            wrech_modal.$checkout_modal.animate({width: 'toggle'}, {duration: 500})
            wrech_modal.$open.show();
        },
        show_modal(){
            wrech_modal.$open.hide();
            wrech_modal.$checkout_modal.animate({width: 'toggle'}, {duration: 500})
        },
        initialStepsState(){
            $('.wrech-step').hide();
        },
        check_info_form(){

        }
    };

    wrech_modal.init();

    wc_checkout_form.init();
    wc_checkout_coupons.init();
    wc_checkout_login_form.init();
    wc_terms_toggle.init();
    wc_add_to_cart.init();

});
