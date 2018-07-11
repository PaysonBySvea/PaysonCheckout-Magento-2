# Payson Checkout 2.0 for Magento 2
==================
## Magento Marketplace
* https://marketplace.magento.com/paysonab-module-paysoncheckout2.html

## Manual installation
*	Download the file PaysonAB_PaysonCheckout2.zip
*	Extract the file to <magento_install>/app/code/, after extraction you should see <magento_install>/app/code/PaysonAB/
Run commands:
*   php bin/magento module:enable PaysonAB_PaysonCheckout2 --clear-static-content
*   php bin/magento setup:upgrade
*   php bin/magento setup:di:compile
*   php bin/magento cache:flush
