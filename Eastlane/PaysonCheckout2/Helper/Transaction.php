<?php

namespace Eastlane\PaysonCheckout2\Helper;
/**
 * Class Transaction
 *
 * @package Eastlane\PaysonCheckout2\Helper
 */
class Transaction
{
    /**
     * @var Data
     */
    protected $_paysonHelper;
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Builder
     */
    protected $_transactionBuilder;

    /**
     * Transaction constructor.
     *
     * @param Data                                                   $paysonHelper
     * @param \Magento\Sales\Model\Order\Payment\Transaction\Builder $builder
     */
    public function __construct(
        \Eastlane\PaysonCheckout2\Helper\Data $paysonHelper,
        \Magento\Sales\Model\Order\Payment\Transaction\Builder $builder
    ) {
        $this->_paysonHelper = $paysonHelper;
        $this->_transactionBuilder = $builder;
    }

    /**
     * @param $order
     * @param $responseObject
     * @return mixed
     */
    public function addTransaction($order, $responseObject)
    {
        try {
            //get payment object from order object
            $payment = $order->getPayment();
            $payment->setLastTransId($responseObject->purchaseId);
            $payment->setTransactionId($responseObject->purchaseId);
            $formatedPrice = $order->getOrderCurrency()->formatTxt(
                $order->getGrandTotal()
            );

            $message = __('The authorized amount is %1.', $formatedPrice);
            //get the object of builder class
            $trans = $this->_transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($responseObject->purchaseId)
                ->setFailSafe(true)
                //build method creates the transaction and returns the object
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );

            $payment->save();
            $order->save();

            return  $transaction->save()->getTransactionId();
        } catch (\Exception $e) {
            $this->_paysonHelper->log($e->getMessage());
        }
    }

    /**
     * @param $order
     */
    public function setTransaction($order)
    {
        try {
            //get payment object from order object
            $payment = $order->getPayment();
            //get the object of builder class
            $trans = $this->_transactionBuilder;

            $formatedPrice = $order->getOrderCurrency()->formatTxt(
                $order->getGrandTotal()
            );

            $message = __('The capture amount is %1.', $formatedPrice);
            $transaction = $trans->setPayment($payment)
                ->setTxnType(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );

            $payment->save();
            $transaction->save();

        } catch (\Exception $e) {
            $this->_paysonHelper->log($e->getMessage());
        }
    }
}
