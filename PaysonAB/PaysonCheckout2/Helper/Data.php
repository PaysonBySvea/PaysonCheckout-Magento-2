<?php
namespace PaysonAB\PaysonCheckout2\Helper;

/**
 * Class Data
 *
 * @package PaysonAB\PaysonCheckout2\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_paysonLogger = $context->getLogger();
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
