<?php
namespace Eastlane\PaysonCheckout2\Model\Api;
/**
 * Class PayData
 *
 * @package Eastlane\PaysonCheckout2\Model\Api
 */
class PayData
{
    /**
     * @var null
     */
    public $currency = null;
    /**
     * @var array
     */
    public $items = array();

    /**
     * @var
     */
    public $totalPriceExcludingTax;
    /**
     * @var
     */
    public $totalPriceIncludingTax;
    /**
     * @var
     */
    public $totalTaxAmount;
    /**
     * @var
     */
    public $totalCreditedAmount;

    /**
     * @param $currencyCode
     * @return $this
     */
    public function payDataInit($currencyCode)
    {
        $this->currency = $currencyCode;
        $this->items = array();
        return $this;
    }

    /**
     * @param $data
     * @return PayData
     */
    public static function create($data)
    {
        $orderItemObject = new OrderItem();
        $payDataObject = new PayData();

        $payData = $payDataObject->payDataInit($data->currency);
        $payData->setTotalPriceExcludingTax($data->totalPriceExcludingTax);

        $payData->setTotalPriceIncludingTax($data->totalPriceIncludingTax);
        $payData->setTotalTaxAmount($data->totalTaxAmount);
        $payData->setTotalCreditedAmount($data->totalCreditedAmount);
        
        foreach ($data->items as $item) {
            $payData->items[] = $orderItemObject->create($item);
        }
        
        return $payData;
    }

    /**
     * @param $item
     */
    public function AddOrderItem($item)
    {
        $this->items = $item;
    }

    /**
     * @param $items
     * @throws PaysonApiException
     */
    public function setOrderItems($items)
    {
        if (!($items instanceof OrderItem)) {
            throw new PaysonApiException("Parameter must be an object of class Item");
        }

        $this->items = $items;
    }

    /**
     * @param $totalPriceExcludingTax
     * @return mixed
     */
    public function setTotalPriceExcludingTax($totalPriceExcludingTax)
    {
        if (is_null($this->totalPriceExcludingTax)) {
            $this->totalPriceExcludingTax = $totalPriceExcludingTax;
        }
        return $this->totalPriceExcludingTax;
    }

    /**
     * @param $totalPriceIncludingTax
     * @return mixed
     */
    public function setTotalPriceIncludingTax($totalPriceIncludingTax)
    {
        if (is_null($this->totalPriceIncludingTax)) {
            $this->totalPriceIncludingTax = $totalPriceIncludingTax;
        }
        return $this->totalPriceIncludingTax;
    }

    /**
     * @param $totalTaxAmount
     * @return mixed
     */
    public function setTotalTaxAmount($totalTaxAmount)
    {
        if (is_null($this->totalTaxAmount)) {
            $this->totalTaxAmount = $totalTaxAmount;
        }
        return $this->totalTaxAmount;
    }

    /**
     * @param $totalCreditedAmount
     * @return mixed
     */
    public function setTotalCreditedAmount($totalCreditedAmount)
    {
        if (is_null($this->totalCreditedAmount)) {
            $this->totalCreditedAmount = $totalCreditedAmount;
        }
        return $this->totalCreditedAmount;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $items = array();
        foreach ($this->items as $item) {
            $items[] = $item;
        }
        return array( 'currency'=>$this->currency, 'items'=>$items , 'totalPriceExcludingTax'=> $this->totalPriceExcludingTax, 'totalPriceIncludingTax'=> $this->totalPriceIncludingTax, 'totalTaxAmount'=>$this->totalTaxAmount, 'totalCreditedAmount'=>$this->totalCreditedAmount);
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
