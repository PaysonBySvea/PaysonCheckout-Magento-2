<?php
namespace Eastlane\PaysonCheckout2\Controller\Payson;
/**
 * Class Cancel
 *
 * @package Eastlane\PaysonCheckout2\Controller\Payson
 */

use Exception;
use Magento\Framework\App\ResponseInterface;

class CancelOrder extends Confirmation
{
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
        $resultPage = $this->resultPageFactory->create();
        try {
            $session = $this->getOnepage()->getCheckout();
            if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
                return $this->resultRedirectFactory->create()->setPath('checkout/payson');
            }
            $this->_coreRegistry->register('cancelPaysonCheckoutId', $session->getLastRealOrder()->getPaysonCheckoutId());
            $session->clearQuote();
        } catch (\Exception $e) {
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }
        return $resultPage;
    }
}