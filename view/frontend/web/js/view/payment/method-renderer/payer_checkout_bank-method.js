/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'ko',
        'jquery'
    ],
    function (Component, additionalValidators, url, customer, quote, ko, $) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Payer_Checkout/payment/payer_checkout_bank-form',
            },
            placeOrder: function (data, event) {
                var self = this;
                if (event) {
                    event.preventDefault();
                }
                if (this.validate() && additionalValidators.validate()) {
                    if (customer.isLoggedIn()) {
                        window.location.replace(url.build('payer/checkout/redirect') + '?payer_method=bank');
                    }
                    else {
                        window.location.replace(url.build('payer/checkout/redirect') + '?payer_method=bank&guestEmail='+quote.guestEmail);
                    }
                    return true;
                }
                return false;
            },
        });
    }
);