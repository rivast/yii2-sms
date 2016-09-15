<?php

namespace rivast\sms;

/**
 * Interface SmsProviderInterface
 * @package rivast\sms
 */
interface SmsProviderInterface
{
    /**
     * @param  string $number the recipient MSISDN
     * @param  string $message the SMS contents
     * @return bool SMS sent or not
     */
    public function sendSms($number, $message);

    /**
     * @return string SMS transaction ID from Provider
     */
    public function getLastSmsId();

    /**
     * @return string last error message returned by the SMS Provider
     */
    public function getLastErrorMessage();
}
