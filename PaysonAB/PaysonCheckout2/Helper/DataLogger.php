<?php
namespace PaysonAB\PaysonCheckout2\Helper;

/**
 * Class Data
 *
 * @package PaysonAB\PaysonCheckout2\Helper
 */
class DataLogger
{
    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    //protected $logger;

    public function __construct() 
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/payson.log');
        $logger = new \Zend_Log();
        //$logger->addWriter($writer);
        $this->_paysonLogger = $logger->addWriter($writer);
    }

    /**
     * @param $message
     */
    public function info($message)
    {
        $this->_paysonLogger->info($message);
    }

    /**
     * @param $message
     */
    public function notice($message)
    {
        $this->_paysonLogger->notice($message);
    }

    /**
     * @param $message
     */
    public function debug($message)
    {
        $this->_paysonLogger->debug($message);
    }

    /**
     * @param $message
     */
    public function alert($message)
    {
        $this->_paysonLogger->alert($message);  
    }

    /**
     * @param $message
     */
    public function warn($message)
     {
        $this->_paysonLogger->warn($message);
     }


}
