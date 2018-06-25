<?php
namespace PaysonAB\PaysonCheckout2\Observer\Sales;

use Magento\Checkout\Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BeforeInvoice implements ObserverInterface
{

    protected $_request;
    protected $_orderRepository;
    protected $_messageManager;
    protected $_url;
    protected $_response;
    protected $_paysonHelper;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $response,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper
    ) {
        $this->_request = $request;
        $this->_orderRepository = $orderRepository;
        $this->_messageManager = $messageManager;
        $this->_url = $url;
        $this->_response = $response;
        $this->_paysonHelper= $paysonHelper;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $items = $observer->getEvent()->getInvoice()->getOrder()->getAllItems();
        $order = $this->_orderRepository->get($this->_request->getParam('order_id'));
        if(($order->getPayment()->getMethodInstance()->getCode() == \PaysonAB\PaysonCheckout2\Model\Paysoncheckout2ConfigProvider::CHECKOUT_CODE)) {
            $message = 'Order place with payson checkout you can not create multiple invoice.';
            foreach ($items as $item)
            {
                switch ($item->getProduct()->getTypeId()) {
                case 'simple':
                    if($item->getParentItemId() == null) {
                        if((int) $item->getQtyOrdered() != (int) $item->getQtyInvoiced()) {
                            $this->_messageManager->addError(__($message));
                            $checkoutPaysonUrl = $this->_url->getUrl('*/*/new', ['order_id' => $this->_request->getParam('order_id')]);
                            $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                            throw new \Exception($message);
                        }
                    } else {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $product = $objectManager->create('\Magento\Sales\Model\Order\ItemRepository')->get($item->getParentItemId());

                        if($product->getProductType() == "bundle") {
                            if((int) $item->getQtyOrdered() != (int) $item->getQtyInvoiced() ) {
                                $this->_messageManager->addError(__($message));
                                $checkoutPaysonUrl = $this->_url->getUrl('*/*/new', ['order_id' => $this->_request->getParam('order_id')]);
                                $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                                throw new \Exception($message);
                            }
                        }
                    }
                    break;
                case 'configurable':
                    if((int) $item->getQtyOrdered() != (int) $item->getQtyInvoiced()) {
                        $this->_messageManager->addError(__($message));
                        $checkoutPaysonUrl = $this->_url->getUrl('*/*/new', ['order_id' => $this->_request->getParam('order_id')]);
                        $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                        throw new \Exception($message);
                    }
                    break;
                case 'bundle':
                    continue;
                        break;
                default:
                    continue;
                        break;
                }
            }
        }
    }
}