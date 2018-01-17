<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 16:51
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

class SignIn extends DataArea
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
     * 充电桩类型
     * @var int
     */
    private $evseType;
    /**
     * 充电桩软件版本
     * @var int
     */
    private $version;
    /**
     * 充电桩项目类型
     * @var int
     */
    private $projectType;
    /**
     * 启动次数
     * @var int
     */
    private $startTimes;
    /**
     * 数据上传模式
     * @var int
     */
    private $uploadModel;
    /**
     * 签到间隔时间
     * @var int
     */
    private $signInInterval;
    /**
     * 运行内部变量
     * @var int
     */
    private $runtimeInnerVar;
    /**
     * 充电抢个数
     * @var int
     */
    private $gunAmount;
    /**
     * 心跳上报周期
     * @var int
     */
    private $heartbeatInterval;
    /**
     * 心跳包检测超时次数
     * @var int
     */
    private $heartbeatTimeoutTimes;
    /**
     * 充电记录数量
     * @var int
     */
    private $chargeLogAmount;
    /**
     * 当前充电桩系统时间
     * @var
     */
    private $systemTime;
    /**
     * 最近一次充电时间
     * @var int
     */
    private $lastChargeTime;
    /**最近一次启动时间
     * @var  int
     */
    private $lastStartChargeTime;
    /**
     * 预留
     * @var
     */
    private $reserved18;



    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
        $this->reserved18=[0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00];
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
    public function getEvseType()
    {
        return $this->evseType;
    }

    /**
     * @param int $evseType
     */
    public function setEvseType($evseType)
    {
        $this->evseType = $evseType;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getProjectType()
    {
        return $this->projectType;
    }

    /**
     * @param int $projectType
     */
    public function setProjectType($projectType)
    {
        $this->projectType = $projectType;
    }

    /**
     * @return int
     */
    public function getStartTimes()
    {
        return $this->startTimes;
    }

    /**
     * @param int $startTimes
     */
    public function setStartTimes($startTimes)
    {
        $this->startTimes = $startTimes;
    }

    /**
     * @return int
     */
    public function getUploadModel()
    {
        return $this->uploadModel;
    }

    /**
     * @param int $uploadModel
     */
    public function setUploadModel($uploadModel)
    {
        $this->uploadModel = $uploadModel;
    }

    /**
     * @return int
     */
    public function getSignInInterval()
    {
        return $this->signInInterval;
    }

    /**
     * @param int $signInInterval
     */
    public function setSignInInterval($signInInterval)
    {
        $this->signInInterval = $signInInterval;
    }

    /**
     * @return int
     */
    public function getRuntimeInnerVar()
    {
        return $this->runtimeInnerVar;
    }

    /**
     * @param int $runtimeInnerVar
     */
    public function setRuntimeInnerVar($runtimeInnerVar)
    {
        $this->runtimeInnerVar = $runtimeInnerVar;
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
    public function getHeartbeatInterval()
    {
        return $this->heartbeatInterval;
    }

    /**
     * @param int $heartbeatInterval
     */
    public function setHeartbeatInterval($heartbeatInterval)
    {
        $this->heartbeatInterval = $heartbeatInterval;
    }

    /**
     * @return int
     */
    public function getHeartbeatTimeoutTimes()
    {
        return $this->heartbeatTimeoutTimes;
    }

    /**
     * @param int $heartbeatTimeoutTimes
     */
    public function setHeartbeatTimeoutTimes($heartbeatTimeoutTimes)
    {
        $this->heartbeatTimeoutTimes = $heartbeatTimeoutTimes;
    }

    /**
     * @return int
     */
    public function getChargeLogAmount()
    {
        return $this->chargeLogAmount;
    }

    /**
     * @param int $chargeLogAmount
     */
    public function setChargeLogAmount($chargeLogAmount)
    {
        $this->chargeLogAmount = $chargeLogAmount;
    }

    /**
     * @return mixed
     */
    public function getSystemTime()
    {
        return $this->systemTime;
    }

    /**
     * @param mixed $systemTime
     */
    public function setSystemTime($systemTime)
    {
        $this->systemTime = $systemTime;
    }

    /**
     * @return int
     */
    public function getLastChargeTime()
    {
        return $this->lastChargeTime;
    }

    /**
     * @param int $lastChargeTime
     */
    public function setLastChargeTime($lastChargeTime)
    {
        $this->lastChargeTime = $lastChargeTime;
    }

    /**
     * @return int
     */
    public function getLastStartChargeTime()
    {
        return $this->lastStartChargeTime;
    }

    /**
     * @param int $lastStartChargeTime
     */
    public function setLastStartChargeTime($lastStartChargeTime)
    {
        $this->lastStartChargeTime = $lastStartChargeTime;
    }

    /**
     * @return mixed
     */
    public function getReserved18()
    {
        return $this->reserved18;
    }

    /**
     * @param mixed $reserved18
     */
    public function setReserved18($reserved18)
    {
        $this->reserved18 = $reserved18;
    }



    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2
        $frame= array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->evseCode,32,0));

        array_push($frame,$this->evseType);

        $frame= array_merge($frame,Tools::decToArray($this->version,4));
        $frame= array_merge($frame,Tools::decToArray($this->projectType,2));
        $frame= array_merge($frame,Tools::decToArray($this->startTimes,4));

        array_push($frame,$this->uploadModel);

        $frame= array_merge($frame,Tools::decToArray($this->signInInterval,2));

        array_push($frame,$this->runtimeInnerVar);
        array_push($frame,$this->gunAmount);
        array_push($frame,$this->heartbeatInterval);
        array_push($frame,$this->heartbeatTimeoutTimes);

        $frame= array_merge($frame,Tools::decToArray($this->chargeLogAmount,4));
        $frame= array_merge($frame,Tools::decToArray($this->systemTime,8));
        $frame= array_merge($frame,Tools::decToArray($this->lastChargeTime,8));
        $frame= array_merge($frame,Tools::decToArray($this->lastStartChargeTime,8));

        $frame= array_merge($frame,$this->reserved18);

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

        $this->evseType = $dataArea[$offset];
        $offset++;

        $this->version = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->projectType = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset+=2;
        $this->startTimes = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->uploadModel = $dataArea[$offset];
        $offset++;

        $this->signInInterval = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset+=2;
        $this->runtimeInnerVar = $dataArea[$offset];
        $offset++;
        $this->gunAmount = $dataArea[$offset];
        $offset++;
        $this->heartbeatInterval = $dataArea[$offset];
        $offset++;
        $this->heartbeatTimeoutTimes = $dataArea[$offset];
        $offset++;


        $this->chargeLogAmount = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->systemTime = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->lastChargeTime = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->lastStartChargeTime = Tools::arrayToDec(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->reserved18 = array_slice($dataArea,$offset,8);
        $offset+=8;


    }

}