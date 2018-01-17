<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea;

use Wormhole\Protocols\Tools;

class StartCharge
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
     * @var int 开启限制数据
     */
    private $startData;

    /**
     * @var int 定时启动
     */
    private $timerStart;

    /**
     * @var int 用户卡号
     */
    private $userCard;



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
    public function getStartData()
    {
        return $this->startData;
    }

    /**
     * @param int $startData
     */
    public function setStartData($startData)
    {
        $this->startData = $startData;
    }


    /**
     * @return int
     */
    public function getTimerStart()
    {
        return $this->timerStart;
    }

    /**
     * @param int $timerStart
     */
    public function setTimerStart($timerStart)
    {
        $this->timerStart = $timerStart;
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






    public function build()
    {
        $frame = array();
        array_push($frame, $this->gunNum);//充电口号
        array_push($frame, $this->controlType);//控制类型

        $frame = array_merge($frame,Tools::decToArray($this->startData,4)); //控制类型
        $frame = array_merge($frame, Tools::decToDbcArray($this->timerStart, 4));//定时启动
        $frame = array_merge($frame, Tools::decToDbcArray($this->userCard, 10));//用户卡号
        return $frame;

    }

    public function load($dataArea)
    {

        $offset = 0;
        $this->gunNum = $dataArea[$offset];
        $offset++;
        $this->controlType = $dataArea[$offset];
        $offset++;
        $this->startData = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->timerStart = Tools::dbcArrayTodec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->userCard = Tools::dbcArrayTodec(array_slice($dataArea,$offset,10));
        $offset = $offset+10;

    }

}