<?php
namespace Wormhole\Protocols\HaiGe;


use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\BaseEvents;
use Wormhole\Protocols\HaiGe\ServerFrame;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Controllers\ProtocolController;
use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\Heartbeat as EvseHeartbeatFrame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Heartbeat as EvseHeartbeatDataArea;

//启动充电
use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\StartCharge as EvseStartChargeFrame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\StartCharge as EvseStartChargeDataArea;

//停止充电
use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\StopCharge as EvseStopChargeFrame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\StopCharge as EvseStopChargeDataArea;

//账单上报
use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\SetBill as EvseSetBillFrame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetBill as EvseSetBillDataArea;


//use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\SignIn as EvseSignInFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
//use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\SignIn as ServerSignInFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\SignIn as ServerSignInDataArea;
//
//use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\Heartbeat as EvseHeartbeatFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Heartbeat as EvseHeartbeatDataArea;
//
//use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\EvseStatus as EvseEvseStatusFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\EvseStatus as EvseEvseStatusDataArea;
//use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\EvseStatus as ServerEvseStatusFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\EvseStatus as ServerEvseStatusDataArea;
//
//use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\StartCharge as EvseStartChargeFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\StartCharge as EvseStartChargeDataArea;
//use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\StartCharge as ServerStartChargeFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\StartCharge as ServerStartChargeDataArea;
//
//use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\EvseControl as EvseEvseControlFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\EvseControl as EvseEvseControlDataArea;
//
//use Wormhole\Protocols\HaiGe\Protocol\Evse\Frame\UploadChargingLog as EvseUploadChargingLogFrame;
//use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\UploadChargingLog as EvseUploadChargingLogDataArea;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class EventsApi extends BaseEvents
{

    protected static $hasUpgradeFrame = FALSE;
    /**
     * 获取控制
     * @param $clientId
     * @param $monitorEvseCode
     * @return bool
     */
    public static function getControl($clientId,$monitorEvseCode){
        $controller = new ProtocolController($clientId);
        return $controller->getControl($monitorEvseCode);
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function message($client_id, $message) {

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  client_id:$client_id, message:".bin2hex($message));
        parent::message($client_id,$message);
        if(!self::continueMessage($client_id)){
            return FALSE;
        }

        /**
         *@var  Frame
         */
        $frame = Frame::load($message);

        if(FALSE === $frame || is_null($frame)){
            return FALSE;
        }

        $controller = new ProtocolController($client_id);
        $serverFrame = new ServerFrame();

        switch ($frame->getOperator()) {
            case EvseHeartbeatFrame::OPERATOR;
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "   (CMD=106)充电桩心跳上报");
                $dataArea = new EvseHeartbeatDataArea();
                $dataArea->load($frame->getDataArea());

                $evseCode = $frame->getDeviceAddress();
                $register = $frame->getRegister();
                $responsenCode = $frame->getResponseCode();
                $carriers = $frame->getCarriers();
                $number = $frame->getNumber();

                $evseStatus = Tools::bcDecToHexStr( $dataArea->getEvseStatus());
                $gunNumber = intval( substr($evseStatus,0,1));
                $chargeStatus = intval( substr($evseStatus,1,1));

                $evseInfo = array('evse_code'=>$evseCode,'evse_name'=>'haige','client_id'=>$client_id,'carriers' =>$carriers,'is_register'=>$register,'responsenCode'=>$responsenCode,
                    'left_time'  => $dataArea->getEvseStatus(), 'charged_power'  => $dataArea->getChargePower(),'charge_money'  => $dataArea->getChargeMoney(), 'voltage'  => $dataArea->getChargeVoltage(),
                    'electric_current'  => $dataArea->getChargeCurrent(), 'duration'  => $dataArea->getChargeTime(), 'power'  => $dataArea->getPower(), 'charge_status'=>$dataArea->getEvseStatus(),
                    'appointmentStatus' => $dataArea->getAppointmentStatus());

                $result = $controller->heartbeat($evseCode,$gunNumber,$evseInfo);
                $evse = array('registe'=>$register, 'responseCode'=>$responsenCode, 'carriers'=>$carriers,'deviceAddress'=>$evseCode, 'number'=>$number);
                $sendFrame = $serverFrame->heartbeat($evse);
                self::sendMsg($client_id,$sendFrame);
                break;


            case EvseStartChargeFrame::OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  (CMD=8) 充电桩对后台下发的充电桩开启充电控制应答");
                $startDataArea = new EvseStartChargeDataArea();
                $startDataArea->load($frame->getDataArea());

                $evseCode = $frame->getDeviceAddress();
                $responseCode = $frame->getResponseCode();

                $gunNumber = $startDataArea->getGunNum();
                $controlType = $startDataArea->getControlType();
                $startData = $startDataArea->getStartData();
                $timeStart = $startDataArea->getTimerStart();
                $userCard = $startDataArea->getUserCard();

                //获取monitorCode
                $result = $controller->startCharge($evseCode, $gunNumber, $responseCode);



                break;

            case EvseStopChargeFrame::OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  (CMD=6)充电桩停止充电应答");

                $dataArea = new EvseStopChargeDataArea();
                $dataArea->load($frame->getDataArea());

                $code = $frame->getDeviceAddress();  //桩code
                $stopResult = $frame->getResponseCode(); //返回值

                $portNumber = $dataArea->getGunNum();     //充电口号
                $controllerType = $dataArea->getControlType(); //控制类型
                $userCard = $dataArea->getUserCard();    //用户卡号
                $chargeTime = $dataArea->getChargeTime();  //累计充电时间
                $status = $dataArea->getChargeStatus(); //中止荷电状态

                $result = $controller->stopCharge($code, $stopResult, $portNumber, $controllerType, $userCard, $chargeTime, $status);
                break;

            case EvseSetBillFrame :: OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  (CMD=6)账单上报");

                $dataArea = new EvseSetBillDataArea();
                $dataArea->load($frame->getDataArea());

                $evseCode = $frame->getDeviceAddress();
                $register = $frame->getRegister();
                $responsenCode = $frame->getResponseCode();
                $carriers = $frame->getCarriers();
                $number = $frame->getNumber();

                $code = $frame->getDeviceAddress();  //桩code
                $stopResult = $frame->getResponseCode(); //返回值

                $startTime = $dataArea->getChargeStartTime();  //充电开始时间
                $chargeEndTime = $dataArea->getChargeEndTime(); //充电结束时间
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  (CMD=6)账单上报::start:$startTime, endtime::$chargeEndTime");
                $cardNum = $dataArea->getCardNum();             //用户卡号
                $beforePower  = $dataArea->getBeforePower();    //充电前电表读数
                $afterPower = $dataArea->getAfterPower();       //充电后电表读数
                $chargePower = $dataArea->getChargePower();     //本次充电电量
                $chargeMoney = $dataArea->getChargeMoney();     //本次充电金额
                $beforeBalance = $dataArea->getBeforeBalance(); //充电前卡余额
                $afterBalance = $dataArea->getAfterBalance();   //充电后卡余额
                $serviceCharge = $dataArea->getServiceCharge(); //服务费金额
                $offlinePayment = $dataArea->getOfflinePayment(); //是否线下支付
                $frame = $frame->getFrameString();

                $result = $controller->chargeRecordUpload($code, $stopResult, $startTime, $chargeEndTime, $cardNum, $beforePower, $afterPower, $chargePower, $chargeMoney, $beforeBalance,
                    $afterBalance, $serviceCharge, $offlinePayment, $frame);
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报result：$result");

                if(TRUE == $result){

                    $sendFrame = $serverFrame->SetBill($register, $responsenCode, $carriers, $evseCode, $number);
                    Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报应答充电桩sendFrame：".bin2hex($sendFrame));
                    self::sendMsg($client_id,$sendFrame);
                }

                break;




            default:
                $result = true;
        }

        return $result;

    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {
        parent::onClose($client_id);

         //TODO 链接断开事件
    }
}