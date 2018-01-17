<?php
namespace Wormhole\Protocols\NJINT\Protocol\Server\Command;
use Wormhole\Protocols\Tools;


/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-26
 * Time: 11:36
 * 包含 命令其实标志、命令个数、命令参数长度、命令参数内容
 */
class EvseControl
{
    /**
     * @var int 命令起始标志
     */
    private $cmdStartPosition;
    /**
     * @var int 命令结束标志位
     */
    private $cmdEndPosition;
    /**
     * @var int 命令个数
     */
    private $cmdNum;
    /**
     * @var array 命令参数
     */
    private $cmdArgsArray;




    public function __construct()
    {
        $this->cmdArgsArray = array_fill(1,17,-1);
        $this->cmdStartPosition =0;

    }


    /**
     * @return int
     */
    public function getCmdStartPosition()
    {
        return $this->cmdStartPosition;
    }

    /**
     * @param int $cmdPosition
     */
    private function setCmdPosition($cmdPosition)
    {
        $this->cmdStartPosition =$this->cmdStartPosition > $cmdPosition || $this->cmdStartPosition == 0 ?$cmdPosition:$this->cmdStartPosition; // 如果已有当前的起始位置在当前命令之后，则设置为当前值；

        $this->cmdEndPosition =$this->cmdEndPosition < $cmdPosition ?$cmdPosition:$this->cmdEndPosition; //

        $this->cmdNum = $this->cmdEndPosition >= $this->cmdStartPosition && $this->cmdStartPosition>0 ? ($this->cmdEndPosition-$this->cmdStartPosition +1):0;
    }

    /**
     * 获取命令参数的长度
     * @return int 根据最近位置计算出来的命令长度
     */
    public function getCmdLength()
    {
        return $this->cmdEndPosition>=$this->cmdStartPosition && $this->cmdStartPosition>=1 ?  ($this->cmdEndPosition-$this->cmdStartPosition +1)*4  :0  ;
    }

    /**
     * 根据命令起始位置计算出的内容
     * @return array
     */
    private function getCmdArgs(){

        //TODO 每次最多设置10个参数
        //$firstPosition =0;
        //$lastPosition =0;
        //for($i = 0;$i<count($this->cmdArgsArray);$i++){
        //    $cmdArg = $this->cmdArgsArray[$i];
        //    if(1==$cmdArg){
        //        if(0==$firstPosition){
        //            $firstPosition = $i+1;
        //        }
        //        $lastPosition = $i+1;
        //    }
        //}
        //
        //if($lastPosition-$firstPosition>=10){
        //
        //}

        $cmdFrame = array();
        for($i=$this->cmdStartPosition;$i<=$this->cmdEndPosition;$i++){
            $tmp  = $this->cmdArgsArray[$i];
            //var_dump( __NAMESPACE__."/".__FUNCTION__."@".__LINE__." tmp:$tmp " );DIE;
            if(-1==$tmp){
                break;
            }
            $tmp = Tools::decToArray($this->cmdArgsArray[$i],4);
            $cmdFrame = array_merge($cmdFrame,$tmp);
        }


        return $cmdFrame;
    }



    /**
     * @return int
     */
    public function getArgStopCharge()
    {
        return $this->cmdArgsArray[2];
    }

    /**
     * @param int $argStopCharge
     */
    public function setArgStopCharge($argStopCharge=0x55)
    {
        //$this->argStopCharge = $argStopCharge;
        $this->cmdArgsArray[2] =$argStopCharge;
        $this->setCmdPosition(2);
    }

    /**
     * @return int
     */
    public function getArgChargeControlType()
    {
        return $this->cmdArgsArray[4];
    }

    /**
     * @param int $argChargeControlType
     */
    public function setArgChargeControlType($argChargeControlType)
    {
        //$this->argChargeControlType = $argChargeControlType;
        $this->cmdArgsArray[4]  = $argChargeControlType;
        $this->setCmdPosition(4);
    }

    /**
     * @return int
     */
    public function getArgChargeVoltage()
    {
        return $this->cmdArgsArray[7];
    }

    /**
     * @param int $argChargeVoltage
     */
    public function setArgChargeVoltage($argChargeVoltage)
    {
        //$this->argChargeVoltage = $argChargeVoltage;
        $this->cmdArgsArray[7]  = $argChargeVoltage;

        if(1 == $this->getArgChargeControlType() ){ //盲充模式下才有效
            $this->setCmdPosition(7);
        }
    }

    /**
     * @return int
     */
    public function getArgChargeCurrent()
    {
        return $this->cmdArgsArray[8];
    }

    /**
     * @param int $argChargeCurrent
     */
    public function setArgChargeCurrent($argChargeCurrent)
    {
        //$this->argChargeCurrent = $argChargeCurrent;
        $this->cmdArgsArray[8] = $argChargeCurrent;

        if(1 == $this->getArgChargeControlType() ){ //盲充模式下才有效
            $this->setCmdPosition(8);
        }
    }

    /**
     * @return int
     */
    public function getArgChargingModel()
    {
        return $this->cmdArgsArray[9];
    }

    /**
     * @param int $argChargingModel
     */
    public function setArgChargingModel($argChargingModel)
    {
        //$this->argChargeType = $argChargeType;
        $this->cmdArgsArray[9] = $argChargingModel;

        $this->setCmdPosition(9);
    }

    /**
     * @return int
     */
    public function getArgCancleReserve()
    {
        return $this->cmdArgsArray[10];
    }

    /**
     * @param int $argCancleReserve
     */
    public function setArgCancleReserve($argCancleReserve=0x55)
    {
        //$this->argCancleReserve = $argCancleReserve;
        $this->cmdArgsArray[10] = $argCancleReserve;

        $this->setCmdPosition(10);
    }

    /**
     * @return int
     */
    public function getArgDeviceReboot()
    {
        return $this->cmdArgsArray[11];
    }

    /**
     * @param int $argDeviceReboot
     */
    public function setArgDeviceReboot($argDeviceReboot=0x55)
    {
        //$this->argDeviceReboot = $argDeviceReboot;
        $this->cmdArgsArray[11] = $argDeviceReboot;
        $this->setCmdPosition(11);
    }

    /**
     * @return int
     */
    public function getArgEnterUpgradeModel()
    {
        return $this->cmdArgsArray[12];
    }

    /**
     * @param int $argEnterUpgradeModel
     */
    public function setArgEnterUpgradeModel($argEnterUpgradeModel=0x55)
    {
        //$this->argEnterUpgradeModel = $argEnterUpgradeModel;
        $this->cmdArgsArray[12] = $argEnterUpgradeModel;
        $this->setCmdPosition(12);
    }

    /**
     * @return int
     */
    public function getArgEnterNormalModel()
    {
        return $this->cmdArgsArray[13];
    }

    /**
     * @param int $argEnterNormalModel
     */
    public function setArgEnterNormalModel($argEnterNormalModel=0x55)
    {
        //$this->argEnterNormalModel = $argEnterNormalModel;
        $this->cmdArgsArray[13] = $argEnterNormalModel;
        $this->setCmdPosition(13);
    }

    /**
     * @return int
     */
    public function getArgReportSignInNow()
    {
        return $this->cmdArgsArray[14];
    }

    /**
     * @param int $argReportSignInNow
     */
    public function setArgReportSignInNow($argReportSignInNow)
    {
        //$this->argReportSignInNow = $argReportSignInNow;
        $this->cmdArgsArray[14] = $argReportSignInNow;
        $this->setCmdPosition(14);
    }

    /**
     * @return int
     */
    public function getArgReportDeviceStatusNow()
    {
        return $this->cmdArgsArray[15];
    }

    /**
     * @param int $argReportDeviceStatusNow
     */
    public function setArgReportDeviceStatusNow($argReportDeviceStatusNow)
    {
        //$this->argReportDeviceStatusNow = $argReportDeviceStatusNow;
        $this->cmdArgsArray[15] = $argReportDeviceStatusNow;
        $this->setCmdPosition(15);
    }

    /**
     * @return int
     */
    public function getArgScanningSuccessPayment()
    {
        return $this->cmdArgsArray[16];
    }

    /**
     * @param int $argScanningSuccessPayment
     */
    public function setArgScanningSuccessPayment($argScanningSuccessPayment=0x55)
    {
        $this->cmdArgsArray[16] = $argScanningSuccessPayment;
        $this->setCmdPosition(16);
    }



    public function build()
    {
        $cmdFrame = Tools::decToArray($this->cmdStartPosition,4); // 命令起始标志；
        array_push($cmdFrame,$this->cmdNum);//命令个数
        //var_dump($cmdFrame);
        //var_dump( __NAMESPACE__."/".__FUNCTION__."@".__LINE__." cmd length:". $this->getCmdLength());
        $cmdFrame = array_merge($cmdFrame,Tools::decToArray($this->getCmdLength(),2)); //命令参数长度
        //var_dump($cmdFrame);
        $cmdFrame = array_merge($cmdFrame,$this->getCmdArgs()); //命令参数
        //var_dump($cmdFrame);
        return $cmdFrame;
    }

    /**
     * @param $command array
     */
    public function load($command){
        $offset =0;
        $this->cmdStartPosition=$command[$offset];
        $offset++;

        $this->cmdNum = $command[$offset];
        $offset++;

        $offset+=2;// 命令参数长度是计算出来的；

        $cmdArgs = array_slice($command,$offset);
        $cmdArgsLength = count($cmdArgs)/4; //4个字节一个
        $this->cmdEndPosition = $this->cmdStartPosition+($cmdArgsLength-1); //结束位置 是起始位置和长度之和减起始位置

        $cmdOffset = 0;
        for($i=0;$i<$cmdArgsLength;$i++){

            $cmd=array_slice($cmdArgs,$cmdOffset,4);
            $cmdOffset+=4;

            $this->cmdArgsArray[$this->cmdStartPosition+$i]=Tools::arrayToDec($cmd);
        }

    }

}