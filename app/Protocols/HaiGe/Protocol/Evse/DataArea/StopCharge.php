<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-10-21
 * Time: 18:05
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea;

use Wormhole\Protocols\Tools;

class StopCharge
{

    /**
     * @var int 充电口号
     */
    private $gunNum;
    /**
     * @var int 控制类型
     */
    private $controlType;
    /**
     * @var int 用户卡号
     */
    private $userCard;

    /**
     * @var int 累计充电时间
     */
    private $chargeTime;
    /**
     * @var int 中止荷电状态
     */
    private $chargeStatus;

    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getGunNum()
    {
        return $this->gunNum;
    }

    /**
     * @param int $gunNum
     */
    public function setGunNum($gunNum)
    {
        $this->gunNum = $gunNum;
    }


    /**
     * @return int
     */
    public function getControlType()
    {
        return $this->controlType;
    }

    /**
     * @param int $controlType
     */
    public function setControlType($controlType)
    {
        $this->controlType = $controlType;
    }

    /**
     * @return int
     */
    public function getUserCard()
    {
        return $this->userCard;
    }

    /**
     * @param int $userCard
     */
    public function setUserCard($userCard)
    {
        $this->userCard = $userCard;
    }


    /**
     * @return int
     */
    public function getChargeTime()
    {
        return $this->chargeTime;
    }

    /**
     * @param int $chargeTime
     */
    public function setChargeTime($chargeTime)
    {
        $this->chargeTime = $chargeTime;
    }


    /**
     * @return int
     */
    public function getChargeStatus()
    {
        return $this->chargeStatus;
    }

    /**
     * @param int $chargeStatus
     */
    public function setChargeStatus($chargeStatus)
    {
        $this->chargeStatus = $chargeStatus;
    }


    public function build(){

        $frame = array();
        array_push($frame, $this->gunNum);//充电口号
        array_push($frame, $this->controlType);//控制类型
        $frame = array_merge($frame, Tools::decToDbcArray($this->userCard, 10));//用户卡号
        $frame = array_merge($frame, Tools::decToArray($this->chargeTime, 2, false));//累计充电时间
        array_push($frame, $this->chargeStatus);//中止荷电状态
        return $frame;
    }


    public function load($dataArea){

        $offset = 0;
        $this->gunNum = $dataArea[$offset];
        $offset++;
        $this->controlType = $dataArea[$offset];
        $offset++;
        $this->userCard = Tools::dbcArrayTodec(array_slice($dataArea,$offset,10));
        $offset = $offset+10;
        $this->chargeTime = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->chargeStatus = $dataArea[$offset];
        $offset++;
        return $this;
    }

}