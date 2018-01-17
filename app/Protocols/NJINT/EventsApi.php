<?php
namespace Wormhole\Protocols\NJINT;


use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\BaseEvents;
use Wormhole\Protocols\NJINT\Protocol\ServerFrame;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\NJINT\Protocol\Frame;
use Wormhole\Protocols\NJINT\Controllers\ProtocolController;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\SignIn as EvseSignInFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
use Wormhole\Protocols\NJINT\Protocol\Server\Frame\SignIn as ServerSignInFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\SignIn as ServerSignInDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\Heartbeat as EvseHeartbeatFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\Heartbeat as EvseHeartbeatDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseStatus as EvseEvseStatusFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseStatus as EvseEvseStatusDataArea;
use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseStatus as ServerEvseStatusFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseStatus as ServerEvseStatusDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\StartCharge as EvseStartChargeFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\StartCharge as EvseStartChargeDataArea;
use Wormhole\Protocols\NJINT\Protocol\Server\Frame\StartCharge as ServerStartChargeFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge as ServerStartChargeDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseControl as EvseEvseControlFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseControl as EvseEvseControlDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UploadChargingLog as EvseUploadChargingLogFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadChargingLog as EvseUploadChargingLogDataArea;

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

        parent::message($client_id,$message);
        if(!self::continueMessage($client_id)){
            return FALSE;
        }

        /**
         *@var  Frame
         */
        $frame = Frame::load($message);

        if(FALSE === $frame){
            return FALSE;
        }

        $controller = new ProtocolController($client_id);
        $serverFrame = new ServerFrame();

        switch ($frame->getOperator()) {
            case EvseSignInFrame::OPERATOR;
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "   (CMD=106)充电桩签到信息上报");
                $dataArea = new EvseSignInDataArea();
                $dataArea->load($frame->getDataArea());

                $result = $controller->signIn($dataArea->getEvseCode(),$dataArea->getEvseType(),$dataArea->getVersion(),$dataArea->getProjectType(),
                    $dataArea->getStartTimes(),$dataArea->getUploadModel(),$dataArea->getSignInInterval(),$dataArea->getRuntimeInnerVar(),$dataArea->getGunAmount(),
                    $dataArea->getHeartbeatInterval(),$dataArea->getHeartbeatTimeoutTimes(),$dataArea->getChargeLogAmount(),$dataArea->getSystemTime(),
                    $dataArea->getLastChargeTime(),$dataArea->getLastStartChargeTime());


                $sendFrame = $serverFrame->signInFrame(1);
                self::sendMsg($client_id,$sendFrame);
                break;

            case EvseHeartbeatFrame::OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " (CMD=102)充电桩上传心跳包信息");
                $dataArea = new EvseHeartbeatDataArea();
                $dataArea->load($frame->getDataArea());

                $result = $controller->heartbeat($dataArea->getEvseCode(),$dataArea->getHeartbeatSequence());

                $sendFrame = $serverFrame->heartbeat($dataArea->getHeartbeatSequence(),($dataArea->getHeartbeatSequence()+1%pow(256,2)));
                self::sendMsg($client_id,$sendFrame);
                break;




            case EvseEvseStatusFrame::OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  (CMD=104)充电桩状态信息包上报");
                $dataArea = new EvseEvseStatusDataArea();
                $dataArea->load($frame->getDataArea());

                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态信息上报,枪口号:".$dataArea->getGunNum());

                $result = $controller->uploadStatus($dataArea->getEvseCode(),$dataArea->getGunNum(),$dataArea->getGunType(),
                    $dataArea->getWorkerStatus(),$dataArea->getWarningStatus(),$dataArea->getCarConnectStatus(),
                    $dataArea->getChargeStartType(),$dataArea->getCardId(),

                    strtotime($dataArea->getChargeStartTime()),$dataArea->getThisChargedPower()*10,$dataArea->getThisChargingFee(),$dataArea->getChargingDuration(),

                $dataArea->getPower()*100,$dataArea->getNowSOCPercent(),$dataArea->getRemainingChargingTime(),//getTimeoutTime
                $dataArea->getAcAChargingVoltage()*100,$dataArea->getAcAChargingCurrent()*100,
                $dataArea->getAcBChargingVoltage()*100,$dataArea->getAcBChargingCurrent()*100,
                $dataArea->getAcCChargingVoltage()*100,$dataArea->getAcCChargingCurrent()*100,
                $dataArea->getDcChargeVoltage()*100,$dataArea->getDcChargeCurrent()*100,
                $dataArea->getBMSChargeModel(),$dataArea->getBMSNeedVoltage()*100,$dataArea->getBMSNeedCurrent()*100,
                $dataArea->getMeterPowerBefore()*10,$dataArea->getMeterPowerNow()*10
                );


                $sendFrame = $serverFrame->evseStatusFrame(1,$dataArea->getGunNum());

                self::sendMsg($client_id,$sendFrame);
                break;

            case EvseStartChargeFrame::OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  (CMD=8) 充电桩对后台下发的充电桩开启充电控制应答");
                $dataArea = new EvseStartChargeDataArea();
                $dataArea->load($frame->getDataArea());

                $result = $controller->startCharge($dataArea->getPoleId(),$dataArea->getGunNum(),$dataArea->getExcuteResult());


                break;

            case EvseEvseControlFrame::OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  (CMD=6)充电桩对后台控制命令应答");

                $dataArea = new EvseEvseControlDataArea();
                $dataArea->load($frame->getDataArea());
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " getCmdStartPosition： ".$dataArea->getCmdStartPosition().'--'.
                    $dataArea->getPoleId().'--'.$dataArea->getGunNum().'--'.$dataArea->getCmdNum().'--'.$dataArea->getExcuteResult());
                $result = $controller->evse_control_command($dataArea->getPoleId(),$dataArea->getGunNum(),$dataArea->getCmdStartPosition(),$dataArea->getCmdNum(),$dataArea->getExcuteResult());


                break;

            case EvseUploadChargingLogFrame::OPERATOR:
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " (CMD=202)充电桩上报充电记录信息 （数据域长度265）");
                $dataArea = new EvseUploadChargingLogDataArea();
                $dataArea->load($frame->getDataArea());

                $powerOfTimes = $dataArea->getPowerOfTime();
                array_walk($powerOfTimes,function(&$power){
                    $power *=10;
                });

                $result = $controller->chargeRecordUpload($dataArea->getEvseCode(),$dataArea->getGunNum(),
                    $dataArea->getCardId(),$dataArea->getStartTime(),$dataArea->getEndTime(),$dataArea->getDuration(),
                    $dataArea->getStartSOC(),$dataArea->getEndSOC(),$dataArea->getStopReson(),$dataArea->getPower()*10,
                    $dataArea->getMeterBefore()*10,$dataArea->getMeterAfter()*10,
                    $dataArea->getChargingFee(),$dataArea->getCardBalanceBefore(),
                    $dataArea->getChargeTactics(),$dataArea->getChargeTacticsArgs(),
                    $dataArea->getCarVIN(),$dataArea->getCarPlateNumber(),
                    $powerOfTimes,
                    $dataArea->getStartType(),$message);


                if(TRUE == $result){

                    $sendFrame = $serverFrame->uploadChargingLog(1,$dataArea->getGunNum(),$dataArea->getCardId());


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