<?php
namespace Gnip\Parsers;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 02.04.12
 * Time: 20:28
 * To change this template use File | Settings | File Templates.
 */
abstract class AbsctractParser implements ParserInterface
{
    /**
     * Parse whole response from gnip
     *
     * @param $string
     *
     * @return array
     */
    public function parse($string)
    {
        $items = array();
        $feed = simplexml_load_string($string);
        foreach ($feed->entry as $item) {
            $items[] = $this->parseItem($item);
//            print_r($items);
//            die();
        }

        return $items;
    }

}
