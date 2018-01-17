<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:14
 */

namespace Wormhole\Protocols\Library;


class BIN implements \JsonSerializable
{
    use Tools;
    private $value=0;
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
    public function getClassNamw(){
        return BIN::class;
    }
    public function __invoke($value="")
    {

        if(empty($value)){
            $this->value=0;
            return;
        }

        if(is_string($value)){
            //$this->value =intval( self::arrayToDec( self::asciiStringToDecArray($value),$this->dir));
            $this->value =intval( self::arrayToDec( self::asciiStringToDecArray($value),$this->dir));
            //$position = $position+$this->length; //当前字段位置
            //$value = substr($value, $position);  //移到下一位
            //return $position;
        }

        if(is_int($value)){
            $this->value =$value;
            return;
        }

    }

    public function __toString()
    {

        return self::decArrayToAsciiString(self::decToArray($this->value,$this->length,$this->dir));
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