<?php


namespace Eastlane\PaysonCheckout2\Api\Data;

interface PaysoncheckoutQueueSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get paysoncheckout_queue list.
     *
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface[]
     */
    public function getItems();

    /**
     * Set quote_id list.
     *
     * @param  \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
