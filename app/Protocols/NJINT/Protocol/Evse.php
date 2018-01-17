<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-30
 * Time: 16:04
 */

namespace Wormhole\Protocols\NJINT\Protocol;


use App\Mocker;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseControl;
use Wormhole\Protocols\NJINT\Protocol\Server\Frame\StartCharge AS ServiceStartCharge;
use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseControl AS ServiceEvseControl;
use Protocols\EvseInterface;
use App\Log;
use App\SocketHelper;
use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseStatus as EvseStatusArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\StartCharge as EvseStartArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseControl as EvseControlArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\Heartbeat as HeartbeatArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadChargingLog as EvseUploadChargingLogDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UploadChargingLog as EvseUploadChargingLogFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\SignIn as SignInDataArea;
class Evse implements  EvseInterface
{

    /**
     * @var Log
     */
    private $log;
    //心跳
    private $heartbeatFrequency;
    private $heartbeatTime;

    private $uploadEvseStatusFrequency; //充电桩状态上报平率
    private $lastUploadEvseStatusTime;//充电桩状态上报最后时间

    //gateway
    private $address;
    private $port;

    //桩数据
    //装编号
    private $poleId;
    //启动充电时间
    private $chargeStartTime;
    /**
     * @var boolean 充电状态 true 充电中 false 没有充电
     */
    private $chargeStatus;
    //用户id
    private $userId;

    /**
     * 流水号
     * @var int
     */
    private $serialNumber;
    //订单号
    private $orderNumber;


    private $pidLockFile;

    private $running;

    private $mocker;


    /**
     * @var int 用户类别
     */
    private $userType;


    /**
     * @var SocketHelper
     */
    private $socket;

    /**
     * @var EvseFrame
     */
    private $evseFrame;
    /**
     * @var ServerFrame
     */
    private $serverFrame;


    /**
     * 心跳序号
     * @var int
     */
    private $heartbeatSequence;
    /**
     * 充电桩发送消息序号
     * @var int
     */
    private $sequence;
    /**
     * 网络通信超时时长 单位s
     * @var int
     */
    private $netTimeout;




    /**
     * @var Frame 帧数据
     */
    private $frame;

    public function __construct($poleId,$protocolName='NJINT')
    {
        $this->poleId = $poleId;

        $this->mocker = new Mocker();

        //初始化桩数据
        $this->heartbeatTime = time();
        $this->heartbeatFrequency = $this->mocker->evse[$protocolName]['heartbeatFrequency'];
        $this->uploadEvseStatusFrequency = $this->mocker->evse[$protocolName]['uploadEvseStatus'];



        //初始化gateway数据
        $this->address = $this->mocker->gateway['ip'];
        $this->port = $this->mocker->gateway['port'];


        //初始化log
        $this->log = new Log($this->poleId);

        //清空启动充电时间
        $this->chargeStartTime = NULL;
        $this->chargeStatus=FALSE;
        $this->userId=0;
        $this->userType=0;

        $this->evseFrame = new EvseFrame();
        $this->serverFrame = new ServerFrame();


    }

    public function __destruct()
    {
        $this->running = FALSE;
        $this->delPidLockFile();
    }


    private function createPidLockFile()
    {


        $this->pidLockFile = $this->getPidFile();
        $pidFile = fopen($this->pidLockFile, "w+");
        fclose($pidFile);
    }

    private function getPidFile()
    {
        $pid = getmypid();
        $folder = dirname(dirname(dirname(dirname(__FILE__)))) . "/pid";

        if (!is_dir($folder)) {
            @mkdir($folder);
        }

        $pidFile = "$folder/$pid" . "_$this->poleId";

        return $pidFile;
    }


    private function delPidLockFile()
    {
        $pid = getmygid();
        exec("kill $pid");
        unlink($this->pidLockFile);
        $this->log->write("evse $this->poleId 关闭");

    }


    /**
     * 桩运行
     */
    function run()
    {

        ignore_user_abort(TRUE); // 后台运行
        set_time_limit(0); // 取消脚本运行时间的超时上限

        $this->socket = new SocketHelper($this->address, $this->port, $this->log);
 
        $this->createPidLockFile();


        $this->running = $this->doSignIn();

        while ($this->running) {
            usleep(500000);
            //读取socket 检查是否需要发送返回信息

            //读取到帧数据
            $buffer = $this->socket->read();
            if ($buffer !== FALSE) { 
                $frame = $this->responseFrame($buffer);

            }

            //不管是否读取到数据，只要过来了就检查是否要发心跳
            $now = time();
            if ($now - $this->heartbeatTime > $this->heartbeatFrequency) { //已经过了 心跳频率时间
                $this->heartbeatTime = $now;
                $result = $this->doHeartbeat();
                $this->running = $result;
            }

            if($now - $this->lastUploadEvseStatusTime > $this->uploadEvseStatusFrequency ){
                $this->lastUploadEvseStatusTime = $now;
                $result = $this->doEvseStatus();
                $this->running = $result;
            }

            //验证是否有刷卡充电
            //$this->running = $this->brushCard();

        }

        $this->stop();
        $this->log->write("evse $this->poleId stopped");
    }

    public function stop()
    {
        $this->running = FALSE;
        $this->delPidLockFile();
    }

    public function responseFrame($message){
        $result = FALSE;
        $frames = Frame::load($message);

        foreach ($frames as $frame){
            switch ($frame->getOperator()){
                case ServiceStartCharge::OPERATOR:{
                    $result=$this->doStartCharge();
                    break;
                }
                case ServiceEvseControl::OPERATOR:{
                    $result=$this->doEvseControl();
                    $this->doEvseChargeRecord();
                    break;
                }
            }
        }
        return $result;
    }
    //刷卡启动
    public function brushCard(){
        $result =FALSE;

        return $result;
    }

    //启动充电
    public function doStartCharge(){
        $dataArea=new EvseStartArea();
        $dataArea->setPoleId($this->poleId);
        $dataArea->setGunNum(1);
        $dataArea->setExcuteResult(0);
        $frame = $this->evseFrame->startCharge($this->getSequence(),$dataArea);
        $result = $this->socket->send($frame);
        return $result;

    }
    //停止充电应答
    public function doEvseControl(){
        $dataArea=new EvseControlArea();
        $dataArea->setPoleId($this->poleId);
        $dataArea->setGunNum(1);
        $dataArea->setCmdStartPosition(1);
        $dataArea->setCmdNum(1);
        $dataArea->setExcuteResult(0);
        $frame = $this->evseFrame->evseControlCMD($this->getSequence(),$dataArea);
        $result = $this->socket->send($frame);
        return $result;
    }


    public function doSignIn(){
        $result = FALSE;
        $dataArea = new SignInDataArea();
        $dataArea->setEvseCode($this->poleId);
        $dataArea->setEvseType(0);
        $dataArea->setVersion(1);
        $dataArea->setProjectType(1);
        $dataArea->setStartTimes(0);
        $dataArea->setUploadModel(1);
        $dataArea->setSignInInterval(300);
        $dataArea->setRuntimeInnerVar(0);
        $dataArea->setGunAmount(1);
        $dataArea->setHeartbeatInterval(300);
        $dataArea->setHeartbeatTimeoutTimes(3);
        $dataArea->setChargeLogAmount(3);
        $dataArea->setSystemTime(time());
        $dataArea->setLastChargeTime(time()-300);
        $dataArea->setLastStartChargeTime(time()-610);
        $frame = $this->evseFrame->signIn($this->getSequence(),$dataArea);
        $result = $this->socket->send($frame);
        //TODO 验证签到....


        return $result;
    }


    /**
     * @return bool
     */
    public function doHeartbeat(){
//         $result =FALSE;
//         $this->heartbeatSequence = $this->heartbeatSequence >=256*256?1:$this->heartbeatSequence++;
//         $frame = $this->evseFrame->heartbeat($this->heartbeatSequence,$this->poleId);

//         $result = $this->socket->send($frame);
//         $this->heartbeatTime = time();
        $dataArea = new HeartbeatArea();
        $dataArea->setEvseCode($this->poleId);
        //$dataArea->
        $frame = $this->evseFrame->heartbeatWithDataArea($this->getSequence(),$dataArea);
        $result = $this->socket->send($frame);
        return $result;

    }  
    
    public function doEvseStatus(){
        $result = FALSE;
                        
        $dataArea = new EvseStatusArea();
        //充电桩编号
        $dataArea->setEvseCode($this->poleId);
        //充电枪数量
        $dataArea->setGunAmount(1);
        //充电枪数量
        $dataArea->setGunNum(1);
        //充电枪类型
        $dataArea->setGunType(2);
        //工作状态
        $dataArea->setWorkerStatus(0);
        //当前SOC % 
        $dataArea->setNowSOCPercent(20);
        //告警状态 
        $dataArea->setWarningStatus(0);
        //车连接状态 
        $dataArea->setCarConnectStatus(1);
        //本次充电累计充电费用 
        $dataArea->setThisChargingFee(0);
        //直流充电电压
        $dataArea->setDcChargeVoltage(220);
        //直流充电电流 
        $dataArea->setDcChargeCurrent(10);
        //BMS需求电压 
        $dataArea->setBMSNeedVoltage(220);
        //BMS需求电流 
        $dataArea->setBMSNeedCurrent(10);
        //BMS充电模式 
        $dataArea->setBMSChargeModel(2);
        //交流A相充电电压
        $dataArea->setAcAChargingVoltage(220);
        //交流B相充电电压 
        $dataArea->setAcBChargingVoltage(220);
        //交流C相充电电压 
        $dataArea->setAcCChargingVoltage(220);
        //交流A相充电电流
        $dataArea->setAcAChargingCurrent(10);
        //交流B相充电电流
        $dataArea->setAcBChargingCurrent(10);
        //交流C相充电电流
        $dataArea->setAcCChargingCurrent(10);
        //剩余充电时间(min)
        $dataArea->setRemainingChargingTime(0);
        //充电时长
        $dataArea->setChargingDuration(0);
        //本次充电累计充电电量
        $dataArea->setThisChargedPower(0);
        //充电前电表读数 
        $dataArea->setMeterPowerBefore(0);
        //当前电表读数
        $dataArea->setMeterPowerNow(0);
        //充电启动方式 
        $dataArea->setChargeStartType(1);
        //充电策略 
        $dataArea->setChargeTactics(0);
        //充电策略参数 
        $dataArea->setChargeTacticsArgs(1);
        //预约标志 
        $dataArea->setReserveFlag(0);
        //充电/预约卡号 
        $dataArea->setCardId('');
        //超时时间
        $dataArea->setTimeoutTime(time());
        //开始充电开始时间
        $dataArea->setChargeStartTime(time());
        //充电前卡余额 
        $dataArea->setCardBalanceBefore(0);
        //升级模式 
        $dataArea->setUpgradeModel(0);
        //充电功率 
        $dataArea->setPower(11);
        $frame = $this->evseFrame->evseStatus($this->getSequence(),$dataArea);
        $result = $this->socket->send($frame);
        return $result;
    }
    //汽车充电完成之后返回
    public function doEvseChargeRecord(){
        $dataArea = new EvseUploadChargingLogDataArea();
        $dataArea->setEvseCode($this->poleId);
        $dataArea->setGunType(2);
        $dataArea->setGunNum(1);
        $dataArea->setCardId('uooooooooooo1');
        $dataArea->setStartTime(date("Ymdhis",time()));
        $dataArea->setEndTime(date("Ymdhis",time()+3600));
        $dataArea->setDuration(3600);
        $dataArea->setStartSOC(1);
        $dataArea->setEndSOC(2);
        $dataArea->setStopReson(0);
        $dataArea->setPower(200);
        $dataArea->setMeterBefore(100);
        $dataArea->setMeterAfter(200);
        $dataArea->setChargingFee(100);
        $dataArea->setCardBalanceBefore(200);
        $dataArea->setChargeTactics(0);
        $dataArea->setChargeTacticsArgs(111);
        $dataArea->setCarVIN(0);
        $dataArea->setCarPlateNumber(0);
        $frame = new EvseUploadChargingLogFrame();
        $frame->setDataArea($dataArea);

        $result = $this->socket->send($frame->getFrameString());
        return $result;
    }
    /**
     * socket读取
     * @param $time int 读取超时
     * @param $timePer int 每次间隔 单位 ms
     */
    public function socketRead($time,$timePer = 500000){
        $now = time();
        $buffer = FALSE;
        while($now+$time>=time()){
            //读取到帧数据
            $buffer = $this->socket->read();
            if($buffer!=FALSE){
                break;
            }
            usleep($timePer);
        }

        return $buffer;
    }

    private function  getSequence(){
        return  $this->sequence>=256 ?1:$this->sequence++;
    }

}
