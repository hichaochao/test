<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 10:16
 */
namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;
class UploadBMSInfo  extends DataArea
{

    /**
     * @var array 协议预留1
     */
    private $reserved1;
    /**
     * @var array 协议预留2
     */
    private $reserved2;

    private $poleId;
    /**
     * @var int 工作状态
     */
    private $workStatus;
    private $carConnectStatus;
    private $BRMCarIdentifymessage;
    private $VBIMessage;
    private $BCPPowerBatteryChargePara;
    private $BROBatteryChargePrepareStatus;
    private $BCLBatteryChargeDemand;
    private $BCSBatteryChargeTotalStatus;
    private $BSMPowerBatteryStatusInfo;
    private $BSMAbortCharge;
    private $BSDBMSStatisticsData;
    private $BEMMessage;


    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
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
    public function getPoleId()
    {
        return $this->poleId;
    }

    /**
     * @param mixed $poleId
     */
    public function setPoleId($poleId)
    {
        $this->poleId = $poleId;
    }

    /**
     * @return int
     */
    public function getWorkStatus()
    {
        return $this->workStatus;
    }

    /**
     * @param int $workStatus
     */
    public function setWorkStatus($workStatus)
    {
        $this->workStatus = $workStatus;
    }

    /**
     * @return mixed
     */
    public function getCarConnectStatus()
    {
        return $this->carConnectStatus;
    }

    /**
     * @param mixed $carConnectStatus
     */
    public function setCarConnectStatus($carConnectStatus)
    {
        $this->carConnectStatus = $carConnectStatus;
    }
    /**
     * @return mixed
     */
    public function getBRMCarIdentifymessage()
    {
        return $this->BRMCarIdentifymessage;
    }

    /**
     * @param mixed $BRMCarIdentifymessage
     */
    public function setBRMCarIdentifymessage($BRMCarIdentifymessage)
    {
        $this->BRMCarIdentifymessage = $BRMCarIdentifymessage;
    }

    /**
     * @return mixed
     */
    public function getVBIMessage()
    {
        return $this->VBIMessage;
    }

    /**
     * @param mixed $VBIMessage
     */
    public function setVBIMessage($VBIMessage)
    {
        $this->VBIMessage = $VBIMessage;
    }

    /**
     * @return mixed
     */
    public function getBCPPowerBatteryChargePara()
    {
        return $this->BCPPowerBatteryChargePara;
    }

    /**
     * @param mixed $BCPPowerBatteryChargePara
     */
    public function setBCPPowerBatteryChargePara($BCPPowerBatteryChargePara)
    {
        $this->BCPPowerBatteryChargePara = $BCPPowerBatteryChargePara;
    }

    /**
     * @return mixed
     */
    public function getBROBatteryChargePrepareStatus()
    {
        return $this->BROBatteryChargePrepareStatus;
    }

    /**
     * @param mixed $BROBatteryChargePrepareStatus
     */
    public function setBROBatteryChargePrepareStatus($BROBatteryChargePrepareStatus)
    {
        $this->BROBatteryChargePrepareStatus = $BROBatteryChargePrepareStatus;
    }

    /**
     * @return mixed
     */
    public function getBCLBatteryChargeDemand()
    {
        return $this->BCLBatteryChargeDemand;
    }

    /**
     * @param mixed $BCLBatteryChargeDemand
     */
    public function setBCLBatteryChargeDemand($BCLBatteryChargeDemand)
    {
        $this->BCLBatteryChargeDemand = $BCLBatteryChargeDemand;
    }

    /**
     * @return mixed
     */
    public function getBCSBatteryChargeTotalStatus()
    {
        return $this->BCSBatteryChargeTotalStatus;
    }

    /**
     * @param mixed $BCSBatteryChargeTotalStatus
     */
    public function setBCSBatteryChargeTotalStatus($BCSBatteryChargeTotalStatus)
    {
        $this->BCSBatteryChargeTotalStatus = $BCSBatteryChargeTotalStatus;
    }

    /**
     * @return mixed
     */
    public function getBSMPowerBatteryStatusInfo()
    {
        return $this->BSMPowerBatteryStatusInfo;
    }

    /**
     * @param mixed $BSMPowerBatteryStatusInfo
     */
    public function setBSMPowerBatteryStatusInfo($BSMPowerBatteryStatusInfo)
    {
        $this->BSMPowerBatteryStatusInfo = $BSMPowerBatteryStatusInfo;
    }

    /**
     * @return mixed
     */
    public function getBSMAbortCharge()
    {
        return $this->BSMAbortCharge;
    }

    /**
     * @param mixed $BSMAbortCharge
     */
    public function setBSMAbortCharge($BSMAbortCharge)
    {
        $this->BSMAbortCharge = $BSMAbortCharge;
    }

    /**
     * @return mixed
     */
    public function getBSDBMSStatisticsData()
    {
        return $this->BSDBMSStatisticsData;
    }

    /**
     * @param mixed $BSDBMSStatisticsData
     */
    public function setBSDBMSStatisticsData($BSDBMSStatisticsData)
    {
        $this->BSDBMSStatisticsData = $BSDBMSStatisticsData;
    }

    /**
     * @return mixed
     */
    public function getBEMMessage()
    {
        return $this->BEMMessage;
    }

    /**
     * @param mixed $BEMMessage
     */
    public function setBEMMessage($BEMMessage)
    {
        $this->BEMMessage = $BEMMessage;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->poleId,32,0));


        $frame=array_merge($frame,$this->workStatus);
        $frame=array_merge($frame,$this->carConnectStatus);
        $frame=array_merge($frame,Tools::decToArray($this->BRMCarIdentifymessage,64 ));
        $frame=array_merge($frame,Tools::decToArray($this->VBIMessage,64));
        $frame=array_merge($frame,Tools::decToArray($this->BCPPowerBatteryChargePara,16 ));
        $frame=array_merge($frame,Tools::decToArray($this->BROBatteryChargePrepareStatus,8  ));
        $frame=array_merge($frame,Tools::decToArray($this->BCLBatteryChargeDemand,8));
        $frame=array_merge($frame,Tools::decToArray($this->BCSBatteryChargeTotalStatus,16 ));
        $frame=array_merge($frame,Tools::decToArray($this->BSMPowerBatteryStatusInfo,8  ));
        $frame=array_merge($frame,Tools::decToArray($this->BSMAbortCharge,8));
        $frame=array_merge($frame,Tools::decToArray($this->BSDBMSStatisticsData,8  ));
        $frame=array_merge($frame,Tools::decToArray($this->BEMMessage,8));

        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->poleId = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;
        $this->workStatus =  $dataArea[$offset];
        $offset+=1;

        $this->carConnectStatus =  $dataArea[$offset];
        $offset+=1;
        $this->BRMCarIdentifymessage = Tools::arrayToDec(array_slice($dataArea,$offset,64));
        $offset+=64;
        $this->VBIMessage = Tools::arrayToDec(array_slice($dataArea,$offset,64));
        $offset+=64;
        $this->BCPPowerBatteryChargePara = Tools::arrayToDec(array_slice($dataArea,$offset,16));
        $offset+=16;
        $this->BROBatteryChargePrepareStatus = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->BCLBatteryChargeDemand = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->BCSBatteryChargeTotalStatus = Tools::arrayToDec(array_slice($dataArea,$offset,16));
        $offset+=16;
        $this->BSMPowerBatteryStatusInfo = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->BSMAbortCharge = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->BSDBMSStatisticsData = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->BEMMessage = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;





    }

}