<?php
namespace Wormhole\Protocols\ZH\Controllers;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-23
 * Time: 17:40
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Wormhole\Http\Controllers\Controller;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\ZH\Protocol;
use Wormhole\Protocols\ZH\Protocol\Frame;
use Wormhole\Protocols\Library\Tools;
//use Wormhole\Protocols\ZH\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
//use Wormhole\Protocols\ZH\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;
use Wormhole\Protocols\ZH\Models\Evse;
use Wormhole\Protocols\ZH\Models\Port;
use Wormhole\Protocols\MonitorServer;
use Wormhole\Protocols\NJINT\Events;

use Wormhole\Protocols\ZH\EventsApi;
//use Wormhole\Protocols\ZH\Protocol\Server\UpgradeFileInfo as ServerUpgradeFileInfo;
//use Wormhole\Protocols\ZH\Protocol\Server\UpgradeComfirm as ServerUpgradeComfirm;
//use Wormhole\Protocols\ZH\Protocol\Server\DeviceReset as ServerDeviceReset;
//use Wormhole\Protocols\ZH\Models\Upgrade;
use Wormhole\Protocols\ZH\Models\UpgradeFileInfo;
//use Wormhole\Protocols\ZH\Protocol\Server\UpgradeDataPack;
class EvseController extends Controller
{

    public function checkHeartbeat($id){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START id : $id ");

        $evse = Evse::where([
                ['id',$id],
                ['last_update_status_time','<',Carbon::now()->subSeconds(5*Protocol::MAX_TIMEOUT) ],  //超过5倍最大超时时间
            ]
        )->first();

        if(is_null($evse)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 桩联网正常 ");
            return FALSE;
        }
        //找到monitor桩编号
        $port = Port::where('evse_id', $evse->id)->get();
        if(empty($port)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 检测桩断线,未找到对应枪 ");
            return false;
        }
        if(TRUE == $evse->online){
            foreach ($port as $v){
               $res = MonitorServer::updateEvseStatus($v->monitor_evse_code,FALSE);
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " $v->monitor_evse_code 桩断线,调用monitor结果 $res ");
            }

        }


        $evse->online = 0;
        //$evse->car_connect_status = FALSE;
        $evse->save();


        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END 心跳超时");
    }




    public function checkStartCharge($id,$orderId,$status){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START id : $id ,orderId : $orderId");

        $evse = Port::where([
                ['id',$id],
                ['order_id',$orderId],
                ['last_operator_status',$status]
            ]
        )->first(); //如果依然是当前操作的状态，启动失败
        if(is_null($evse)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动收到响应 END ");
            return FALSE;
        }

        MonitorServer::startCharge($orderId,FALSE);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END 启动未收到响应 ");
    }




    public function checkStopCharge($id,$orderId,$status){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START id : $id ,orderId : $orderId");

        $port = Port::where([
                ['id',$id],
                ['order_id',$orderId],
                ['last_operator_status',$status]
            ]
        )->first();

        if(is_null($port)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 收到停止响应, END ");
            return FALSE;
        }
       $res = MonitorServer::stopCharge($orderId,FALSE);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 未收到停止响应 $res");
    }



    //下发升级文件信息
    public function upgradeInfo(){

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件信息下发start");
        //查找升级中的桩,没有则找到一个空闲的开始升级下发
        $evseInfo = Upgrade::where('upgrade_state', 1)->first();
        if(empty($evseInfo)){
            $evseInfo = Upgrade::where('upgrade_state', 0)->first();
            $monitorEvseCode = $evseInfo->monitor_code;
            $evseInfo->upgrade_state = 1;
            $result = $evseInfo->save();
        }else{
            $monitorEvseCode = $evseInfo->monitor_code;
        }
        $code = $evseInfo->code; //桩code
        $packageNumber = $evseInfo->package_number; //包序号
        $fileIds = json_decode($evseInfo->file_id);  //文件id,数组
        $fileId = $fileIds[$packageNumber];
        $checkSum = $evseInfo->check_sum; //文件校验和
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " code:$code, packageNumber:$packageNumber, fileId:$fileId");
        //通过id找到文件信息
        $upgradeInfo = UpgradeFileInfo::where('id',$fileId)->firstOrFail();

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件信息下发: monitorEvseCode:$monitorEvseCode");
        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件信息下发: workerId:$workerId");
        $upgrade = new ServerUpgradeFileInfo();
        $upgrade->code($code);
        $upgrade->size($evseInfo->file_size);             //文件大小
        $upgrade->packetNumber($evseInfo->packet_number);//数据包总个数
        $upgrade->packetLength($upgradeInfo->packet_size);//单包数据长度
        $upgrade->checkSum($checkSum);                     //文件校验和

        $frame = strval($upgrade);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件信息下发: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件信息下发: sendResult:$sendResult");

        $info = array('monitorEvseCode'=>$monitorEvseCode,'packageNumber'=>$packageNumber);
        return $info;



    }


    //下发升级数据包
    public function upgradePacket(){

        //查找升级中的桩,没有则找到一个空闲的开始升级下发
        $evseInfo = Upgrade::where('upgrade_state', 1)->first();
        $monitorEvseCode = $evseInfo->monitor_code;
        $packageNumber = $evseInfo->package_number; //包序号
        $fileIds = json_decode($evseInfo->file_id);  //文件id,数组
        $fileId = $fileIds[$packageNumber];
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发,当前包序号: packageNumber:$packageNumber");
        //通过id找到文件信息
        $upgradeInfo = UpgradeFileInfo::where('id',$fileId)->firstOrFail();
        $content = $upgradeInfo->content; //文件数据
        $content = base64_decode($content);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发: monitorEvseCode:$monitorEvseCode");
        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();
        $code = $evse->code;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发: workerId:$workerId");

        $handle = fopen('./tt',"a");
        //组装帧
        $upgradeDataPack = new UpgradeDataPack();
        $upgradeDataPack->code($code);
        $upgradeDataPack->data($content);
        $frame = strval($upgradeDataPack);
        fwrite($handle,$upgradeDataPack->data);
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发: sendResult:$sendResult");

        $info = array('monitorEvseCode'=>$monitorEvseCode,'packageNumber'=>$packageNumber);
        return $info;

    }

    //更新确认
    public function updateConfirmation($monitorEvseCode){

        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $code = $evse->code;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 更新确认下发: workerId:$workerId");

        //组装帧
        $upgradeComfirm = new ServerUpgradeComfirm();
        $upgradeComfirm->code($code);
        $frame = strval($upgradeComfirm);
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 更新确认下发: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 更新确认下发: sendResult:$sendResult");




    }

    //设备复位
    public function resetDevice($monitorEvseCode){

        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 设备复位下发: workerId:$workerId");

        //组装帧
        $deviceReset = new ServerDeviceReset();
        $deviceReset->code($evse->code);
        $frame = strval($deviceReset);
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 设备复位下发: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 设备复位下发: sendResult:$sendResult");

    }






}