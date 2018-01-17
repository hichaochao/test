<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-26
 * Time: 17:39
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;

class StartCharge extends DataArea
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
     * @var int 充电枪口
     */
    private $gunNum;

    /**
     * 充电生效类型
     * @var int 0:即时充电；1：定时启动充电；2:预约充电
     */
    private $chargeType;

    /**
     * @var array 协议预留5
     */
    private $reserved5;
    /**
     * 充电策略
     * @var int 0:充满为止     1:时间控制充电     2:金额控制充电     3:电量控制充电
     */
    private $chargeTactics;
    /**
     * 充电策略参数
     * @var int 时间单位为1秒金额单位为0.01元电量时单位为0.01kw
     */
    private $chargeTacticsArgs;


    /**
     * 预约/定时启动时间
     * @var int 标准时间
     */
    private $chargeStartTime;

    /**
     * 预约超时时间
     * @var int 单位分钟
     */
    private $reserveTimeoutTime;

    /**
     * 用户卡号/用户识别号
     * @var string ASSIC 码，不够长度填’\0’
     */
    private $userId;
    /**
     * 断网充电标志
     * @var int 0‐不允许 1‐允许
     */
    private $netOffChargeStatus;

    /**
     * 离线可充电电量
     * @var int 单位：0.01kw
     */
    private $offlineChargePower;




    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
        $this->reserved5=[0x00,0x00,0x00,0x00];
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
    public function getChargeType()
    {
        return $this->chargeType;
    }

    /**
     * @param int $chargeType
     */
    public function setChargeType($chargeType)
    {
        $this->chargeType = $chargeType;
    }

    /**
     * @return array
     */
    public function getReserved5()
    {
        return $this->reserved5;
    }

    /**
     * @param array $reserved5
     */
    public function setReserved5($reserved5)
    {
        $this->reserved5 = $reserved5;
    }

    /**
     * @return int
     */
    public function getChargeTactics()
    {
        return $this->chargeTactics;
    }

    /**
     * @param int $chargeTactics
     */
    public function setChargeTactics($chargeTactics)
    {
        $this->chargeTactics = $chargeTactics;
    }

    /**
     * @return int
     */
    public function getChargeTacticsArgs()
    {
        return $this->chargeTacticsArgs;
    }

    /**
     * @param int $chargeTacticsArgs
     */
    public function setChargeTacticsArgs($chargeTacticsArgs)
    {
        $this->chargeTacticsArgs = $chargeTacticsArgs;
    }

    /**
     * @return int
     */
    public function getChargeStartTime()
    {
        return $this->chargeStartTime;
    }

    /**
     * @param int $chargeStartTime
     */
    public function setChargeStartTime($chargeStartTime)
    {
        $this->chargeStartTime = $chargeStartTime;
    }

    /**
     * @return int
     */
    public function getReserveTimeoutTime()
    {
        return $this->reserveTimeoutTime;
    }

    /**
     * @param int $reserveTimeoutTime
     */
    public function setReserveTimeoutTime($reserveTimeoutTime)
    {
        $this->reserveTimeoutTime = $reserveTimeoutTime;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getNetOffChargeStatus()
    {
        return $this->netOffChargeStatus;
    }

    /**
     * @param int $netOffChargeStatus
     */
    public function setNetOffChargeStatus($netOffChargeStatus)
    {
        $this->netOffChargeStatus = $netOffChargeStatus;
    }

    /**
     * @return int
     */
    public function getOfflineChargePower()
    {
        return $this->offlineChargePower;
    }

    /**
     * @param int $offlineChargePower
     */
    public function setOfflineChargePower($offlineChargePower)
    {
        $this->offlineChargePower = $offlineChargePower;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2
        array_push($frame,$this->gunNum);//充电枪口
        $frame=array_merge($frame,Tools::decToArray($this->chargeType,4)); //充电生效类型
        $frame = array_merge($frame, $this->reserved5); //预留5
        $frame=array_merge($frame,Tools::decToArray($this->chargeTactics,4)); //充电策略
        $frame=array_merge($frame,Tools::decToArray($this->chargeTacticsArgs,4)); //充电策略参数
        $frame=array_merge($frame,Tools::dateToDecArray($this->chargeStartTime)); //预约/定时启动时间
        array_push($frame,$this->reserveTimeoutTime); //预约超时时间，单位分钟


        $userId =MY_Tools::asciiToDecArrayWithLength($this->userId,32,0);//用户标识，ASCII，32为，不足补\0



        $frame=array_merge($frame,$userId); //用户标识，ASCII，32为，不足补\0

        array_push($frame,$this->netOffChargeStatus); //断网充电标志
        $frame=array_merge($frame,Tools::decToArray($this->offlineChargePower,4)); //离线可充电电量


        return $frame;
    }

    /**
     * @param array $dataArea
     */
    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->gunNum = $dataArea[$offset];
        $offset++;

        $this->chargeType = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->reserved5 =  Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->chargeTactics =  Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->chargeTacticsArgs =  Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->chargeStartTime =  Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->reserveTimeoutTime = $dataArea[$offset];
        $offset++;

        $this->userId =trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;

        $this->netOffChargeStatus = $dataArea[$offset];
        $offset++;

        $this->offlineChargePower =  Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;






    }


}