<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 17:59
 */

namespace Wormhole\Protocols\ZH\Protocol;

use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\Library\ASCII;
use Wormhole\Protocols\Library\BIN;
use Wormhole\Protocols\Library\BCD;
use Wormhole\Protocols\Library\CheckSumCS;
use Wormhole\Protocols\Library\Tools;

class Frame implements \JsonSerializable
{
    public $isValid = FALSE;
    //起始字符
    protected  $start = 0x68;
    //长度
    protected $length = [BIN::class,2,TRUE];
    //版本V
    protected $version = [BCD::class,2,TRUE];
    //控制域C
    protected $control_field = [controlField::class,1,FALSE];

    //地址域A
    //行政区划码A1
    protected $division_code = [BCD::class,3,TRUE];
    //终端地址A2
    protected $terminal_address = [BCD::class,6,FALSE];
    //主站地址和组地址标志A3
    protected $master_station_address = [masterStationAddress::class,1,FALSE];

    //应用层功能码
    protected $afn = [BIN::class,1,TRUE];
    //帧序列域SEQ
    protected $seq = [sequenceDomain::class,1,TRUE];
    //数据单元标识
    protected $fn = [BIN::class,1,TRUE];
    //帧校验和
    protected $check = [CheckSumCS::class,1,FALSE];

    //结束字符
    protected $end = 0x16;


    public function __construct()
    {
        $properties = get_object_vars($this);

        foreach ($properties as $key=>$value){
            $repeat = count($value);
            
            if(1 == $repeat && !class_exists($value)){
                $this->$key = $value;
                continue;
            }

            if(1 == $repeat && class_exists($value[0])){

                $this->$key = new $value[0];
                continue;
            }

            $class = $value[0];
            $length = 1 < $repeat ? $value[1] : NULL;
            $dir = 3 == $repeat ? $value[2] : NULL;
            if(class_exists($class)){
                if(is_null($length) && is_null($dir)){

                    $this->$key = new $class();
                    continue;
                }

                if(!is_null($length) && is_null($dir)){

                    $this->$key = new $class($length);
                    continue;
                }

                if(!is_null($length) && !is_null($dir)){
                    $this->$key = new $class($length,$dir);
                    continue;
                }
            }

        }

    }

    public function __toString()
    {

        $properties = get_object_vars($this);

        $this->unsetProperty($properties);

        $dataArea = '';

        foreach ($properties as $key=>$value){
            $dataArea .= $value;
        }

        $frameCheck = '';
        $frame = chr($this->start);
        $this->length(strlen($dataArea)+1+3+6+1+1+1+1);
        $frame .=strval($this->length);
        $frame .=strval($this->version);
        $frame .= chr($this->start);
        $frame .= $control_field = strval($this->control_field); //控制域
        $frame .= $division_code = strval($this->division_code); //行政区划码A1
        $frame .= $terminal_address = strval($this->terminal_address); //终端地址A2
        $frame .= $master_station_address = strval($this->master_station_address); //主站地址和组地址标志A3

        $afn = $this->afn($this->funCode);
        $frame .= $afn = strval($this->afn); //应用层功能码
        $frame .= $seq = strval($this->seq); //帧序列域SEQ

        $fn = $this->fn($this->identificat);
        $frame .= $fn = strval($this->fn); //数据单元标识

        $frame .=strval($dataArea); //数据单元
        $frameCheck = $control_field.$division_code.$terminal_address.$master_station_address.$afn.$seq.$fn.$dataArea;
        $frameArr = Tools::asciiStringToDecArray($frameCheck);
        //校验
        $this->check($frameArr);
        $frame.= strval($this->check);

        $frame .= chr($this->end);

        return $frame;
    }


    public function __get($name)
    {

        return $this->$name;
    }
    function jsonSerialize()
    {
        $array = [];
        foreach ($this as $key=>$value){

            $array[$key]= $value;
        }
        return $array;
    }
    public function __call($name, $arguments)
    {
//         $this->$name = $arguments[0];

        if(is_object($this->$name)) {
            call_user_func($this->$name, $arguments[0]);
        }else{
            $type = gettype($this->$name);
            switch ($type){
                case "boolean":{
                    $this->$name=boolval($arguments[0]);
                    break;
                }
                case "string":{
                    $this->$name=strval($arguments[0]);
                    break;
                }
                case "float":{
                    $this->$name=floatval($arguments[0]);
                    break;
                }
                case "array":{
                    $this->$name=array_values($arguments[0]);
                    break;
                }
                case "object":{
                    call_user_func($this->$name, $arguments[0]);
                }
            }
        }
    }
    public function __invoke($value)
    {

        //起始域
        $position = 0;
        $properties = get_object_vars($this);
        $this->unsetProperty($properties);
        $startArea = new BIN(1, FALSE);
        $str = substr($value, $position, 1);
        $startArea($str);


        if ($startArea->getValue() != $this->start) { //无有效开始
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 无效开始 start:".$startArea->getValue(). 'value:$value');
            return false;
        }
        $position++;

        //数据长度
        $leng = $this->length;
        $str = substr($value, $position, 2);
        $leng($str);
        $position = $position + 2;

        //版本V
        $version = $this->version;
        $str = substr($value, $position, 2);
        $version($str);
        $position = $position + 2;

        $startArea = new BIN(1, FALSE);
        $str = substr($value, $position, 1);
        $startArea($str);
        $position++;

        $checkPos = $position;
        //控制域C
        $controlField = $this->control_field;
        $str = substr($value, $position, 1);
        $controlField($str);
        $position = $position + 1;

        //行政区划码
        $divisiConode = $this->division_code;
        $str = substr($value, $position, 3);
        $divisiConode($str);
        $position = $position + 3;

        //终端地址
        $terminalAddress = $this->terminal_address;
        $str = substr($value, $position, 6);
        $terminalAddress($str);
        $position = $position + 6;

        //主站地址和组地址标志A3
        $masterStationAddress = $this->master_station_address;
        $str = substr($value, $position, 1);
        $masterStationAddress($str);
        $position = $position + 1;

        //应用层功能码
        $afn = $this->afn;
        $str = substr($value, $position, 1);
        $afn($str);
        $position = $position + 1;

        //帧序列域SEQ
        $seq = $this->seq;
        $str = substr($value, $position, 1);
        $seq($str);
        $position = $position + 1;

        //数据单元标识
        $fn = $this->fn;
        $str = substr($value, $position, 1);
        $fn($str);
        $position = $position + 1;

        //数据域
        foreach ($properties as $key => $v) {

            $length =  empty($value) ? 0:$this->$key->length;
            $faild = $this->$key;
            $str = substr($value, $position, $length);
            $faild($str);
            $position = $position + $length;
        }

        $check = $this->check;
        $str = substr($value, $position, 1);
        $check($str);
        $check1 = $check->getValue();

        //校验
        $frame = substr($value, $checkPos, strlen($value) - $checkPos - 2);
        $frameArr = Tools::asciiStringToDecArray($frame);
        $this->check($frameArr);
        $check2 = $this->check->getValue();

//        if($check1 != $check2){
//            return false;
//        }
        $position++;
        //结束字符
        $endArea = new BIN(1, FALSE);
        $str = substr($value, $position, 1);
        $endArea($str);


        $this->isValid = TRUE;

        return $this;
    }

    private function unsetProperty(array &$properties){

        unset($properties['start']);
        unset($properties['length']);
        //命令码
        unset($properties['version']);
        //功能码
        unset($properties['control_field']);
        unset($properties['division_code']);


        unset($properties['terminal_address']);
        unset($properties['master_station_address']);

        unset($properties['afn']);
        unset($properties['seq']);
        unset($properties['fn']);

        unset($properties['funCode']);
        unset($properties['identificat']);

        unset($properties['check']);
        unset($properties['end']);

        unset($properties['isValid']);
    }

}