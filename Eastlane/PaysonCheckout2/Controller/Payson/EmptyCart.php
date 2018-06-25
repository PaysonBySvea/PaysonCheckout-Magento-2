<?php
namespace Eastlane\PaysonCheckout2\Controller\Payson;

use Exception;

/**
 * Class EmptyCart
 *
 * @package Eastlane\PaysonCheckout2\Controller\Payson
 */
class EmptyCart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Eastlane\PaysonCheckout2\Model\Config
     */
    protected $_paysonConfig;

    /**
     * EmptyCart constructor.
     *
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Eastlane\PaysonCheckout2\Helper\Order     $orderHelper
     * @param \Eastlane\PaysonCheckout2\Helper\Data      $paysonHelper
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \Eastlane\PaysonCheckout2\Model\Config     $paysonConfig
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Eastlane\PaysonCheckout2\Helper\Order $orderHelper,
        \Eastlane\PaysonCheckout2\Helper\Data $paysonHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Eastlane\PaysonCheckout2\Model\Config $paysonConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_orderHelper = $orderHelper;
        $this->_paysonHelper = $paysonHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_paysonConfig = $paysonConfig;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try{
            $resultPage = $this->resultPageFactory->create();
            if ($this->_paysonConfig->isEnabled()) {
                if (!$this->_orderHelper->hasActiveQuote()) {
                    $this->_checkoutSession->clearQuote();
                    return $resultPage;
                }
            }
        } catch (\Exception $e) {
            $this->_paysonHelper->error($e->getMessage());
        }
        return $resultPage;
    }
}
