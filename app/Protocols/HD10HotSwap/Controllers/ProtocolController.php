<?php
namespace Wormhole\Protocols\HD10HotSwap\Controllers;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-23
 * Time: 17:40
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Wormhole\Http\Controllers\Controller;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\CommonTools;
use Wormhole\Protocols\HD10\EventsApi;
use Wormhole\Protocols\HD10\Models\ChargeOrderMapping;
use Wormhole\Protocols\HD10\Models\ChargeRecord;
use Wormhole\Protocols\HD10\Models\ChargeRecordFrame;
use Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\Protocol\Frame;

use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;
use Wormhole\Protocols\HD10\Models\Evse;

use Wormhole\Protocols\MonitorServer;
class ProtocolController extends \Wormhole\Protocols\HD10\Controllers\ProtocolController
{

    /**
     * 业务逻辑：
     * 1、查询是否存在订单；
     * 2、查询是wormhole是否存储过；
     * 3、如果没有存储过，直接上报，根据上报结果，存储数据；
     * 4、如果存储过，并且上报失败，则上报monitor，根据上报结果更新存储数据；
     * @param $code
     * @param $evseOrderId
     * @param $startTime
     * @param $stopTime
     * @param $chargedPower
     * @param $fee
     * @param string $frame ascii 帧字符串
     * @return bool
     */
    public  function uploadChargeInfo($code,$evseOrderId,$startTime,$stopTime,$chargedPower,$fee,$frame){

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

        $order = ChargeOrderMapping::where([
            [
                "code",$code
            ],
            [
                "evse_order_id",$evseOrderId
            ]
        ])->firstOrFail();
        $evse = Evse::where("code",$code)->firstOrFail();

        $chargeRecordFrame = [
            'evse_id'=>$evse->id,
            'code'=>$evse->code,
            'monitor_code'=>$evse->monitor_code,
            'frame'=>bin2hex($frame)
        ];
        $frameResult = ChargeRecordFrame::create($chargeRecordFrame);


        //查询是否已经存储过
        $chargeRecord = ChargeRecord::where([['order_id',$order->order_id]])->first();


        if(is_null($chargeRecord)){ //没有存储过
            $formattedPower = $this->formatPower($startTime,$stopTime,$chargedPower);

            $data =[
                'evse_id'=>$order->evse_id,
                'code'=>$order->code,
                'monitor_code'=>$order->monitor_code,
                'order_id'=>$order->order_id,
                'evse_order_id'=>$order->evse_order_id,
                'start_time'=>Carbon::createFromTimestamp( $startTime),
                'end_time'=>Carbon::createFromTimestamp($stopTime),
                'charged_power'=>$chargedPower,
                'duration'=>$stopTime-$startTime,
                'fee' =>$fee,
                'formatted_power'=>json_encode($formattedPower),
                'is_billing'=>$order->is_billing,
                'start_type'=>$order->start_type,
                'start_args'=>$order->start_args,
                'charge_type'=>$order->charge_type,
                'charge_args'=>$order->charge_args,
                'stop_reason'=> (10 == $evse->last_operator_status)? 1:0,  //10未后台停止成功，其他没有时间上报，暂时不考虑
            ];
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Save charge record".json_encode($data));
            $chargeRecord = ChargeRecord::create($data);

        }

        $pushResult = FALSE;
        if(FALSE == $chargeRecord->push_monitor_result){

            $formattedPower = json_decode( $chargeRecord->formatted_power,TRUE);
            array_walk($formattedPower,function(&$power){
                $power['power'] =round($power['power']/1000,2);
            });

           $pushResult = MonitorServer::uploadChargeRecord($order->order_id,
                $startTime,$stopTime,
                round($chargedPower/1000,2),$formattedPower,TRUE,4,
                $stopTime-$startTime,round($fee/100));
        }

        if(FALSE == $pushResult){
            return FALSE;
        }

        $chargeRecord->push_monitor_result = TRUE;
        $updateResult = $chargeRecord->save();

        return $updateResult;
    }


}