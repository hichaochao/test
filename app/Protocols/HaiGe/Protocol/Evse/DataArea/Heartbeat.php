<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 15:42
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea;

use Wormhole\Protocols\Tools;

class Heartbeat
{

    /**
     * 预约状态
     * @var int
     */
    private $appointmentStatus;
    /**
     * 充电桩状态
     * @var int
     */
    private $evseStatus;

    /**
     * 本次充电电量
     *@var int
     */
    private $chargePower;

    /**
     * 本次充电金额
     *@var int
     */
    private $chargeMoney;

    /**
     * 停车位状态
     *@var int
     */
    private $parkStatus;

    /**
     * 停车位锁是否开启
     *@var int
     */
    private $isLock;

    /**
     * 故障代码
     *@var int
     */
    private $fault;

    /**
     * 充电电压
     *@var int
     */
    private $chargeVoltage;

    /**
     * 充电电流
     *@var int
     */
    private $chargeCurrent;

    /**
     * 充电时间
     *@var int
     */
    private $chargeTime;

    /**
     * 输出功率
     *@var int
     */
    private $power;

    /**
     * 充电接口状态
     *@var int
     */
    private $interfaceStatus;

    /**
     * 当前荷电状态
     *@var int
     */
    private $socStatus;

    /**
     * 估算剩余充电时间
     *@var int
     */
    private $leftTime;

    /**
     * 详细故障代码
     *@var int
     */
    private $detailFault;

    /**
     * 预留
     *@var int
     */
    private $reserve;



    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getAppointmentStatus()
    {
        return $this->appointmentStatus;
    }


    /**
     * @param int $appointmentStatus
     */
    public function setAppointmentStatus($appointmentStatus)
    {
        $this->appointmentStatus = $appointmentStatus;
    }


    /**
     * @param int $appointmentStatus
     */
    public function setLockChannel($appointmentStatus)
    {
        $this->appointmentStatus = $appointmentStatus;
    }


    /**
     * @return int
     */
    public function getEvseStatus()
    {
        return $this->evseStatus;
    }

    /**
     * @param int $evseStatus
     */
    public function setEvseStatus($evseStatus)
    {
        $this->evseStatus = $evseStatus;
    }


    /**
     * @return int
     */
    public function getChargePower()
    {
        return $this->chargePower;
    }

    /**
     * @param int $chargePower
     */
    public function setChargePower($chargePower)
    {
        $this->chargePower = $chargePower;
    }



    /**
     * @return int
     */
    public function getChargeMoney()
    {
        return $this->chargeMoney;
    }

    /**
     * @param int $chargeMoney
     */
    public function setChargeMoney($chargeMoney)
    {
        $this->chargeMoney = $chargeMoney;
    }


    /**
     * @return int
     */
    public function getParkStatus()
    {
        return $this->parkStatus;
    }

    /**
     * @param int $parkStatus
     */
    public function setParkStatus($parkStatus)
    {
        $this->parkStatus = $parkStatus;
    }


    /**
     * @return int
     */
    public function getIsLock()
    {
        return $this->isLock;
    }

    /**
     * @param int $isLock
     */
    public function setIsLock($isLock)
    {
        $this->isLock = $isLock;
    }


    /**
     * @return int
     */
    public function getFault()
    {
        return $this->fault;
    }

    /**
     * @param int $fault
     */
    public function setFault($fault)
    {
        $this->fault = $fault;
    }


    /**
     * @return int
     */
    public function getChargeVoltage()
    {
        return $this->chargeVoltage;
    }

    /**
     * @param int $chargeVoltage
     */
    public function setChargeVoltage($chargeVoltage)
    {
        $this->chargeVoltage = $chargeVoltage;
    }


    /**
     * @return int
     */
    public function getChargeCurrent()
    {
        return $this->chargeCurrent;
    }

    /**
     * @param int $chargeCurrent
     */
    public function setChargeCurrent($chargeCurrent)
    {
        $this->chargeCurrent = $chargeCurrent;
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
    public function getInterfaceStatus()
    {
        return $this->interfaceStatus;
    }

    /**
     * @param int $interfaceStatus
     */
    public function setInterfaceStatus($interfaceStatus)
    {
        $this->power = $interfaceStatus;
    }


    /**
     * @return int
     */
    public function getSocStatus()
    {
        return $this->socStatus;
    }

    /**
     * @param int $socStatus
     */
    public function setSocStatus($socStatus)
    {
        $this->socStatus = $socStatus;
    }


    /**
     * @return int
     */
    public function getLeftTime()
    {
        return $this->leftTime;
    }

    /**
     * @param int $leftTime
     */
    public function setLeftTime($leftTime)
    {
        $this->leftTime = $leftTime;
    }


    /**
     * @return int
     */
    public function getDetailFault()
    {
        return $this->detailFault;
    }

    /**
     * @param int $detailFault
     */
    public function setDetailFault($detailFault)
    {
        $this->detailFault = $detailFault;
    }



    /**
     * @return int
     */
    public function getReserve()
    {
        return $this->reserve;
    }

    /**
     * @param int $reserve
     */
    public function setReserve($reserve)
    {
        $this->reserve = $reserve;
    }






    public function build(){

        $frame = array();
        array_push($frame, $this->appointmentStatus);//预约状态ra
        array_push($frame, $this->evseStatus);       //充电桩状态
        $frame = array_merge($frame,Tools::decToArray($this->chargePower,4)); //本次充电电量

        $frame = array_merge($frame,Tools::decToArray($this->chargeMoney,4)); //本次充电金额

        array_push($frame, $this->parkStatus);//停车位状态
        array_push($frame, $this->isLock);    //停车位锁是否开启
        array_push($frame, $this->fault);//故障代码

        $frame = array_merge($frame,Tools::decToArray($this->chargeVoltage,3)); //充电电压
        $frame = array_merge($frame,Tools::decToArray($this->chargeCurrent,3)); //充电电流
        $frame = array_merge($frame,Tools::decToArray($this->chargeTime,3)); //充电时间
        $frame = array_merge($frame,Tools::decToArray($this->power,3)); //输出功率

        array_push($frame, $this->interfaceStatus);//充电接口状态
        array_push($frame, $this->socStatus);       //当前荷电状态

        $frame = array_merge($frame,Tools::decToArray($this->leftTime,2)); //剩余时间
        $frame = array_merge($frame,Tools::decToArray($this->detailFault,4,false)); //详细故障代码
        $frame = array_merge($frame,Tools::decToArray($this->reserve,10,false)); //预留

        return $frame;

    }

    public function load($dataArea){

        $offset = 0;
        $this->appointmentStatus = $dataArea[$offset];
        $offset++;
        $this->evseStatus = $dataArea[$offset];
        $offset++;
        $this->chargePower = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->chargeMoney = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;

        $this->parkStatus = $dataArea[$offset];
        $offset++;
        $this->isLock = $dataArea[$offset];
        $offset++;
        $this->fault = $dataArea[$offset];
        $offset++;

        $this->chargeVoltage = Tools::arrayToDec(array_slice($dataArea,$offset,3));
        $offset = $offset+3;
        $this->chargeCurrent = Tools::arrayToDec(array_slice($dataArea,$offset,3));
        $offset = $offset+3;
        $this->chargeTime = Tools::arrayToDec(array_slice($dataArea,$offset,3));
        $offset = $offset+3;
        $this->power = Tools::arrayToDec(array_slice($dataArea,$offset,3));
        $offset = $offset+3;

        $this->interfaceStatus = $dataArea[$offset];
        $offset++;
        $this->socStatus = $dataArea[$offset];
        $offset++;

        $this->leftTime = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->detailFault = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->reserve = Tools::arrayToDec(array_slice($dataArea,$offset,10));
        $offset = $offset+10;

        //return array($this->chargePower, $this->chargeMoney, $this->chargeVoltage, $this->chargeCurrent, $this->power);

    }
}