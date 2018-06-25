<?php
namespace Eastlane\PaysonCheckout2\Model;

use Magento\Framework\Escaper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class Paysoncheckout2ConfigProvider
 *
 * @package Eastlane\PaysonCheckout2\Model
 */
class Paysoncheckout2ConfigProvider implements ConfigProviderInterface
{
    const CHECKOUT_CODE = 'paysoncheckout2';
    protected $escaper;
    protected $paymentHelper;

    /**
     * Paysoncheckout2ConfigProvider constructor.
     *
     * @param Escaper       $escaper
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        Escaper         $escaper,
        PaymentHelper   $paymentHelper
    ) {
        $this->escaper         = $escaper;
        $this->paymentHelper   = $paymentHelper;
    }

    /**
     * Payment config.
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'paysoncheckout2' => [

                ],
            ],
        ];
    }

    /**
     * Get payemnt Method by code.
     *
     * @param string $code
     *
     * @return \Magento\Payment\Model\MethodInterface
     */
    public function getMethod($code)
    {
        return $this->paymentHelper->getMethodInstance($code);
    }
}
