/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'payer_checkout_invoice',
                component: 'Payer_Checkout/js/view/payment/method-renderer/payer_checkout_invoice-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);