<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eastlane\PaysonCheckout2\Model;

use Exception;
use Eastlane\PaysonCheckout2\Exception\ExceptionCodeList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Contact module configuration
 */
class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * Merchant identifier assigned to client by Payson.
     *
     * @var string $merchantId
     */
    protected $merchantId;
    /**
     * @var
     */
    protected $apiKey;
    /**
     * @var
     */
    protected $apiUrl;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->validateData();
    }

    /**
     * Validate Client credentials data.
     */
    private function validateData()
    {
        if($this->isEnabled()){
            $this->validateMerchantId();
            $this->validateApiKey();
            //$this->validateApiUrl();
            $this->validateTermsUrl();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function validateMerchantId()
    {
        if (empty($valid = $this->getCheckoutMerchantId())) {
            $valid = self::TEST_MERCHANT_ID;
        }
        return $valid;
    }

    /**
     * Validate API Key
     *
     * Message Display If api key is empty
     */
    public function validateApiKey()
    {
        if (empty($valid = $this->getApiKey())) {
            $valid = self::TEST_API_KEY;
        }
        return $valid;
    }

    /**
     * Validate API Key
     *
     * Message Display If api key is empty
     */
    /*public function validateApiUrl()
    {
        if (empty($valid = $this->getApiUrl())) {
            throw new \Exception(
                ExceptionCodeList::getErrorMessage(ExceptionCodeList::MISSING_REQUEST_URL),
                ExceptionCodeList::MISSING_REQUEST_URL
            );
        }
        return $valid;
    }*/

    /**
     * Get checkout Merchant ID from database.
     *
     * @return string API integration Checkout Merchant ID
     */
    public function getCheckoutMerchantId()
    {
        return $this->merchantId = $this->scopeConfig->getValue(
            self::MERCHANT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get checkout Secret from database.
     *
     * @return string API integration Secret
     */
    public function getApiKey()
    {
        return $this->apiKey = $this->scopeConfig->getValue(
            self::API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */

    public function getApiUrl()
    {
        return $this->apiUrl = $this->scopeConfig->getValue(self::REQUEST_URL, ScopeInterface::SCOPE_STORE);
    }

    
    /**
     * {@inheritdoc}
     */
    public function getTheme()
    {
        return $this->scopeConfig->getValue(self::THEME, ScopeInterface::SCOPE_STORE);
    }

    
    /**
     * Validate API Key
     *
     * Message Display If api key is empty
     */
    public function validateTermsUrl()
    {
        if (empty($valid = $this->getTermsUrl())) {
            throw new \Exception(
                ExceptionCodeList::getErrorMessage(ExceptionCodeList::MISSING_TERMS_URL),
                ExceptionCodeList::MISSING_TERMS_URL
            );
        }
        return $valid;
    }

    /**
     * @return mixed
     */
    public function getTermsUrl()
    {
        return $this->scopeConfig->getValue(self::TERMS_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getShippingTaxClass()
    {
        return $this->scopeConfig->getValue('tax/classes/shipping_tax_class', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get capture enabled or disbaled.
     *
     * @return bool
     */
    public function getCaptureInMagento()
    {
        return $this->scopeConfig->getValue(self::CAPTURE_IN_MAGENTO, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isTestmode()
    {
        return $this->scopeConfig->isSetFlag(
            self::TESTMODE,
            ScopeInterface::SCOPE_STORE
        );        
    }

    /**
     * @return bool
     */
    public function allowspecific()
    {
        return $this->scopeConfig->isSetFlag(
            self::ALLOWSPECIFIC,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function countryCode()
    {
        return $this->countryCode = $this->scopeConfig->getValue(
            self::COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getPaysoncheckoutTitle()
    {
        return $this->scopeConfig->getValue(
            self::PAYSONCHECKOUT_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
