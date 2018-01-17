<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 17:03
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;


use Wormhole\Protocols\Tools;

class ReadChargeLogHistory
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;
    /**
     * @var int 0:无效，1：有效
     */
    private $isValid;

    /**
     * @var int
     * 用户id
     */
    private $userId;
    /**
     * @var int
     * 开始时间戳
     */
    private $startChargeTime;

    /**
     * @var int
     * 结束时间戳
     */
    private $stopChargeTime;

    /**
     * @var int
     * 电量
     */
    private $power;

    /**
     * @var int
     * 金额
     */
    private $money;

    /**
     * @return string
     */
    public function getEvseCode()
    {
        return $this->evseCode;
    }

    /**
     * @param string $evseCode
     */
    public function setEvseCode($evseCode)
    {
        $this->evseCode = $evseCode;
    }

    /**
     * @return int
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * @param int $isValid
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getStartChargeTime()
    {
        return $this->startChargeTime;
    }

    /**
     * @param int $startChargeTime
     */
    public function setStartChargeTime($startChargeTime)
    {
        $this->startChargeTime = $startChargeTime;
    }

    /**
     * @return int
     */
    public function getStopChargeTime()
    {
        return $this->stopChargeTime;
    }

    /**
     * @param int $stopChargeTime
     */
    public function setStopChargeTime($stopChargeTime)
    {
        $this->stopChargeTime = $stopChargeTime;
    }

    /**
     * @return int
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * @param int $power
     */
    public function setPower($power)
    {
        $this->power = $power;
    }

    /**
     * @return int
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * @param int $money
     */
    public function setMoney($money)
    {
        $this->money = $money;
    }



    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        $frame[] = $this->isValid;

        $frame = array_merge($frame,Tools::decToArray($this->userId,8),FALSE);

        $frame = array_merge($frame,Tools::decToArray($this->startChargeTime,4,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->stopChargeTime,4,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->power,4,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->money,4,FALSE));

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->isValid = $dataArea[$offset++];

        $this->userId = Tools::arrayToDec(array_slice($dataArea,$offset,8),FALSE);;
        $offset+=8;

        $this->startChargeTime = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;

        $this->stopChargeTime = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;

        $this->power = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;

        $this->money = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
    }
}