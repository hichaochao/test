<?php
namespace Wormhole\Protocols\NJINT\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Null_;
use Ramsey\Uuid\Uuid;
use Wormhole\Protocols\CommonTools;
use Wormhole\Protocols\NJINT\EventsApi;
use Wormhole\Protocols\NJINT\Models\ChargeOrderMapping;
use Wormhole\Protocols\MonitorServer;
use Wormhole\Protocols\NJINT\Models\ChargeRecord;
use Wormhole\Protocols\NJINT\Models\ChargeRecordFrame;
use Wormhole\Protocols\NJINT\Models\Evse;
use Wormhole\Protocols\NJINT\Models\Port;

/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-12-14
 * Time: 14:25
 */
class ProtocolController
{
    use CommonTools;
    protected $workerId;

    public function __construct($workerId)
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START worker:$workerId");
        $this->workerId = $workerId;

    }
    public function getControl($monitorEvseCode){
        $evse = Port::where('monitor_code',$monitorEvseCode)->first();
        $result =FALSE;
        if(NULL != $evse){
            $evseWorkerId = $evse->worker_id;
            $result = EventsApi::binding($this->workerId,$evseWorkerId);
        }
        return $result;
    }
    /**
     * 签到
     * @param $code string 桩编号
     * @param $evseType int 充电桩类型
     * @param $version int 充电桩软件版本
     * @param $projectType int 充电桩项目类型
     * @param $startTimes int 启动次数
     * @param $uploadModel int 数据上传模式
     * @param $signInInterval int 签到间隔时间
     * @param $runtimeInnerVar int  运行内部变量
     * @param $portQuantity int 充电抢个数
     * @param $heartbeatInterval int 心跳上报周期
     * @param $heartbeatTimeoutTimes int 心跳包检测超时次数
     * @param $chargeLogAmount int 充电记录数量
     * @param $systemTime int 当前充电桩系统时间
     * @param $lastChargeTime int 最近一次充电时间
     * @param $lastStartChargeTime int 最近一次启动时间
     */
    public function signIn($code, $evseType, $version, $projectType, $startTimes, $uploadModel, $signInInterval, $runtimeInnerVar,
                           $portQuantity, $heartbeatInterval, $heartbeatTimeoutTimes, $chargeLogAmount,
                           $systemTime, $lastChargeTime, $lastStartChargeTime
    )
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START");


        $evse = Evse::firstOrCreate(['code' => $code, 'port_quantity' => $portQuantity,]);


        //更新桩体数据
        $evse->heartbeat_period = $heartbeatInterval;
        $evse->online = TRUE;
        $evse->last_update_status_time = Carbon::now();
        $evse->save();


        return TRUE;

    }

    /**
     * 心跳
     * @param string $code 桩编号
     * @param int $sequence 心跳序号
     * @return bool
     */
    public function heartbeat($code, $sequence)
    {
        $evse = Evse::where('code', $code)->first();
        $evse->online = TRUE;
        $evse->last_update_status_time = Carbon::now();
        $evse->save();
        return TRUE;
    }

    /**
     * @param $code
     * @param $portNumber
     * @param $portType
     * @param $workStatus
     * @param $alarmStatus
     * @param $carConnectStatus
     * @param $chargeStartType
     * @param $cardId
     * @param int $startTime 开始时间，时间戳
     * @param int $chargedPower 已充电量,单位：wh
     * @param int $fee 已充金额，单位：分
     * @param int $duration 已充时长，单位：s
     * @param int $power 当前功率，单位：w
     * @param $currentSOC
     * @param int $leftTime 剩余时间，单位分钟
     * @param int $acAVoltage a相电压，单位：mV
     * @param int $acACurrent a相电流，单位：mA
     * @param int $acBVoltage b相电压，单位：mV
     * @param int $acBCurrent b相电流，单位：mA
     * @param int $acCVoltage c相电压，单位：mV
     * @param int $acCCurrent c相电流，单位：mA
     * @param int $dcVoltage  直流电压，单位：mV
     * @param int $dcCurrent  直流电流，单位：mA
     * @param $bmsMode
     * @param int $voltageBMS bms需求电压，单位：mV
     * @param int $currentBMS bms需求电流，单位：mA
     * @return bool
     */
    public function uploadStatus($code, $portNumber, $portType, $workStatus, $alarmStatus, $carConnectStatus,
                                 $chargeStartType,$cardId,
                                 $startTime, $chargedPower, $fee, $duration, $power,
                                 $currentSOC, $leftTime,
                                 $acAVoltage, $acACurrent,
                                 $acBVoltage, $acBCurrent,
                                 $acCVoltage, $acCCurrent,
                                 $dcVoltage, $dcCurrent,
                                 $bmsMode, $voltageBMS, $currentBMS, $meterPowerBefore, $meterPowerNow
    )
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START worker:$this->workerId");
        $evse = Evse::where('code', $code)->firstOrFail();


        $condition = [
            ['code', '=', $code],
            ['port_number', '=', $portNumber]
        ];
        $port = Port::where($condition)->first();
        $currentType = $portType == 1 ? MonitorServer::CURRENT_DC : MonitorServer::CURRENT_AC;
        $isCharging = $workStatus == 1 || $workStatus == 2 ? 1 : 0;
        if (NULL == $port) { //枪口不存在，创建枪；

            //向monitor获取桩编号；
            //$platformName = Config::get("gateway.platform_name");
            //$platformIP = Config::get("gateway.host");
            //$platformPort = Config::get("gateway.port");


            $codes = [0 => [
                'code'=>$code,
                'port'=>$portNumber
            ]
            ];



            $portMonitorCodes = MonitorServer::addEvse( $codes, $currentType);


            if(empty($portMonitorCodes)){ //没有monitor code 或为false 则返回，不继续创建
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 未能获取到monitor code");
                return FALSE;
            }

            $portData = [
                'id' => Uuid::uuid4(),
                'evse_id' => $evse->id,
                'worker_id'=>$this->workerId,
                'code' => $code,
                'port_number' => $portNumber,
                'monitor_code' => $portMonitorCodes[0][0],
                'port_type' => $currentType,
                'heartbeat_period'=>$evse->heartbeat_period,
                //状态更新
                'online' => 1,
                'last_update_status_time' => Carbon::now(),
                'is_car_connected' => $carConnectStatus == 2 ? 1 : 0,
                'is_charging' => $isCharging,
                'warning_status' => $alarmStatus,
                'current_soc' => $currentSOC,



            ];
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " create port :".json_encode($portData));
            //未找到创建
            Port::create($portData);
            return TRUE;

        }

        $orderId = $this->swapCardEvent($port,$workStatus,$chargeStartType,$chargeStartType,$cardId);
        if(!empty($orderId)){
            $port->order_id = $orderId;
            //TODO 更新启动数据
        }

        //组织上报信息



        $needUpdate = TRUE != $port->online
            || $isCharging != $port->is_charging
            || $carConnectStatus != $port->is_car_connected
            || $alarmStatus != $port->warning_status
        ;
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END ,needUpdate : $needUpdate ");
        if(TRUE == $needUpdate){
            $chargeStatus = 1 ==$workStatus || 2 == $workStatus ? MonitorServer::WORK_STATUS_CHARGING:MonitorServer::WORK_STATUS_FREE;
            $chargeStatus = 6 == $workStatus || $alarmStatus  ? MonitorServer::WORK_STATUS_FAILURE:$chargeStatus;

            $isConneted = 2 == $carConnectStatus ? TRUE:FALSE;
            $updateStatus = MonitorServer::updateEvseStatus($port->monitor_code,TRUE,$chargeStatus,$isConneted);
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 更新桩 $code monitorCode:".$port->monitor_code." 状态结果：$updateStatus");
        }


        $port->port_type = $currentType;
        $port->online = TRUE;
        $port->last_update_status_time = Carbon::now();
        $port->is_car_connected = $carConnectStatus == 2 ? 1 : 0;
        $port->is_charging = $isCharging;
        $port->warning_status = $alarmStatus;
        $port->worker_id = $this->workerId;
        $port->start_time = TRUE == $isCharging ? Carbon::createFromTimestamp( $startTime):NULL;
        $port->charged_power = $chargedPower;
        $port->fee = $fee;
        $port->duration = $duration;
        $port->power = $power;

        $port->current_soc = $currentSOC;
        $port->left_time = $leftTime;
        $port->ac_a_voltage = $acAVoltage;
        $port->ac_a_current = $acACurrent;
        $port->ac_b_voltage = $acBVoltage;
        $port->ac_b_current = $acBCurrent;
        $port->ac_c_voltage = $acCVoltage;
        $port->ac_c_current = $acCCurrent;

        $port->dc_voltage = $dcVoltage;
        $port->dc_current = $dcCurrent;

        $port->bms_mode = $bmsMode;
        $port->bms_voltage= $voltageBMS;
        $port->bms_current = $currentBMS;
        $port->last_update_charge_info_time = Carbon::now();

        $port->before_current_ammeter_reading = $meterPowerBefore;
        $port->current_ammeter_reading = $meterPowerNow;


        $port->save();







        return TRUE;
    }

    protected function swapCardEvent($port,$workStatus,$chargeStartType,$monitorCode,$cardId){
        //如果当前不是启动状态，但桩说是启动状态并且是刷卡，则上报刷卡启动充电事件
        $swapped = FALSE == $port->is_charging && (1 == $workStatus || 2 == $workStatus) && 0 == $chargeStartType;

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 是否刷卡： $swapped , 桩状态： $port->is_charging , workStatus:$workStatus, chargeStartType:$chargeStartType  ");
        if(FALSE === $swapped){

            return FALSE;

        }

        $orderId = MonitorServer::swipeCardEvent($cardId,$monitorCode);
        if (FALSE === $orderId){
            return FALSE;
        }
        return $orderId;
    }
    /**
     * 桩方来的启动响应
     * @param $code
     * @param $portNumber
     * @param $result
     * @return bool
     */
    public function startCharge($code, $portNumber, $result)
    {

        $condition = [
            ['code', '=', $code],
            ['port_number', '=', $portNumber]
        ];
        $port = Port::where($condition)->first();

        if(1 != $port->task_status) //启动充电中
        {
            return FALSE;
        }

        // 判断自身状态，调用monitor，启动充电响应接口；
        $sendResult = MonitorServer::startCharge($port->order_id, 0 == $result);

        // 更新装状态；
        $port->is_charging = $result ? TRUE : FALSE;
        $port->last_update_status_time = Carbon::now();
        $port->task_status = 0;
        $port->is_car_connected = TRUE;
        $port->start_time = Carbon::now();

        //todo 清空临时充电数据
        $port->charged_power = 0;
        $port->fee = 0;
        $port->duration = 0;
        $port->power = 0;
        $port->current_soc = 0;
        $port->left_time = 0;
        $port->ac_a_voltage = 0;
        $port->ac_a_current = 0;
        $port->ac_b_voltage = 0;
        $port->ac_b_current = 0;
        $port->ac_c_voltage = 0;
        $port->ac_c_current = 0;
        $port->dc_voltage = 0;
        $port->dc_current = 0;
        $port->bms_mode = 0;
        $port->bms_voltage = 0;
        $port->bms_current = 0;
        $port->last_update_charge_info_time = Carbon::now();


        $port->save();


        $orderMap = ChargeOrderMapping::where([
            ['port_id',$port->id,],
            ['order_id',$port->order_id]
        ])->first();
        if(!is_null($orderMap)){
            $orderMap->is_start_success = TRUE;
            $orderMap->save();
        }



        return TRUE;


    }

    public function evse_control_command($code, $portNumber, $startCmdIndex, $cmdNumber, $cmdResult)
    {

        $result = FALSE;
        //判断是否是停止充电命令。命令标示2的话，为停止充电；
        if (2 == $startCmdIndex) {
            $result = $this->stopCharge($code, $portNumber, $cmdResult);
        }


        //todo 如果不是暂时没有需要处理的地方

        return $result;
    }

    private function stopCharge($code, $portNumber, $stopResult)
    {
        $result = FALSE;
        //TODO 判断自身状态，调用monitor停止充电响应接口
        $condition = [
            ['code', '=', $code],
            ['port_number', '=', $portNumber]
        ];
        $port = Port::where($condition)->first();
        if (null == $port) {
            return FALSE;
        }

        if (0 != $stopResult) {//停止失败
            //调用monitor停止失败接口

            MonitorServer::stopCharge($port->order_id,FALSE);


        }
        //todo 调用停止成功monitor接口
        MonitorServer::stopCharge($port->order_id,TRUE);

        //todo 更新协议数据
        $port->is_charging = FALSE;
        $port->last_update_status_time = Carbon::now();
        $port->task_status = 0;

        //todo 清空临时数据，
        $port->order_id = "";
        $port->evse_order_id = "";

        $port->start_type = 0;
        $port->start_args = 0;
        $port->charge_type = 0;
        $port->charge_args = 0;
        $port->user_balance = 0;
        $port->start_time = NULL;
        $port->charged_power = 0;
        $port->fee = 0;
        $port->duration = 0;
        $port->power = 0;
        $port->current_soc = 0;
        $port->left_time = 0;
        $port->ac_a_voltage = 0;
        $port->ac_a_current = 0;
        $port->ac_b_voltage = 0;
        $port->ac_b_current = 0;
        $port->ac_c_voltage = 0;
        $port->ac_c_current = 0;
        $port->dc_voltage = 0;
        $port->dc_current = 0;
        $port->bms_mode = 0;
        $port->bms_voltage = 0;
        $port->bms_current = 0;
        $port->last_update_charge_info_time = Carbon::now();
        $port->save();

        return $result;
    }


    /**
     * 当充电记录无法找到订单的时候，直接存储，并且不上报monitor
     * 当充电记录找的到订单，但没有存储过的时候，存储，并上报
     *
     * @param $code
     * @param $portNumber
     * @param $cardId
     * @param $startTime
     * @param $stopTime
     * @param $duration
     * @param $startSOC
     * @param $endSoc
     * @param $stopReson
     * @param $power
     * @param $meterBefore
     * @param $meterAfter
     * @param $fee
     * @param $cardBalanceBefore
     * @param $chargeTactics
     * @param $chargeTacticsArgs
     * @param $cardVIN
     * @param $plateNumber
     * @param array $powerOfTimes
     * @param $startType
     * @param $frame
     * @return bool
     */
    public function chargeRecordUpload($code, $portNumber, $cardId,
                                       $startTime, $stopTime, $duration, $startSOC, $endSoc,
                                       $stopReson, $power, $meterBefore, $meterAfter, $fee, $cardBalanceBefore, $chargeTactics, $chargeTacticsArgs,
                                       $cardVIN, $plateNumber, array $powerOfTimes, $startType, $frame)
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");


        $port = Port::where([['code', $code], ['port_number', $portNumber]])->firstOrFail();


        $order =  ChargeOrderMapping::where([
            [
                "code",$code
            ],
            [
                "evse_order_id",$cardId
            ],
            [
                'is_start_success',TRUE
            ]
        ])->first();

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " code : $code , evse_order_id : $cardId ,  order is null : ".is_null($order));

        //数据规范
        $startTime = strtotime($startTime);
        $stopTime = strtotime($stopTime);


        $orderId = is_null($order)? "":$order->order_id;
        $evseOrderId = is_null($order)? $cardId:$order->evse_order_id;




        //保存充电记录帧
        ChargeRecordFrame::create([
            "evse_id"=>$port->evse_id,
            "port_id"=>$port->id,
            "code"=>$port->code,
            "port_number"=>$port->port_number,
            "monitor_code"=>$port->monitor_code,
            "frame" => bin2hex($frame)
        ]);

        //判断充电记录是否已经存在，存在忽略

        $chargeRecord = ChargeRecord::where("evse_order_id",$cardId)->first();

        if(is_null($chargeRecord) || is_null($order)){  //不存在，本地保存，并上报
            $formattedPower = $this->formatPowerOfTime($startTime, $stopTime, $powerOfTimes);
            $data = [
                'evse_id' => $port->evse_id,
                'code' => $code,
                'port_id' => $port->id,
                'port_number' => $port->port_number,
                'port_type' => $port->port_type,
                'monitor_code' => $port->monitor_code,

                'order_id' => $orderId,
                'evse_order_id' => $evseOrderId,

                'start_type' => $port->start_type, //默认取当前的数据
                'start_args' => $port->start_args,
                'charge_type' => $chargeTactics,  //已充电桩的为准
                'charge_args' => $chargeTacticsArgs, //已充电桩的为准

                'start_time' => Carbon::createFromTimestamp($startTime),
                'end_time' => Carbon::createFromTimestamp(  $stopTime),
                'duration' => $duration,
                'start_soc' => $startSOC,
                'end_soc' => $endSoc,
                'stop_reason' => $stopReson,
                'charged_power' => $power,
                'fee' => $fee,
                'times_power' => json_encode( $powerOfTimes),
                'formatted_power'=>json_encode($formattedPower),
                'evse_start_type' => $startType,


                //其他
                'card_id' => $cardId,
                'meter_before' => $meterBefore,
                'meter_after' => $meterAfter,
                'card_balance_before' => $cardBalanceBefore,
                'vin' => $cardVIN,
                'plate_number' => $plateNumber,

            ];


            $chargeRecord = ChargeRecord::create($data);

            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " create chargeRecored:",$data);
            $chargeRecord->push_monitor_result = is_null($order) ? TRUE:FALSE;
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " push_monitor_result is :" .$chargeRecord->push_monitor_result);
        }

        if(FALSE == $chargeRecord->push_monitor_result){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " upload charge record ,orderId:".$order->order_id);
            $formattedPower = json_decode( $chargeRecord->formatted_power,TRUE);

            array_walk($formattedPower,function(&$power){
                $power['power'] =round($power['power']/1000,2);
            });

            $pushResult = MonitorServer::uploadChargeRecord($order->order_id,
                $startTime,$stopTime,
                round($power/1000,2),$formattedPower,FALSE,4,
                $duration,round($fee/100,2));

            if(FALSE == $pushResult){
                return FALSE;
            }
        }


        $chargeRecord->push_monitor_result = TRUE;
        $updateResult = $chargeRecord->save();
        $order->delete();

        return $updateResult;

    }


}