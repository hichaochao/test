<?php
namespace Wormhole\Protocols\HaiGe\Controllers;

use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\HaiGe\EventsApi;
use Wormhole\Protocols\HaiGe\Jobs\CheckHeartbeat;
use Wormhole\Protocols\HaiGe\Models\ChargeOrderMapping;
use Wormhole\Protocols\HaiGe\Protocol;
use Wormhole\Protocols\MonitorServer;
//use Wormhole\Protocols\HaiGe\Models\ChargeRecord;
//use Wormhole\Protocols\HaiGe\Models\ChargeRecordFrame;
use Wormhole\Protocols\HaiGe\Models\Evse;
use Wormhole\Protocols\HaiGe\Models\Port;

use Wormhole\Protocols\HaiGe\Models\ChargeRecord;
use Wormhole\Protocols\HaiGe\Models\ChargeRecordFrame;

use Wormhole\Protocols\CommonTools;

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

    public function __construct($workerId='')
    {
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START worker:$workerId");
        $this->workerId = $workerId;

    }

    public function getControl($monitorEvseCode)
    {
        $evse = Port::where('monitor_code', $monitorEvseCode)->first();
        $result = FALSE;
        if (NULL != $evse) {
            $evseWorkerId = $evse->worker_id;
            $result = EventsApi::binding($this->workerId, $evseWorkerId);
        }
        return $result;
    }


    /**
     * 心跳
     * @param string $code 桩编号
     * @param int $gunNumber 枪口号
     * @return bool
     */
    public function heartbeat($evseCode, $gunNumber, $evseInfo)
    {
        $result = FALSE;
        $condition = [
            ['evse_code', '=', $evseCode],
            ['port_number', '=', $gunNumber]
        ];
        $port = Port::where($condition)->first();
        $evse = Evse::where('code', $evseCode)->first();

        if (empty($port)) {
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 未能获找到桩,创建start");
            //调用 monitor 创建桩接口
            //$evseCodeArr = array(array('evse'=>$evseCode,'port'=>$gunNumber));
            $evseCodeArr = [
                0 =>[
                'code' => $evseCode,
                'port' => $gunNumber
            ]
            ];
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . "创建桩evsecode:$evseCode, port:$gunNumber");
            $portMonitorCodes = MonitorServer::addEvse($evseCodeArr, MonitorServer::CURRENT_AC);
            //$portMonitorCodes = 'hg00001';

            if (empty($portMonitorCodes)) { //没有monitor code 或为false 则返回，不继续创建
                Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 未能获取到monitor code");
                return FALSE;
            }
            $evseId = Uuid::uuid();
            $portData = [
                'id' => $evseId,
                'code' => $evseInfo['evse_code'],
                'name' => $evseInfo['evse_name'],
                'worker_id' => $this->workerId,
                'carriers' => $evseInfo['carriers'],
                'is_register' => $evseInfo['is_register'],
                'response_code' => $evseInfo['responsenCode']
            ];
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " create port :" . json_encode($portData));
            Evse::create($portData);

            //创建枪
            $gun_id = Uuid::uuid();
            $gunNumber = 0;
            $gun = array(
                'evse_id' => $evseId,
                'evse_code' => $evseInfo['evse_code'],
                'port_number' => $gunNumber++,
                'monitor_evse_code' => $portMonitorCodes[0][0],
                'id' => $gun_id,
                'net_status' => true
            );
            Port::create($gun);


            $result = TRUE;
        } else {

            //更新桩数据
            $evse->name = $evseInfo['evse_name'];
            $evse->worker_id = $this->workerId;
            $evse->carriers = $evseInfo['carriers'];
            $evse->is_register = $evseInfo['is_register'];
            $evse->response_code = $evseInfo['responsenCode'];
            $evse->save();

            //更新枪数据
            $port->charge_status = $evseInfo['charge_status'];
            $port->net_status = true;
            $port->left_time = $evseInfo['left_time'] * 60;             //s秒
            $port->charged_power = $evseInfo['charged_power'] * 1000; //wh(瓦)
            $port->charge_money = $evseInfo['charge_money'] * 100;     //分
            $port->voltage = $evseInfo['voltage'] * 1000;                //mv(毫伏)
            $port->electric_current = $evseInfo['electric_current'] * 1000;  //mh(毫安)
            $port->duration = $evseInfo['duration'] * 60;                           //秒
            $port->power = $evseInfo['power'];                               //w
            $port->last_update_status_time = date('Y-m-d H:i:s', time());
            $port->save();

            $car_conn_status = $evseInfo['appointmentStatus']; //枪是否连接
            $chargeStatus = $evseInfo['charge_status']; //充电状态

            //心跳上报充电状态是空闲中,但是表中充电状态为充电中，不要跟新monitor的充电状态
            $status = $port->charge_status;
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 心跳判断充电状态status" . $status. 'chargeStatus:'.$chargeStatus);

            $task_status = $port->task_status;
            $startTime = $port->start_chrge_time; //启动时间
            $endTime = $port->end_chrge_time; //停止时间
            if($task_status != 0){
                if($task_status  == 1 && !empty($startTime) && strtotime($startTime) + 6 > time()){
                    $result = true;
                }elseif($task_status == 2 && !empty($endTime) && strtotime($endTime) + 6 > time()){
                    $result = true;
                }

            }

            //$startTime = $port->start_chrge_time; //启动时间
            //$endTime = $port->end_chrge_time; //启动时间
            //$startTime = empty($startTime) ? time() : $startTime;
//            if(!empty($startTime) || !empty($endTime)) {
//
//                if ($status != $chargeStatus  && strtotime($startTime) + 6 > time()) {
//                    return true;
//                } elseif ($status != $chargeStatus && strtotime($endTime) + 6 > time()) {
//                    return true;
//                }
//            }

            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 心跳，当前数据库中联网状态和充电状态status" . $port->net_status. 'chargeStatus:'.$port->charge_status);
            //检查上报信息是否和当前信息一致
            $needUpdate = TRUE != $port->net_status || $chargeStatus != $port->charge_status;
            if( TRUE == $needUpdate){

                $updateStatus = MonitorServer::updateEvseStatus($port->monitor_evse_code, TRUE, $chargeStatus, $car_conn_status);
                Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 更新枪,充电状态:$chargeStatus,    $evseCode monitorCode:" . $port->monitor_evse_code . " 状态结果：$updateStatus");
            }

            //枪链接状态:$car_conn_status TODO,
            $result = true;
        }

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ ." id : $port->id");
        //增加queue检查心跳；
        $job = (new CheckHeartbeat($port->id))
            ->onQueue(env('APP_KEY'))
            ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
        dispatch($job);

        return $result;
    }

    public function uploadStatus($code, $portNumber, $portType, $workStatus, $currentSOC, $alarmStatus, $carConnectStatus,
                                 $chargeStartType, $cardId
    )
    {
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START worker:$this->workerId");
        $evse = Evse::where('code', $code)->first();
        if (NULL === $evse) {
            return FALSE;
        }

        $condition = [
            ['code', '=', $code],
            ['port_number', '=', $portNumber]
        ];
        $port = Port::where($condition)->first();

        $isCharging = $workStatus == 1 || $workStatus == 2 ? 1 : 0;
        if (NULL == $port) { //枪口不存在，创建枪；

            //向monitor获取桩编号；
            //$platformName = Config::get("gateway.platform_name");
            //$platformIP = Config::get("gateway.local_address");
            //$platformPort = Config::get("gateway.local_port");


            $codes = [0 => $code];

            $currentType = $portType == 1 ? MonitorServer::CURRENT_DC : MonitorServer::CURRENT_AC;

            $portMonitorCodes = MonitorServer::addEvse($codes, $currentType);


            if (empty($portMonitorCodes)) { //没有monitor code 或为false 则返回，不继续创建
                Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 未能获取到monitor code");
                return FALSE;
            }

            $portData = [
                'id' => Uuid::uuid(),
                'evse_id' => $evse->id,
                'worker_id' => $this->workerId,
                'code' => $code,
                'port_number' => $portNumber,
                'monitor_code' => $portMonitorCodes[0][0],

                'heartbeat_period' => $evse->heartbeat_period,
                //状态更新
                'online' => 1,
                'last_update_status_time' => Carbon::now(),
                'is_car_connected' => $carConnectStatus == 2 ? 1 : 0,
                'charge_status' => $isCharging,
                'warning_status' => $alarmStatus,
                'current_soc' => $currentSOC


            ];
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " create port :" . json_encode($portData));
            //未找到创建
            Port::create($portData);
            return TRUE;

        }

        $orderId = $this->swapCardEvent($port, $workStatus, $chargeStartType, $chargeStartType, $cardId);
        if (!empty($orderId)) {
            $port->order_id = $orderId;
            //TODO 更新启动数据
        }

        $port->online = TRUE;
        $port->last_update_status_time = Carbon::now();
        $port->is_car_connected = $carConnectStatus == 2 ? 1 : 0;
        $port->is_charging = $isCharging;
        $port->warning_status = $alarmStatus;
        $port->current_soc = $currentSOC;
        $port->worker_id = $this->workerId;

        $port->save();


        $chargeStatus = 1 == $workStatus || 2 == $workStatus ? MonitorServer::WORK_STATUS_CHARGING : MonitorServer::WORK_STATUS_FREE;
        $chargeStatus = 6 == $workStatus || $alarmStatus ? MonitorServer::WORK_STATUS_FAILURE : $chargeStatus;

        $updateStatus = MonitorServer::updateEvseStatus($port->monitor_code, TRUE, $chargeStatus);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 更新桩 $code monitorCode:" . $port->monitor_code . " 状态结果：$updateStatus");


        return TRUE;
    }

    protected function swapCardEvent($port, $workStatus, $chargeStartType, $monitorCode, $cardId)
    {
        //如果当前不是启动状态，但桩说是启动状态并且是刷卡，则上报刷卡启动充电事件
        $swapped = FALSE == $port->is_charging && (1 == $workStatus || 2 == $workStatus) && 0 == $chargeStartType;

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 桩状态： $port->is_charging , workStatus:$workStatus, chargeStartType:$chargeStartType  ");
        if (FALSE === $swapped) {
            return FALSE;

        }

        $orderId = MonitorServer::swipeCardEvent($cardId, $monitorCode);
        if (FALSE === $orderId) {
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
        //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电成功:evse_code：$code，port_number：$portNumber");
        $condition = [
            ['evse_code', '=', $code],
            ['port_number', '=', $portNumber]
        ];
        $port = Port::where($condition)->first();

        if (1 != $port->task_status) //启动充电中
        {
            return FALSE;
        }
        //sleep(5);
        // 判断自身状态，调用monitor，启动充电响应接口；
        $res = 0 == $result ? TRUE : FALSE;
        $sendResult = MonitorServer::startCharge($port->order_id, $res);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电成功,调用monitor结果:$sendResult");
        // 更新装状态；
        $port->charge_status = 0 == $result ? TRUE : FALSE;
        $port->last_update_status_time = Carbon::now();
        $port->task_status = 0;
        //$port->is_car_connected = TRUE;

        //todo 清空临时充电数据
        $port->power = 0;
        $port->charged_power = 0;
        $port->charge_money = 0;
        $port->duration = 0;
        $port->voltage = 0;
        $port->electric_current = 0;
        $port->left_time = 0;
        $port->warning_status = 0;
        $port->start_soc = 0;
        $port->current_soc = 0;


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

    public function stopCharge($code, $stopResult, $portNumber, $controllerType, $userCard, $chargeTime, $status)
    {
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电收到响应start,响应结果:$stopResult");
        $result = FALSE;
        //TODO 判断自身状态，调用monitor停止充电响应接口
        $condition = [
            ['evse_code', '=', $code],
            ['port_number', '=', $portNumber]
        ];
        $port = Port::where($condition)->first();
        if (null == $port) {
            return FALSE;
        }

        if (0 != $stopResult) {//停止失败
            //调用monitor停止失败接口
            MonitorServer::stopCharge($port->order_id, FALSE);

        }
        //sleep(5);
        //todo 调用停止成功monitor接口
        $res = MonitorServer::stopCharge($port->order_id, TRUE);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电调用monitor结果:$res");
        //todo 更新协议数据
        $port->charge_status = FALSE;
        $port->last_update_status_time = Carbon::now();
        $port->task_status = 0;

        //todo 清空临时数据，
        $port->evse_order_id = 0;
        $port->start_type = 0;
        $port->start_args = 0;
        $port->charge_type = 0;
        $port->charge_args = 0;
        $port->task_status = 0;
        //$port->start_chrge_time = 0;
        //$port->	reservation_start_charge_time = 0;
        $port->power = 0;
        $port->charged_power = 0;
        $port->charge_money = 0;
        $port->duration = 0;
        $port->voltage = 0;
        $port->electric_current = 0;
        $port->left_time = 0;
        $port->port_status = 0;
        $port->emergency_status = 0;
        $port->warning_status = 0;
        $port->start_soc = 0;
        $port->current_soc = 0;
        //$port->last_operator_time = 0;

        $port->save();

        return $result;
    }

    public function chargeRecordUpload($code, $stopResult, $startTimes, $endTimes, $cardNum, $beforePower, $afterPower, $chargePower, $chargeMoney, $beforeBalance,
                                       $afterBalance, $serviceCharge, $offlinePayment, $frame)
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 接收账单Start,结果:$stopResult");

        $portNumber = 0;//TODO 账单上报,没有上报枪口号

        $condition = [
            ['evse_code', '=', $code],
            ['port_number', '=', $portNumber]
        ];
        $port = Port::where($condition)->first();

        $startTimes = $port->start_chrge_time;
        $endTimes = $port->end_chrge_time;

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 查找枪表数据 startTimes：，".$startTimes." endTimes：".$endTimes);
        $startTime = strtotime($startTimes);
        $endTime = strtotime($endTimes);
        //Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " startTime：$startTime， endTime：$endTime");
        //Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " startTimes：$startTimes， endTimes：$endTimes");


        //判断充电记录是否已经存在，存在忽略
        $condition = [
            ['evse_code', '=', $code],
            ['port_number', '=', $portNumber],
            ['start_time', '=', $startTime],
            ['end_time', '=', $endTime]

        ];
        $record = ChargeRecord::where($condition)->count();

        if (0 != $record) {
            //已存在
            return TRUE; //true 表示成功，不需要在上报了
        }

        //获取充电桩／充电口数据
        $port = Port::where([['evse_code', $code], ['port_number', $portNumber]])->firstOrFail();

        //保存充电记录帧
        ChargeRecordFrame::create([
            "id" => Uuid::uuid(),
            "frame" => bin2hex($frame),
            "evse_id" => $port->evse_id,
            "port_id" => $port->id,
            "code" => $code,
            "port_number" => $port->port_number,
            "monitor_code" => $port->monitor_evse_code,
        ]);

        $evse = $port->evse_code;

        //充电时长
        $duration = floor(($endTime - $startTime) / 60);
        //TODO 调用monitor 充电记录 接口
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 接收账单，充电时长,结果:$duration");
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 接收账单，id:".$port->id);
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 接收账单，时间:".Carbon::createFromTimestamp($startTime).'----startTime:'.$startTime.', endTime:'.$endTime);
        // 保存记录（添加是否调用monitor成功）
//        $record = ChargeRecord::firstOrCreate(
//            [
//                'port_id' => $port->gun_id,
//                'start_time' => Carbon::createFromTimestamp($startTime)
//            ]
//        );

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 接收账单，开始时间: $startTime,结束时间: $endTime,充电时长: $duration,电量:$chargePower");
        //查询是否已经存储过
        //$chargeRecord = ChargeRecord::where(['order_id',$order->order_id])->first();.
        $formatePower = json_encode($this->formatPower($startTime, $endTime, $chargePower));
        $updateData = [
            'evse_id' => $port->evse_id,
            'evse_code' => $code,
            'port_id' => $port->id,
            'port_number' => $port->port_number,
            //'port_type' => $port->port_type,
            'monitor_code' => $port->monitor_evse_code,
            //'order_id' => $port->order_id,
            //'charge_user_card' => $port->user_id,

            'start_type' => $port->start_type, //默认取当前的数据
            //'start_args' => $port->start_args,
            'charge_type' => $port->charge_type,  //充电策略
            'charge_args' => $port->charge_args, //充电参数

            'start_time' => Carbon::createFromTimestamp($startTime),
            'end_time' => Carbon::createFromTimestamp($endTime),
            'duration' => $duration,
            //'start_soc' => $startSOC,
            //'end_soc' => $endSoc,
            //'stop_reason' => $stopReson,
            'charged_power' => $chargePower,
            'charged_fee' => $chargeMoney,
            'times_power' =>$formatePower ,
            //'evse_start_type' => $startType,


            //其他
            'card_id' => $cardNum, //用户卡号
            'meter_before' => $beforePower,
            'meter_after' => $afterPower,
            'card_balance_before' => $beforeBalance,
            //'vin' => $cardVIN,
            //'plate_number' => $plateNumber,

        ];

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "账单数据: " . json_encode($updateData));

        $chargeRecord = ChargeRecord::create($updateData);
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . 'chargeRecord:'.$chargeRecord);
        //$record->update($updateData);


//        if (is_object($record)) {
//            return TRUE;
//        }

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "账单数据: order_id" . $port->order_id.
    'startTime:'.$startTime.'endTime:'.$endTime.'chargePower:'.round($chargePower/1000,2).'times_power：'.$formatePower.
    'duration:'.$duration.'chargeMoney:'.$chargeMoney);
        //调用monitor
        $pushResult = MonitorServer::uploadChargeRecord(
            $port->order_id,$startTime, $endTime, round($chargePower/1000,2), json_decode($formatePower, TRUE), false, 4,
            $duration, $chargeMoney);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . '账单调用monitor结果:'.$pushResult);
        if(FALSE == $pushResult){
            return FALSE;
        }

        $orderMap = ChargeOrderMapping::where([
            ['port_id',$port->id,],
            ['order_id',$port->order_id],
            ['is_start_success',TRUE]
        ])->delete();
        //$orderMap->delete();


        return TRUE;
    }



    /**
     * @param $startTime
     * @param $stopTime
     * @param $power integer 电量，单位 wh
     * @return array
     */
//    private function formatPower($startTime,$stopTime,$power){
//        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . '电量分割:startTime:'.$startTime.'stopTime:'.$stopTime.'power:'.$power);
//        //计算开始时间
//        $startMinute = intval(date("i", $startTime)) < 30 ?0:30;
//        $calcStartTime = strtotime( date("Y-m-d H:$startMinute:0",$startTime));
//
//        $duration = $stopTime-$startTime;
//        $perSecondPower = $duration > 0 ? $power/$duration:$power;//每秒钟电量
//        //var_dump("perSecondPower :".$perSecondPower);
//        $perSecondPowerArray = array_fill(0,$duration,$perSecondPower);
//        //var_dump("duration :".$duration);
//
//        $fillNumber = $startTime - $calcStartTime ;
//        $fillArray = array_fill(0,$fillNumber,0);
//        $filledPerSeccondPowerArray = array_merge($fillArray,$perSecondPowerArray);
//
//
//
//        //half hour power and time;
//        $halfHourPower=[];
//        $tmpCalcStartTime = $calcStartTime;
//        $tmpStartTime = $startTime;
//
//        while (count($filledPerSeccondPowerArray) > 0){
//            //var_dump(count($filledPerSeccondPowerArray));
//            $halfPower["time"] = $tmpCalcStartTime;
//
//
//            $tmpCalcStartTime += 30*60;
//            $halfPower["duration"]= $tmpCalcStartTime-$tmpStartTime;
//            $halfPower['power'] = ceil( array_sum(array_splice($filledPerSeccondPowerArray,0,30*60)));
//
//            $tmpStartTime = $tmpCalcStartTime;
//            $halfHourPower[] = $halfPower;
//        }
//        if(array_key_exists(count($halfHourPower)-2,$halfHourPower)){
//
//            $halfHourPower[count($halfHourPower)-1]['duration']= $stopTime - $halfHourPower[count($halfHourPower)-1]['time'] ;
//        }
//
//        return $halfHourPower;
//    }










    /**
     * 格式化点亮数据
     * @param $startTime
     * @param $endTime
     * @param array $powerOfTimes
     * @return array
     */
    //private function formatPowerOfTime($startTime, $endTime, array $powerOfTimes)
    //{

    //组织数据顺序
    //$halfNumbers = intval(($endTime-$startTime)/60);
    //获取开始时间的启动位置；
    //$index = intval(date("H", $startTime))*2 + floor(intval(date("i", $startTime))/30);
    //$spliteArray = array_splice($powerOfTimes,0,$index);

    //$fillArray = array_fill(0,$halfNumbers,0);
    //$powerOfTimes = array_merge($powerOfTimes,$spliteArray,$fillArray);
    //$powerOfTimes = array_splice($powerOfTimes,0,$halfNumbers);


    //计算开始时间
    //$startSecond = intval(date("s", $startTime)) < 30 ?0:30;
    //$calcStartTime = strtotime( date("Y-m-d H:i:$startSecond",$startTime));

    //组织格式化数据
    //$tmpCalcStartTime = $calcStartTime;
    //$formattedPower = [];
//        foreach ($powerOfTimes as $power){
//            $powerFormatted[]=[
//                "time" => $tmpCalcStartTime ,
//                "power" => $power,
//                "duration" => 30
//            ];
//            $tmpCalcStartTime += 30*60;
//        }
//        $formattedPower[0]['duration'] = $startTime-$calcStartTime;
//        $formattedPower[count($formattedPower)]['duration']= $endTime - $tmpCalcStartTime;
//
//        return $formattedPower;

//
//        //原有逻辑作废
//        //计算时间数据
//        $timeArray1 = array_fill(0, 1440, 0);
//        $startMinute = intval(date("H", $startTime)) * 60 + intval(date("i", $startTime));
//        $endMinute = intval(date("H", $endTime)) * 60 + intval(date("i", $endTime));
//        $array2FillNumber = $endMinute - $startMinute == 0 ? 1 : $endMinute - $startMinute;
//        $timeArray2 = array_fill($startMinute, $array2FillNumber, 1);
//
//        $timeArray = array_replace($timeArray1, $timeArray2);
//
//        //power of time [10,34,10];  半小时分割
//        $startMonth = date("m", $startTime);
//        $startDay = date("d", $startTime);
//        $tmpStart = strtotime(date("2016-$startMonth-$startDay 0:00:00"));
//        $powerFormatted = [];
//        for ($i = 0; $i < 48; $i++) {
//            $tmpHour = date("H", $tmpStart);
//            $tmpMinute = date("i", $tmpStart);
//
//            $powerFormatted[] = [
//                "time" => $startMonth * 1000000 + $startDay * 10000 + $tmpHour * 100 + $tmpMinute,
//                "power" => $powerOfTimes[$i],
//                "duration" => array_sum(array_slice($timeArray, $i * 30, 30))
//            ];
//
//            $tmpStart = strtotime(date("2016-$startMonth-$startDay H:i:00", $tmpStart + 30 * 60));
//        }
//
//        return $powerFormatted;


    //}










}