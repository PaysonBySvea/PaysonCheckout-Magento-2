<?php
namespace PaysonAB\PaysonCheckout2\Controller\Payson;

use Exception;

/**
 * Class Index
 *
 * @package PaysonAB\PaysonCheckout2\Controller\Payson
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $paysonConfig;
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $_response;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder
     */
    protected $_buildOrder;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi
     */
    protected $_paysonApi;
    /**
     * @var string
     */
    protected $_blockName = 'payson.index';

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\View\Result\PageFactory         $resultPageFactory
     * @param \PaysonAB\PaysonCheckout2\Helper\Order             $orderHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Data              $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Model\Config             $paysonConfig
     * @param \Magento\Framework\App\Response\Http               $response
     * @param \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder
     * @param \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi      $paysonApi
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \Magento\Framework\App\Response\Http $response,
        \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder,
        \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi $paysonApi
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_orderHelper = $orderHelper;
        $this->_paysonHelper = $paysonHelper;
        $this->paysonConfig = $paysonConfig;
        $this->_url = $context->getUrl();
        $this->_response = $response;
        $this->_buildOrder = $buildOrder;
        $this->_paysonApi = $paysonApi;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $paysonLoggerHelper  = $this->_paysonHelper;
            $resultPage = $this->resultPageFactory->create();
            if ($this->paysonConfig->isEnabled()) {
                if (!$this->_orderHelper->hasActiveQuote()) {
                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson/emptycart');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    return $resultPage;
                }

                $validate = $this->_paysonApi->Validate();
                if ($validate->status == "Approved") {
                    $quote = $this->_orderHelper->getQuote();
                    $checkoutId = $quote->getData(\PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN);
                    $quote = $this->_buildOrder->saveShippingMethod($quote);
                    if (is_object($quote)) {
                        $this->_buildOrder->paysonSaveInformation($quote, $checkoutId);
                    }
                }
            } else {
                $checkoutPaysonUrl = $this->_url->getUrl('checkout/cart');
                $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                return $resultPage;
            }
            return $resultPage;
        } catch (\Exception $e) {
            $setBlock = $this->_view->getLayout()->getBlock($this->_blockName);
            $setBlock->setPaysonAuthError($e->getMessage());
            $paysonLoggerHelper->log($e->getMessage());
            return $resultPage;
        }
    }
}
