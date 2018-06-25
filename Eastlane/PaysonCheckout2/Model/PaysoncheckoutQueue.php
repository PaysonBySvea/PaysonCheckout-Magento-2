<?php


namespace Eastlane\PaysonCheckout2\Model;

use Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface;

class PaysoncheckoutQueue extends \Magento\Framework\Model\AbstractModel implements PaysoncheckoutQueueInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eastlane\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue');
    }

    /**
     * Get paysoncheckout_queue_id
     *
     * @return string
     */
    public function getPaysoncheckoutQueueId()
    {
        return $this->getData(self::PAYSONCHECKOUT_QUEUE_ID);
    }

    /**
     * Set paysoncheckout_queue_id
     *
     * @param  string $paysoncheckoutQueueId
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setPaysoncheckoutQueueId($paysoncheckoutQueueId)
    {
        return $this->setData(self::PAYSONCHECKOUT_QUEUE_ID, $paysoncheckoutQueueId);
    }

    /**
     * Get quote_id
     *
     * @return string
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Set quote_id
     *
     * @param  string $quote_id
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setQuoteId($quote_id)
    {
        return $this->setData(self::QUOTE_ID, $quote_id);
    }

    /**
     * Get order_id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order_id
     *
     * @param  string $order_id
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setOrderId($order_id)
    {
        return $this->setData(self::ORDER_ID, $order_id);
    }

    /**
     * Get checkout_id
     *
     * @return string
     */
    public function getCheckoutId()
    {
        return $this->getData(self::CHECKOUT_ID);
    }

    /**
     * Set checkout_id
     *
     * @param  string $checkout_id
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setCheckoutId($checkout_id)
    {
        return $this->setData(self::CHECKOUT_ID, $checkout_id);
    }

    /**
     * Get created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     *
     * @param  string $created_at
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setCreatedAt($created_at)
    {
        return $this->setData(self::CREATED_AT, $created_at);
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param  string $status
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get payson_response
     *
     * @return string
     */
    public function getPaysonResponse()
    {
        return $this->getData(self::PAYSON_RESPONSE);
    }

    /**
     * Set payson_response
     *
     * @param  string $payson_response
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setPaysonResponse($payson_response)
    {
        return $this->setData(self::PAYSON_RESPONSE, $payson_response);
    }
}
