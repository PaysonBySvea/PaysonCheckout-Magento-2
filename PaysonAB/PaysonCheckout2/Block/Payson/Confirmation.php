<?php

namespace PaysonAB\PaysonCheckout2\Block\Payson;
/**
 * Class Confirmation
 *
 * @package PaysonAB\PaysonCheckout2\Block\Payson
 */
class Confirmation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;

    /**
     * Confirmation constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \PaysonAB\PaysonCheckout2\Helper\Order           $orderHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        array $data = []
    ) {
        $this->_orderHelper = $orderHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function confirmationHtml()
    {
        if($checkoutId = $this->_orderHelper->getPaysonLastOrder()->getPaysonCheckoutId()) {
            $api = $this->_orderHelper->getApi();
            $checkout = $api->GetCheckout($checkoutId);

            return $checkout->snippet;
        }
        return false;
    }
}
