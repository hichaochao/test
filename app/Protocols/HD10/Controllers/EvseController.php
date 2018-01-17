<?php
namespace Wormhole\Protocols\HD10\Controllers;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-23
 * Time: 17:40
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Wormhole\Http\Controllers\Controller;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\Library\Tools;
use Ramsey\Uuid\Uuid;

use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;
use Wormhole\Protocols\HD10\Models\Evse;
use Wormhole\Protocols\HD10\Models\UpgradeTask;

use Wormhole\Protocols\MonitorServer;
use Wormhole\Protocols\NJINT\Events;

use Wormhole\Protocols\HD10\EventsApi;
use Wormhole\Protocols\HD10\Protocol\Server\UpgradeFileInfo as ServerUpgradeFileInfo;
use Wormhole\Protocols\HD10\Protocol\Server\UpgradeComfirm as ServerUpgradeComfirm;
use Wormhole\Protocols\HD10\upgradeQueue\FileInformation;
use Wormhole\Protocols\HD10\Protocol\Server\DeviceReset as ServerDeviceReset;
use Wormhole\Protocols\HD10\Models\Upgrade;
use Wormhole\Protocols\HD10\Models\UpgradeFileInfo;
use Wormhole\Protocols\HD10\Models\UpgradeFileInfo as UpgradeFile;
use Wormhole\Protocols\HD10\Protocol\Server\UpgradeDataPack;



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
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END ");
            return FALSE;
        }
        if(TRUE == $evse->online){
            MonitorServer::updateEvseStatus($evse->monitor_code,FALSE);
        }

        // 离线不做补偿，不自动停止。
//        //如果充电中，需要做补偿；
//        if(TRUE == $evse->is_charging){
//            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 断线补偿 ");
//            $protocol = new ProtocolController();
//            $protocol->uploadChargeInfo($evse->code,$evse->evse_order_id,
//                strtotime($evse->start_time),strtotime($evse->last_update_charge_info_time),
//                $evse->charged_power,$evse->fee,'',TRUE
//                );
//
//        }

        $evse->online = FALSE;
        $evse->is_charging = FALSE;
        $evse->car_connect_status = FALSE;
        $evse->save();


        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END 心跳超时");
    }

    public function checkStartCharge($id,$orderId,$status){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START id : $id ,orderId : $orderId");

        $evse = Evse::where([
                ['id',$id],
                ['order_id',$orderId],
                ['last_operator_status',$status]
            ]
        )->first(); //如果依然是当前操作的状态，启动失败
        if(is_null($evse)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END ");
            return FALSE;
        }

        MonitorServer::startCharge($orderId,FALSE);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END 启动失败");
    }

    public function checkStopCharge($id,$orderId,$status){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START id : $id ,orderId : $orderId");

        $evse = Evse::where([
                ['id',$id],
                ['order_id',$orderId],
                ['last_operator_status',$status]
            ]
        )->first();

        if(is_null($evse)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END ");
            return FALSE;
        }
        MonitorServer::stopCharge($orderId,FALSE);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END 停止失败");
    }



    //准备升级
    public function upgradeTask($taskId){

        
        //检查任务是否有效,是否存在
        $date = Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 时间 $date");
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级准备");

        //获取monitorcode
        $upgradeInfo = UpgradeTask::where('task_id',$taskId)->first();
        if(empty($upgradeInfo)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 未找到升级id: $taskId ");
            return false;
        }
        
        //更改任务状态
        $upgradeInfo->status = 1; //升级中
        $upgradeInfo->save();
        
        $monitorCodes = $upgradeInfo->code;
        $fileId = $upgradeInfo->file_id;
        $packetSize = $upgradeInfo->packet_size;
        $monitorCodes = json_decode($monitorCodes, true);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 单包长度 $packetSize ");
        //分割长度
        $packctLen = $packetSize; //128  256 384
        //分割文件
        //$content = file_get_contents($upgradeUrl);
        $server = Config::get('monitor.pubHost');
        $file = Config::get('monitor.getFile');
        $url = $server.$file.'/'.$fileId;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件地址 $url ");
        $content = file_get_contents($url);
        //$res = MonitorServer::get($url);
        //$content = file_get_contents($res);
        //var_dump('-------'.$content);die;
        //$content = file_get_contents('/data/wwwroot/wormhole_hd8212/public/HD-MINI2-V3.0UC-20170616.bin');
        if(empty($content)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 未获取到文件信息");
            return false;
        }
        $contentArr = array();
        $len = strlen($content);         //内容长度
        $divisionNum = ceil($len / $packctLen); //包总个数
        $j = 0;
        //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 文件信息 len:$len, divisionNum:$divisionNum");
        //把分开的文件存入到数组
        for ($i = 0; $i < $divisionNum; $i++) {
            $contentArr[$i] = substr($content, $j, $packctLen);
            //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 分开的文件 ".json_encode(substr($content, $j, $packctLen)));
            $j = $j + $packctLen;
        }

        //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 分开的文件存入数组 ".json_encode($contentArr));

        //存放包的id
        $fileId = array();
        //每一包的状态
        $status = [];
        //把分割文件存入到数据库
        for ($i = 0; $i < count($contentArr); $i++) {
            $id = Uuid::uuid4();//strval(Uuid::uuid4());
            $fileId[] = $id;
            UpgradeFile::create([
                'id' => $id,
                'file_id' => $upgradeInfo->file_id,
                'package_number' => $i,
                'packet_size' => strlen($contentArr[$i]),
                'content' => base64_encode($contentArr[$i]),
                'monitor_task_id' => $upgradeInfo->task_id

            ]);
            $status[$i] = 0;
        }
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 文件表 monitor_task_id ".$upgradeInfo->task_id);

        //通过monitorcode找到code
            $codes = Evse::whereIn("monitor_code", $monitorCodes)
            ->select("code")
            ->get(); //找出所有code

        if(empty($codes)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 未找到code ");
            return false;
        }

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 获取所有code ".json_encode($codes));
        //文件校验和
        $checkSum = Tools::getBCCByPlus(Tools::asciiStringToDecArray($content), 2);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " fild_id:$upgradeInfo->file_id".
           'code'.$codes[0]['code']);

        //添加任务信息
        for ($k = 0; $k < count($codes); $k++) {

            $res = Upgrade::create([
                'id' => Uuid::uuid4(),
                'file_id' => $upgradeInfo->file_id,
                'monitor_task_id' => $upgradeInfo->task_id,
                'code' => $codes[$k]['code'],
                'monitor_code' => $monitorCodes[$k],
                'upgrade_state' => 0,
                'task_id' => 0,
                'package_number' => 0,
                'file_size' => $len,
                'packet_number' => $divisionNum,
                'check_sum' => $checkSum,
                'failure_times' => 0,
                'is_success' => json_encode($status)

            ]);

        }

        if(empty($res)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 任务信息添加失败");
            return false;
        }

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件存储成功");

        //生成队列下发升级文件信息
        $job = (new FileInformation())
            ->onQueue(env('APP_KEY'));
        dispatch($job);


//        return $this->response->array([
//            'status_code'=>200,
//            'message'=>'',
//            'data'=>true
//
//        ]);
        
        
    }



    //下发升级文件信息
    public function upgradeInfo(){
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 准备升级文件信息下发");
        //首先判断升级任务里面有没有正在升级的,有继续升级此任务里面的桩,没有则升级等待的任务
        $upgrade_task = UpgradeTask::where('status', 1)->first();
        if(!empty($upgrade_task)){
            $task_id = $upgrade_task->task_id;
        }else{
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 准备下发升级文件信息, 未找到正在升级中的任务 ");
            return false;
        }

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件信息下发start");
        //查找升级中的桩,没有则找到一个空闲的开始升级下发
        $condition = [
            ['upgrade_state', '=', 1],
            ['monitor_task_id', '=', $task_id]
        ];
        $evseInfo = Upgrade::where($condition)->first();
        if(empty($evseInfo)){
            $condition = [
                ['upgrade_state', '=', 0],
                ['monitor_task_id', '=', $task_id]
            ];
            $evseInfo = Upgrade::where($condition)->first();


            if(empty($evseInfo)){
                Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 查看当前升级任务是否还有其他桩要升级 ");
                //如果任务结束,更改任务表升级状态
                $upgradeInfo = UpgradeTask::where('task_id',$task_id)->first();
                if(empty($upgradeInfo)){
                    Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 任务升级结束,更改任务状态,未找到数据 monitor_task_id:".$evseInfo->monitor_task_id);
                    return false;
                }
                $upgradeInfo->status = 2; //执行结束
                $upgradeInfo->save();



                //此任务升级结束,删除相应数据
                $res = UpgradeTask::where('task_id', $task_id)->delete();
                $res = Upgrade::where('monitor_task_id', $task_id)->delete();
                $res = UpgradeFile::where('monitor_task_id', $task_id)->delete();

                //当前任务结束,查看任务表中是否还有其他任务
                $upgrade_task = UpgradeTask::where('status', 0)->first();
                if(!empty($upgrade_task)){
                    Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 还有等待的升级任务,继续执行 ");
                    sleep(60);
                    $this->upgradeTask($upgrade_task->task_id);
                }
                Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 当前升级任务结束 ");
                return true;
            }
            $monitorEvseCode = $evseInfo->monitor_code;
            $evseInfo->upgrade_state = 1;
            $result = $evseInfo->save();



        }else{
            $monitorEvseCode = $evseInfo->monitor_code;
        }


        $code = $evseInfo->code; //桩code
        $packageNumber = $evseInfo->package_number; //包序号
        //$fileIds = json_decode($evseInfo->file_id);  //文件id,数组
        //$fileId = $fileIds[$packageNumber];
        $fileId = $evseInfo->file_id;
        $checkSum = $evseInfo->check_sum; //文件校验和
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " code:$code, packageNumber:$packageNumber, fileId:$fileId");



        //重置数据包是否成功发送状态.因为失败后会重新执行,要清空里面的值
        $arr = [];
        $isSuccess = $evseInfo->is_success;
        $isSuccess = json_decode($isSuccess, true);
        foreach ($isSuccess as $k=>$v){
            $arr[$k] = 0;
        }
        $evseInfo->is_success = json_encode($arr);
        $evseInfo->save();


        //通过文件id和包序号找到文件信息
        $condition = [
            ['file_id', '=', $fileId],
            ['package_number', '=', $packageNumber]
        ];
        //$upgradeInfo = UpgradeFileInfo::where('id',$fileId)->firstOrFail();
        $upgradeInfo = UpgradeFileInfo::where($condition)->firstOrFail();

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

        //如果升级文件信息下发成功,则表示升级开始
        if($sendResult){
            //告诉monitor开始升级了
            $task_id = $evseInfo->monitor_task_id;
            $monitorCode = $evseInfo->monitor_code;
            MonitorServer::update_evse_upgrade_type($task_id,$monitorCode,2);
        }


        $info = array('monitorEvseCode'=>$monitorEvseCode,'packageNumber'=>$packageNumber);
        return $info;



    }


    //下发升级数据包
    public function upgradePacket(){

        //查找升级中的桩,没有则找到一个空闲的开始升级下发
        $evseInfo = Upgrade::where('upgrade_state', 1)->first();
        $monitorEvseCode = $evseInfo->monitor_code;
        $packageNumber = $evseInfo->package_number; //包序号
        //$fileIds = json_decode($evseInfo->file_id);  //文件id,数组
        //$fileId = $fileIds[$packageNumber];
        $fileId = $evseInfo->file_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发,当前包序号: packageNumber:$packageNumber");

        //初始化包状态字段,是否收到响应
        $isSuccess = json_decode($evseInfo->is_success);
        $isSuccess[$packageNumber] = 0;
        $evseInfo->is_success = json_encode($isSuccess);

        //通过fielId和包序号找到文件信息
        $condition = [
            ['file_id', '=', $fileId],
            ['package_number', '=', $packageNumber]
        ];
        $upgradeInfo = UpgradeFileInfo::where($condition)->firstOrFail();
        $content = $upgradeInfo->content; //文件数据
        $content = base64_decode($content);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发: monitorEvseCode:$monitorEvseCode");
        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();
        $code = $evse->code;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据包下发: workerId:$workerId");

        //$handle = fopen('/data/wwwroot/wormhole_hd8212/public/tt',"a");
        //组装帧
        $upgradeDataPack = new UpgradeDataPack();
        $upgradeDataPack->code($code);
        $upgradeDataPack->data($content);
        $frame = strval($upgradeDataPack);
        //fwrite($handle,$upgradeDataPack->data);
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
    public function resetDevice($monitorEvseCode, $taskId){

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

        //桩升级完毕,更改状态
         $condition = [
             ['monitor_code',$monitorEvseCode],
             ['monitor_task_id',$taskId]
         ];

        $upgrade = Upgrade::where($condition)->first();
        if(empty($upgrade)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 设备复位下发后未找到,更改升级状态未找到相应数据 monitorEvseCode:$monitorEvseCode, taskId:$taskId");
        }
        $upgrade->upgrade_state = 2; //升级成功
        $upgrade->save();
        //复位之后调用monitor升级成功
        MonitorServer::update_evse_upgrade_type($taskId,$monitorEvseCode,3);


        //复位下发完之后,继续发下一个桩
        $this->upgradeInfo();



    }



    //如果升级成功或者失败,此桩已升级结束,删除此桩数据
    public function eliminate($monitor_code){

        $res = Upgrade::where('monitor_code', $monitor_code)->delete();
        if(empty($res)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级数据删除失败 monitor_code:$monitor_code");
            return false;
        }
        return true;

    }






}