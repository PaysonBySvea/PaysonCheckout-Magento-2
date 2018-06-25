<?php
/**
 * @category   Eastlane
 * @package    Eastlane_PaysonCheckout2
 */
namespace Eastlane\PaysonCheckout2\Logger\Handler;

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
