<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 14:42
 */

namespace Wormhole\Protocols\NJINT\Protocol\Base;


class MY_Tools
{
    public static function asciiToDecArrayWithLength($asciiString,$len,$value=0){
        $strArray = \Wormhole\Protocols\Tools::asciiStringToDecArray( $asciiString);
        $tmpArray = array_fill(0,$len,$value);
        $tmpArray = array_merge($strArray,$tmpArray);
        $strArray = array_slice($tmpArray,0,$len);

        return $strArray;
    }
}