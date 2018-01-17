<?php
namespace Wormhole\Protocols\HD10;
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\BaseEvents;
use Wormhole\Protocols\HD10\Controllers\ProtocolController;


use Wormhole\Protocols\HD10\Jobs\Events;
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



use Wormhole\Protocols\HD10\Protocol\Server\UpgradeFileInfo;

use Wormhole\Protocols\HD10\Protocol\Evse\UpgradeFileInfo as EvseUpgradeFileInfo;
use Wormhole\Protocols\HD10\Protocol\Evse\UpgradeDataPack as EvseUpgradeDataPack;
use Wormhole\Protocols\HD10\Protocol\Evse\UpgradeComfirm as EvseUpgradeComfirm;

use Wormhole\Protocols\HD10\upgradeQueue\UpgradePacket;
use Wormhole\Protocols\HD10\upgradeQueue\FileInformation;
use Wormhole\Protocols\HD10\upgradeQueue\UpdateConfirmation as UpdateConfirmationQueue;
use Wormhole\Protocols\HD10\upgradeQueue\DeviceReset;

use Wormhole\Protocols\HD10\Models\Upgrade;
use Wormhole\Protocols\HD10\Models\UpgradeFileInfo as UpgradeInfo;
use Wormhole\Protocols\HD10\Protocol\Frame1;


use Wormhole\Protocols\HD10\Protocol\Server\Frame\ReadChargeLogHistory AS ServerReadChargeHistoryFrame;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\ReadChargeLogHistory AS ServerReadChargeHistoryDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ReadChargeLogHistory AS EvseReadChargeHistoryFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ReadChargeLogHistory AS EvserReadChargeHistoryDataArea;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class EventsApi extends BaseEvents
{

    protected static $hasUpgradeFrame=FALSE; //暂时不开启，
    


    /**
     * 获取控制
     * @param $clientId
     * @param $monitorEvseCode
     * @return bool
     */
    public static function getControl($clientId, $monitorEvseCode)
    {
        $controller = new EvseController($clientId);

        return $controller->getControl($monitorEvseCode);
    }

    //public static function removeControl($clientId){
    //
    //}


    /**
     * 当客户端发来消息时触发
     * @param string $client_id 连接id
     * @param mixed $message 具体消息
     * @return bool
     */
    public static function message($client_id, $message)
    {

        Log::debug(__NAMESPACE__ . "\\".__CLASS__ ."\\" . __FUNCTION__ . "@" . __LINE__ . "  client_id:$client_id, message:" . bin2hex($message));

        //升级帧解析
        $frame1 = new Frame1();
        $frame = $frame1($message);
        if(empty($frame)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 帧无效");
            return false;
        }
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " cmd:,".$frame->cmd." func:".$frame->func .", isValid:".$frame->isValid."");
        if(!empty($frame) || $frame->isValid == 1){
            switch ($frame->cmd .' '. $frame->func ){
                case (0x12 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 升级文件信息");
                    self::UpgradeInformation($message);
                    break;
                case (0x12 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 升级数据包");
                    self::UpgradePackage($message);
                    break;
                case (0x12 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 更新确认");
                    self::updateConfirmation($message);
                    break;
                default:
                    $result = true;

            }
        }


        parent::message($client_id, $message);
        if (!self::continueMessage($client_id)) {
            return FALSE;
        }

        



        /**
         * @var  Frame
         */
        $frame = Frame::load($message);

        switch ($frame->getCommandCode() . ' ' . $frame->getFunctionCode()) {
            case (EvseSignInFrame::commandCode . ' ' . EvseSignInFrame::functionCode);
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 登陆");
                self::signIn($client_id, $frame);
                break;
            case (EvseHeartbeatFrame::commandCode . ' ' . EvseHeartbeatFrame::functionCode);
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 心跳");
                self::heartbeat($client_id, $frame);
                break;

            case (EvseCardSignFrame::commandCode .' ' . EvseCardSignFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 卡片签权");
                $result = self::cardSign($client_id,$frame);
                break;
            //启动充电相关
            //预约
            case (EvseReservationChargeFrame::commandCode .' ' . EvseReservationChargeFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 预约响应");
                $result =  self::doReservationResponse($client_id,$frame);
                break;
            //自检
            case (EvseStartChargeCheckFrame::commandCode .' ' . EvseStartChargeCheckFrame::functionCode);
                 Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 启动充电前自检");
                $result =  self::startChargeCheck($client_id,$frame);
                break;
            //启动充电
            case (EvseStartChargeFrame::commandCode .' ' . EvseStartChargeFrame::functionCode);
                 Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩启动");
                $result = self::startCharge($client_id,$frame);
                break;
            //启动失败，需要解约；
            case (EvseUnreservationChargeFrame::commandCode . ' ' .EvseUnreservationChargeFrame::functionCode);
                 Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 解约");
                $result = self::doUnReservationResponseAsynchronous($client_id,$frame);
                break;



            case (EvseStopChargeFrame::commandCode .' ' . EvseStopChargeFrame::functionCode);
                 Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩停止");
                $result = self::stopCharge($client_id,$frame);
                break;
            case (EvseChargeLogFrame::commandCode . ' ' .EvseChargeLogFrame::functionCode);
                 Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩记录上报");
                $result = self::uploadChargeInfo($client_id,$frame);
                break;
            case (EvseChargeRealtimeFrame::commandCode .' ' . EvseChargeRealtimeFrame::functionCode);
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩实时记录上报");
                $result = self::realtimeChargeInfo($client_id,$frame);
                break;
            case (EvseEventUploadFrame::commandCode.' '.EvseEventUploadFrame::functionCode):
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 事件上报");
                $result = self::eventUpload($client_id,$frame);
                break;
            case (EvseReadChargeHistoryFrame::commandCode.' '.EvseReadChargeHistoryFrame::functionCode):
                Log::debug(__NAMESPACE__  . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 读取历史记录");
                $result = self::readChargeHistory($client_id,$frame);
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

        self::sendMsg($clientId,$sendFrame);

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

        self::sendMsg($clientId,$sendFrame);
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

            self::sendMsg($clientId,$sendFrame);
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

            self::sendMsg($clientId,$sendFrame);
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

    private static function uploadChargeInfo($clientId, Frame $frame){
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
        self::sendMsg($clientId,$sendFrame);

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


    //下发升级文件信息响应
    private static function UpgradeInformation($message){

        $date = date('Y-m-d H:i:s', time());
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级文件信息接收start ".$date);
        //解析帧
        $upgradeFileInfo = new EvseUpgradeFileInfo();
        $frame_load = $upgradeFileInfo($message);
        //如果返回是接受,生成升级包队列,否则继续第一步
        $res = $frame_load->result->getValue(); //TODO
        $code = $frame_load->code->getValue();
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级文件信息接收成功, res:$res, code:$code ".$date);
        //$code = 'HDT00003';
        //升级表包序号加1,更改下发状态
        $upgrade = Upgrade::where('code', $code)->firstOrFail();
        if(empty($upgrade)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "未找到相应数据,code:$code ");
            return false;
        }
        $packageNumber = $upgrade->package_number;
        $isSuccess = json_decode($upgrade->is_success);
        if($res == 0xaa){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级文件信息接收成功, 包序号:$packageNumber ".$date);
            $isSuccess[$packageNumber] = 2;
            $upgrade->is_success = json_encode($isSuccess);
            //$upgrade->failure_times = 0;
            $upgrade->save();

            //创建升级数据包队列
            //dispatch(new UpgradePacket());
            $job = (new UpgradePacket())
                ->onQueue(env('APP_KEY'));
            dispatch($job);
        }elseif($res == 0x55){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级文件信息接收但失败, res:$res, code:$code ".$date);

            //判断失败三次则调用monitor
            if( $upgrade->failure_times == 3 ){ //$upgrade['failure_times'] == 3
//                $upgrade->failure_times = 0;
//                $upgrade->upgrade_state = 2;
//                $upgrade->save();
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级文件信息接收但失败, 超过三次 ".$date);
                return false;
            }
            //响应失败更改状态
            $isSuccess[$packageNumber] = 4;
            $upgrade->is_success = json_encode($isSuccess);
            //$upgrade->upgrade_state = 0;
            $upgrade->package_number = 0;
            //$upgrade->failure_times++;
            $upgrade->save();



            //dispatch(new FileInformation());
            $job = (new FileInformation())
                ->onQueue(env('APP_KEY'));
            dispatch($job);

        }
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级文件信息接收end, res:$res, code:$code ".$date);

    }

    //下发升级数据包
    private static function UpgradePackage($message){

        $date = date('Y-m-d H:i:s', time());
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级数据包接收start ".$date);
        $upgradeDataPack = new EvseUpgradeDataPack();
        $frame_load = $upgradeDataPack($message);
        //如果返回是接受,生成升级包队列,否则继续第一步
        $res = $frame_load->result->getValue(); //TODO
        $code = $frame_load->code->getValue();
        //Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认 code:$code ");
        //$code = 'HDT00003';
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级数据包接收成功, res:$res, code:$code ".$date);
        //升级表包序号加1,更改下发状态
        $upgrade = Upgrade::where('code', $code)->firstOrFail();
        $packageNumber = $upgrade->package_number;
        $isSuccess = json_decode($upgrade->is_success);
        if($res == 0xaa){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级数据包接收成功, 包序号:$packageNumber ".$date);
            //判断数据包是不是下发完毕
            if($upgrade->package_number == $upgrade->packet_number - 1){
                //更新确认队列
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认队列start ".$date);
                //dispatch(new UpdateConfirmationQueue($upgrade->monitor_code));
                $job = (new UpdateConfirmationQueue($upgrade->monitor_code))
                    ->onQueue(env('APP_KEY'));
                dispatch($job);
                $isSuccess[$packageNumber] = 3;
                $upgrade->is_success = json_encode($isSuccess);
                //$upgrade->failure_times = 0;
                $upgrade->save();
                //return true;
            }else{
                $isSuccess[$packageNumber] = 3;
                $upgrade->is_success = json_encode($isSuccess);
                $upgrade->package_number++;
                //$upgrade->failure_times = 0;
                $upgrade->save();
                //继续下发升级文件信息
                //dispatch(new FileInformation());
                //$job = (new FileInformation());
                //->onQueue(env('APP_KEY'));
                //dispatch($job);
                $job = (new UpgradePacket())
                ->onQueue(env('APP_KEY'));
                dispatch($job);

            }


        }elseif($res == 0x55){

            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级数据包接收但失败, 包序号:$packageNumber ".$date);


            //判断失败三次则调用monitor
            if( $upgrade->failure_times == 3 ){
//                $upgrade->failure_times = 0;
//                $upgrade->upgrade_state = 2;
//                $upgrade->save();
                return false;
            }

            //响应失败更改状态
            $isSuccess[$packageNumber] = 5;
            //$upgrade->failure_times++;
            $upgrade->is_success = json_encode($isSuccess);
            //$upgrade->upgrade_state = 0;
            $upgrade->package_number = 0;
            $upgrade->save();
            //dispatch(new FileInformation());
            $job = (new FileInformation())
                ->onQueue(env('APP_KEY'));
            dispatch($job);
        }


    }


    //更新确认
    private static function updateConfirmation($message){
        $date = date('Y-m-d H:i:s', time());
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认start".$date);

        $upgradeComfirm = new EvseUpgradeComfirm();
        $frame_load = $upgradeComfirm($message);
        //如果返回是接受,生成升级包队列,否则继续第一步
        $res = $frame_load->result->getValue(); //TODO
        $code = $frame_load->code->getValue();
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认 code:$code ");
        //$code = 'HDT00003';
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认结果res:$res".$date);
        $upgrade = Upgrade::where('code', $code)->firstOrFail();
        if($res == 0){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认成功".$date);
            $upgrade->confirm_status = 1;
            //$upgrade->failure_times = 0;
            $upgrade->save();
            //如果更新确认成功,设备复位队列
            //dispatch(new DeviceReset($upgrade['monitor_code']));
            $job = (new DeviceReset($upgrade->monitor_code, $upgrade->monitor_task_id))
                ->onQueue(env('APP_KEY'));
            dispatch($job);

        }elseif($res == 1){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认失败".$date);
            //判断失败超过3次
            if($upgrade->failure_times == 3){
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认失败3次,升级失败");
//                调用monitor接口,更新确认失败
//                升级失败调monitor接口
                //MonitorServer::update_evse_upgrade_type($task_id,$monitorCode,4);
                //$upgrade->failure_times = 0;
                $upgrade->save();
                //return false;
            }else{

                //dispatch(new UpdateConfirmationQueue($code));
                $job = (new UpdateConfirmationQueue($upgrade->monitor_code))
                ->onQueue(env('APP_KEY'));
                dispatch($job);

                //响应失败更改状态
                $upgrade->confirm_status = 2;
                //$upgrade->failure_times++;
                $upgrade->save();


            }



        }



    }







    public static function sendReadChargeHistory($clientId,$code,$orderId){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 读取充电记录 START");

        $frame = new ServerReadChargeHistoryFrame();
        $dataArea = new ServerReadChargeHistoryDataArea();
        $dataArea->setEvseCode($code);
        $dataArea->setUserId($orderId);
        $frame->setDataArea($dataArea);
        $sendFrame = $frame->build();

        self::sendMsg($clientId,$sendFrame);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 读取充电记录 END");
    }

    private static function readChargeHistory($clientId, Frame $frame){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "充电记录处理 START");
        $controller = new ProtocolController($clientId);

        $dataArea = new EvserReadChargeHistoryDataArea();
        $dataArea->load($frame->getDataArea());

        $code = $dataArea->getEvseCode();
        $isValid = $dataArea->getIsValid();
        $orderId = $dataArea->getUserId();
        $startTime =$dataArea->getStartChargeTime();
        $stopTime = $dataArea->getStopChargeTime();
        $chargedPower = $dataArea->getPower()*10;
        $fee = $dataArea->getMoney();

        if(FALSE == $isValid){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 无效的充电记录 END");
            return;
        }


        $result = $controller->readChargeHistory($code,$orderId,$startTime,$stopTime,$chargedPower,$fee);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "充电记录处理 END");
    }

}
