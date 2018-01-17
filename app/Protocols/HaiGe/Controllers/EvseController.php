<?php
 namespace Wormhole\Protocols\HaiGe\Controllers;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-23
 * Time: 17:40
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Wormhole\Http\Controllers\Controller;
use Wormhole\Protocols\HaiGe\Models\Port;
use Wormhole\Protocols\HaiGe\Protocol;
use Wormhole\Protocols\HaiGe\Protocol\Frame;

use \Curl\Curl;
use Wormhole\Protocols\HaiGe\ServerFrame;

//心跳
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Heartbeat as HeartbeatDataArea;
use Wormhole\Protocols\HaiGe\Models\Evse;


use Wormhole\Protocols\HaiGe\Events;
 use Wormhole\Protocols\MonitorServer;
 use Wormhole\Protocols\HaiGe\Controllers\ProtocolController;

 class EvseController extends Controller
{
    public function __construct()
    {

    }

    public function checkHeartbeat($id){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START id : $id ");

        $evse = Port::where([
                ['id',$id],
                ['last_update_status_time','<=',Carbon::now()->subSeconds(Protocol::MAX_TIMEOUT) ],  //超过5倍最大超时时间
            ])->first();

        if(is_null($evse)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END ");
            return FALSE;
        }
        if(TRUE == $evse->online){
            MonitorServer::updateEvseStatus($evse->monitor_code,FALSE);
        }


        $evse->net_status = FALSE;
        $evse->charge_status = 0;
        $evse->port_status = 0;
        $evse->save();


        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END 心跳超时");

    }

     public  function checkStartCharge($id,$orderId,$status){
         Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START id : $id ,orderId : $orderId");

         $evse = Port::where([
                 ['id',$id],
                 ['order_id',$orderId],
                 ['task_status',$status]
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

         $evse = Port::where([
                 ['id',$id],
                 ['order_id',$orderId],
                 ['task_status',$status]
             ]
         )->first();

         if(is_null($evse)){
             Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END ");
             return FALSE;
         }

         MonitorServer::stopCharge($orderId,FALSE);

         Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END 停止失败");
     }

}

