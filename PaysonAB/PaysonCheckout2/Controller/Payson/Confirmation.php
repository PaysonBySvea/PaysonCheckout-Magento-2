<?php
namespace PaysonAB\PaysonCheckout2\Controller\Payson;

use Exception;

/**
 * Class Confirmation
 *
 * @package PaysonAB\PaysonCheckout2\Controller\Payson
 */
class Confirmation extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data (Deprecated)
     * @var \PaysonAB\PaysonCheckout2\Helper\DataLogger
     */
    protected $_paysonHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Confirmation constructor.
     *
     * @param \Magento\Framework\App\Action\Context       $context
     * @param \Magento\Framework\View\Result\PageFactory  $resultPageFactory
     * @param \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper
     * @param \Magento\Framework\Registry                 $coreRegistry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_paysonHelper = $paysonHelper;
        $this->_coreRegistry = $coreRegistry;
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
        try {
            $session = $this->getOnepage()->getCheckout();
            
            if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
                return $this->resultRedirectFactory->create()->setPath('checkout/payson');
            }
            $session->clearQuote();
        } catch (\Exception $e) {
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->debug($e->getMessage());
        }
        return $resultPage;
    }

    /**
    * Get one page checkout model
    *
    * @return             \Magento\Checkout\Model\Type\Onepage
    * @codeCoverageIgnore
    */
    public function getOnepage()
    {
        return $this->_objectManager->get(\Magento\Checkout\Model\Type\Onepage::class);
    }
}
