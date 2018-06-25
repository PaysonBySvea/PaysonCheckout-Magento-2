<?php
/**
 * @category   PaysonAB
 * @package    PaysonAB_PaysonCheckout2
 */
namespace PaysonAB\PaysonCheckout2\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Payson extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/payson.log';
    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
