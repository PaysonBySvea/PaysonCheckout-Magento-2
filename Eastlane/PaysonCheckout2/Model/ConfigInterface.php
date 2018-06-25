<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eastlane\PaysonCheckout2\Model;

/**
 * Eastl;ane module configuration
 */
interface ConfigInterface
{
    /**
     * Enabled config path
     */
    const ENABLED = 'payment/paysoncheckout2/enabled';
    const PAYSONCHECKOUT_TITLE = 'payment/paysoncheckout2/title';
    const TESTMODE = 'payment/paysoncheckout2/test';
    const MERCHANT_ID = 'payment/paysoncheckout2/agent_id';
    const API_KEY = 'payment/paysoncheckout2/api_key';
    const REQUEST_URL = 'payment/paysoncheckout2/request_url';
    const TERMS_URL = 'payment/paysoncheckout2/terms_url';
    const THEME = 'payment/paysoncheckout2/checkout_theme';
    const CHECKOUT_ID_COLUMN = 'payson_checkout_id';
    const CAPTURE_IN_MAGENTO = 'payment/paysoncheckout2/capture_from_magento';

    const TEST_MERCHANT_ID = '4';
    const TEST_API_KEY = '2acab30d-fe50-426f-90d7-8c60a7eb31d4';

    const ALLOWSPECIFIC = 'payment/paysoncheckout2/allowspecific';
    const COUNTRY_CODE = 'payment/paysoncheckout2/specificcountry';


    /**
     * Check if payson module is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * fetch Checkout Merchant id, used for Checkout order type
     *
     * @return string
     */
    public function getCheckoutMerchantId();

    /**
     * fetch Checkout Secret word, used for Checkout order type
     *
     * @return string
     */
    public function getApiKey();

    /**
     * get magento general locale configuration
     *
     * @return string
     */
    public function getLocale();


    /**
    * fetch payson module request url
    *
    * @return string
    */

    public function getApiUrl();

    /**
    * fetch payson module terms url
    *
    * @return string
    */

    public function getTermsUrl();

    /**
    * fetch payson module payson checkout 2 theme
    *
    * @return string
    */

    public function getTheme();

    /**
     * fetch payson module capture configuration
     *
     * @return bool
    */
    public  function getCaptureInMagento();

    /**
     * Check if payson module is sandbox mode
     *
     * @return bool
     */
    public function isTestmode();


    /**
     * @return mixed
     */
    public function allowspecific();

    /**
     * @return mixed
     */
    public function countryCode();

    /**
     * @return mixed
     */
    public function getPaysoncheckoutTitle();
}
