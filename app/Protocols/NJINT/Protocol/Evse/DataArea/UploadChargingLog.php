<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 16:57
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;
class UploadChargingLog  extends DataArea
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
     * @var string 充电桩编号
     */
    private $evseCode;
    private $gunType;
    private $gunNum;
    private $cardId;
    private $startTime;
    private $endTime;
    private $duration;
    private $startSOC;
    private $endSOC;
    private $stopReson;
    private $power;
    private $meterBefore;
    private $meterAfter;
    private $chargingFee;
    private $reserved17;
    private $cardBalanceBefore;
    private $reserved19;
    private $reserved20;
    private $reserved21;
    private $chargeTactics;
    private $chargeTacticsArgs;
    private $carVIN;
    private $carPlateNumber;
    /**
     * 时段电量， 48 *2字节 每半小时一个
     * @var array
     */
    private $powerOfTime;
    private $startType;

    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
        $this->reserved17=[0x00,0x00,0x00,0x00];
        $this->reserved19=[0x00,0x00,0x00,0x00];
        $this->reserved20=[0x00,0x00,0x00,0x00];
        $this->reserved21=0x00;

        $this->powerOfTime = array_fill(0,48,0);
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
     * @return mixed
     */
    public function getEvseCode()
    {
        return $this->evseCode;
    }

    /**
     * @param mixed $evseCode
     */
    public function setEvseCode($evseCode)
    {
        $this->evseCode = $evseCode;
    }


    /**
     * @return mixed
     */
    public function getGunType()
    {
        return $this->gunType;
    }

    /**
     * @param mixed $gunType
     */
    public function setGunType($gunType)
    {
        $this->gunType = $gunType;
    }

    /**
     * @return mixed
     */
    public function getGunNum()
    {
        return $this->gunNum == NULL ? 0:$this->gunNum;
    }

    /**
     * @param mixed $gunNum
     */
    public function setGunNum($gunNum)
    {
        $this->gunNum = $gunNum;
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
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param mixed $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param mixed $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getStartSOC()
    {
        return $this->startSOC;
    }

    /**
     * @param mixed $startSOC
     */
    public function setStartSOC($startSOC)
    {
        $this->startSOC = $startSOC;
    }

    /**
     * @return mixed
     */
    public function getEndSOC()
    {
        return $this->endSOC;
    }

    /**
     * @param mixed $endSOC
     */
    public function setEndSOC($endSOC)
    {
        $this->endSOC = $endSOC;
    }

    /**
     * @return mixed
     */
    public function getStopReson()
    {
        return $this->stopReson;
    }

    /**
     * @param mixed $stopReson
     */
    public function setStopReson($stopReson)
    {
        $this->stopReson = $stopReson;
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
    public function getMeterBefore()
    {
        return $this->meterBefore;
    }

    /**
     * @param mixed $meterBefore
     */
    public function setMeterBefore($meterBefore)
    {
        $this->meterBefore = $meterBefore;
    }

    /**
     * @return mixed
     */
    public function getMeterAfter()
    {
        return $this->meterAfter;
    }

    /**
     * @param mixed $meterAfter
     */
    public function setMeterAfter($meterAfter)
    {
        $this->meterAfter = $meterAfter;
    }

    /**
     * @return mixed
     */
    public function getChargingFee()
    {
        return $this->chargingFee;
    }

    /**
     * @param mixed $chargingFee
     */
    public function setChargingFee($chargingFee)
    {
        $this->chargingFee = $chargingFee;
    }

    /**
     * @return mixed
     */
    public function getReserved17()
    {
        return $this->reserved17;
    }

    /**
     * @param mixed $reserved17
     */
    public function setReserved17($reserved17)
    {
        $this->reserved17 = $reserved17;
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
    public function getReserved19()
    {
        return $this->reserved19;
    }

    /**
     * @param mixed $reserved19
     */
    public function setReserved19($reserved19)
    {
        $this->reserved19 = $reserved19;
    }

    /**
     * @return mixed
     */
    public function getReserved20()
    {
        return $this->reserved20;
    }

    /**
     * @param mixed $reserved20
     */
    public function setReserved20($reserved20)
    {
        $this->reserved20 = $reserved20;
    }

    /**
     * @return mixed
     */
    public function getReserved21()
    {
        return $this->reserved21;
    }

    /**
     * @param mixed $reserved21
     */
    public function setReserved21($reserved21)
    {
        $this->reserved21 = $reserved21;
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
    public function getCarVIN()
    {
        return $this->carVIN;
    }

    /**
     * @param mixed $carVIN
     */
    public function setCarVIN($carVIN)
    {
        $this->carVIN = $carVIN;
    }

    /**
     * @return mixed
     */
    public function getCarPlateNumber()
    {
        return $this->carPlateNumber;
    }

    /**
     * @param mixed $carPlateNumber
     */
    public function setCarPlateNumber($carPlateNumber)
    {
        $this->carPlateNumber = $carPlateNumber;
    }

    /**
     * @return mixed 0：本地刷卡启动
    1：后台启动
    2：本地管理员启动

     */
    public function getStartType()
    {
        return $this->startType;
    }

    /**
     * @param mixed $startType
     */
    public function setStartType($startType)
    {
        $this->startType = $startType;
    }

    /**
     * @return array
     */
    public function getPowerOfTime()
    {
        return $this->powerOfTime;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->evseCode,32,0));


        array_push($frame,$this->gunType);
        array_push($frame,$this->gunNum);

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->cardId,32,0));

        $frame=array_merge($frame,Tools::dateToDecArray($this->startTime));
        $frame=array_merge($frame,Tools::dateToDecArray($this->endTime));
        $frame=array_merge($frame,Tools::decToArray($this->duration,4));

        array_push($frame,$this->startSOC);
        array_push($frame,$this->endSOC);

        $frame=array_merge($frame,Tools::decToArray($this->stopReson,4));
        $frame=array_merge($frame,Tools::decToArray($this->power,4));
        $frame=array_merge($frame,Tools::decToArray($this->meterBefore,4));
        $frame=array_merge($frame,Tools::decToArray($this->meterAfter,4));
        $frame=array_merge($frame,Tools::decToArray($this->chargingFee,4));

        $frame=array_merge($frame,$this->reserved17);

        $frame=array_merge($frame,Tools::decToArray($this->cardBalanceBefore,4));

        $frame=array_merge($frame,$this->reserved19);
        $frame=array_merge($frame,$this->reserved20);
        array_push($frame,$this->reserved21);

        array_push($frame,$this->chargeTactics);
        $frame=array_merge($frame,Tools::decToArray($this->chargeTacticsArgs,4));

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->carVIN,17,0));

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->carPlateNumber,8,0));

        $powerOfTimeTmpArray =array();
        for($i=0;$i<count($this->powerOfTime);$i++){
            $powerOfTimeTmpArray = array_merge($powerOfTimeTmpArray,Tools::decToArray($this->powerOfTime[$i],2));
        }
        $frame = array_merge($frame,$powerOfTimeTmpArray);
        array_push($frame,$this->startType);


        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->evseCode = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;


        $this->gunType = $dataArea[$offset];
        $offset+=1;

        $this->gunNum = $dataArea[$offset];
        $offset+=1;

        $this->cardId = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;

        $this->startTime = Tools::decArrayToDate(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->endTime = Tools::decArrayToDate(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->duration = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->startSOC = $dataArea[$offset];
        $offset+=1;

        $this->endSOC = $dataArea[$offset];
        $offset+=1;

        $this->stopReson = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->power = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->meterBefore = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->meterAfter = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->chargingFee = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->reserved17 =array_slice($dataArea,$offset,4);
        $offset+=4;

        $this->cardBalanceBefore = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->reserved19 =array_slice($dataArea,$offset,4);
        $offset+=4;

        $this->reserved20 =array_slice($dataArea,$offset,4);
        $offset+=4;

        $this->reserved21 =  $dataArea[$offset];
        $offset+=1;

        $this->chargeTactics =  $dataArea[$offset];
        $offset+=1;

        $this->chargeTacticsArgs = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->carVIN = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,17)));
        $offset+=17;

        $this->carPlateNumber = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        for($i=0;$i<count($this->powerOfTime);$i++){
            $this->powerOfTime[$i]= Tools::arrayToDec(array_slice($dataArea,$offset,2));
            $offset+=2;
        }

        $this->startType =$dataArea[$offset];
        $offset+=1;

    }

}