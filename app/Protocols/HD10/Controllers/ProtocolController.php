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
use Ramsey\Uuid\Uuid;
use Wormhole\Http\Controllers\Controller;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\CommonTools;
use Wormhole\Protocols\EvseConstant;
use Wormhole\Protocols\HD10\EventsApi;
use Wormhole\Protocols\HD10\Jobs\CheckHeartbeat;
use Wormhole\Protocols\HD10\Jobs\CheckStartCharge;
use Wormhole\Protocols\HD10\Models\ChargeOrderMapping;
use Wormhole\Protocols\HD10\Models\ChargeRecord;
use Wormhole\Protocols\HD10\Models\ChargeRecordFrame;
use Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\Protocol\Frame;

use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;
use Wormhole\Protocols\HD10\Models\Evse;

use Wormhole\Protocols\MonitorServer;
class ProtocolController extends Controller
{
    use CommonTools;
    protected  $workerId ;
    public function __construct($workerId='')
    {
        $this->workerId = $workerId;
    }

    public function signIn($code){

        Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 桩编号为：".$code);


        $evse = Evse::where('code',$code)->first();

        if(NULL === $evse){ //未找到，创建操作
            Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 未找到充电桩：".$code ." 创建桩");

            $codes = [
                0=>[
                'code'=>$code,
                'port'=>0
                ]
            ];

            $portMonitorCodes = MonitorServer::addEvse($codes,MonitorServer::CURRENT_AC);  //航电只有交流桩

            $evse = Evse::create([
                'code'=>$code,
                'protocol_name'=> \Wormhole\Protocols\HD10\Protocol::NAME,
                'monitor_code'=>$portMonitorCodes[0][0],  //HD 只有单枪，所以，取0号位置想的port=0
                'port_type'=> MonitorServer::CURRENT_AC
            ]);
        }

        $isCharging = $evse->is_charging;

        //签到不作为在线的判断依据，按照心跳来确定。
        //为了防止一直签到，所以签到，就作为桩不在线。等心跳处理在线状态。
        $signInResult = MonitorServer::updateEvseStatus($evse->monitor_code,FALSE,FALSE,TRUE);

        $evse->is_charging = FALSE;
        $evse->worker_id = $this->workerId;
        $evse->online = FALSE; //更新成功，则本地保存成功，失败保存失败    1 联网 ；0：断网
        $evse->save();


        //桩充电中，读取历史充电记录
        if(TRUE == $isCharging){
            EventsApi::sendReadChargeHistory($this->workerId,$code,$evse->evse_order_id);
        }



        Log::debug( __NAMESPACE__ . "/".__CLASS__ ."/"  . __FUNCTION__ . "@" . __LINE__ . " END 更新结果：");

//        return $signInResult;
        return TRUE;
    }
    public function heartbeat($code, $chargeStatus, $warningStatus, $gunConnectStatus, $emergencyStatus){
        Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " Start");
        $evse = Evse::where("code",$code)->firstOrFail();


        $lastOperatorTime = $evse->last_operator_time;
        $monitor_code = $evse->monitor_code;
        $evseInfoChargeStatus = $evse->is_charging;
        $passedTime = time()-$lastOperatorTime;

        //协议记录启动中\预约中、自检中，桩记录空闲，再最大等待时间内，不进行操作
        if(( 2 == $evseInfoChargeStatus || 4 == $evseInfoChargeStatus || 5 == $evseInfoChargeStatus )
            && 0 == $chargeStatus &&  $passedTime < Protocol::MAX_TIMEOUT ){
            Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 预约、自检、启动 等待时间内，不进行操作");
            return FALSE;
        }
        //停止中,桩上报状态为充电中， 最大等待时间内，不心跳
        if( 3 == $evseInfoChargeStatus
            && 1 == $chargeStatus &&  $passedTime < Protocol::MAX_TIMEOUT){
            Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 停止 等待时间内，不进行操作");
            return FALSE;
        }


        $needUpdate = TRUE != $evse->online
            || $chargeStatus != $evse->is_charging
            || $gunConnectStatus != $evse->car_connect_status
            || ((1 == $warningStatus || 1 == $emergencyStatus) && EvseConstant::WORK_STATUS_NORMAL == $evse->warning_status)
            || ((0 == $warningStatus && 0 == $emergencyStatus) && EvseConstant::WORK_STATUS_NORMAL != $evse->warning_status)
          ;
        Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . PHP_EOL. " code:$code,
            evse Status: online=>1 , chargeStatus=>". intval($chargeStatus) ." carConnectStatus=>".intval($gunConnectStatus)." warningStatus=>".intval($warningStatus) ." emergencyStatus => $emergencyStatus" .PHP_EOL."
            db Status :  online=>".$evse->online." , chargeStatus=>". intval($evse->is_charging) ." carConnectStatus=>".intval($evse->car_connect_status)." warningStatus=>".intval($evse->warning_status).PHP_EOL."            
            是否需要更新monitor：".intval($needUpdate));


        //状态更新才对服务器进行更新
        if( TRUE == $needUpdate){
            //工作状态判别，这里，因为告警不属于故障，所以只有充电状态
            $workStatus = 1 == $chargeStatus ? MonitorServer::WORK_STATUS_CHARGING: MonitorServer::WORK_STATUS_FREE;
            $workStatus = 1 == $warningStatus ? MonitorServer::WORK_STATUS_FAILURE:$workStatus; //告警状态
            $workStatus = 1 == $emergencyStatus ? MonitorServer::WORK_STATUS_FAILURE:$workStatus; //急停标记为故障

            $isConneted  = $gunConnectStatus ? TRUE:FALSE;
            $updateResponse = MonitorServer::updateEvseStatus($monitor_code,TRUE,$workStatus,TRUE);

            if(EvseConstant::WORK_STATUS_NORMAL == $evse->warning_status )
            {
                $warningStatus = 1 == $warningStatus ? EvseConstant::WORK_STATUS_OTHER: $evse->warning_status;
                $warningStatus = 1 == $emergencyStatus ? EvseConstant::WORK_STATUS_EMERGENCY_STOP:$warningStatus;
            }else{
                $warningStatus = 1 == $warningStatus ? $evse->warning_status:EvseConstant::WORK_STATUS_NORMAL;
                $warningStatus = 1 == $emergencyStatus ? EvseConstant::WORK_STATUS_EMERGENCY_STOP:$warningStatus;
            }


            $evse->online=TRUE;
            $evse->is_charging=$chargeStatus;
            $evse->car_connect_status=$gunConnectStatus;
            $evse->warning_status=$warningStatus;


        }
        $evse->last_update_status_time = Carbon::now();

        $evse->save();

        ////告警 判断； 由事件判断
        //if(1==$emergencyStatus ){
        //    $warningResponse = MonitorServer::newAlarm($monitor_code,MonitorServer::ALARM_EMERGENCY_STOP,"急停");
        //}

        //增加queue检查心跳；
        $job = (new CheckHeartbeat($evse->id))
                    ->onQueue(env('APP_KEY'))
                    ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT*10));
        dispatch($job);



        return TRUE;
    }

    public  function cardSign($code,$cardNumber){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

        $evse = Evse::where("code",$code)->firstOrFail();
        $result = MonitorServer::swipeCardToStartCharge($cardNumber,$evse->monitor_code);

        return $result;
    }

    /**
     * 预约
     * @param $code
     * @param bool $result 成功true ，失败false
     * @return bool 预约成功并处理成功，继续自检操作
     */
    public  function reservation($code, $result){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

        $evse = Evse::where("code",$code)->firstOrFail();

        if(0 != $evse->last_operator_status){ //如果最后操作不是预约，则不响应预约操作
            return FALSE;
        }

        //失败，调用启动充电失败命令
        if(FALSE == $result){
            $result = MonitorServer::startCharge($evse->order_id,FALSE);
            if($result){
                $evse->order_id="";
                $evse->is_billing = 0;
                $evse->start_type = 0;
                $evse->start_args = 0;
                $evse->charge_type = 0;
                $evse->charge_args = 0;
                $evse->user_balance = 0;
                $evse->last_operator_time = Carbon::now();
                $evse->last_operator_status = 2;
                $evse->save();

            }
            $this->unreservation($code,$evse->evse_order_id);
            return FALSE;
        }

        //预约成功，准备自检
       return $this->doStartChargeCheck($evse);




    }

    public  function unreservation($code,$userId){

    }

    /**
     * 组织自检需要的操作数据
     * @param $evse
     * @return bool
     */
    public  function doStartChargeCheck($evse){
        // 自检操作；
        $evse->last_operator_time = Carbon::now();
        $evse->last_operator_status = 3;
        $evse->save();

        //添加检查任务
        $job = (new CheckStartCharge($evse->id,$evse->order_id,3))
            ->onQueue(env('APP_KEY'))
            ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
        dispatch($job);

        return TRUE;
    }


    public function startChargeCheck($code,$result){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

        $evse = Evse::where("code",$code)->firstOrFail();

        if(0 != $result){ //自检失败 1：故障；2：被占用；3：未插抢
            $result = MonitorServer::startCharge($evse->order_id,FALSE);
            if($result){
                $evse->order_id="";
                $evse->is_billing = 0;
                $evse->start_type = 0;
                $evse->start_args = 0;
                $evse->charge_type = 0;
                $evse->charge_args = 0;
                $evse->user_balance = 0;
                $evse->last_operator_time = Carbon::now();
                $evse->last_operator_status = 5;
                $evse->save();

            }
            $this->unreservation($code,$evse->evse_order_id);
            return FALSE;

        }

        return $this->doStartCharge($evse);
    }

    public  function doStartCharge($evse){
        // 自检操作；
        $evse->last_operator_time = Carbon::now();
        $evse->last_operator_status = 6;
        $evse->save();
        //添加检查任务
        $job = (new CheckStartCharge($evse->id,$evse->order_id,6))
            ->onQueue(env('APP_KEY'))
            ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
        dispatch($job);
        return [
            "code"=>$evse->code,
            "userId"=>$evse->evse_order_id,
            "isBilling"=>$evse->is_billing,
            "chargeType"=>$evse->charge_type,
            "chargeArgs"=>$evse->charge_args,
            "userBalance"=>$evse->user_balance
        ];


    }
    public  function startCharge($code,$result){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

        $evse = Evse::where("code",$code)->firstOrFail();

        if(0 == $result){ // 启动成功
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动成功。");
            $evse->last_operator_status = 7;
            $evse->last_operator_time =Carbon::now();
            $evse->is_charging = 1;
            $evse->last_update_status_time=Carbon::now();
            $evse->charged_power = 0;
            $evse->duration = 0;
            $evse->fee = 0;
            $evse->voltage = 0;
            $evse->current = 0;
            $evse->power = 0;


            //启动成功后，更新关联状态未成功；
            $orderMap =  ChargeOrderMapping::where([
                [
                    "code",$code
                ],
                [
                    "order_id",$evse->order_id
                ]
            ])->first();

            if(!is_null($orderMap)){
                $orderMap->is_start_success = TRUE;
                $orderMap->save();
            }

        }else{
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动失败！");
            $evse->order_id="";
            $evse->is_billing = 0;
            $evse->start_type = 0;
            $evse->start_args = 0;
            $evse->charge_type = 0;
            $evse->charge_args = 0;
            $evse->user_balance = 0;
            $evse->last_operator_time = Carbon::now();
            $evse->last_operator_status = 8;

        }
        $evse->save();

        $result = MonitorServer::startCharge($evse->order_id,0 == $result);

        return $result;
    }

    public  function stopCharge($code,$result){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

        $evse = Evse::where("code",$code)->firstOrFail();


        $orderId = $evse->order_id;
        //清空数据
        if(0 == $result){
            $evse->order_id="";
            $evse->is_billing = 0;
            $evse->start_type = 0;
            $evse->start_args = 0;
            $evse->charge_type = 0;
            $evse->charge_args = 0;
            $evse->user_balance = 0;
            $evse->last_operator_time = Carbon::now();
            $evse->last_operator_status = 10;
            $evse->is_charging = 0;
            $evse->last_update_status_time=Carbon::now();

            $evse->charged_power = 0;
            $evse->duration = 0;
            $evse->fee = 0;
            $evse->voltage = 0;
            $evse->current = 0;
            $evse->power = 0;
        }else{
            $evse->last_operator_time = Carbon::now();
            $evse->last_operator_status = 11;
        }
        $evse->last_stop_reason = 1;
        $evse->save();

        //调用停止成功；
        $result = MonitorServer::stopCharge($orderId,0==$result);

        //if(FALSE == $result){
        //    return FALSE;
        //}
        //
        //
        ////等待10倍超时，在进行处理
        //sleep(10* Protocol::MAX_TIMEOUT);
        //
        //$nowEvse = Evse::where("code",$code)->firstOrFail();
        //
        //if(9 == $nowEvse->last_operator_status
        //&& $evse->last_operator_time == $nowEvse->last_operator_time){  //10倍时间超时，并且没有被操作更新过，进行补偿
        //    $stopTime = time();
        //    $formattedPower = $this->formatPower($evse->start_time,$stopTime,$evse->charged_power);
        //    MonitorServer::uploadChargeRecord($evse->order_id,$evse->start_time,$stopTime,$evse->charged_power,$formattedPower,FALSE,4,
        //        $stopTime-$evse->start_time,$evse->fee);
        //
        //}

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END， stop result：$result");

        return $result;

    }

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
     * @param bool $offline 是否断线导致
     * @return bool
     */
    public  function uploadChargeInfo($code,$evseOrderId,$startTime,$stopTime,$chargedPower,$fee,$frame,$offline=FALSE){

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

        $order = ChargeOrderMapping::where([
            [
                "code",$code
            ],
            [
                "evse_order_id",$evseOrderId
            ],
            [
                "is_start_success",TRUE
            ]
        ])
            ->orderBy('created_at','desc')
            ->firstOrFail();
        $evse = Evse::where("code",$code)->firstOrFail();

        //历史记录上报，但没有收到停止信息时，需要更新桩状态；
        //当前桩状态为充电中，并且上报的订单和桩存储的订单相同，则更新桩状态为空闲
        if(TRUE == $evse->is_charging && $evse->evse_order_id == $order->evse_order_id){
            $evse->is_charging = FALSE;
            MonitorServer::stopCharge($order->order_id,TRUE);
        }



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

            $startTime = $startTime == 0 ? strtotime($evse->start_time):$startTime;
            $stopTime = $stopTime == 0 ? time():$stopTime;

            $formattedPower = $this->formatPower($startTime,$stopTime,$chargedPower);

            $data =[
                'evse_id'=>$order->evse_id,
                'code'=>$order->code,
                'monitor_code'=>$order->monitor_code,
                'port_type' => $evse->port_type,
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
            ];
            if(FALSE == $offline){
                $data['stop_reason']= $evse->last_stop_reason; //由事件引发，自动处理；
            }else{
                $data['stop_reason'] = 7;
            }


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
                round($chargedPower/1000,2),$formattedPower,FALSE,4,
                $stopTime-$startTime,round($fee/100));
        }

        if(FALSE == $pushResult){
            return FALSE;
        }

        $chargeRecord->push_monitor_result = TRUE;
        $updateResult = $chargeRecord->save();

        $order->delete(); //删除上传成功的数据；

        //充电桩数据清理；
        $evse->order_id="";
        $evse->is_billing = 0;
        $evse->start_type = 0;
        $evse->start_args = 0;
        $evse->charge_type = 0;
        $evse->charge_args = 0;
        $evse->user_balance = 0;
        $evse->last_operator_time = Carbon::now();
        $evse->last_operator_status = 10;
        $evse->is_charging = 0;
        $evse->last_update_status_time=Carbon::now();

        $evse->charged_power = 0;
        $evse->duration = 0;
        $evse->fee = 0;
        $evse->voltage = 0;
        $evse->current = 0;
        $evse->power = 0;
        $evse->save();


        return $updateResult;
    }

    /**
     * @param $code
     * @param $voltage
     * @param $current
     * @param $duration
     * @param int $chargedPower 已充电量，单位：wh
     * @param $fee
     * @param int $power 功率 单位：w
     */
    public  function realtimeChargeInfo($code, $voltage, $current, $duration, $chargedPower, $fee, $power){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");
        $evse = Evse::where([
            [
                "code",$code
            ],
        ])->firstOrFail();

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ ." startTime:$duration ,now :  ".time() ." power:$power,voltage:$voltage,current:$current,fee:$fee");

        $evse->charged_power = $chargedPower;
        $evse->duration = $duration;
        $evse->fee = $fee;
        $evse->voltage = $voltage;
        $evse->current = $current;
        $evse->power = $power;
        $evse->last_update_charge_info_time = Carbon::now();
        $result = $evse->save();

        return $result;


    }

    /**
     * 事件上报
     * @param $code string 桩编号
     * @param $status int 状态
     */
    public function eventUpload($code,$status){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");
        $evse = Evse::where([
            [
                "code",$code
            ],
        ])->firstOrFail();

        $result =FALSE;


        switch ($status){
            case 1: {//正常;
                $evse->last_stop_reason = 1;
                $result = MonitorServer::updateEvseStatus($evse->monitor_code,TRUE,MonitorServer::WORK_STATUS_FREE,TRUE);
                break;
            }
            case 2: {//过压
                $evse->warning_status = EvseConstant::WORK_STATUS_OVER_VOLTAGE;
                $evse->last_stop_reason = 2;
                $result = MonitorServer::updateEvseStatus($evse->monitor_code,TRUE,MonitorServer::WORK_STATUS_FAILURE,TRUE);
                break; }
            case 3: { //过流
                $evse->warning_status = EvseConstant::WORK_STATUS_OVERCURRENT;
                $evse->last_stop_reason = 3;
                $result = MonitorServer::updateEvseStatus($evse->monitor_code,TRUE,MonitorServer::WORK_STATUS_FAILURE,TRUE);
                break;
            }
            case 4: {//漏电
                $evse->warning_status = EvseConstant::WORK_STATUS_LEAKAGE;
                $evse->last_stop_reason = 4;
                $result = MonitorServer::updateEvseStatus($evse->monitor_code,TRUE,MonitorServer::WORK_STATUS_FAILURE,TRUE);
                break;
            }
            case 5: //急停
            {
                $evse->warning_status = EvseConstant::WORK_STATUS_EMERGENCY_STOP;
                $evse->is_charging = FALSE;
                $evse->last_stop_reason = 5;
                $result = MonitorServer::updateEvseStatus($evse->monitor_code,TRUE,MonitorServer::WORK_STATUS_FAILURE,TRUE);
                break;
            }
            case 6: //拔枪
            {

                $evse->is_charging = FALSE;
                $evse->car_connect_status = 0;
                $result = MonitorServer::updateEvseStatus($evse->monitor_code,TRUE,MonitorServer::WORK_STATUS_FREE,TRUE);
                break;
            }
            case 7: {//插抢
                $evse->is_charging = FALSE;
                $evse->car_connect_status = 1;
                $evse->last_stop_reason = 7;
                $result = MonitorServer::updateEvseStatus($evse->monitor_code,TRUE,MonitorServer::WORK_STATUS_FREE,TRUE);
                break;
            }
        }

        $evse->save();




        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " End");

        return $result;
    }

    public function getControl($monitorEvseCode){
        $evse = Evse::where('monitor_code',$monitorEvseCode)->first();
        $result =FALSE;
        if(NULL != $evse){
            $evseWorkerId = $evse->worker_id;
            $result = EventsApi::binding($this->workerId,$evseWorkerId);
        }
        return $result;
    }

    public function readChargeHistory($code,$evseOrderId,$startTime,$endTime,$chargedPower,$fee){
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 读取充电记录 Start ".PHP_EOL."
                code:$code , evseOrderId:$evseOrderId ; startTime:$startTime ; stopTime:$endTime ; chargedPower:$chargedPower ; fee:$fee");


        //当前业务：
        //断电后，短时间内联网签到，发现协议依然充电中，则进行记录补偿;取订单的最后记录；
        self::uploadChargeInfo($code,$evseOrderId,$startTime,$endTime,$chargedPower,$fee,'',TRUE);


        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 读取充电记录 End");


    }

}