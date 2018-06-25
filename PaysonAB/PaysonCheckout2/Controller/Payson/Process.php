<?php
namespace PaysonAB\PaysonCheckout2\Controller\Payson;

use Exception;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Process
 *
 * @package PaysonAB\PaysonCheckout2\Controller\Payson
 */
class Process extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $_paysonConfig;

    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data
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
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \PaysonAB\PaysonCheckout2\Model\Config     $paysonConfig
     * @param \PaysonAB\PaysonCheckout2\Helper\Data      $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order     $orderHelper
     * @param \Magento\Framework\App\Action\Context      $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
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
                    $block = $resultPage->getLayout()->getBlock('payson.process');
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
            $this->_paysonHelper->error($e->getMessage());
        }
    }
}