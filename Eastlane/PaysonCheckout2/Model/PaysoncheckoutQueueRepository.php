<?php


namespace Eastlane\PaysonCheckout2\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Eastlane\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue\CollectionFactory as PaysoncheckoutQueueCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Eastlane\PaysonCheckout2\Api\PaysoncheckoutQueueRepositoryInterface;
use Magento\Framework\Api\SortOrder;
use Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueSearchResultsInterfaceFactory;
use Eastlane\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue as ResourcePaysoncheckoutQueue;

class PaysoncheckoutQueueRepository implements paysoncheckoutQueueRepositoryInterface
{
    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    protected $searchResultsFactory;

    protected $dataPaysoncheckoutQueueFactory;

    protected $paysoncheckoutQueueCollectionFactory;

    protected $resource;

    protected $paysoncheckoutQueueFactory;

    private $storeManager;


    /**
     * @param ResourcePaysoncheckoutQueue                      $resource
     * @param PaysoncheckoutQueueFactory                       $paysoncheckoutQueueFactory
     * @param PaysoncheckoutQueueInterfaceFactory              $dataPaysoncheckoutQueueFactory
     * @param PaysoncheckoutQueueCollectionFactory             $paysoncheckoutQueueCollectionFactory
     * @param PaysoncheckoutQueueSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper                                 $dataObjectHelper
     * @param DataObjectProcessor                              $dataObjectProcessor
     * @param StoreManagerInterface                            $storeManager
     */
    public function __construct(
        ResourcePaysoncheckoutQueue $resource,
        PaysoncheckoutQueueFactory $paysoncheckoutQueueFactory,
        PaysoncheckoutQueueInterfaceFactory $dataPaysoncheckoutQueueFactory,
        PaysoncheckoutQueueCollectionFactory $paysoncheckoutQueueCollectionFactory,
        PaysoncheckoutQueueSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->paysoncheckoutQueueFactory = $paysoncheckoutQueueFactory;
        $this->paysoncheckoutQueueCollectionFactory = $paysoncheckoutQueueCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPaysoncheckoutQueueFactory = $dataPaysoncheckoutQueueFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
    ) {
        /* if (empty($paysoncheckoutQueue->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $paysoncheckoutQueue->setStoreId($storeId);
        } */
        try {
            $paysoncheckoutQueue->getResource()->save($paysoncheckoutQueue);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the paysoncheckoutQueue: %1',
                    $exception->getMessage()
                )
            );
        }
        return $paysoncheckoutQueue;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($paysoncheckoutQueueId)
    {
        $paysoncheckoutQueue = $this->paysoncheckoutQueueFactory->create();
        $paysoncheckoutQueue->getResource()->load($paysoncheckoutQueue, $paysoncheckoutQueueId);
        if (!$paysoncheckoutQueue->getId()) {
            throw new NoSuchEntityException(__('paysoncheckout_queue with id "%1" does not exist.', $paysoncheckoutQueueId));
        }
        return $paysoncheckoutQueue;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->paysoncheckoutQueueCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /**
 * @var SortOrder $sortOrder 
*/
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Eastlane\PaysonCheckout2\Api\Data\PaysoncheckoutQueueInterface $paysoncheckoutQueue
    ) {
        try {
            $paysoncheckoutQueue->getResource()->delete($paysoncheckoutQueue);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the paysoncheckout_queue: %1',
                    $exception->getMessage()
                )
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($paysoncheckoutQueueId)
    {
        return $this->delete($this->getById($paysoncheckoutQueueId));
    }
}
