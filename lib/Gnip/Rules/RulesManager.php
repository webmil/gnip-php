<?php
namespace Gnip\Rules;

use Gnip\Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 31.03.12
 * Time: 10:38
 * To change this template use File | Settings | File Templates.
 */
class RulesManager
{
    const REQUEST_TYPE_REMOVE = 0;
    const REQUEST_TYPE_ADD = 1;

    /**
     * @var \Gnip\Configuration\ConfigurationInterface
     */
    private $_config;

    /**
     * @var array
     */
    private $_rulesQueue = array();

    /**
     * if NULL rulles will be added to all available chanels
     *
     * @var string
     */
    private $_chanelUrl;

    /**
     * @var
     */
    private $_requestType;

    /**
     * @var array
     */
    private $_errors = array();

    private $_userAgent = 'USERAGENT';

    /**
     * @param \Gnip\Configuration\ConfigurationInterface $config
     */
    public function __construct(\Gnip\Configuration\ConfigurationInterface $config)
    {
        $this->_config = $config;

        //add keywords request is default
        $this->_requestType = self::REQUEST_TYPE_ADD;

        //add rules to all chanels by default
        $this->_chanelUrl = NULL;
    }

    /**
     * Add rules to queue
     *
     * @param array|string $rule rules to add to queue
     *
     * @return \Gnip\Rules\RulesManager
     */
    public function add($rule)
    {
        if (is_array($rule)) {
            $this->_rulesQueue = array_merge($this->_rulesQueue, $rule);
        }
        else {
            array_push($this->_rulesQueue, (string)$rule);
        }
        return $this;
    }

    /**
     * Remove rules from queue
     *
     * @param array|string $rule rules to add to queue
     *
     * @return \Gnip\Rules\RulesManager
     */
    public function removeFromQueue($rule)
    {
        if (is_array($rule)) {
            $this->_rulesQueue = array_diff($this->_rulesQueue, $rule);
        }
        else {
            foreach ($this->_rulesQueue as $key => $value) {
                if ($value == $rule) {
                    echo $value;
                    unset($this->_rulesQueue[$key]);
                }
            }
        }

        return $this;
    }

    /**
     * Create add rules request
     *
     * @return \Gnip\Rules\RulesManager
     */
    public function doAddRequest()
    {
        $this->_requestType = self::REQUEST_TYPE_ADD;
        $this->clearErrors();
        return $this;
    }

    /**
     * Create remove rules request
     *
     * @return \Gnip\Rules\RulesManager
     */
    public function doRemoveRequest()
    {
        $this->_requestType = self::REQUEST_TYPE_REMOVE;
        $this->clearErrors();
        return $this;
    }

    /**
     * @param string $snName Data collector name
     *
     * @return \Gnip\Rules\RulesManager
     */
    public function setChanel($snName)
    {
        $this->_chanelUrl = $this->_config->getRuleUrl($snName);

        return $this;
    }

    /**
     * Get rules count in queue
     *
     * @return int
     */
    public function getQueueSize()
    {
        return count($this->_rulesQueue);
    }

    /**
     * Get array of existing rules from Gnip
     *
     * @param string $snName Data collector name
     *
     * @return array Array of existing rules
     */
    public function getRules($snName)
    {
        $response = $this->_doRequest($this->_config->getRuleUrl($snName));
        $response = json_decode($response, true);
        $rezRules = array();
        foreach ($response['rules'] as $rule) {
            $rezRules[] = $rule['value'];
        }
        unset($rules);

        return $rezRules;
    }

    /**
     * @return array
     */
    public function getQueue()
    {
        return $this->_rulesQueue;
    }

    /**
     * Do request with rules queue
     *
     * @return array|bool
     */
    public function commit()
    {
        //if chanell url is not set, add or remove rules from all chanels
        $error = false;
        $results = array();
        $this->clearErrors();
        if (is_null($this->_chanelUrl)) {
            $urls = $this->_config->getRulesUrlsArray();
            foreach ($urls as $snName => $url) {
                try {
                    $this->_doRequest($url);
                    $results[$snName] = true;
                } catch (Exception\GnipNetworkException $e) {
                    $results[$snName] = false;
                    $error = true;
                    $this->_addError($snName, $e->getMessage());
                }
            }
        }
        //if url is set, do request for one chanel
        else {
            try {
                $this->_doRequest($this->_chanelUrl);
            } catch (Exception\GnipNetworkException $e) {
                $results = false;
                $error = true;
                $this->_addError($this->_chanelUrl, $e->getMessage());
            }
        }
        //clear queue if everything goes good without errors
        if ($error !== false) {
            $this->_rulesQueue = array();
        }

        return $results;
    }

    /**
     * Get errors after commit
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Clear errors stack
     */
    public function clearErrors()
    {
        $this->_errors = array();
    }

    /**
     * Add error to stack
     *
     * @param string $snName
     * @param string $message
     */
    private function _addError($snName, $message)
    {
        $this->_errors[$snName] = $message;
    }

    private function _formatRequestArray()
    {
        $queue = $this->getQueue();
        $res = array();
        foreach ($queue as $rule) {
            $res[] = array('value' => $rule);
        }
        return $res;
    }

    /**
     * @param string $url        Rule url
     * @param bool   $getRequest set to true for geting list of active rules
     *
     * @return mixed
     * @throws \Gnip\Exception\GnipNetworkException
     */
    private function _doRequest($url, $getRequest = false)
    {
        //format rules request
        $postData = null;
        if ($getRequest === true) {
            $postData = json_encode(array('rules' => $this->_formatRequestArray()));
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($ch, CURLOPT_USERPWD,
                    $this->_config->getLogin() . ":" . $this->_config->getPassword());
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //        curl_setopt($ch, CURLOPT_VERBOSE, true);
        //        curl_setopt($ch, CURLOPT_HEADER, true);
        //        curl_setopt($ch, CURLOPT_NOBODY, false);

        if (!is_null($postData)) {
            if (!is_null($this->_requestType) && $this->_requestType == self::REQUEST_TYPE_REMOVE) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            }
            else {
                curl_setopt($ch, CURLOPT_POST, true);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        $response = curl_exec($ch);

        if ((curl_errno($ch) == 6) OR (curl_errno($ch) == 7)) {
            throw new Exception\GnipNetworkException('Network error ' . curl_errno($ch));
        }
        else {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (strpos((string)$http_status, '20') === false) {
                throw new Exception\GnipNetworkException('Response status ' . $http_status . ' : ' . curl_error($ch));
            }

            else {
                if (trim($response) == '') {
                    throw new Exception\GnipNetworkException('Response is empty ' . curl_errno($ch));
                }
                curl_close($ch);
                return $response;
            }
        }

    }
}
