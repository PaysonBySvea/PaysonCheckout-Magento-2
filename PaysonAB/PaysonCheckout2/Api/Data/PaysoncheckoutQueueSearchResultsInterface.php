<?php


namespace PaysonAB\PaysonCheckout2\Api\Data;

interface PaysoncheckoutQueueSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get paysoncheckout_queue list.
     *
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface[]
     */
    public function getItems();

    /**
     * Set quote_id list.
     *
     * @param  \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
