magento2-Payer_Checkout
======================

Payer Checkout payment gateway Magento2 extension

Install
=======

1. Go to Magento2 root folder

2. Enter following commands to install module:

    ```bash
    composer config repositories.payercheckout git https://github.com/mikaelwallgren/magento2-Payer_Checkout.git
    composer require payer/checkout:dev-master
    ```
   Wait while dependencies are updated.

3. Enter following commands to enable module:

    ```bash
    php bin/magento module:enable Payer_Checkout --clear-static-content
    php bin/magento setup:upgrade
    ```
4. Enable and configure Payer Checkout in Magento Admin under Stores/Configuration/Payment Methods/Payer Checkout
