<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-30
 * Time: 16:04
 */

namespace HaiGe;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\Tools;


//预约指令
use Wormhole\Protocols\HaiGe\Server\Frame\Appointment AS AppointmentFrame;
use Wormhole\Protocols\HaiGe\Server\DataArea\Appointment AS AppointmentArea;

//取消预约
use Wormhole\Protocols\HaiGe\Server\Frame\CancelAppointment AS CancelAppointmentFrame;
use Wormhole\Protocols\HaiGe\Server\DataArea\CancelAppointment AS CancelAppointmentArea;

//取消预约应答
use Wormhole\Protocols\HaiGe\Server\Frame\CancelAppointmentResponse AS CancelAppointmentResponseFrame;

//获取费率
use Wormhole\Protocols\HaiGe\Server\Frame\Get24HsCommodityStrategy AS Get24HsCommodityStrategyFrame;

//心跳
use Wormhole\Protocols\HaiGe\Server\Frame\Heartbeat AS HeartbeatFrame;

//刷卡
use Wormhole\Protocols\HaiGe\Server\Frame\PayByCard AS PayByCardFrame;
use Wormhole\Protocols\HaiGe\Server\DataArea\PayByCard AS PayByCardArea;

//重启指令
use Wormhole\Protocols\HaiGe\Server\Frame\Restart AS RestartFrame;

//设置费率
use Wormhole\Protocols\HaiGe\Server\Frame\Set24HsCommodityStrategy AS Set24HsCommodityStrategyFrame;
use Wormhole\Protocols\HaiGe\Server\DataArea\Set24HsCommodityStrategy AS Set24HsCommodityStrategyArea;

//账单
use Wormhole\Protocols\HaiGe\Server\Frame\SetBill AS SetBillFrame;

//对时设置
use Wormhole\Protocols\HaiGe\Server\Frame\SetTime AS SetTimeFrame;
use Wormhole\Protocols\HaiGe\Server\DataArea\SetTime AS SetTimeArea;

//开启充电
use Wormhole\Protocols\HaiGe\Server\Frame\StartCharge AS StartChargeFrame;
use Wormhole\Protocols\HaiGe\Server\DataArea\StartCharge AS StartChargeArea;

//开启充电回复
use Wormhole\Protocols\HaiGe\Server\Frame\StartChargeResponse AS StartChargeResponseFrame;

//停止充电
use Wormhole\Protocols\HaiGe\Server\Frame\StopCharge AS StopChargeFrame;
use Wormhole\Protocols\HaiGe\Server\DataArea\StopCharge AS StopChargeArea;

//停止充电回复
use Wormhole\Protocols\HaiGe\Server\Frame\StopChargeResponse AS StopChargeResponseFrame;




//心跳
//use hg\Evse\Frame\Heartbeat AS HeartbeatEvseFrame;
//use hg\Evse\DataArea\Heartbeat AS HeartbeatEvseArea;


class Evse
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

    public function __construct($poleId,$protocolName='hg')
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


        $this->running = $this->Hearbeat();

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
            //echo time().'-------'.$this->heartbeatTime.'--------'.$now - $this->heartbeatTime .'----------------'.$this->heartbeatFrequency.'-------';
            if ($now - $this->heartbeatTime > $this->heartbeatFrequency) { //已经过了 心跳频率时间
                $this->heartbeatTime = $now;
                $result = $this->Hearbeat();
                $this->running = $result;
            }


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

            switch ($frames->getOperator()){
                //预约
                case AppointmentFrame::OPERATOR:{
                    $result=$this->doAppointment();
                    break;
                }
                //取消预约
                case CancelAppointmentFrame::OPERATOR:{
                    $result=$this->CancelAppointment();
                    break;
                }
                //取消预约应答
                case CancelAppointmentResponseFrame::OPERATOR:{

                    break;
                }
                //获取费率
                case Get24HsCommodityStrategyFrame::OPERATOR:{
                    $result=$this->Get24HsCommodityStrategy();
                    break;
                }
                //设置费率
                case Set24HsCommodityStrategyFrame::OPERATOR:{
                    $result=$this->Set24HsCommodityStrategy();
                    break;
                }

                //心跳
                case HeartbeatFrame::OPERATOR:{
                    $result=$this->Hearbeat();
                    break;
                }
                //账单
                case SetBillFrame::OPERATOR:{
                    $result=$this->bill();
                    break;
                }
                //刷卡
                case PayByCardFrame::OPERATOR:{
                    $result=$this->PayByCard();
                    break;
                }
                //重启
                case RestartFrame::OPERATOR:{
                    $result=$this->Restart();
                    break;
                }

                //对时设置
                case SetTimeFrame::OPERATOR:{
                    $result=$this->SetTime();
                    break;
                }
                //开启充电
                case StartChargeFrame::OPERATOR:{
                    $result=$this->startCharge();
                    break;
                }
                //开启充电回复
                case StartChargeResponseFrame::OPERATOR:{

                    break;
                }
                //停止充电
                case StopChargeFrame::OPERATOR:{
                    $result=$this->stopCharge();
                    break;
                }
                //停止充电回复
                case StopChargeResponseFrame::OPERATOR:{

                    break;
                }

            }

        return $result;
    }

    //预约
    public function doAppointment(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->Appointment($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //取消预约
    public function CancelAppointment(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->CancelAppointment($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //费率查询
    public function Get24HsCommodityStrategy(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->Get24HsCommodityStrategy($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //设置费率
    public function Set24HsCommodityStrategy(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->Set24HsCommodityStrategy($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //心跳
    public function Hearbeat(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->Hearbeat($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //账单
    public function bill(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->bill($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //刷卡
    public function PayByCard(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->PayByCard($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //重启
    public function Restart(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->Restart($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //对时时间
    public function SetTime(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->SetTime($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //开启充电
    public function startCharge(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->startCharge($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }

    //停止充电
    public function stopCharge(){

        $register = 0;
        $responseCode = 0;
        $carriers = '0001';
        $deviceAddress = '0731000100010001';
        $number = '20150510132011';

        $frame = $this->evseFrame->stopCharge($register, $responseCode, $carriers, $deviceAddress, $number);
        $result = $this->socket->send($frame);
        return $result;

    }




    /**
     * socket读取
     * @param $time int 读取超时
     * @param $timePer int 每次间隔 单位 ms
     * @return bool
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
