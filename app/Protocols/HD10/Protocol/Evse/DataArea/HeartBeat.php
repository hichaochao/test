<?php
namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;


use Wormhole\Protocols\Tools;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 15:31
 */
class HeartBeat
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    /**
     * @var int
     * 充电类型
     */
    private $chargeType;

    /**
     * @var int
     * 警告状态
     */
    private $warningType;

    /**
     * @var int
     * 枪状态
     */
    private $gunType;

    /**
     * @var int
     * 急停状态
     */
    private $emergencyType;

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
     * @param $ChargeType
     */
    public function setChargeType($chargeType){
        $this->chargeType=$chargeType;
    }

    public function getChargeType(){
        return $this->chargeType;
    }

    /**
     * @param $warningType
     */
    public function setWarningType($warningType){
        $this->warningType=$warningType;
    }

    public function getWarningType(){
        return $this->warningType;
    }

    /**
     * @param $gunType
     */
    public function setGunType($gunType){
        $this->gunType=$gunType;
    }

    public function getGunType(){
        return $this->gunType;
    }

    /**
     * @param $emergencyType
     */
    public function setEmergencyType($emergencyType){
        $this->emergencyType=$emergencyType;
    }

    public function getEmergencyType(){
        return $this->emergencyType;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        array_push($frame,$this->chargeType);
        array_push($frame,$this->warningType);
        array_push($frame,$this->gunType);
        array_push($frame,$this->emergencyType);


        return $frame;
    }

    public function load($dataArea){
        $offset = 0;

        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->chargeType = array_slice($dataArea,$offset,1)[0];
        $offset+=1;

        $this->warningType = array_slice($dataArea,$offset,1)[0];
        $offset+=1;

        $this->gunType =  array_slice($dataArea,$offset,1)[0];
        $offset+=1;

        $this->emergencyType =  array_slice($dataArea,$offset,1)[0];
        $offset+=1;
    }
}