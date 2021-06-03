<?php

namespace Sxqibo\Logistics\common;

use Spatie\ArrayToXml\ArrayToXml;

class Utility
{
    /**
     * Convert an array to xml
     * @param $array array to convert
     * @param string $customRoot [$customRoot = '']
     * @return string
     */
    public static function arrayToXml(array $array, $customRoot = '')
    {
        return ArrayToXml::convert($array, $customRoot, true, 'UTF-8');
    }

    /**
     * Convert an xml string to an array
     * @param string $xmlstring
     * @return array
     */
    public static function xmlToArray($xmlstring)
    {
        return json_decode(json_encode(simplexml_load_string($xmlstring)), true);
    }

    public static function validateData($data)
    {

        return true;
    }
}
