/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url'
    ],
    function (Component, additionalValidators, url) {
        'use strict';

        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Payer_Checkout/payment/payer_checkout-form',
            },
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }
                if (this.validate() && additionalValidators.validate()) {
                    console.log(data);
                    console.log(event);
                    window.location.replace(url.build('payer/checkout/redirect'));
                    return true;
                }
                return false;
            },
        });
    }
);