<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-10-13
 * Time: 16:51
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Server\DataArea;
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





    public function build(){

        $frame = array();
        array_push($frame, $this->gunNum);//充电口号
        array_push($frame, $this->controlType);//控制类型
        $frame = array_merge($frame, Tools::decToDbcArray($this->userCard, 10));//用户卡号
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

    }


}