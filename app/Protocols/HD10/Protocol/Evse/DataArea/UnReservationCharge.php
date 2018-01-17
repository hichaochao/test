<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 18:14
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;


use Wormhole\Protocols\Tools;

class UnReservationCharge
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    /**
     * @var int
     * 响应结果
     */
    private $resultCode;

    /**
     * @param $evseCode
     */
    public function setEvseCode($evseCode){
        $this->evseCode = $evseCode;
    }

    public function getEvseCode(){
        return $this->evseCode;
    }

    /**
     * @param $resultCode
     */
    public function setResultCode($resultCode){
        $this->resultCode = $resultCode;
    }

    public function getResultCode(){
        return $this->resultCode;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);
        
        array_push($frame,$this->resultCode);

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));

        $offset+=8;
        $this->resultCode = array_slice($dataArea,$offset,1)[0];
    }
}