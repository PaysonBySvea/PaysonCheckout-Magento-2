<?php
namespace Eastlane\PaysonCheckout2\Helper;

/**
 * Class Data
 *
 * @package Eastlane\PaysonCheckout2\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_paysonLogger;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface              $paysonLogger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $paysonLogger
    ) {
        $this->_paysonLogger            = $paysonLogger;
        parent::__construct($context);
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        $this->_paysonLogger->addDebug($message);
    }

    /**
     * @param $message
     */
    public function critical($message)
    {
        $this->_paysonLogger->critical($message);
    }

    /**
     * @param $message
     */
    public function error($message)
    {
        $this->_paysonLogger->addError($message);
    }
}
