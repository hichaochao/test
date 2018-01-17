<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-03
 * Time: 15:19
 */

namespace Wormhole\Protocols\Library;


class CheckXOR implements \JsonSerializable
{

    use Tools;
    private $value=0;
    public $length;

    public function __construct($length=2)
    {
        $this->length=$length;
    }
    public function getValue(){
        return $this->value;
    }

    public function __invoke($value="")
    {
        if(!is_array($value)){
            $value = self::asciiStringToDecArray($value);
        }
        $this->value = self::getBCCByOr($value,$this->length);

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