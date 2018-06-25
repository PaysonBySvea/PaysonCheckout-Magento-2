<?php
namespace PaysonAB\PaysonCheckout2\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PaysoncheckoutQueueRepositoryInterface
{


    /**
     * Save paysoncheckout_queue
     *
     * @param  \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
    );

    /**
     * Retrieve paysoncheckout_queue
     *
     * @param  string $paysoncheckoutQueueId
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($paysoncheckoutQueueId);

    /**
     * Retrieve paysoncheckout_queue matching the specified criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete paysoncheckout_queue
     *
     * @param  \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \PaysonAB\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
    );

    /**
     * Delete paysoncheckout_queue by ID
     *
     * @param  string $paysoncheckoutQueueId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($paysoncheckoutQueueId);
}
