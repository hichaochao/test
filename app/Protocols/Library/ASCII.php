<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:14
 */

namespace Wormhole\Protocols\Library;


class ASCII implements \JsonSerializable
{
    use Tools;
    private $value="";
    public $length;

    public function __construct($length=4,$dir=true)
    {
        $this->length=$length;
        $this->dir = $dir;
        $this->value = str_pad("",$length,chr(0x00));
    }
    public function getValue(){
        return $this->value;
    }

    public function __invoke($value="")
    {
        $this->value = $value;


    }

    public function __toString()
    {

        if(strlen($this->value) < $this->length){
            $this->value = str_pad($this->value,$this->length,chr(0x00));
        }else if(strlen($this->value) > $this->length){
            $this->value = substr($this->value,$this->length);
        }


        return substr( $this->value,0,$this->length);
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