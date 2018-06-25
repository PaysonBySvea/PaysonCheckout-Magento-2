<?php
namespace Eastlane\PaysonCheckout2\Controller\Payson;
/**
 * Class PaysonRespone
 *
 * @package Eastlane\PaysonCheckout2\Controller\Payson
 */
class PaysonRespone extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var
     */
    protected $_checkoutSession;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * PaysonRespone constructor.
     *
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory
     * @param \Eastlane\PaysonCheckout2\Helper\Order           $orderHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Eastlane\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_orderHelper = $orderHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->paysonConfig = $this->_objectManager->get('\Eastlane\PaysonCheckout2\Model\Config');

        if ($this->paysonConfig->isEnabled()) {
            $quote =  $this->_orderHelper->getQuote();
            $callPaysonApi = $this->_orderHelper->getApi();
            $checkout = $callPaysonApi->GetCheckout($quote->getPaysonCheckoutId());
            if($checkout->status == 'readyToPay') {
                return $this->updateRespons($checkout);
            }
            $shippingMethod = $this->getRequest()->getParam('shipping_method');
            $this->_orderHelper->setShippingMethod($shippingMethod);

            $this->_quoteObj = $quote;
            $shippingMethodName = $shippingMethod['carrier_code'].'_'.$shippingMethod['method_code'];

            $shippingAddress = $this->_quoteObj->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->setShippingMethod($shippingMethodName);
            $this->_quoteObj->setTotalsCollectedFlag(false);
            $this->_quoteObj->collectTotals()->save();


            $this->_orderHelper->updateCart($quote->getPaysonCheckoutId());

            $checkout = $callPaysonApi->GetCheckout($quote->getPaysonCheckoutId());
            return $this->updateRespons($checkout);
        }
    }

    public function updateRespons($checkout)
    {
        $result = $this->_resultJsonFactory->create();
        return $result->setData(['success' => $checkout->snippet]);
    }
}
