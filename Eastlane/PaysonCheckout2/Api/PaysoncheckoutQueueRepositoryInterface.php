<?php
namespace Eastlane\PaysonCheckout2\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PaysoncheckoutQueueRepositoryInterface
{


    /**
     * Save paysoncheckout_queue
     *
     * @param  \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
    );

    /**
     * Retrieve paysoncheckout_queue
     *
     * @param  string $paysoncheckoutQueueId
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($paysoncheckoutQueueId);

    /**
     * Retrieve paysoncheckout_queue matching the specified criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete paysoncheckout_queue
     *
     * @param  \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
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
