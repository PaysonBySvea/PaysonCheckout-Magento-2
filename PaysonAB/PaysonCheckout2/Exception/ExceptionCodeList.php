<?php

namespace PaysonAB\PaysonCheckout2\Exception;
/**
 * Class ExceptionCodeList
 *
 * @package PaysonAB\PaysonCheckout2\Exception
 */
class ExceptionCodeList
{
    const COMMUNICATION_ERROR = 10000;
    const MISSING_MERCHANT_ID = 20001;
    const MISSING_API_KEY = 20002;
    const MISSING_REQUEST_URL = 20003;
    const MISSING_TERMS_URL = 20004;
    const INPUT_VALIDATION_ERROR = 30000;
    const UNKNOWN_CODE_MESSAGE = 'Unknown code error';

    /**
     * Return Message for given exception code
     *
     * @param  $exceptionCode
     * @return string
     */
    public static function getErrorMessage($exceptionCode)
    {
        $exceptionCode = intval($exceptionCode);

        $exceptionMessageList = array(
            self::COMMUNICATION_ERROR => 'Api Client Error',
            self::MISSING_MERCHANT_ID => 'Missing Payson Merchant Id',
            self::MISSING_API_KEY => 'Missing Payson Api Key',
            self::MISSING_REQUEST_URL => 'Missing Payson Api Url',
            self::MISSING_TERMS_URL => 'Missing Terms Url',
            self::INPUT_VALIDATION_ERROR => 'Input Validation Error'
        );

        if (isset($exceptionMessageList[$exceptionCode])) {
            return $exceptionMessageList[$exceptionCode];
        }

        return self::UNKNOWN_CODE_MESSAGE;
    }
}
