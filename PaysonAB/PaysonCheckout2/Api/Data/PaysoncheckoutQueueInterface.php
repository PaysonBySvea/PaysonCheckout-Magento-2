<?php


namespace PaysonAB\PaysonCheckout2\Api\Data;

interface PaysoncheckoutQueueInterface
{
    const PAYSONCHECKOUT_QUEUE_ID = 'paysoncheckout_queue_id';
    const PAYSON_RESPONSE = 'payson_response';
    const CREATED_AT = 'created_at';
    const QUOTE_ID = 'quote_id';
    const STATUS = 'status';
    const ORDER_ID = 'order_id';
    const CHECKOUT_ID = 'checkout_id';


    /**
     * Get paysoncheckout_queue_id
     *
     * @return string|null
     */
    public function getPaysoncheckoutQueueId();

    /**
     * Set paysoncheckout_queue_id
     *
     * @param  string $paysoncheckout_queue_id
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setPaysoncheckoutQueueId($paysoncheckoutQueueId);

    /**
     * Get quote_id
     *
     * @return string|null
     */
    public function getQuoteId();

    /**
     * Set quote_id
     *
     * @param  string $quote_id
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setQuoteId($quote_id);

    /**
     * Get order_id
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     *
     * @param  string $order_id
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setOrderId($order_id);


    /**
     * Get chekcout_id
     *
     * @return string|null
     */
    public function getCheckoutId();

    /**
     * Set checkout_id
     *
     * @param  string $checkout_id
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setCheckoutId($checkout_id);


    /**
     * Get created_at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     *
     * @param  string $created_at
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setCreatedAt($created_at);

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param  string $status
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setStatus($status);

    /**
     * Get payson_response
     *
     * @return string|null
     */
    public function getPaysonResponse();

    /**
     * Set payson_response
     *
     * @param  string $payson_response
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     */
    public function setPaysonResponse($payson_response);
}
