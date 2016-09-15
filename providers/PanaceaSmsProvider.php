<?php

namespace rivast\sms\providers;

use Yii;
use yii\base\InvalidParamException;

use rivast\sms\SmsProviderInterface;
use PanaceaMobile\PanaceaApi;

class PanaceaSmsProvider implements SmsProviderInterface
{
    /**
     * Panacea account user name
     * @var string
     */
    public $username = null;

    /**
     * Panacea account password
     * @var string
     */
    public $password = null;

    /**
     * Panacea authentication token
     * @var string
     */
    public $authToken = null;

    /**
     * Panacea's SMS Transaction ID
     * @var string
     */
    private $_smsId = null;

    /**
     * Error message from Panacea
     * @var string
     */
    private $_errorMessage = null;

    /**
     * Sends an SMS to the given mobile number
     * containing the specified message
     *
     * @param string $number the recipient MSISDN
     * @param string $message the SMS contents
     * @return bool SMS sent or not
     */
    public function sendSms($number, $message) {

        if (empty($this->username)) {
            Yii::error('Panacea Username was not defined.', __METHOD__);
            throw new InvalidParamException('Panacea Username was not defined.');
        }

        // If password is empty, use the Auth Token as the password. Panacea accepts either
        $this->password = empty($this->password) ? $this->authToken : $this->password;

        if (empty($this->password)) {
            Yii::error('Panacea Password was not defined.', __METHOD__);
            throw new InvalidParamException('Panacea Password was not defined.');
        }

        if (empty($number)) {
            Yii::warning('SMS recipient was not provided.', __METHOD__);
            throw new InvalidParamException('SMS recipient was not provided.');
        }

        if (empty($message)) {
            Yii::warning('SMS message was not provided.', __METHOD__);
            throw new InvalidParamException('SMS message was not provided.');
        }

        $panacea = new PanaceaApi;

        $panacea->setUsername($this->username);
        $panacea->setPassword($this->password);

        // Send the SMS
        $response = $panacea->message_send($number, $message);

        $this->_smsId = isset($response['details']) ? $response['details'] : null;

        // SMS sending successful
        if($response['status'] === 1) {
            Yii::info('Panacea SMS sent: '.$this->_smsId, __METHOD__);
            return true; // Return Message ID
        }

        // SMS sending failed
        if($response['status'] < 0) {
            $this->_errorMessage = isset($response['message']) ? $response['message'] : null;
            Yii::warning("Panacea SMS failed: {$this->_smsId}. {$this->_errorMessage}", __METHOD__);
        }

        return false;
    }

    /**
     * Returns Panacea's assigned SMS transaction ID
     *
     * @return string
     */
    public function getLastSmsId()
    {
        return $this->_smsId;
    }

    /**
     * If SMS sending failed, return the error
     * message from Panacea
     *
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->_errorMessage;
    }
}