<?php
namespace Gnip\Parsers;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 31.03.12
 * Time: 16:06
 * To change this template use File | Settings | File Templates.
 */
interface ParserInterface
{
    /**
     * Parse whole response from gnip
     *
     * @abstract
     *
     * @param $string
     */
    public function parse($string);

    /**
     * Parse one entry from gnip response coverted to SimpleXMLElement
     * @abstract
     *
     * @param \SimpleXMLElement $item
     */
    public function parseItem(\SimpleXMLElement $item);

}
