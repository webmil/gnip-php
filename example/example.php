<?php
use Gnip\Configuration;
use Gnip\Rules;
use Gnip\Collectors;
use Gnip\Parsers;
//create autoloader
function __autoload($class)
{
    $parts = str_replace('\\', '/', $class);
    $fileName = dirname(__FILE__) . "/../lib/" . $parts . '.php';
    if(file_exists($fileName)) {
        require $fileName;
    }
}
//basic configuration array
$params = array(
    'login' => 'password',
    'password' => 'login',
    'datacollectorhost' => 'domain.gnip.com',
    'datacollectors' => array(
        'facebook' => 1
    )
);

//create configuration
$config = new Configuration\BaseConfiguration($params);

//create rules manager
$rulesManager = new Rules\RulesManager($config);

//get active rules from gnip
$rulesManager->getRules('google');

//add rules to gnip
$rulesManager->add('test');
$rulesManager->add(array('tes2','test3'));
$rulesManager->commit();

//remove rule from gnip
$rulesManager->add('tes2')->doRemoveRequest()->commit();

//load feed from gnip
$loader = new Collectors\Loader($config);
$feed = $loader->getFeed('google');

//parse gnip feed
$parser = new Parsers\Parser();
$parser->parse($feed, 'google');
