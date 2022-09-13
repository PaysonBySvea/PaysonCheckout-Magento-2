<?php
namespace PaysonAB\PaysonCheckout2\Controller\Payson;

use Exception;
use Magento\Framework\App\ResponseInterface;

/**
 * Class CancelProcess
 *
 * @package PaysonAB\PaysonCheckout2\Controller\Payson
 */
class CancelProcess extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $_paysonConfig;

    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data (Deprecated)
     * @var \PaysonAB\PaysonCheckout2\Helper\DataLogger
     */
    protected $_paysonHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;

    /**
     * Process constructor.
     *
     * @param \Magento\Framework\View\Result\PageFactory  $resultPageFactory
     * @param \PaysonAB\PaysonCheckout2\Model\Config      $paysonConfig
     * @param \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order      $orderHelper
     * @param \Magento\Framework\App\Action\Context       $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_paysonConfig = $paysonConfig;
        $this->_paysonHelper = $paysonHelper;
        $this->_orderHelper = $orderHelper;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try{
            $resultPage = $this->_resultPageFactory->create();
            $quoteId = $this->getRequest()->getParam('id', null);
            if ($this->_paysonConfig->isEnabled() && !is_null($quoteId)) {
                if($this->_orderHelper->getQuote()->getId() == $quoteId) {
                    $resultPage->getConfig()->getTitle()->set('Payson Processing');
                    $block = $resultPage->getLayout()->getBlock('payson.cancelprocess');
                    $block->setQuoteId($quoteId);
                    return $resultPage;
                }
                /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect 
*/
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('checkout/payson');
            }
            /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect 
*/
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('checkout/cart/', ['_current' => true, 'id' => null]);
        } catch (\Exception $e) {
            $this->_paysonHelper->debug($e->getMessage());
        }
    }
}
