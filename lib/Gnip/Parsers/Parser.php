<?php
namespace Gnip\Parsers;
use Gnip\Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: blacky
 * Date: 02.04.12
 * Time: 21:09
 * To change this template use File | Settings | File Templates.
 */
class Parser extends AbsctractParser
{
    /**
     * @param string $string
     * @param string $parserName
     *
     * @return array
     */
    public function parse($string, $parserName = null)
    {
        $parserClass = $this->_getClass($parserName);

        var_dump($parserClass);
        return $parserClass->parse($string);
    }

    /**
     * @param \SimpleXMLElement $item
     * @param string            $parserName
     *
     * @return array
     */
    public function parseItem(\SimpleXMLElement $item, $parserName = null)
    {
        $parserClass = $this->_getClass($parserName);

        return $parserClass->parseItem($item);
    }

    /**
     * @param $parserName
     *
     * @return ParserInterface
     */
    private function _getClass($parserName)
    {
        $className = __NAMESPACE__ . '\\' . 'BaseParser';
        if (!is_null($parserName)) {
            $className = __NAMESPACE__ . '\\' . ucfirst($parserName) . 'Parser';

            //check if class exist. false to prevent autoload
            if (!class_exists($className)) {
                throw new Exception\ParserException('No parser class with name '. $className);
            }
        }

        return new $className;
    }
}