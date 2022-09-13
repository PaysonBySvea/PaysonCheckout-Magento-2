<?php

namespace PaysonAB\PaysonCheckout2\Block\Payson;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi
     */
    protected $_paysonApi;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder
     */
    protected $_buildOrder;

    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $_config;
    /**
     * Index constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi      $paysonApi
     * @param \PaysonAB\PaysonCheckout2\Helper\DataLogger        $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order             $orderHelper
     * @param \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi $paysonApi,
        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder,
        \PaysonAB\PaysonCheckout2\Model\Config $config
    ) {
        $this->_paysonApi = $paysonApi;
        $this->_storeManager = $context->getStoreManager();
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
            $paysonLoggerHelper->debug($e);
            $paysonLoggerHelper->info($e->getMessage());
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
