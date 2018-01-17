<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:14
 */

namespace Wormhole\Protocols\Library;


class BCD implements \JsonSerializable
{
    use Tools;
    private $value;
    public $length;
    protected $dir=TRUE;

    public function __construct($length=4,$dir=true)
    {
        $this->length=$length;
        $this->dir = $dir;
    }
    public function getValue(){
        return $this->value;
    }

    public function __invoke($value="")//, $position=0
    {

        if(empty($value)){
            $this->value=0;
            return;
        }

        if(is_string($value)){
            //$str = substr($value, $position, $this->length);
            $this->value=Tools::asciiStringToHexArray($value);
            //$position = $position+$this->length; //当前字段位置
        }

        if(is_int($value)){
            $this->value =$value;
            return;
        }

    }

    public function __toString()
    {
        
        //return self::decArrayToAsciiString(self::decToArray($this->value,$this->length,$this->dir));
        return self::decArrayToAsciiString(self::decToArray(self::bcd_compress($this->value),$this->length,$this->dir));

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