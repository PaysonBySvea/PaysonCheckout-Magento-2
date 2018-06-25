<?php

namespace PaysonAB\PaysonCheckout2\Block\Google;
/**
 * Class Confirmation
 *
 * @package PaysonAB\PaysonCheckout2\Block\Payson
 */
class Tagmanager extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\SalesRule\CouponCodeName
     */
    protected $_salesruleCoupon;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Test constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context                $context
     * @param \PaysonAB\PaysonCheckout2\Helper\Order                          $orderHelper
     * @param \Magento\Checkout\Model\Session                                 $checkoutSession
     * @param \PaysonAB\PaysonCheckout2\Model\SalesRule\CouponCodeName        $salesruleCoupon
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Json\Helper\Data                             $jsonHelper
     * @param array                                                           $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \PaysonAB\PaysonCheckout2\Model\SalesRule\CouponCodeName $salesruleCoupon,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_orderHelper = $orderHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_salesruleCoupon = $salesruleCoupon;
        $this->_collectionFactory = $collectionFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function actionField()
    {
        $lastRealOrder = $this->_checkoutSession->getLastRealOrder();

        $couponCode = $this->_salesruleCoupon->getCouponCode($lastRealOrder->getAppliedRuleIds());

        $result = [
            'id' => $lastRealOrder->getIncrementId(),
            'affiliation' => $lastRealOrder->getStoreName(),
            'revenue' =>  $lastRealOrder->getGrandTotal(),
            'tax' => $lastRealOrder->getTaxAmount(),
            'shipping' => $lastRealOrder->getShippingInclTax(),
            'coupon' => $couponCode ? $couponCode : null
        ];
        $encodedResult = $this->jsonHelper->jsonEncode($result);
        return $encodedResult;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts()
    {
        $this->order = $this->_checkoutSession->getLastRealOrder();
        $itemsResult = $productOption = [];
        foreach ($this->order->getAllVisibleItems() as $orderItem) {
            /* get category name */
            $categoryIds = $orderItem->getProduct()->getCategoryIds();
            $categoryId = end($categoryIds);
            $collection = $this->_collectionFactory->create();
            $collection->addAttributeToFilter('entity_id', $categoryId)
                ->addAttributeToSelect('*');
            $categoryName = $collection->getFirstItem()->getName();
            /* get category name */

            /* product variant */
            $itemOption = $this->getOrderOptions($orderItem);

            $itemsResult[] = [
                'name'=> $orderItem->getName(),
                'id'=> $orderItem->getSku(),
                'price'=> $orderItem->getPriceInclTax(),
                'category'=> $categoryName,
                'variant'=> $itemOption ? $itemOption : null ,
                'quantity'=>  $orderItem->getQtyOrdered()
            ];
        }
        $encodedResult = $this->jsonHelper->jsonEncode($itemsResult);
        return $encodedResult;
    }


    /**
     * Get order options
     *
     * @return array
     */
    public function getOrderOptions($orderItem)
    {
        $result = [];
        if ($options = $orderItem->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        $optionLabel = [];
        if(isset($result) && !empty($result)) {
            foreach ($result as $_option)
            {
                $optionLabel[] = $_option['label'];
            }
        }
        $result = implode(',', $optionLabel);
        return $result;
    }
}
