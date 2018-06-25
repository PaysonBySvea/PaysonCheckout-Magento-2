<?php
namespace PaysonAB\PaysonCheckout2\Helper;

/**
 * @category   PaysonAB
 * @package    PaysonAB_PaysonCheckout2
 */
use PaysonAB\PaysonCheckout2\Model\Api\OrderItem;

/**
 * Class Order
 *
 * @package PaysonAB\PaysonCheckout2\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    const PHYSICAL = 'physical';
    const DISCOUNT = 'discount';
    const FEE = 'fee';
    const EXTRAVERIFICATION = 'none';
    const REQUESTPHONE = 1;

    /**
     * @var array
     */
    protected $_supportedCurrencyCodes = array(
        'EUR', 'SEK'
    );

    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $_paysonConfig;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi
     */
    protected $_paysonApi;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\Merchant
     */
    protected $_merchant;
    /**
     * @var
     */
    protected $_api;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\PayData
     */
    protected $_payData;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var OrderItem
     */
    protected $_orderItem;
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculation;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customers;
    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_coreLocale;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\Customer
     */
    protected $_paysonCustomer;
    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $_addresss;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\Gui
     */
    protected $_gui;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Api\Checkout
     */
    protected $_paysonCheckout;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue
     */
    protected $_paysoncheckoutQueue;
    /**
     * @var Data
     */
    protected $_paysonHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var
     */
    protected $_quote;
    /**
     * @var
     */
    protected $_shippingMethod;
    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $_addressInterfaceFactory;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepositoryInterface;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\SalesRule\CouponCodeName
     */
    protected $_salesruleCoupon;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Order constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                    $context
     * @param \Magento\Framework\Locale\Resolver                       $coreLocale
     * @param \Magento\Checkout\Model\Session                          $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory                        $orderFactory
     * @param \Magento\Catalog\Model\Product                           $itemsCollection
     * @param \Magento\Customer\Model\Session                          $customerSession
     * @param \Magento\Customer\Model\Customer                         $customers
     * @param \Magento\Customer\Model\Address                          $addresss
     * @param \Magento\Store\Model\StoreManagerInterface               $storeManager
     * @param \Magento\Tax\Model\Calculation                           $calculation
     * @param \PaysonAB\PaysonCheckout2\Model\Config                   $paysonConfig
     * @param \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi            $paysonApi
     * @param \PaysonAB\PaysonCheckout2\Model\Api\Merchant             $merchant
     * @param \PaysonAB\PaysonCheckout2\Model\Api\PayData              $payData
     * @param OrderItem                                                $orderItem
     * @param \PaysonAB\PaysonCheckout2\Model\Api\Customer             $paysonCustomer
     * @param \PaysonAB\PaysonCheckout2\Model\Api\Gui                  $gui
     * @param \PaysonAB\PaysonCheckout2\Model\Api\Checkout             $paysonCheckout
     * @param \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue      $paysoncheckoutQueue
     * @param Data                                                     $paysonHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime              $date
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory       $addressInterfaceFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface         $addressRepositoryInterface
     * @param \PaysonAB\PaysonCheckout2\Model\SalesRule\CouponCodeName $salesruleCoupon
     * @param \Magento\Directory\Model\RegionFactory                   $regionFactory
     * @param \Magento\Framework\Pricing\Helper\Data                   $pricingHelper
     * @param \Magento\Framework\Registry                              $registry
     * @param array                                                    $layoutProcessors
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Locale\Resolver $coreLocale,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Customer\Model\Address $addresss,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Calculation $calculation,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi $paysonApi,
        \PaysonAB\PaysonCheckout2\Model\Api\Merchant $merchant,
        \PaysonAB\PaysonCheckout2\Model\Api\PayData $payData,
        OrderItem $orderItem,
        \PaysonAB\PaysonCheckout2\Model\Api\Customer $paysonCustomer,
        \PaysonAB\PaysonCheckout2\Model\Api\Gui $gui,
        \PaysonAB\PaysonCheckout2\Model\Api\Checkout $paysonCheckout,
        \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressInterfaceFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \PaysonAB\PaysonCheckout2\Model\SalesRule\CouponCodeName $salesruleCoupon,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Registry $registry,
        array $layoutProcessors = []
    ) {
        $this->_paysonConfig = $paysonConfig;
        $this->_paysonApi = $paysonApi;
        $this->_storeManager = $storeManager;
        $this->_merchant = $merchant;
        $this->_context = $context;
        $this->_payData = $payData;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_orderItem = $orderItem;
        $this->_calculation = $calculation;
        $this->_customerSession = $customerSession;
        $this->_customers = $customers;
        $this->_coreLocale = $coreLocale;
        $this->_paysonCustomer = $paysonCustomer;
        $this->_addresss = $addresss;
        $this->_gui = $gui;
        $this->_paysonCheckout = $paysonCheckout;
        $this->_paysoncheckoutQueue = $paysoncheckoutQueue;
        $this->_paysonHelper = $paysonHelper;
        $this->_date = $date;
        $this->_addressInterfaceFactory = $addressInterfaceFactory;
        $this->_addressRepositoryInterface = $addressRepositoryInterface;
        $this->_salesruleCoupon = $salesruleCoupon;
        $this->_regionFactory = $regionFactory;
        $this->pricingHelper = $pricingHelper;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    /**
     * @param $shippingMethod
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->_shippingMethod = $shippingMethod;
    }

    /**
     * @return mixed
     */
    public function getShippingMethod()
    {
        return $this->_shippingMethod;
    }

    /**
     * @return $this
     */
    public function loadPaysonApi()
    {

        $callPaysonApi = $this->getApi();

        $paysonMerchant = $this->_getMerchant();
        $payData = $this->_getQuotePayData();

        $customer = $this->initCustomer();

        $store = $this->_storeManager->getStore();
        $description = sprintf('Order from %s', $store->getFrontendName());

        // Init GUI
        $locale = strstr($this->_paysonConfig->getLocale(), '_', true);
        $theme = $this->_paysonConfig->getTheme();

        $guiCountriesSet = $this->_guiCountriesSet();
        $paysonGui = $this->_gui;
        $paysonGui->guiInit($locale, $theme, Self::EXTRAVERIFICATION, Self::REQUESTPHONE, $guiCountriesSet);
        $checkout = $this->_paysonCheckout->checkoutInit($paysonMerchant, $payData, $paysonGui, $customer, $description);

        return $checkout;
    }

    /**
     * @param $paysonResponse
     * @return string
     */
    public function convertToJson($paysonResponse)
    {
        return json_encode($paysonResponse);
    }

    /**
     * @return array|null
     */
    protected function _guiCountriesSet()
    {
        $allowspecific = $this->_paysonConfig->allowspecific();
        $countryCode = $this->_paysonConfig->countryCode() ? $this->_paysonConfig->countryCode() : null;
        $countries = null;
        if($allowspecific && $countryCode) {
            $countries = explode(',', $countryCode);
        }
        return $countries;
    }
    /**
     * @return \PaysonAB\PaysonCheckout2\Model\Api\Merchant
     * @throws \Exception
     */
    private function _getMerchant()
    {
        $quoteId = $this->getQuote()->getId();
        // URLs used by payson for redirection after a completed/canceled/notification purchase.
        $checkoutUri     = $this->_storeManager->getStore()->getBaseUrl().'checkout/payson/cancelprocess?id='.$quoteId;
        $confirmationUri = $this->_storeManager->getStore()->getBaseUrl().'checkout/payson/process?id='.$quoteId;
        $notificationUri = $this->_storeManager->getStore()->getBaseUrl().'checkout/payson/notification?id='.$quoteId;
        $termsUri   = $this->_storeManager->getStore()->getBaseUrl().''.$this->_paysonConfig->validateTermsUrl();
        $this->_merchant->metchantInit($checkoutUri, $confirmationUri, $notificationUri, $termsUri, 1);
        return $this->_merchant;
    }

    /**
     * @return $this
     * @throws \PaysonAB\PaysonCheckout2\Model\Api\PaysonApiException
     */
    public function getApi()
    {
        if (is_null($this->_api)) {
            $merchantId = $this->_paysonApi->getMerchantId();
            $apiKey = $this->_paysonApi->getApiKey();

            $paysonApi = $this->_paysonApi;
            $this->_api = $paysonApi->init($merchantId, $apiKey);
        }

        return $this->_api;
    }

    /**
     * @return \PaysonAB\PaysonCheckout2\Model\Api\PayData
     * @throws \PaysonAB\PaysonCheckout2\Model\Api\PaysonApiException
     */

    private function _getQuotePayData()
    {
        $quote = $this->getQuote();
        $payData = $this->_payData;
        $requestCurrency = $this->_registry->registry('currency');
        $currencyCode = $requestCurrency ? $requestCurrency : $quote->getQuoteCurrencyCode();
        $payData->payDataInit(strtolower($currencyCode));
        $payData->setTotalPriceExcludingTax($quote->getSubtotal());
        $payData->setTotalPriceIncludingTax($quote->getGrandTotal());


        // Re-create paydata
        $discount = 0;
        $totalItemsTaxAmount = 0;


        // Add items and discount
        $orderItems = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $orderItems[] = $this->prepareQuoteItemData($item, $payData);
            $discount += ($item->getDiscountAmount());
            $totalItemsTaxAmount +=  $item->getTaxAmount();
        }
        $payData->setTotalTaxAmount($totalItemsTaxAmount);
        $payData->setTotalCreditedAmount(0);
        // Calculate price for shipping
        $shipping = $this->prepareQuoteShippingData($quote);

        if (!empty($shipping)) {
            $orderItems[] = $shipping;
        }

        if ($discount > 0) {
            $paysonOrderItem = $this->_orderItem;
            $couponLable = $this->_salesruleCoupon->getCouponLabel($quote->getAppliedRuleIds());
            $couponName = $couponLable ? $couponLable : 'discount';
            $discountObject = $paysonOrderItem->orderItemInit($couponName, -$discount, 1, 0.25, 'discount', Self::DISCOUNT, 0, 0, 0, 0);
            $orderItems[] = get_object_vars($discountObject);
        }
        $payData->AddOrderItem($orderItems);
        return $payData;
    }

    /**
     * @param $item
     * @param $payData
     * @return array
     * @throws \PaysonAB\PaysonCheckout2\Model\Api\PaysonApiException
     */

    protected function prepareQuoteItemData($item, $payData)
    {
        $attributesString = "";

        if (($children = $item->getChildrenItems()) != null && !$item->getProduct()->getTypeId() == "configurable") {
            foreach ($children as $child) {
                $this->prepareQuoteItemData($child, $payData);
            }
            return;
        }

        $productOptions = $item->getProductOptions();

        if (is_null($productOptions)) {
            $productOptions = array();
        }

        if (array_key_exists('attributes_info', $productOptions)) {
            foreach ($productOptions['attributes_info'] as $attribute) {
                $attributesString .= $attribute['label'] . ": " . $attribute['value'] . ", ";
            }

            if ($attributesString != "") {
                $attributesString = substr($attributesString, 0, strlen($attributesString) - 2);
            }
        }

        $name = $item->getName() . ($attributesString != "" ? " - " . $attributesString : "");
        $sku = $item->getSku();

        $name = strlen($name) <= 128 ? $name : substr($name, 128);
        $sku = strlen($sku) <= 128 ? $sku : substr($sku, 128);

        $tax_mod = (float) $item->getTaxPercent();
        $tax_mod /= 100;
        $tax_mod = round($tax_mod, 5);

        $qty = $item->getQty();
        $qty = round($qty, 2);

        $price = $item->getPrice() != $item->getPriceInclTax() ? $item->getPriceInclTax() : $item->getPrice();
        $type = self::PHYSICAL;

        $totalPriceExcludingTax = $item->getRowTotal() ? $item->getRowTotal() : 0;
        $totalPriceIncludingTax = $item->getRowTotalInclTax() ? $item->getRowTotalInclTax() : 0;
        $totalitemTaxAmount = $item->getTaxAmount() ? $item->getTaxAmount() : 0 ;
        $discountRate = null;
        if($item->getNoDiscount()) {
            $discountRate = $item->getDiscountPercent();
        }

        $paysonOrderItem = $this->_orderItem;
        $paysonOrderItem->orderItemInit($name, $price, $qty, $tax_mod, $sku, $type, $totalPriceExcludingTax, $totalPriceIncludingTax, $totalitemTaxAmount, $discountRate);

        return get_object_vars($paysonOrderItem);
    }

    /**
     * @param $quote
     * @return array
     * @throws \PaysonAB\PaysonCheckout2\Model\Api\PaysonApiException
     */
    protected function prepareQuoteShippingData($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $this->getShippingMethod();
        $tax_calc = $this->_calculation;
        $store = $this->_storeManager->getStore($quote->getStoreId());
        $customer = $this->_customerSession->getCustomer();

        $tax_rate_req = $tax_calc->getRateRequest(
            $quote->getShippingAddress(),
            $quote->getBillingAddress(),
            $customer->getTaxClassId(),
            $store
        );

        $totalPriceExcludingTax = $shippingAddress->getShippingAmount() ? $shippingAddress->getShippingAmount() : 0;
        $totalPriceIncludingTax = $shippingAddress->getShippingInclTax() ? $shippingAddress->getShippingInclTax() : 0;
        $totalShippingTaxAmount = $shippingAddress->getShippingTaxAmount() ? $shippingAddress->getShippingTaxAmount() : 0;
        $discountRate = null;
        if(($price = (float) $shippingAddress->getShippingInclTax()) > 0) {
            $tax_mod = $tax_calc->getRate($tax_rate_req->setProductClassId($this->_paysonConfig->getShippingTaxClass()));
            $tax_mod /= 100;
            $tax_mod = round($tax_mod, 5);
            $price = round($price - $shippingAddress->getShippingDiscountAmount(), 2);
            $sku = is_null($shippingMethod) ? $shippingAddress->getShippingMethod() : $shippingMethod['carrier_code']."_".$shippingMethod['method_code'];
            $paysonOrderItem = $this->_orderItem;
            $paysonOrderItem->orderItemInit($shippingAddress->getShippingDescription(), $price, 1, $tax_mod, $sku, Self::FEE, $totalPriceExcludingTax, $totalPriceIncludingTax, $totalShippingTaxAmount, $discountRate);
            return get_object_vars($paysonOrderItem);
        }
        return [];
    }

    /**
     * @return \PaysonAB\PaysonCheckout2\Model\Api\Customer
     */
    private function initCustomer()
    {
        $guestMode = false;
        $quote = $this->getQuote();

        $customer = $this->getCustomer($quote->getCustomerId());


        $countryCodeCustomer = "";
        if (is_object($customer->getDefaultBillingAddress())) {
            $countryCodeCustomer = $customer->getDefaultBillingAddress()->getCountry();
        } else {
            $countryCodeCustomer = $this->getLenguageCode();
        }

        $firstname = $customer->getFirstname();
        $lastname = $customer->getLastname();
        $email =  $customer->getEmail();
        $telephone = $customer->getTelephone();
        $socialSecurityNo = '';
        $city =  '';
        $street = '';
        $postCode ='';
        $country = $countryCodeCustomer;

        if (!$guestMode && $customer->getDefaultBilling()) {
            $billingAddress = $this->_addresss->load($customer->getDefaultBilling());

            if ($billingAddress->getId()) {
                $firstname = $billingAddress->getData('firstname');
                $lastname = $billingAddress->getData('lastname');
                $telephone = $billingAddress->getData('telephone');
                $street = $billingAddress->getData('street');
                $city = $billingAddress->getData('city');
                $postCode = $billingAddress->getData('postcode');
                $country = $billingAddress->getData('country_id');
            }
        }
        $paysonCustomer = $this->_paysonCustomer->customerInit($firstname, $lastname, $email, $telephone, $socialSecurityNo, $city, $country, $postCode, $street);
        return $this->_paysonCustomer;
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function getCustomer($customerId)
    {
        //Get customer by customerID
        return $this->_customers->load($customerId);
    }

    /**
     * @return \Magento\Framework\Locale\Resolver|string
     */
    protected function getLenguageCode()
    {
        $locale = $this->_coreLocale;

        $locale->getLocale();
        $locale = strstr($locale->getLocale(), '_', true);

        if (strtoupper($locale)) {
            switch ($locale) {
            case 'da':
            case 'no':
            case 'se':
            case 'fi':
            case 'ru':
            case 'sv':{
                $locale = 'se';
                break;
}
            default: {
                $locale = 'en';
}
            }
        }
        return $locale;
    }

    /**
     * @return bool
     */
    public function hasActiveQuote()
    {
        return $this->getQuote()->hasItems();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!isset($this->_quote)) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }
        return $this->_quote;
    }


    /**
     * @param $checkoutId
     * @return mixed
     */

    public function updateCart($checkoutId)
    {
        $callPaysonApi = $this->getApi();

        // Fetch checkout and set new paydata
        $checkout = $callPaysonApi->GetCheckout($checkoutId);
        switch ($checkout->status) {
        case 'shipped':
        case 'paidToAccount':
        case 'canceled':
        case 'expired':
        case 'denied':

            // Don't try to update checkout at this point
            return $checkout;
        }

        $payData = $this->_getQuotePayData();
        $customerData = $this->initCustomer();
        $guiCountriesSet = $this->_guiCountriesSet();

        $checkout->payData->items = $payData->items;
        $checkout->customer = $customerData;
        $checkout->gui->countries = $guiCountriesSet;
        // Update and return
        $checkout = $callPaysonApi->UpdateCheckout($checkout);
        return $checkout;
    }

    /**
     * @param $paysonCustomer
     * @return \Magento\Quote\Model\Quote|null
     */

    public function convertQuoteToOrder($paysonCustomer)
    {
        $quote = $this->getQuote();

        if (is_null($quote)) {
            return null;
        }

        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->getCustomer($quote->getCustomerId());

            $this->_setCustomerDefaultAddress($customer, $quote, $paysonCustomer);

            $addressData = $this->_udateShippingAddress($paysonCustomer);
            $quote->setCustomerEmail($paysonCustomer->email);

        } else {
            $addressData = $this->_udateShippingAddress($paysonCustomer);
            $quote->setCustomerEmail($paysonCustomer->email);
        }
        $quote = $this->_addAddress($quote, $addressData);

        $quote = $this->_setRegionId($quote);
        $this->_quoteObj = $quote;

        if($this->_quoteObj->getShippingAddress()->getShippingMethod()) {
            $shippingAddress = $this->_quoteObj->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->setShippingMethod($this->_quoteObj->getShippingAddress()->getShippingMethod());

            $this->_quoteObj->setTotalsCollectedFlag(false);
            $this->_quoteObj->collectTotals()->save();
        }
        if (!$this->_customerSession->isLoggedIn()) {
            $quote->setCustomerIsGuest(true);
        }
        $quote->save();
        return $quote;
    }

    /**
     * @param $quote
     * @return mixed
     */
    protected function _setRegionId($quote)
    {
        $shippingAddress = $quote->getShippingAddress();

        $regionCollection = $this->_regionFactory->create()->getCollection()->addCountryFilter($shippingAddress->getCountryId());
        $regions = $regionCollection->toOptionArray();
        if(!empty($regions)) {
            if (is_null($shippingAddress->getRegionId())) {
                $shippingAddress->setRegionId(isset($regions[1]['value']));
            }

            $billingAddress = $quote->getBillingAddress();

            if (is_null($billingAddress->getRegionId())) {
                $billingAddress->setRegionId(isset($regions[1]['value']));
            }
        }
        return $quote;
    }
    /**
     * @param $customer
     * @param $quote
     * @param $paysonCustomer
     */
    protected function _setCustomerDefaultAddress($customer, $quote, $paysonCustomer)
    {
        if (! $customer->getDefaultBillingAddress()) {

            $addressData = $this->_udateShippingAddress($paysonCustomer);
            $quote->setCustomerEmail($paysonCustomer->email);
            $quote = $this->_addAddress($quote, $addressData);

            $billingAddress = $quote->getBillingAddress();
            $billingAddress->setIsDefaultBilling(true);
            $billingAddressData = $billingAddress;

            $billingAddressData['street']= [$billingAddressData['street']];

            try{
                // Add Address For created customer
                $address = $this->_addressInterfaceFactory->create();

                $address->setCustomerId($customer->getId())
                    ->setFirstname($billingAddressData->getFirstname() ? $billingAddressData->getFirstname() : null)
                    ->setLastname($billingAddressData->getLastname() ? $billingAddressData->getLastname() : null)
                    ->setCountryId($billingAddressData->getCountryId() ? $billingAddressData->getCountryId() : null)
                    ->setRegionId($billingAddressData->getRegionId() ? $billingAddressData->getRegionId() : null) //state/province, only needed if the country is USA
                    ->setPostcode($billingAddressData->getPostcode() ? $billingAddressData->getPostcode() : null)
                    ->setCity($billingAddressData->getCity() ? $billingAddressData->getCity() : null)
                    ->setTelephone($billingAddressData->getTelephone() ? $billingAddressData->getTelephone() : null)
                    ->setFax($billingAddressData->getFax() ? $billingAddressData->getFax() : null)
                    ->setCompany($billingAddressData->getCompany() ? $billingAddressData->getCompany() : null)
                    ->setStreet($billingAddressData->getStreet() ? $billingAddressData->getStreet() : null)
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true);
                $this->_addressRepositoryInterface->save($address);
            }
            catch (\Exception $e) {
                $this->_paysonHelper->error($e->getMessage());
            }
        }
    }

    /**
     * @param $quote
     * @param $addressData
     * @return mixed
     */
    protected function _addAddress($quote, $addressData)
    {
        //Add address array to both billing AND shipping address.
        $quote->getBillingAddress()->addData($addressData);
        $quote->getShippingAddress()->addData($addressData);
        return $quote;
    }

    /**
     * @param $paysonCustomer
     * @return array
     */
    private function _udateShippingAddress($paysonCustomer)
    {
        return array(
            'firstname' => $paysonCustomer->firstName,
            'lastname' => $paysonCustomer->lastName,
            'street' => $paysonCustomer->street,
            'city' => $paysonCustomer->city,
            'postcode'=> $paysonCustomer->postalCode,
            'telephone' => $paysonCustomer->phone,
            'country_id' => $paysonCustomer->countryCode
        );
    }

    /**
     * @param $defaultAddress
     * @return array
     */
    protected function _updateDefaultAddress($defaultAddress)
    {
        return array(
            'firstname' => $defaultAddress->getFirstname(),
            'lastname' => $defaultAddress->getLastname(),
            'street' => $defaultAddress->getStreet(),
            'city' => $defaultAddress->getCity(),
            'postcode'=> $defaultAddress->getPostcode(),
            'telephone' => $defaultAddress->getTelephone(),
            'country_id' => $defaultAddress->getCountryId(),
            'region' => $defaultAddress->getRegion()
        );
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getPaysonLastOrder()
    {
        return $this->_order = $this->_orderFactory->create()->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());
    }

    /**
     * @param $quoteId
     * @return $this
     */
    public function getPaysonInfo($quoteId)
    {
        try{
            $paysoncheckoutCollection = $this->_paysoncheckoutQueue->load($quoteId, 'quote_id');
            return $paysoncheckoutCollection;
        } catch (\Exception $e) {
            $this->_paysonHelper->error($e->getMessage());
        }

    }

    /**
     * @return bool
     */
    public function getCurrencyAllowed()
    {
        $currentCurrencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $requestCurrency = $this->_registry->registry('currency');
        $currencyCode = $requestCurrency ? $requestCurrency : $currentCurrencyCode;

        if(in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return true;
        }
        return false;
    }

    public function currencyConvertPrice($price)
    {
        return round($this->pricingHelper->currency($price, false, false), 2);
    }
}