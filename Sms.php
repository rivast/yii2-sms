<?php
/**
 * Yii2 SMS extension
 *
 * @category  Web-yii2
 * @package   yii2-sms
 * @author    Riaan van Staden <vanstadenr1@gmail.com>
 * @copyright 2016 Riaan van Staden <vanstadenr1@gmail.com>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   1.0.1
 * @link      http://github.com/rivast/yii2-sms
 */

namespace rivast\sms;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Object;

/**
 * SMS object class
 */
class Sms extends Object implements SmsInterface
{
    /**
     * Whether or not to send the actual message
     * @var boolean
     */
    public $dryRun = false;

    /**
     * The default SMS provider to use
     * @var string
     */
    public $defaultProvider = null;

    /**
     * A list of available SMS providers
     * @var array
     */
    public $providers = [];

    /**
     * An optional callable function after
     * SMS transaction completed
     *
     * @var callable
     */
    public $afterSend = null;

    /**
     * SMS Providers components
     * @var array
     */
    private $_providers = [];

    /**
     * Validates required parameters and initializes
     * the SMS object
     */
    public function init()
    {
        if ($this->defaultProvider === null) {
            throw new InvalidParamException('Default SMS provider was not defined.');
        }

        if (count($this->providers) === 0 || !array_key_exists($this->defaultProvider, $this->providers)) {
            throw new InvalidParamException('No valid SMS provider was defined.');
        }

        foreach($this->providers as $name => $config) {
            $config['class'] = "\\rivast\\sms\\providers\\{$name}SmsProvider";
            $this->_providers[$name] = Yii::createObject($config);
        }

        parent::init();
    }

    /**
     * Sends an SMS to the given recipient via the chosen
     * SMS provider containing the specified message
     *
     * @param  string $number the recipient MSISDN
     * @param  string $message the SMS contents
     * @param  array  $extra additional parameters for the callback function
     * @return bool SMS sent or not
     */
    public function send($number, $message, $extra = [])
    {
        Yii::trace("Sending SMS to {$number}: {$message}", __METHOD__);

        if ($this->dryRun) {
            $sent = true;
        } else {
            $sent = $this->getProvider()->sendSMS($number, $message);
        }

        if (!empty($this->afterSend)) {
            call_user_func_array($this->afterSend, array($number, $message, $sent, $this->lastErrorMessage, $extra));
        }

        return $sent;
    }

    /**
     * Returns the unique transaction ID assigned
     * by the SMS provider for the SMS sent
     *
     * @return string
     */
    public function getLastSmsId() {
        return $this->getProvider()->getLastSmsId();
    }

    /**
     * Get the last error message returned by
     * the SMS provider
     *
     * @return string
     */
    public function getLastErrorMessage() {
        return $this->getProvider()->getLastErrorMessage();
    }

    /**
    * Returns the SMS provider
    * @return BaseSMSProvider
    */
    private function getProvider() {
        return $this->_providers[$this->defaultProvider];
    }

}