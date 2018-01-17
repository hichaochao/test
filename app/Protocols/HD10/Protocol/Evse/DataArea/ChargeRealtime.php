<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 11:51
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;


use Wormhole\Protocols\Tools;

class ChargeRealtime
{
    /**
     * @var string
     */
    private $evseCode;

    /**
     * @var int
     * 功率
     */
    private $power;

    /**
     * @var int
     * 金额
     */
    private $money;

    /**
     * @var int
     * 电压
     */
    private $voltage;

    /**
     * @var int
     * 电流
     */
    private $electricCurrent;

    /**
     * @var int
     * 充电时间戳
     */
    private $chargeTime;
    /**
     * 电量
     * @var int
     */
    private $chargedPower;



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

    /**
     * @return int
     */
    public function getVoltage()
    {
        return $this->voltage;
    }

    /**
     * @param int $voltage
     */
    public function setVoltage($voltage)
    {
        $this->voltage = $voltage;
    }

    /**
     * @return int
     */
    public function getElectricCurrent()
    {
        return $this->electricCurrent;
    }

    /**
     * @param int $electricCurrent
     */
    public function setElectricCurrent($electricCurrent)
    {
        $this->electricCurrent = $electricCurrent;
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
    public function getChargedPower()
    {
        return $this->chargedPower;
    }

    /**
     * @param int $chargedPower
     */
    public function setChargedPower($chargedPower)
    {
        $this->chargedPower = $chargedPower;
    }


    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        $frame = array_merge($frame,Tools::decToArray($this->voltage,2,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->electricCurrent,2,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->chargeTime,4,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->power,4,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->money,4,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->chargedPower,4,FALSE));

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->voltage = Tools::arrayToDec(array_slice($dataArea,$offset,2),FALSE);
        $offset+=2;

        $this->electricCurrent = Tools::arrayToDec(array_slice($dataArea,$offset,2),FALSE);
        $offset+=2;

        $this->chargeTime = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;

        $this->chargedPower = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;

        $this->money = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;

        $this->power = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;
    }
}