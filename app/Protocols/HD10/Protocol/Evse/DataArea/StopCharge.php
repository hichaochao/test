<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 10:17
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;


use Wormhole\Protocols\Tools;

class StopCharge
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    private $resultCode;

    /**
     * @return mixed
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * @param mixed $resultCode
     */
    public function setResultCode($resultCode)
    {
        $this->resultCode = $resultCode;
    }

    /**
     * @param $evseCode
     */
    public function setEvseCode($evseCode){
        $this->evseCode = $evseCode;
    }

    public function getEvseCode(){
        return $this->evseCode;
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