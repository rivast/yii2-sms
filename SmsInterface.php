<?php

namespace rivast\sms;

/**
 * Interface SmsInterface
 * @package rivast\sms
 */
interface SmsInterface
{

    /**
     * @param  string $number the recipient MSISDN
     * @param  string $message the SMS contents
     * @return bool SMS sent or not
     */
    public function send($number, $message);

}
