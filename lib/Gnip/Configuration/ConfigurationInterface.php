<?php
namespace Gnip\Configuration;
/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 30.03.12
 * Time: 23:12
 * To change this template use File | Settings | File Templates.
 */
interface ConfigurationInterface
{
    public function getLogin();

    public function getDataCollectorHost();

    public function getPassword();

    public function getActivitiesUrlsArray();

    public function getStreamUrlsArray();

    public function getRulesUrlsArray();

    public function getArray();

    public function getStreamUrl($snName);

    public function getActivityUrl($snName);

    public function getRuleUrl($snName);
}
