<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

class EvseStatus extends DataArea
{
    /**
     * @var array 协议预留1
     */
    private $reserved1;
    /**
     * @var array 协议预留2
     */
    private $reserved2;

    /**
     * 充电桩编号
     * @var string
     */
    private $evseCode;


    /**
     * 充电抢数量
     * @var int
     */
    private $gunAmount;
    /**
     * 充电口号
     * @var int
     */
    private $gunNum;
    /**
     * 充电抢类型
     * @var int
     */
    private $gunType;
    /**
     * 工作状态
     * @var int
     */
    private $workerStatus;
    /**
     * 当前SOC%
     * @var int
     */
    private $nowSOCPercent;
    /**
     * 告警状态
     * @var
     */
    private $warningStatus;

    /**
     * 车连接状态
     * @var int
     */
    private $carConnectStatus;
    /**
     * 本次充电累计充电费用
     * @var
     */
    private $thisChargingFee;
    /**
     * 内部变量2
     * @var unknown
     */
    private $reserved12;
    /**
     * 内部变量3
     * @var unknown
     */
    private $reserved13;
    /**
     * 直流充电电压 
     * @var string
     */
    private $dcChargeVoltage;
    /**
     * 直流充电电流 
     * @var string
     */
    private $dcChargeCurrent;
    /**
     * BMS需求电压 
     * @var string
     */
    private $BMSNeedVoltage;
    /**
     * BMS需求电流 
     * @var string
     */
    private $BMSNeedCurrent;
    /**
     * BMS充电模式 
     * @var int
     */
    private $BMSChargeModel;
    /**
     * 交流A相充电电压 
     * @var string
     */
    private $acAChargingVoltage;
    /**
     * 交流B相充电电压 
     * @var string
     */
    private $acBChargingVoltage;
    /**
     * 交流C相充电电压
     * @var string
     */
    private $acCChargingVoltage;
    /**
     * 交流A相充电电流 
     * @var string
     */
    private $acAChargingCurrent;
    /**
     * 交流B相充电电流 
     * @var string
     */
    private $acBChargingCurrent;
    /**
     * 交流C相充电电流 
     * @var string
     */
    private $acCChargingCurrent;
    /**
     * 剩余充电时间(min)
     * @var string
     */
    private $remainingChargingTime;
    /**
     * 充电时长(秒)
     * @var int
     */
    private $chargingDuration;
    /**
     * 本次充电累计充电电量
     * @var string
     */
    private $thisChargedPower;
    /**
     * 充电前电表读数 
     * @var string
     */
    private $meterPowerBefore;
    /**
     * 当前电表读数 
     * @var string
     */
    private $meterPowerNow;
    /**
     * 充电启动方式 
     * @var int
     */
    private $chargeStartType;
    /**
     * 充电策略 
     * @var int
     */
    private $chargeTactics;
    /**
     * 充电策略参数 
     * @var int
     */
    private $chargeTacticsArgs;
    /**
     * 预约标志 
     * @var int
     */
    private $reserveFlag;
    /**
     * 充电/预约卡号 
     * @var string
     */
    private $cardId;
    /**
     * 超时时间
     * @var int
     */
    private $timeoutTime;
    /**
     * 预约/开始充电开始时间
     * @var string
     */
    private $chargeStartTime;
    /**
     * 充电前卡余额 
     * @var string
     */
    private $cardBalanceBefore;
    /**
     * 升级模式 
     * @var int
     */
    private $upgradeModel;
    /**
     * 充电功率 
     * @var string
     */
    private $power;
    /**
     * 系统变量3 
     * @var unknown
     */
    private $reserved40;

    /**
     * 系统变量4
     * @var unknown
     */
    private $reserved41;
    /**
     * 系统变量5 
     * @var unknown
     */
    private $reserved42;





    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
        $this->reserved12=[0x00,0x00,0x00,0x00];
        $this->reserved13=[0x00,0x00,0x00,0x00];
        $this->reserved40=[0x00,0x00,0x00,0x00];
        $this->reserved41=[0x00,0x00,0x00,0x00];
        $this->reserved42=[0x00,0x00,0x00,0x00];
    }





    /**
     * @return array
     */
    public function getReserved1()
    {
        return $this->reserved1;
    }

    /**
     * @param array $reserved1
     */
    public function setReserved1($reserved1)
    {
        $this->reserved1 = $reserved1;
    }

    /**
     * @return array
     */
    public function getReserved2()
    {
        return $this->reserved2;
    }

    /**
     * @param array $reserved2
     */
    public function setReserved2($reserved2)
    {
        $this->reserved2 = $reserved2;
    }

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
    public function getGunAmount()
    {
        return $this->gunAmount;
    }

    /**
     * @param int $gunAmount
     */
    public function setGunAmount($gunAmount)
    {
        $this->gunAmount = $gunAmount;
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
    public function getGunType()
    {
        return $this->gunType;
    }

    /**
     * @param int $gunType
     */
    public function setGunType($gunType)
    {
        $this->gunType = $gunType;
    }

    /**
     * @return int
     */
    public function getWorkerStatus()
    {
        return $this->workerStatus;
    }

    /**
     * @param int $workerStatus
     */
    public function setWorkerStatus($workerStatus)
    {
        $this->workerStatus = $workerStatus;
    }

    /**
     * @return int
     */
    public function getNowSOCPercent()
    {
        return $this->nowSOCPercent;
    }

    /**
     * @param int $nowSOCPercent
     */
    public function setNowSOCPercent($nowSOCPercent)
    {
        $this->nowSOCPercent = $nowSOCPercent;
    }
    /**
     * @return mixed
     */
    public function getWarningStatus()
    {
        return $this->warningStatus;
    }

    /**
     * @param mixed $warningStatus
     */
    public function setWarningStatus($warningStatus)
    {
        $this->warningStatus = $warningStatus;
    }
    /**
     * @return int
     */
    public function getCarConnectStatus()
    {
        return $this->carConnectStatus;
    }

    /**
     * @param int $carConnectStatus
     */
    public function setCarConnectStatus($carConnectStatus)
    {
        $this->carConnectStatus = $carConnectStatus;
    }

    /**
     * @return mixed
     */
    public function getThisChargingFee()
    {
        return $this->thisChargingFee;
    }

    /**
     * @param mixed $thisChargingFee
     */
    public function setThisChargingFee($thisChargingFee)
    {
        $this->thisChargingFee = $thisChargingFee;
    }

    /**
     * @return mixed
     */
    public function getReserved12()
    {
        return $this->reserved12;
    }

    /**
     * @param mixed $reserved12
     */
    public function setReserved12($reserved12)
    {
        $this->reserved12 = $reserved12;
    }

    /**
     * @return mixed
     */
    public function getReserved13()
    {
        return $this->reserved13;
    }

    /**
     * @param mixed $reserved13
     */
    public function setReserved13($reserved13)
    {
        $this->reserved13 = $reserved13;
    }

    /**
     * @return mixed
     */
    public function getDcChargeVoltage()
    {
        return $this->dcChargeVoltage;
    }

    /**
     * @param mixed $dcChargeVoltage
     */
    public function setDcChargeVoltage($dcChargeVoltage)
    {
        $this->dcChargeVoltage = $dcChargeVoltage;
    }

    /**
     * @return mixed
     */
    public function getDcChargeCurrent()
    {
        return $this->dcChargeCurrent;
    }

    /**
     * @param mixed $dcChargeCurrent
     */
    public function setDcChargeCurrent($dcChargeCurrent)
    {
        $this->dcChargeCurrent = $dcChargeCurrent;
    }

    /**
     * @return mixed
     */
    public function getBMSNeedVoltage()
    {
        return $this->BMSNeedVoltage;
    }

    /**
     * @param mixed $BMSNeedVoltage
     */
    public function setBMSNeedVoltage($BMSNeedVoltage)
    {
        $this->BMSNeedVoltage = $BMSNeedVoltage;
    }

    /**
     * @return mixed
     */
    public function getBMSNeedCurrent()
    {
        return $this->BMSNeedCurrent;
    }

    /**
     * @param mixed $BMSNeedCurrent
     */
    public function setBMSNeedCurrent($BMSNeedCurrent)
    {
        $this->BMSNeedCurrent = $BMSNeedCurrent;
    }

    /**
     * @return mixed
     */
    public function getBMSChargeModel()
    {
        return $this->BMSChargeModel;
    }

    /**
     * @param mixed $BMSChargeModel
     */
    public function setBMSChargeModel($BMSChargeModel)
    {
        $this->BMSChargeModel = $BMSChargeModel;
    }

    /**
     * @return mixed
     */
    public function getAcAChargingVoltage()
    {
        return $this->acAChargingVoltage;
    }

    /**
     * @param mixed $acAChargingVoltage
     */
    public function setAcAChargingVoltage($acAChargingVoltage)
    {
        $this->acAChargingVoltage = $acAChargingVoltage;
    }

    /**
     * @return mixed
     */
    public function getAcBChargingVoltage()
    {
        return $this->acBChargingVoltage;
    }

    /**
     * @param mixed $acBChargingVoltage
     */
    public function setAcBChargingVoltage($acBChargingVoltage)
    {
        $this->acBChargingVoltage = $acBChargingVoltage;
    }

    /**
     * @return mixed
     */
    public function getAcCChargingVoltage()
    {
        return $this->acCChargingVoltage;
    }

    /**
     * @param mixed $acCChargingVoltage
     */
    public function setAcCChargingVoltage($acCChargingVoltage)
    {
        $this->acCChargingVoltage = $acCChargingVoltage;
    }

    /**
     * @return mixed
     */
    public function getAcAChargingCurrent()
    {
        return $this->acAChargingCurrent;
    }

    /**
     * @param mixed $acAChargingCurrent
     */
    public function setAcAChargingCurrent($acAChargingCurrent)
    {
        $this->acAChargingCurrent = $acAChargingCurrent;
    }

    /**
     * @return mixed
     */
    public function getAcBChargingCurrent()
    {
        return $this->acBChargingCurrent;
    }

    /**
     * @param mixed $acBChargingCurrent
     */
    public function setAcBChargingCurrent($acBChargingCurrent)
    {
        $this->acBChargingCurrent = $acBChargingCurrent;
    }

    /**
     * @return mixed
     */
    public function getAcCChargingCurrent()
    {
        return $this->acCChargingCurrent;
    }

    /**
     * @param mixed $acCChargingCurrent
     */
    public function setAcCChargingCurrent($acCChargingCurrent)
    {
        $this->acCChargingCurrent = $acCChargingCurrent;
    }

    /**
     * @return mixed
     */
    public function getRemainingChargingTime()
    {
        return $this->remainingChargingTime;
    }

    /**
     * @param mixed $remainingChargingTime
     */
    public function setRemainingChargingTime($remainingChargingTime)
    {
        $this->remainingChargingTime = $remainingChargingTime;
    }

    /**
     * @return mixed
     */
    public function getChargingDuration()
    {
        return $this->chargingDuration;
    }

    /**
     * @param mixed $chargingDuration
     */
    public function setChargingDuration($chargingDuration)
    {
        $this->chargingDuration = $chargingDuration;
    }

    /**
     * @return mixed
     */
    public function getThisChargedPower()
    {
        return $this->thisChargedPower;
    }

    /**
     * @param mixed $thisChargedPower
     */
    public function setThisChargedPower($thisChargedPower)
    {
        $this->thisChargedPower = $thisChargedPower;
    }

    /**
     * @return mixed
     */
    public function getMeterPowerBefore()
    {
        return $this->meterPowerBefore;
    }

    /**
     * @param mixed $meterPowerBefore
     */
    public function setMeterPowerBefore($meterPowerBefore)
    {
        $this->meterPowerBefore = $meterPowerBefore;
    }

    /**
     * @return mixed
     */
    public function getMeterPowerNow()
    {
        return $this->meterPowerNow;
    }

    /**
     * @param mixed $meterPowerNow
     */
    public function setMeterPowerNow($meterPowerNow)
    {
        $this->meterPowerNow = $meterPowerNow;
    }

    /**
     * @return mixed
     */
    public function getChargeStartType()
    {
        return $this->chargeStartType;
    }

    /**
     * @param mixed $chargeStartType
     */
    public function setChargeStartType($chargeStartType)
    {
        $this->chargeStartType = $chargeStartType;
    }

    /**
     * @return mixed
     */
    public function getChargeTactics()
    {
        return $this->chargeTactics;
    }

    /**
     * @param mixed $chargeTactics
     */
    public function setChargeTactics($chargeTactics)
    {
        $this->chargeTactics = $chargeTactics;
    }

    /**
     * @return mixed
     */
    public function getChargeTacticsArgs()
    {
        return $this->chargeTacticsArgs;
    }

    /**
     * @param mixed $chargeTacticsArgs
     */
    public function setChargeTacticsArgs($chargeTacticsArgs)
    {
        $this->chargeTacticsArgs = $chargeTacticsArgs;
    }

    /**
     * @return mixed
     */
    public function getReserveFlag()
    {
        return $this->reserveFlag;
    }

    /**
     * @param mixed $reserveFlag
     */
    public function setReserveFlag($reserveFlag)
    {
        $this->reserveFlag = $reserveFlag;
    }

    /**
     * @return mixed
     */
    public function getCardId()
    {
        return $this->cardId;
    }

    /**
     * @param mixed $cardId
     */
    public function setCardId($cardId)
    {
        $this->cardId = $cardId;
    }

    /**
     * @return mixed
     */
    public function getTimeoutTime()
    {
        return $this->timeoutTime;
    }

    /**
     * @param mixed $timeoutTime
     */
    public function setTimeoutTime($timeoutTime)
    {
        $this->timeoutTime = $timeoutTime;
    }

    /**
     * @return mixed
     */
    public function getChargeStartTime()
    {
        return $this->chargeStartTime;
    }

    /**
     * @param mixed $chargeStartTime
     */
    public function setChargeStartTime($chargeStartTime)
    {
        $this->chargeStartTime = $chargeStartTime;
    }

    /**
     * @return mixed
     */
    public function getCardBalanceBefore()
    {
        return $this->cardBalanceBefore;
    }

    /**
     * @param mixed $cardBalanceBefore
     */
    public function setCardBalanceBefore($cardBalanceBefore)
    {
        $this->cardBalanceBefore = $cardBalanceBefore;
    }

    /**
     * @return mixed
     */
    public function getUpgradeModel()
    {
        return $this->upgradeModel;
    }

    /**
     * @param mixed $upgradeModel
     */
    public function setUpgradeModel($upgradeModel)
    {
        $this->upgradeModel = $upgradeModel;
    }

    /**
     * @return mixed
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * @param mixed $power
     */
    public function setPower($power)
    {
        $this->power = $power;
    }

    /**
     * @return mixed
     */
    public function getReserved40()
    {
        return $this->reserved40;
    }

    /**
     * @param mixed $reserved40
     */
    public function setReserved40($reserved40)
    {
        $this->reserved40 = $reserved40;
    }

    /**
     * @return mixed
     */
    public function getReserved41()
    {
        return $this->reserved41;
    }

    /**
     * @param mixed $reserved41
     */
    public function setReserved41($reserved41)
    {
        $this->reserved41 = $reserved41;
    }

    /**
     * @return mixed
     */
    public function getReserved42()
    {
        return $this->reserved42;
    }






    /**
     * @param mixed $reserved42
     */
    public function setReserved42($reserved42)
    {
        $this->reserved42 = $reserved42;
    }












    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->evseCode,32,0)); //充电桩编号
        array_push($frame,$this->gunAmount);//充电抢数量
        array_push($frame,$this->gunNum);//充电口号
        array_push($frame,$this->gunType);//充电抢类型；
        array_push($frame,$this->workerStatus);//工作状态；

        array_push($frame,$this->nowSOCPercent);//当前SOC%；

        $frame = array_merge($frame,Tools::decToArray($this->warningStatus,4));//告警状态
        array_push($frame,$this->carConnectStatus);//车连接状态

        $frame = array_merge($frame,Tools::decToArray($this->thisChargingFee,4));//本次充电累计充电费用

        $frame = array_merge($frame,$this->reserved12); //预留12
        $frame = array_merge($frame,$this->reserved13); //预留13

        $frame = array_merge($frame,Tools::decToArray($this->dcChargeVoltage,2));//直流充电电压
        $frame = array_merge($frame,Tools::decToArray($this->dcChargeCurrent,2));//直流充电电流

        $frame = array_merge($frame,Tools::decToArray($this->BMSNeedVoltage,2));//BMS需求电压
        $frame = array_merge($frame,Tools::decToArray($this->BMSNeedCurrent,2));//BMS需求电流
        array_push($frame,$this->BMSChargeModel);//BMS充电模式

        $frame = array_merge($frame,Tools::decToArray($this->acAChargingVoltage,2));//交流A相充电电压
        $frame = array_merge($frame,Tools::decToArray($this->acBChargingVoltage,2));//交流B相充电电压
        $frame = array_merge($frame,Tools::decToArray($this->acCChargingVoltage,2));//交流C相充电电压
        $frame = array_merge($frame,Tools::decToArray($this->acAChargingCurrent,2));//交流A相充电电流
        $frame = array_merge($frame,Tools::decToArray($this->acBChargingCurrent,2));//交流B相充电电流
        $frame = array_merge($frame,Tools::decToArray($this->acCChargingCurrent,2));//交流C相充电电流

        $frame = array_merge($frame,Tools::decToArray($this->remainingChargingTime,2));//剩余充电时间(min)
        $frame = array_merge($frame,Tools::decToArray($this->chargingDuration,4));//充电时长
        $frame = array_merge($frame,Tools::decToArray($this->thisChargedPower,4));//本次充电累计充电电量       （0.01kwh）

        $frame = array_merge($frame,Tools::decToArray($this->meterPowerBefore,4));//充电前电表读数
        $frame = array_merge($frame,Tools::decToArray($this->meterPowerNow,4));//当前电表读数

        array_push($frame,$this->chargeStartType);//充电启动方式
        array_push($frame,$this->chargeTactics);//充电策略
        $frame = array_merge($frame,Tools::decToArray($this->chargeTacticsArgs,4));//充电策略参数

        array_push($frame,$this->reserveFlag);//预约标志

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->cardId,32,0)); //充电/预约卡号
        array_push($frame,$this->timeoutTime); //单位分钟

        //时间格式为 20160811154641ff 的 16进制值
        $frame = array_merge($frame,Tools::dateToDecArray($this->chargeStartTime));//预约/开始充电开始时间

        $frame = array_merge($frame,Tools::decToArray($this->cardBalanceBefore,4));//充电前卡余额

        $frame = array_merge($frame,Tools::decToArray($this->upgradeModel,4));//升级模式

        $frame = array_merge($frame,Tools::decToArray($this->power,4));//充电功率

        $frame = array_merge($frame,$this->reserved40);//预留40
        $frame = array_merge($frame,$this->reserved41);//预留41
        $frame = array_merge($frame,$this->reserved42);//预留42

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;


        $this->evseCode =trim( Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;

        $this->gunAmount = $dataArea[$offset];
        $offset++;

        $this->gunNum = $dataArea[$offset];
        $offset++;

        $this->gunType = $dataArea[$offset];
        $offset++;

        $this->workerStatus = $dataArea[$offset];
        $offset++;

        $this->nowSOCPercent = $dataArea[$offset];
        $offset++;

        $this->warningStatus = Tools::arrayToDec( array_slice( $dataArea,$offset,4));
        $offset+=4;

        $this->carConnectStatus = $dataArea[$offset];
        $offset++;

        $this->thisChargingFee = Tools::arrayToDec( array_slice( $dataArea,$offset,4));
        $offset+=4;

        $this->reserved12 = array_slice(  $dataArea,$offset,4);
        $offset+=4;

        $this->reserved13 = array_slice(  $dataArea,$offset,4);
        $offset+=4;

        $this->dcChargeVoltage =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->dcChargeCurrent =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;

        $this->BMSNeedVoltage =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->BMSNeedCurrent =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->BMSChargeModel = $dataArea[$offset];
        $offset++;


        $this->acAChargingVoltage =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->acBChargingVoltage =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->acCChargingVoltage =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->acAChargingCurrent =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->acBChargingCurrent =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->acCChargingCurrent =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;

        $this->remainingChargingTime =  Tools::arrayToDec(array_slice(  $dataArea,$offset,2));
        $offset+=2;
        $this->chargingDuration =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->thisChargedPower =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->meterPowerBefore =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->meterPowerNow =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->chargeStartType = $dataArea[$offset];
        $offset++;
        $this->chargeTactics = $dataArea[$offset];
        $offset++;
        $this->chargeTacticsArgs = Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;

        $this->reserveFlag =  $dataArea[$offset];
        $offset++;
        $this->cardId = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;
        $this->timeoutTime =  $dataArea[$offset];
        $offset++;

        //时间格式为 20160811154641ff 的 16进制值
        $this->chargeStartTime = Tools::decArrayToDate(array_slice(  $dataArea,$offset,8));
        $offset+=8;

        $this->cardBalanceBefore =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->upgradeModel = Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->power =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->reserved40 =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->reserved41 =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;
        $this->reserved42 =  Tools::arrayToDec(array_slice(  $dataArea,$offset,4));
        $offset+=4;




 ;

    }
}