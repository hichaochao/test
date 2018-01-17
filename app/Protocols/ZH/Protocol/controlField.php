<?php
/**
 * Created by PhpStorm.
 * User: chao
 * Date: 2017/4/21
 * Time: 14:05
 */

namespace Wormhole\Protocols\ZH\Protocol;


use Wormhole\Protocols\Library\BIN;
use Wormhole\Protocols\Library\BIT;
use Wormhole\Protocols\Library\BitArray;
use Wormhole\Protocols\Library\Tools;
class controlField extends BIT
{

    protected $controlField = [
        BitArray::class,[['retain',6],['flag_bit',1],['direction_bit',1]],1
    ];



    public $data;
    public $length = 1;
    public $value='';//001300150010001100120013
    public $dir = TRUE;


    public function __construct($length=4, $dir=TRUE, $data=0)
    {
        $this->length=$length;
        $this->dir = $dir;
        $this->data = $data;
    }

    public function getValue(){
        return $this->data;
    }

    public function __invoke($value = "", $position=0)
    {

        
        if(empty($value)){
            $this->value=0;
            return;
        }

        if(is_string($value)){

            //控制域
            $bitArray = new $this->controlField[0]($this->controlField[1],$this->controlField[2]);
            //$this->data[$i]['status'] = $bitArray;
            //call_user_func($bitArray, $value, $position);
            $bitArray($value, $position);
            $this->data = $bitArray->getValue();

        }

        if(is_int($value)){
            $this->value =$value;
            return;
        }




    }




    public function __toString()
    {

        if(empty($this->data)){
            return Tools::decArrayToAsciiString(Tools::decToArray($this->value,$this->length,$this->dir));
        }

        $bitArray = new $this->controlField[0]($this->controlField[1],$this->controlField[2], $this->data);
        $str = strval($bitArray);
        return $str;
    }


}