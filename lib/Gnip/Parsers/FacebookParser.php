<?php
namespace Gnip\Parsers;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 02.04.12
 * Time: 13:30
 * To change this template use File | Settings | File Templates.
 */
class FacebookParser extends AbsctractParser
{
    /**
     * Parse one record from gnip response
     *
     * @param \SimpleXMLElement $item
     *
     * @return array
     */
    public function parseItem(\SimpleXMLElement $item)
    {
        $rez['id'] = (string)$item->children()->id;
        $rez['published'] = (string)$item->children()->published;
        $rez['updated'] = (string)$item->children()->updated;
        $rez['title'] = (string)$item->children()->title;
        $rez['url'] = (string)$item->children()->link->attributes()->href;

        $ns_gnip = $item->children('http://www.gnip.com/schemas/2010');
        $ns_activity = $item->children('http://activitystrea.ms/spec/1.0/');
        $ns_service = $item->children('http://activitystrea.ms/service-provider');

        $object = $ns_activity->object->children();

        if($ns_activity->object->children('gnip', true) && $ns_activity->object->children('gnip', true)->statistics->attributes()){
            $rez['object']['favoriteCount'] = (string)$ns_activity->object->children('gnip', true)->statistics->attributes()->favoriteCount;
        }
        $rez['object']['id'] = (string)$object->id;
        $rez['object']['title'] = (string)$object->title;
        $rez['object']['content'] = (string)$object->content;
        $rez['object']['subtitle'] = (string)$object->subtitle;
        $rez['author']['name'] = (string)$item->author->children()->name;
        $rez['author']['url'] = (string)$item->author->children()->uri;
        $rez['service']['provider'] = (string)$ns_service->provider->children()->name;
        $rez['service']['url'] = (string)$ns_service->provider->children()->uri;

        foreach ($object->link as $as) {
            if ($as->attributes()->rel == 'related') {
                $rez['object']['related'][] = array(
                    'url'    => (string)$as->attributes()->href
                );
            }
            elseif ($as->attributes()->rel == 'preview') {
                $rez['object']['preview'][] = array(
                    'url'    => (string)$as->attributes()->href
                );
            } elseif ($as->attributes()->rel == 'enclosure') {
                $rez['object']['enclosure'][] = array(
                    'url'    => (string)$as->attributes()->href
                );
            }
        }

        foreach ($ns_activity->actor->children()->link as $as) {
            if ($as->attributes()->rel == "photo") {
                $rez['author']['photo'] = (string)$as->attributes()->href;
            }
        }

        foreach ($ns_gnip->matching_rules->matching_rule as $rule) {
            $rez['rules'][] = (string)$rule;
        }

        return $rez;
    }
}
