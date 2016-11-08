/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'ko',
        'jquery'
    ],
    function (Component, additionalValidators, url, ko, $) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Payer_Checkout/payment/payer_checkout_all-form',
            },
            placeOrder: function (data, event) {
                var self = this;
                if (event) {
                    event.preventDefault();
                }
                if (this.validate() && additionalValidators.validate()) {
                    window.location.replace(url.build('payer/checkout/redirect')+'?payer_method=all');
                    return true;
                }
                return false;
            },
        });
    }
);