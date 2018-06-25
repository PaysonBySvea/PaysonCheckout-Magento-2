<?php

namespace Eastlane\PaysonCheckout2\Block\Payson;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Eastlane\PaysonCheckout2\Model\Api\PaysonApi
     */
    protected $_paysonApi;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Eastlane\PaysonCheckout2\Model\Payment\BuildOrder
     */
    protected $_buildOrder;

    /**
     * @var \Eastlane\PaysonCheckout2\Model\Config
     */
    protected $_config;
    /**
     * Index constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Eastlane\PaysonCheckout2\Model\Api\PaysonApi      $paysonApi
     * @param \Eastlane\PaysonCheckout2\Helper\Data              $paysonHelper
     * @param \Eastlane\PaysonCheckout2\Helper\Order             $orderHelper
     * @param \Eastlane\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Eastlane\PaysonCheckout2\Model\Api\PaysonApi $paysonApi,
        \Eastlane\PaysonCheckout2\Helper\Data $paysonHelper,
        \Eastlane\PaysonCheckout2\Helper\Order $orderHelper,
        \Eastlane\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder,
        \Eastlane\PaysonCheckout2\Model\Config $config
    ) {
        $this->_paysonApi = $paysonApi;
        $this->_storeManager = $storeManager;
        $this->_paysonHelper = $paysonHelper;
        $this->_orderHelper = $orderHelper;
        $this->_buildOrder = $buildOrder;
        $this->_config = $config;
        parent::__construct($context);
    }


    /**
     * @param $quoteId
     * @return bool|mixed
     */
    public function paysonResponse($quoteId)
    {
        try {
            $payson = $this->_buildOrder->paysonCollection($quoteId);
            if (!empty($payson['id'])) {
                return json_decode($payson['payson_response']);
            }
            return false;
        } catch (\Exception $e) {
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->critical($e);
            $paysonLoggerHelper->log($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function getIframeSnippet()
    {
        if ($this->_orderHelper->hasActiveQuote()) {
            $checkout = $this->paysonResponse($this->_orderHelper->getQuote()->getId());
            if ($checkout) {
                return $checkout->snippet;
            }
            return false;
        }
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        $quote = $this->_orderHelper->getQuote();
        return $this->_buildOrder->getShippingMethods($quote);
    }

    /**
     * @return mixed
     */
    public function getAuthentication()
    {
        return $this->getPaysonAuthError();
    }

    public function getTitle()
    {
        return $this->_config->getPaysoncheckoutTitle();
    }

}
