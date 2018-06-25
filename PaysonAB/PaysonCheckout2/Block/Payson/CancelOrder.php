<?php

namespace PaysonAB\PaysonCheckout2\Block\Payson;
/**
 * Class CancelOrder
 *
 * @package PaysonAB\PaysonCheckout2\Block\Payson
 */
class CancelOrder extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * CancelOrder constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory                $orderFactory
     * @param \PaysonAB\PaysonCheckout2\Helper\Order           $orderHelper
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_orderHelper = $orderHelper;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function cancelHtml()
    {
        if($checkoutId = $this->_coreRegistry->registry('cancelPaysonCheckoutId')) {
            $api = $this->_orderHelper->getApi();
            $checkout = $api->GetCheckout($checkoutId);

            return $checkout->snippet;
        }
    }
}
