<?php
namespace Gnip\Parsers;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 02.04.12
 * Time: 20:27
 * To change this template use File | Settings | File Templates.
 */
class BaseParser extends AbsctractParser
{
    /**
     * Parse one entry from gnip response coverted to SimpleXMLElement
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

        $rez['object']['id'] = (string)$object->id;
        $rez['object']['title'] = (string)$object->title;
        $rez['object']['content'] = (string)$object->content;
        $rez['object']['subtitle'] = (string)$object->subtitle;
        $rez['author']['name'] = (string)$item->author->children()->name;
        $rez['author']['url'] = (string)$item->author->children()->uri;
        $rez['service']['provider'] = (string)$ns_service->provider->children()->name;

        return $rez;
    }


}
