<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-03
 * Time: 11:14
 */

namespace Wormhole\Protocols\Library;


class CheckSumCS implements \JsonSerializable
{

    use Tools;
    private $value=0;
    public $length;

    public function __construct($length=1)
    {
        $this->length=$length;
    }
    public function getValue(){
        return $this->value;
    }

    public function __invoke($value="", $position=0)
    {

        if(!is_array($value)){
            $str = substr($value, $position, $this->length);
            $this->value = intval( self::arrayToDec( self::asciiStringToDecArray($str)));
            return;
        }

        $this->value = self::getBCCByPlus($value,$this->length);
        return;
    }

    public function __toString()
    {
        return self::decArrayToAsciiString(self::decToArray($this->value,$this->length));
    }

    function jsonSerialize()
    {
        $array = [];
        foreach ($this as $key=>$value){

            $array[$key]= $value;
        }
        return $array;
    }
}