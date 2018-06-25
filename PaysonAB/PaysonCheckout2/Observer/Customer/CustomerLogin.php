<?php
namespace PaysonAB\PaysonCheckout2\Observer\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Validator\Exception;


class CustomerLogin implements ObserverInterface
{

    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * CustomerLogin constructor.
     *
     * @param \PaysonAB\PaysonCheckout2\Helper\Data            $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order           $orderHelper
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param CustomerRepositoryInterface                      $customerRepository
     */
    public function __construct(
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_paysonHelper = $paysonHelper;
        $this->_orderHelper = $orderHelper;
        $this->_addressRepository = $addressRepository;
        $this->_customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $customerId = $customer->getDefaultShippingAddress()->getCustomerId();
        /**
 * @var CustomerInterface $customer 
*/
        $customer = $this->_customerRepository->getById($customerId);
        /**
 * @var Address $defaultAddress 
*/
        $defaultAddressId = $customer->getDefaultShipping();
        /**
 * @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository 
*/
        try {
            //This is our Customer Address
            $defaultShippingAddress = $this->_addressRepository->getById($defaultAddressId);

            //Convert the Customer Address to a Quote Address
            //Using the ObjectManager is bad practice, use DI instead, but how?
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /**
 * @var \Magento\Quote\Model\Quote\Address $quoteBillingAddress 
*/
            $quoteShippingAddress = $objectManager->create('Magento\Quote\Model\Quote\Address');
            $quoteShippingAddress->importCustomerAddressData($defaultShippingAddress);

            /*customer save using quote*/
            $quote = $this->_orderHelper->getQuote();
            $quote->setShippingAddress($quoteShippingAddress);
            $quote->save();
            return $this;
        } catch (\Exception $e) {
            $this->_paysonHelper->error($e->getMessage());
        }

    }
}