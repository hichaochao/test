<?php


namespace Wormhole\Protocols\HD10\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;


use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\HD10\Controllers\ProtocolController;
use Wormhole\Protocols\HD10\EventsApi;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\ServerFrame;
use Wormhole\Protocols\Tools;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\SignIn AS EvseSignInFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\SignIn AS ServerSignInFrame;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\SignIn as ServerSignInDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\HeartBeat AS EvseHeartbeatFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\CardSign AS EvseCardSignFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\CardSign AS EvseCardSignDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ReservationCharge AS EvseReservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ReservationCharge AS EvseReservationChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\UnReservationCharge AS EvseUnreservationChargeFrame;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartChargeCheck AS EvseStartChargeCheckFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartChargeCheck AS EvseStartChargeCheckDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartCharge AS EvseStartChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartCharge AS EvseStartChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StopCharge AS EvseStopChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StopCharge AS EvseStopChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeLog AS EvseChargeLogFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeLog AS EvseChargeLogDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeRealtime AS EvseChargeRealtimeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeRealtime AS EvseChargeRealtimeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\EventUpload AS EvseEventUploadFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\EventUpload AS EvseEventUploadDataArea;


class Events implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $workerID;
    protected $frame;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($workerID,$message)
    {
        $this->workerID = $workerID;
        $this->frame = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var  Frame
         */
        $frame = Frame::load($this->frame);

        switch ($frame->getCommandCode() . ' ' . $frame->getFunctionCode()) {
            case (EvseSignInFrame::commandCode . ' ' . EvseSignInFrame::functionCode);
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 登陆");
                self::signIn($this->workerID, $frame);
                break;
            case (EvseHeartbeatFrame::commandCode . ' ' . EvseHeartbeatFrame::functionCode);
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 心跳");
                self::heartbeat($this->workerID, $frame);
                break;

            case (EvseCardSignFrame::commandCode .' ' . EvseCardSignFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 卡片签权");
                $result = self::cardSign($this->workerID,$frame);
                break;
            //启动充电相关
            //预约
            case (EvseReservationChargeFrame::commandCode .' ' . EvseReservationChargeFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 预约响应");
                $result =  self::doReservationResponse($this->workerID,$frame);
                break;
            //自检
            case (EvseStartChargeCheckFrame::commandCode .' ' . EvseStartChargeCheckFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 启动充电前自检");
                $result =  self::startChargeCheck($this->workerID,$frame);
                break;
            //启动充电
            case (EvseStartChargeFrame::commandCode .' ' . EvseStartChargeFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩启动");
                $result = self::startCharge($this->workerID,$frame);
                break;
            //启动失败，需要解约；
            case (EvseUnreservationChargeFrame::commandCode . ' ' .EvseUnreservationChargeFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 解约");
                $result = self::doUnReservationResponseAsynchronous($this->workerID,$frame);
                break;



            case (EvseStopChargeFrame::commandCode .' ' . EvseStopChargeFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩停止");
                $result = self::stopCharge($this->workerID,$frame);
                break;
            case (EvseChargeLogFrame::commandCode . ' ' .EvseChargeLogFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩记录上报");
                $result = self::uploadChargeInfo($this->workerID,$frame);
                break;
            case (EvseChargeRealtimeFrame::commandCode .' ' . EvseChargeRealtimeFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩实时记录上报");
                $result = self::realtimeChargeInfo($this->workerID,$frame);
                break;
            case (EvseEventUploadFrame::commandCode.' '.EvseEventUploadFrame::functionCode):
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 事件上报");
                $result = self::eventUpload($this->workerID,$frame);
                break;
            default:
                $result = true;
        }

    }



    private static function signIn($clientId, Frame $frame)
    {
        $controller = new ProtocolController($clientId);

        $dataArea = new EvseSignInDataArea();
        $dataArea->load($frame->getDataArea());
        $code  = $dataArea->getEvseCode();

        $result = $controller->signIn($code);



        $frame = (new ServerFrame())->signIn($code,$result);
        Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 响应帧： ".bin2hex($frame));
        $sendResult  = EventsApi::sendMsg($clientId,$frame);


    }

    private static function heartbeat($clientId, Frame $frame)
    {
        $controller = new ProtocolController($clientId);

        $dataArea = new EvseHeartBeatDataArea();
        $dataArea->load($frame->getDataArea());
        $code = $dataArea->getEvseCode();
        $chargeStatus = $dataArea->getChargeType();
        $warningStatus = $dataArea->getWarningType();
        $gunStatus = $dataArea->getGunType();
        $emergencyStatus = $dataArea->getEmergencyType();

        $result = $controller->heartbeat($code, $chargeStatus, $warningStatus, $gunStatus, $emergencyStatus);

        $serverFrame = new ServerFrame();
        $sendFrame = $serverFrame->heartBeat($code);

        EventsApi::sendMsg($clientId,$sendFrame);

    }

    private static function cardSign($clientId, Frame $frame){
        $controller = new ProtocolController($clientId);

        $cardSignDataArea = new EvseCardSignDataArea();
        $cardSignDataArea->load($frame->getDataArea());
        $evseCode = $cardSignDataArea->getEvseCode();
        $cardNumber = (string)$cardSignDataArea->getCardNumber();

        $result=$controller->cardSign($evseCode,$cardNumber);

        $serverFrame = new ServerFrame();
        $cardSignResult = FALSE === $result ? 0:1;
        $sendFrame = $serverFrame->cardSign($evseCode,$cardSignResult);

        EventsApi::sendMsg($clientId,$sendFrame);
    }

    private static function doReservationResponse($clientId, Frame $frame){
        $controller = new ProtocolController($clientId);

        $dataArea = new EvseReservationChargeDataArea();
        $dataArea->load($frame->getDataArea());

        $evseCode = $dataArea->getEvseCode();
        $reserveResult = $dataArea->getResultCode();




        $result=$controller->reservation($evseCode,0 == $reserveResult);

        if(TRUE == $result){ //操作成功，继续启动自检

            $serverFrame = new ServerFrame();
            $sendFrame = $serverFrame->startChargeCheck($evseCode);

            EventsApi::sendMsg($clientId,$sendFrame);
        }
    }

    private static function startChargeCheck($clientId, Frame $frame){
        $controller = new ProtocolController($clientId);
        $dataArea = new EvseStartChargeCheckDataArea;
        $dataArea->load($frame->getDataArea());

        $evseCode = $dataArea->getEvseCode();
        $checkResult = $dataArea->getResultCode();

        $result = $controller->startChargeCheck($evseCode,$checkResult);



        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 自检数据成功，准备启动数据：".json_encode($result));

        if(is_array($result)){ //返回结果
            $code = $result["code"];
            $userId = $result["userId"];
            $isBilling = $result["isBilling"];
            $chargeType = $result["chargeType"];
            $chargeArgs = $result["chargeArgs"];
            $userBalance = $result["userBalance"];

            $serverFrame = new ServerFrame();
            $sendFrame = $serverFrame->startCharge($code,$userId,$isBilling,$chargeType,$chargeArgs,$userBalance);

            EventsApi::sendMsg($clientId,$sendFrame);
        }

    }
    private static function startCharge($clientId, Frame $frame){
        $controller = new ProtocolController($clientId);

        $startDataArea = new EvseStartChargeDataArea();
        $startDataArea->load($frame->getDataArea());

        $evseCode = $startDataArea->getEvseCode();
        $result = $startDataArea->getResultCode();

        $result = $controller->startCharge($evseCode,$result);

    }

    private static function doUnReservationResponseAsynchronous($clientId, Frame $frame){

    }

    private static function stopCharge($clientId, Frame $frame){
        $controller = new ProtocolController($clientId);

        $evseStopChargeDataArea = new EvseStopChargeDataArea();
        $evseStopChargeDataArea->load($frame->getDataArea());
        $evseCode = $evseStopChargeDataArea->getEvseCode();
        $result = $evseStopChargeDataArea->getResultCode();

        $result = $controller->stopCharge($evseCode,$result);
    }

    public static function uploadChargeInfo($clientId, Frame $frame){
        $controller = new ProtocolController($clientId);

        $evseStopChargeDataArea = new EvseChargeLogDataArea();
        $evseStopChargeDataArea->load($frame->getDataArea());
        $evseCode = $evseStopChargeDataArea->getEvseCode();
        $evseOrderId = $evseStopChargeDataArea->getUserId();
        $startTime = $evseStopChargeDataArea->getStartChargeTime();
        $stopTime = $evseStopChargeDataArea->getStopChargeTime();
        $chargedPower = $evseStopChargeDataArea->getPower()*10;
        $fee = $evseStopChargeDataArea->getMoney();


        $result = $controller->uploadChargeInfo($evseCode,$evseOrderId,$startTime,$stopTime,$chargedPower,$fee, $frame->getFrameString());

        $serverFrame = new ServerFrame();
        $sendFrame = $serverFrame->uploadChargeInfo($evseCode,FALSE == $result ? 0:1);
        EventsApi::sendMsg($clientId,$sendFrame);

    }
    private static function realtimeChargeInfo($clientId, Frame $frame){
        $controller = new ProtocolController($clientId);

        $dataArea = new EvseChargeRealtimeDataArea();
        $dataArea->load($frame->getDataArea());

        // 充电桩编号（桩自身的）
        $code = $dataArea->getEvseCode();
        $voltage = $dataArea->getVoltage() *100;
        $current = $dataArea->getElectricCurrent()*100;
        $startTime = $dataArea->getChargeTime();
        $chargedPower = $dataArea->getChargedPower() * 10;
        $fee = $dataArea->getMoney();
        $power = $dataArea->getPower() * 100;

        $result = $controller->realtimeChargeInfo($code,$voltage,$current,$startTime,$chargedPower,$fee,$power);



    }

    private static function eventUpload($clientId, Frame $frame){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "事件上报处理 START");
        $controller = new ProtocolController($clientId);

        $dataArea = new EvseEventUploadDataArea();
        $dataArea->load($frame->getDataArea());

        $code = $dataArea->getEvseCode();
        $status = $dataArea->getStatus();

        $result = $controller->eventUpload($code,$status);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "事件上报处理 END");
    }
}
