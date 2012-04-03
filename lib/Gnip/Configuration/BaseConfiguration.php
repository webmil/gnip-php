<?php
namespace Gnip\Configuration;
use Gnip\Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 30.03.12
 * Time: 23:24
 * To change this template use File | Settings | File Templates.
 */
class BaseConfiguration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $_login;

    /**
     * @var string
     */
    private $_password;

    /**
     * @var string
     */
    private $_datacollectorhost;

    /**
     * @var array
     */
    private $_dataCollectors;

    /**
     * @var array
     */
    private $_dataCollectorsUrlsArray = array();

    /**
     * @var array
     */
    private $_streamDataCollectorsUrlsArray = array();

    /**
     * @var array
     */
    private $_RulesUrlsArray = array();

    /**
     * @var string
     */
    private $_stream = 'stream.xml';

    /**
     * @var string
     */
    private $_activities = 'activities.xml';

    /**
     * @var string
     */
    private $_rules = 'rules.json';


    /**
     * @var array
     */
    private $_availableOptions = array('login', 'password', 'datacollectorhost', 'datacollectors');

    /**
     * Input array
     * array(
     * 'login' => '',
     * 'password' => '',
     * 'datacollectorhost' => '',
     * 'datacollectors' => array(
     *     'collectorName' => $collectorId,
     *     ....
     *      ),
     * )
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->_checkInputConfigArray($configuration);

        //if everything is ok after validation, write config variables
        $this->setLogin($configuration['login']);
        $this->setPassword($configuration['password']);
        $this->setDatacollectorHost($configuration['datacollectorhost']);
        $this->setDataCollectors($configuration['datacollectors']);
    }

    /**
     * Get Account Login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Set login data
     *
     * @param string $login Account Login
     */
    public function setLogin($login)
    {
        $this->_login = $login;
    }

    /**
     * Get Account Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @param array $dataCollectors
     */
    public function setDataCollectors(array $dataCollectors)
    {
        $this->_dataCollectors = $dataCollectors;
    }

    /**
     * Set password data
     *
     * @param string $password Account Password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     * @param  $datacollectorhost
     */
    public function setDatacollectorHost($datacollectorhost)
    {
        $this->_datacollectorhost = $datacollectorhost;
    }

    /**
     * @return string
     */
    public function getDataCollectorHost()
    {
        return $this->_datacollectorhost;
    }

    /**
     * Return activities data collectors urls as array
     * 'social network name' => 'data collector url'
     *
     * @return array
     */
    public function getActivitiesUrlsArray()
    {
        return $this->_createdataCollectorsUrls();
    }


    /**
     * @return array
     */
    public function getStreamUrlsArray()
    {
        return $this->_createStreamDataCollectorsUrls();
    }

    /**
     * @return array
     */
    public function getRulesUrlsArray()
    {
        return $this->_createRulesUrls();
    }

    /**
     * @param $snName
     *
     * @return string
     * @throws \Gnip\Exception\WrongConfigurationException
     */
    public function getStreamUrl($snName)
    {
        $urls = $this->getStreamUrlsArray();
        if (!isset($urls[$snName])) {
            throw new Exception\WrongConfigurationException('no url for ' . $snName);
        }

        return $urls[$snName];
    }

    /**
     * @param $snName
     *
     * @return string
     * @throws \Gnip\Exception\WrongConfigurationException
     */
    public function getActivityUrl($snName)
    {
        $urls = $this->getActivitiesUrlsArray();
        if (!isset($urls[$snName])) {
            throw new Exception\WrongConfigurationException('no url for ' . $snName);
        }

        return $urls[$snName];
    }

    /**
     * @param $snName
     *
     * @return string
     * @throws \Gnip\Exception\WrongConfigurationException
     */
    public function getRuleUrl($snName)
    {
        $urls = $this->getRulesUrlsArray();
        if (!isset($urls[$snName])) {
            throw new Exception\WrongConfigurationException('no url for ' . $snName);
        }

        return $urls[$snName];
    }

    /**
     * @return array
     */
    public function getArray()
    {
        $config = array(
            'login'              => $this->getLogin(),
            'password'           => $this->getPassword(),
            'datacollectorhost'  => $this->getDataCollectorHost(),
            'activitycollectors' => $this->getActivitiesUrlsArray(),
            'stramcollectors'    => $this->getStreamUrlsArray(),
            'rules'              => $this->getRulesUrlsArray()
        );

        return $config;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->getArray());
    }

    /**
     * Check input config array keys for wrong elements
     *
     * @param array $configArray
     *
     * @return bool
     * @throws \Gnip\Exception\WrongConfigurationException
     */
    private function _checkInputConfigArray($configArray)
    {
        $availableOptions = $this->_availableOptions;

        //check if all input options keys are correct
        foreach (array_keys($configArray) as $configKey) {
            if (!in_array($configKey, $availableOptions)) {
                throw new Exception\WrongConfigurationException('wrong input config key: ' . $configKey);
            }
        }
        $missedKeys = array_diff($availableOptions, array_keys($configArray));
        if (!empty($missedKeys)) {
            throw new Exception\WrongConfigurationException('You must specify: ' . implode(',', $missedKeys));
        }
        $this->_checkInputDataCollectors($configArray['datacollectors']);

        //if there are no problems, return true
        return true;
    }

    /**
     * Check if datacollector option is array and throw expection no error
     *
     * @param mixed $dataCollectors
     *
     * @return bool
     * @throws \Gnip\Exception\WrongConfigurationException
     */
    private function _checkInputDataCollectors($dataCollectors)
    {
        //check if dataCollectors key is array
        if (!is_array($dataCollectors)) {
            throw new Exception\WrongConfigurationException('DataCollectors element must be array');
        }

        return true;
    }

    /**
     * @return array
     */
    private function  _createdataCollectorsUrls()
    {
        if (empty($this->_dataCollectorsUrlsArray)) {
            foreach ($this->_dataCollectors as $snName => $collectorId) {
                $this->_dataCollectorsUrlsArray[$snName] = sprintf('https://%s/data_collectors/%d/%s', $this->_datacollectorhost, $collectorId, $this->_activities);
            }
        }
        return $this->_dataCollectorsUrlsArray;
    }

    /**
     * @return array
     */
    private function  _createStreamDataCollectorsUrls()
    {
        if (empty($this->_streamDataCollectorsUrlsArray)) {
            foreach ($this->_dataCollectors as $snName => $collectorId) {
                $this->_streamDataCollectorsUrlsArray[$snName] = sprintf('https://%s/data_collectors/%d/%s', $this->_datacollectorhost, $collectorId, $this->_stream);
            }
        }
        return $this->_streamDataCollectorsUrlsArray;
    }

    /**
     * @return array
     */
    private function _createRulesUrls()
    {
        if (empty($this->_RulesUrlsArray)) {
            foreach ($this->_dataCollectors as $snName => $collectorId) {
                $this->_RulesUrlsArray[$snName] = sprintf('https://%s/data_collectors/%d/%s', $this->_datacollectorhost, $collectorId, $this->_rules);
            }
        }
        return $this->_RulesUrlsArray;
    }
}
