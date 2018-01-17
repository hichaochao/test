<?php
namespace Wormhole\Protocols\HD10\Protocol\Server\DataArea;

use Wormhole\Protocols\Tools;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 17:15
 */
class StartCharge
{
    //用户id
    private $userId;

    //充电类型
    private $chargeType;

    //充电付款方式
    private $chargePayType;

    //充电参数
    private $chargeParameter;

    //用户余额
    private $userBalance;

    //桩地址
    private $evseCode;

    /**
     * @param $userId
     */
    public function setUserId($userId){
        $this->userId = $userId;
    }

    public function getUserId(){
        return $this->userId;
    }

    /**
     * @param $chargeType
     */
    public function setChargeType($chargeType){
        $this->chargeType = $chargeType;
    }

    public function getChargeType(){
        return $this->chargeType;
    }

    /**
     * @param $chargePayType
     */
    public function setChargePayType($chargePayType){
        $this->chargePayType = $chargePayType;
    }

    public function getChargePayType(){
        return $this->chargePayType;
    }

    /**
     * @param $chargeParameter
     */
    public function setChargeParameter($chargeParameter){
        $this->chargeParameter = $chargeParameter;
    }

    public function getChargeParameter(){
        return $this->chargeParameter;
    }

    /**
     * @param $userBalance
     */
    public function setUserBalance($userBalance){
        $this->userBalance = $userBalance;
    }

    public function getUserBalance(){
        return $this->userBalance;
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
        $frame = array_merge(Tools::asciiStringToDecArray($this->evseCode),Tools::decToArray($this->userId,8,FALSE));

        array_push($frame,$this->chargePayType);
        array_push($frame,$this->chargeType);
        $frame = array_merge($frame,Tools::decToArray($this->chargeParameter,4,FALSE));
        $frame = array_merge($frame,Tools::decToArray($this->userBalance,4,FALSE));

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->userId = Tools::arrayToDec(array_slice($dataArea,$offset,8,FALSE));
        $offset+=8;

        $this->chargePayType =array_slice($dataArea,$offset,1)[0];
        $offset+=1;

        $this->chargeType =array_slice($dataArea,$offset,1)[0];
        $offset+=1;

        $this->chargeParameter = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;

        $this->userBalance =  Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;
    }
}