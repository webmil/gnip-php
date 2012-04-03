<?php
namespace Gnip\Collectors;

use Gnip\Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 31.03.12
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */
class Loader
{
    private $_userAgent = 'USERAGENT';

    /**
     * @var int
     */
    private $_maxResults = 100;

    /**
     * @var string
     */
    private $_sinceDate;

    /**
     * @var string
     */
    private $_toDate;

    /**
     * @var \Gnip\Configuration\ConfigurationInterface
     */
    private $_config;

    /**
     * @param \Gnip\Configuration\ConfigurationInterface $config
     */
    public function __construct(\Gnip\Configuration\ConfigurationInterface $config)
    {
        $this->_config = $config;
    }

    /**
     * @param int $maxResults
     */
    public function setMaxResults($maxResults)
    {
        $this->_maxResults = $maxResults;
    }

    /**
     * @return int
     */
    public function getMaxResults()
    {
        return $this->_maxResults;
    }

    /**
     * @param \DateTime $sinceDate
     */
    public function setSinceDate(\DateTime $sinceDate)
    {
        $this->_sinceDate = $sinceDate->format('YmdHis');
    }

    /**
     * @return string
     */
    public function getSinceDate()
    {
        return $this->_sinceDate;
    }

    /**
     * @param \DateTime $toDate
     */
    public function setToDate(\DateTime $toDate)
    {
        $this->_toDate = $toDate->format('YmdHis');
        ;
    }

    /**
     * @return string
     */
    public function getToDate()
    {
        return $this->_toDate;
    }

    /**
     * @return string
     */
    public function getFormattedSinceDate()
    {
        return $this->_sinceDate;
    }

    /**
     * @param $snName
     *
     * @return mixed
     */
    public function getFeed($snName)
    {
        $response = $this->_doRequest($this->_config->getActivityUrl($snName));

        return $response;
    }

    /**
     * Get feeds for all configured data collectors
     *
     * @return array
     */
    public function getAllFeeds()
    {
        $response = array();
        $activitiesUrls = $this->_config->getActivitiesUrlsArray();
        foreach ($activitiesUrls as $snName => $url) {
            $response[$snName] = $this->_doRequest($url);
        }

        return $response;
    }

    /**
     * @param string $url Collector url
     *
     * @return mixed
     * @throws \Gnip\Exception\GnipNetworkException
     */
    private function _doRequest($url)
    {
        $limiter = '?';
        if (isset($this->_maxResults)) {
            $url .= $limiter . 'max=' . $this->getMaxResults();
            $limiter = '&';
        }
        if (isset($this->_sinceDate)) {
            $url .= $limiter . 'since_date=' . $this->getSinceDate();
        }

        if (isset($this->_toDate)) {
            $url .= $limiter . 'to_date=' . $this->getToDate();
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
