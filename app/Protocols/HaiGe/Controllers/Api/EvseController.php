<?php
namespace Wormhole\Protocols\HaiGe\Controllers\Api;
/**
 * Created by PhpStorm.
 * User: sichao
 * Date: 2016/11/29
 * Time: 15:24
 */
use DB;
use Wormhole\Http\Controllers\Api\BaseController;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\HaiGe\Jobs\CheckStartCharge;
use Wormhole\Protocols\HaiGe\Protocol;
use Wormhole\Protocols\HD10\Jobs\CheckStopCharge;
use Wormhole\Protocols\MonitorServer;
use Wormhole\Protocols\Tools;
use Wormhole\Validators\RealtimeChargeInfoValidator;
use Wormhole\Validators\GetStatusValidator;
use Wormhole\Validators\GetMultiStaticticsPowerValidator;
use Wormhole\Validators\GetChargeHistoryValidator;
use Wormhole\Validators\StopChargeValidator;
use Wormhole\Validators\SetCommodityValidator;
use Wormhole\Protocols\HaiGe\EventsApi;
use Wormhole\Protocols\HaiGe\Models\Port;
use Wormhole\Validators\StartChargeValidator;
use Wormhole\Protocols\HaiGe\Models\Evse;
use Wormhole\Protocols\HaiGe\ServerFrame;

use Wormhole\Protocols\HaiGe\Models\ChargeOrderMapping;
use Illuminate\Support\Facades\Config;

use Wormhole\Protocols\HaiGe\Models\ChargeRecord;

class EvseController extends BaseController
{
    public function startCharge(StartChargeValidator $chargeValidator, $hash)
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "开启充电START");
        $params = $this->request->all();
        $validator = $chargeValidator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . "获取充电数据");
        $orderId = $params['order_id'];
        $userId = '15701695891';//$params['user_id']; TODO userid
        $monitorEvseCode = $params['evse_code'];
        $startType = $params['start_type'];  //启动模式：0、立即；1、定时
       $startArgs = $params['start_args']; //启动模式参数：立即：无效；定时：时间，单位：s
        $chargeTactics = $params['charge_type']; //启动模式：0、立即；1、定时
        $chargeTactiesArgs = $params['charge_args']; //充电参数

        $userBalance = $params['user_balance'];   //用户余额：单位：分

        $controlType = 0;
        $chargeDuration = 0;

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 开启充电数据：monitorEvseCode : $monitorEvseCode,
        startType:$startType, startArgs:$startArgs, chargeTactics:$chargeTactics, chargeTactiesArgs:$chargeTactiesArgs, userBalance:$userBalance");

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 开启充电：monitorEvseCode : $monitorEvseCode");
        //通过monitor找到client_id
        $port = Port::where('monitor_evse_code', $monitorEvseCode)->firstOrFail();//firstOrFail
        $evse = $port->evse;


        $workerId = $port->evse->worker_id;
//var_dump($port);die;
        //存储充电数据
        $port->order_id = $orderId;
        $port->evse_order_id = $userId;
        ///$port->user_id = $userId;
        $port->task_status = 1;
        $port->charge_type = $chargeTactics;
        $port->charge_args = $chargeTactiesArgs;
        $port->last_operator_time = date("Y-m-d H:i:s", time());
        $port->start_chrge_time = date("Y-m-d H:i:s", time());
        $port->save();

        //组装帧，发送
        $register = $evse->is_register;
        $responseCode = $evse->response_code;
        $carriers = $evse->carriers;
        $deviceAddress = $evse->code;
        $number = date('YmdHis', time());

        //添加 order map 数据
        ChargeOrderMapping::create([
            'evse_id'=>$port->evse_id,
            'code'=>$port->evse_code,
            'monitor_code'=>$port->monitor_evse_code,
            'order_id'=>$port->order_id,
            'evse_order_id'=>$port->evse_order_id,
            //'is_billing'=>$port->is_billing,
            'start_type'=>$port->start_type,
            'start_args'=>$port->start_args,
            'charge_type'=>$port->charge_type,
            'charge_args'=>$port->charge_args,
            'port_id' =>$port->id,

            //'user_balance'=>$port->user_balance,

        ]);

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电发送 start");
        //组装启动重点帧，发送
        $serverFrame = new ServerFrame();
        //启动模式充满和定时
        if($chargeTactics == 0){
            $controlType = 0x04;
        }elseif($chargeTactics == 1){
            $controlType = 0x03;
        }
        //充电时长
        if(!empty($chargeTactiesArgs)){
            $chargeDuration = $chargeTactiesArgs / 60;
        }

        $frame = $serverFrame->StartCharge($register, $responseCode, $carriers, $deviceAddress, $number, $controlType, $chargeDuration);
        $fram_s= Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电发送桩编号: $deviceAddress, fram_s:$fram_s");



        $times = 0;
        while($times++ < 3){

            //0.2秒发送一次请求 发三次
            for ($i=0;$i<=3;$i++){
                $sendResult = EventsApi::sendMsg($workerId, $frame);
                usleep(200000);
                Log::debug( __CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 第 $i 次 END result:$sendResult");
            }
            //停止2秒后，查看状态是否改变
            sleep(2);
            $condition = [
                ['evse_code', '=', $deviceAddress],
                ['port_number', '=', 0]
            ];
            $port = Port::where($condition)->first();
            $status = $port->task_status;
            Log::debug( __CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 启动充电状态 result:".$status);
            if(0 == $status) {
                break;
            }
        }


        //添加检查任务
        $job = (new CheckStartCharge($port->id,$orderId,1))
            ->onQueue(env('APP_KEY'))
            ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
        dispatch($job);

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电发生结果: $sendResult");
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array(
            [
                'status_code' => 201,
                'message' => "command send sucesss"
            ]
        );
    }




    public function stopCharge(StopChargeValidator $validator, $hash){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电START");
        $params = $this->request->all();
        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        //查找充电中的枪数据
        $orderId = $params['order_id'];
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " order:$orderId");
        $port = Port::where([['order_id', $orderId],['charge_status', 1]])->first();
        if(empty($port)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电，order_id出错或者不在充电中order_id：$orderId");
            return false;
        }
        $port->task_status = 2;
        $port->end_chrge_time = date("Y-m-d H:i:s", time());
        $port->save();
        //取出worker_id
        $evse = Evse::where('id', $port->evse_id)->firstOrFail();
        $workerId = $evse->worker_id;

        //组装帧，发送
        $register = $evse->is_register;
        $responseCode = $evse->response_code;
        $carriers = $evse->carriers;
        $deviceAddress = $evse->code;
        $number = date('YmdHis', time());
        //echo $register.'--'.$responseCode.'--'.$carriers.'--'.$deviceAddress.'--'.$number;die;
        $serverFrame = new ServerFrame();
        $frame = $serverFrame->stopCharge($register, $responseCode, $carriers, $deviceAddress, $number);

        $times = 0;
        while($times++ < 3){

            for ($i=0;$i<=3;$i++){
                $sendResult = EventsApi::sendMsg($workerId, $frame);;
                usleep(200000);
                Log::debug( __CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 第 $i 次 END result:$sendResult");
            }
            sleep(2);
            $condition = [
                ['evse_code', '=', $deviceAddress],
                ['port_number', '=', 0]  //TODO暂时是0枪口
            ];
            $port = Port::where($condition)->first();
            $status = $port->task_status;
            Log::debug( __CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " 停止充电状态 result:".$status);
            if(0 == $status) {
                break;
            }

        }


        //Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电下发结果:$sendResult");
        if (TRUE == $sendResult) {
            //添加检查任务
            $job = (new CheckStopCharge($port->id,$orderId,2))
                ->onQueue(env('APP_KEY'))
                ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
            dispatch($job);
            $message = [
                'status_code' => 201,
                'message' => "command send success"
            ];
        } else {
            $message = [
                'status_code' => 500,
                'message' => "command send failed"
            ];
        }


        return $this->response->array($message);


    }



    /**
     * 实时充电数据
     * @api
     * @see ApiInterface::realtimeChargeInfo()
     * @param RealtimeChargeInfoValidator $validator
     * @param $hash
     * @return mixed
     */
    public function realtimeChargeInfo(RealtimeChargeInfoValidator $validator, $hash)
    {//借用stop charge，只需要订单号
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $orderId = $params['order_id'];
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 实时充电记录order_id:$orderId");
        $port = Port::where([['order_id', $orderId], ['charge_status', 1]])->firstOrFail();

        //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 实时充电记录".$port->order_id);

        //todo 组织返回数据即可
        $startTime = $port->start_chrge_time;
        $startTime = strtotime($startTime);
        $data = [
            'order_id' => $port->order_id,
            'evse_code' => $port->monitor_evse_code,
            'start_time' => $startTime,      //开始充电时间
            'duration' => round($port->duration / 60, 2),              //充电时长 分
            'power' => round($port->power/1000, 2),                   //功率 kw
            'charged_power' => round($port->charged_power / 1000, 2),  //电量 kwh(度)
            'fee' => round($port->charge_money / 100, 2),          //充电桩实时金额 元
            'port_type' => 1,
            'charge_volt_a' => 0,
            'charge_curt_a' => 0,
            'charge_volt_b' => 0,
            'charge_curt_b' => 0,
            'charge_volt_c' => 0,
            'charge_curt_c' => 0,
            'charge_mode' => 0,

            'require_volt' => $port->voltage / 1000,                     //充电桩实时电压 V(伏)
            'require_curt' => $port->electric_current / 1000, //充电桩实时电流 A(安)
            'drt_charge_volt' => 0,
            'drt_charge_curt' => 0
            //'left_time' => $port->left_time / 60,                 //剩余时间 分

        ];
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>$data

        ]);

    }


    /**
     * 获取充电桩状态
     * @param RealtimeChargeInfoValidator $validator
     * @param $hash
     * @return array
     */
    public function getStatus(GetStatusValidator $validator, $hash){


        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $evseStatuInfo = array();

        //$port = Port::where('monitor_evse_code', $params['monitor_code'])->firstOrFail();
        $monitor_code = $params['monitor_code'];
        $start_time = $params['start_time']; //开始时间
        $date_type = $params['date_type']; //时间类型
        $start_time = date('Y-m-d H:i:m', $start_time);

        
        switch ($date_type) {

            case 'year' :
                $evseStatus = ChargeRecord::where([['start_time', '>', $start_time],['monitor_code', $monitor_code]])
                    ->selectRaw("DATE_FORMAT( `start_time`, '%Y' ) AS startTime, count(*) as amount")
                    ->groupBy('startTime')
                    ->get();
                break;
            case 'month' :
                $evseStatus = ChargeRecord::where([['start_time', '>', $start_time],['monitor_code', $monitor_code]])
                    ->selectRaw("DATE_FORMAT( `start_time`, '%Y-%m-01' ) AS startTime, count(*) as amount")
                    ->groupBy('startTime')
                    ->get();
                break;
            case 'day' :
                $evseStatus = ChargeRecord::where([['start_time', '>', $start_time],['monitor_code', $monitor_code]])
                    ->selectRaw("DATE_FORMAT( `start_time`, '%Y-%m-%d' ) AS startTime, count(*) as amount")
                    ->groupBy('startTime')
                    ->get();
                break;
            case 'hour' :
                $evseStatus = ChargeRecord::where([['start_time', '>', $start_time],['monitor_code', $monitor_code]])
                    ->selectRaw("DATE_FORMAT(  `start_time`, '%Y-%m-%d %H:00:00' ) AS startTime, count(*) as amount")
                    ->groupBy('startTime')
                    ->get();
                break;
            case 'week' :
                $evseStatus = ChargeRecord::where([['start_time', '>', $start_time],['monitor_code', $monitor_code]])
                    ->selectRaw("DATE_FORMAT( `start_time`, '%Y-%u' ) AS startTime, count(*) as amount")
                    ->groupBy('startTime')
                    ->get();
                break;

        }
        //var_dump($evseStatus);die;
        //DATE_FORMAT
        //当前用户充电数据
        $port = Port::where([['monitor_evse_code','=', $monitor_code],['charge_status', 1]])->firstOrFail();
        if(empty($port)){
            return $this->response->array([
                'status_code'=>500,
                'message'=>'未找到当前用户充电数据',
                'data'=>[]

            ]);
        }
        //桩自身信息
        $evseStatuInfo['status']['monitor_code'] = $monitor_code;
        $evseStatuInfo['status']['code'] = $port->evse_code;
        $evseStatuInfo['protocol_name'] = Protocol::NAME;
        $evseStatuInfo['status']['version'] = 0;
        $evseStatuInfo['status']['port_number'] = $port->port_number;
        $evseStatuInfo['status']['port_type'] = MonitorServer::CURRENT_AC;

        //状态数据
        $evseStatuInfo['status']['online'] = $port->net_status;
        $evseStatuInfo['status']['is_car_connected'] = 0;  //车辆是否连接
        $evseStatuInfo['status']['is_charging'] = $port->charge_status;
        $evseStatuInfo['status']['warning_status'] = $port->warning_status;
        $evseStatuInfo['status']['last_update_status_time'] = $port->last_update_status_time;
        $evseStatuInfo['status']['current_soc'] = 0;
        $evseStatuInfo['status']['task_status'] = $port->task_status;


        //桩充电启动信息
        $evseStatuInfo['status']['order_id'] = $port->order_id;
        $evseStatuInfo['status']['evse_order_id'] = $port->evse_order_id;
        $evseStatuInfo['status']['start_type'] = $port->start_type;
        $evseStatuInfo['status']['start_args'] = $port->start_args;
        $evseStatuInfo['status']['charge_type'] = $port->charge_type;
        $evseStatuInfo['status']['charge_args'] = $port->charge_args;
        $evseStatuInfo['status']['user_balance'] = 0; //用户余额


        //充电数据
        $evseStatuInfo['status']['start_time'] = $port->start_chrge_time;
        $evseStatuInfo['status']['charged_power'] = $port->charged_power;
        $evseStatuInfo['status']['fee'] = $port->charge_money;
        $evseStatuInfo['status']['duration'] = $port->duration;
        $evseStatuInfo['status']['power'] = $port->power;
        $evseStatuInfo['status']['ac_a_voltage'] = $port->voltage;
        $evseStatuInfo['status']['ac_a_current'] = $port->electric_current;
        $evseStatuInfo['status']['ac_b_voltage'] = 0;
        $evseStatuInfo['status']['ac_b_current'] = 0;
        $evseStatuInfo['status']['ac_c_voltage'] = 0;
        $evseStatuInfo['status']['ac_c_current'] = 0;
        $evseStatuInfo['status']['dc_voltage'] = 0;
        $evseStatuInfo['status']['dc_current'] = 0;
        $evseStatuInfo['status']['last_update_charge_info_time'] = $port->last_update_status_time;
        $evseStatuInfo['status']['left_time'] = $port->left_time;
        $evseStatuInfo['status']['before_current_ammeter_reading'] = 0; //TODO 充电前电表读数
        $evseStatuInfo['status']['current_ammeter_reading'] = 0; //TODO 当前前电表读数


            //bsm 信息
        $evseStatuInfo['status']['bms_mode'] = 0;
        $evseStatuInfo['status']['bms_voltage'] = 0;
        $evseStatuInfo['status']['bms_current'] = 0;



        //历史充电充电用户
        $history_statistics = ChargeRecord::where('monitor_code', '=', $monitor_code)
            ->selectRaw("sum(duration) as duration, sum(charged_power) as charged_power, count(*) as nummber")
            ->get();
        if(empty($history_statistics)){
            return $this->response->array([
                'status_code'=>500,
                'message'=>'未找到历史充电充电用户',
                'data'=>[]

            ]);
        }
        $evseStatuInfo['total_charge_info']['total_duration'] = $history_statistics[0]->duration;
        $evseStatuInfo['total_charge_info']['total_power'] = $history_statistics[0]->charged_power;
        $evseStatuInfo['total_charge_info']['total_charge_times'] = $history_statistics[0]->nummber;


        //缺少的时间补上 'charge_times'=>'0', 'create_time'=>$time
        $charge_time_repair = [];
        for($i=0;$i<count($evseStatus);$i++){
            $charge_time_repair[$i]['charge_times'] = $evseStatus[$i]->amount;
            $charge_time_repair[$i]['create_time'] = $evseStatus[$i]->startTime;
        }
        $start_time = date('Y-m-d 00:00:00',strtotime("-30 days", time()));
        $charge_times_info = $this->fill_charge_times_data($charge_time_repair, $start_time);

        //充电时间，用户个数
        for($i=0;$i<count($charge_times_info);$i++){

            $evseStatuInfo['charge_times_info'][$i]['create_time'] = $charge_times_info[$i]['charge_times'];//$evseStatus[$i]->startTime;
            $evseStatuInfo['charge_times_info'][$i]['charge_times'] = $charge_times_info[$i]['create_time'];//$evseStatus[$i]->amount;

        }


        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>$evseStatuInfo

        ]);


    }




    /**
     * 获取多个桩的功率数据
     */
    public function getMultiStaticticsPower(GetMultiStaticticsPowerValidator $validator, $hash){

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $powers = 0;

        $info = Evse::whereIn('monitor_evse_code', $params['monitor_codes'])
            ->selectRaw("sum(power) as power")
            ->first();
        if(!empty($info)){
            $powers = $info['power'];
        }

        
        $data = array('power'=>$powers);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>$data

        ]);

    }



    /**
     * 获取历史纪录
     */
    public function getChargeHistory(GetChargeHistoryValidator $validator, $hash){

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        //按条件查找需要数据
        $sort_by = empty($params['sort_by']) ? 'end_time' : $params['sort_by'];
        $sort_type = empty($params['sort_type']) ? 'desc' : $params['sort_type'];
        $page_now = empty($params['page_now']) ? '1' : $params['page_now'];
        $limit = empty($params['limit']) ? '10' : $params['limit'];

        //按条件查找需要数据
        $historyInfo = DB::table('haige_charge_records')
            ->where('monitor_code', $params['monitor_code'])
            ->orderBy($sort_by, $sort_type)
            ->skip($page_now)
            ->take($limit)
            ->get();

        //如果没找到数据,返回空
        if(empty($historyInfo)){
            return $this->response->array([
                'status_code'=>500,
                'message'=>'未获取历史纪录',
                'data'=>[]

            ]);
        }

        $historyData = array();
        $total_num = count($historyInfo);
        for($i=0;$i<$total_num;$i++){

            $historyData[$i]['id'] = $historyInfo[$i]->charge_records_id;
            $historyData[$i]['monitor_code'] = $historyInfo[$i]->monitor_code;
            $historyData[$i]['code'] = $historyInfo[$i]->evse_code;
            $historyData[$i]['port_number'] = $historyInfo[$i]->port_number;
            $historyData[$i]['port_type'] = $historyInfo[$i]->port_type;
            $historyData[$i]['order_id'] = 0;//$historyInfo[$i]->;
            $historyData[$i]['evse_order_id'] = 0;//$historyInfo[$i]->;
            $historyData[$i]['start_type'] = $historyInfo[$i]->start_type;
            $historyData[$i]['start_args'] = 0;//$historyInfo[$i]->;
            $historyData[$i]['charge_type'] = $historyInfo[$i]->charge_type;
            $historyData[$i]['charge_args'] = $historyInfo[$i]->charge_args;
            $historyData[$i]['start_time'] = $historyInfo[$i]->start_time;
            $historyData[$i]['end_time'] = $historyInfo[$i]->end_time;
            $historyData[$i]['duration'] = $historyInfo[$i]->duration;
            $historyData[$i]['charged_power'] = $historyInfo[$i]->charged_power;
            $historyData[$i]['fee'] = $historyInfo[$i]->charged_fee;
            $historyData[$i]['stop_reason'] = $historyInfo[$i]->stop_reason;
            //$historyData[$i]['push_monitor_result'] = $historyInfo[$i]->;
            $historyData[$i]['start_soc'] = $historyInfo[$i]->start_soc;
            $historyData[$i]['end_soc'] = $historyInfo[$i]->end_soc;
            $historyData[$i]['meter_before'] = $historyInfo[$i]->meter_before;
            $historyData[$i]['meter_after'] = $historyInfo[$i]->meter_after;
            $historyData[$i]['card_balance_before'] = 0;
            $historyData[$i]['plate_number'] = 0;
            $historyData[$i]['vin'] = 0;


        }


        $data = array('total_num'=>$total_num, 'list'=>$historyData);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>$data

        ]);



    }


    /**
     * 费率设置
     */
    public function setCommodity(SetCommodityValidator $validator, $hash){

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $commodity = $params['commodity'];
        $service = $params['service'];
        //00点-24点 每半小时分割 48个时间段
        $commodity_info = $this->commodity($commodity, 1);
        $service_info = $this->commodity($service, 2);

        $commodity_data = [];
        //将费率和服务费价格相加
        foreach($commodity_info as $k=>$v){

            $commodity_data[$k] = $v+$service_info[$k];

        }

       list($last_key, $last) = (end($commodity_data) ? each($commodity_data) : each($commodity_data)); //获取最后一个key

        //相同价格，时间段合并
        $commodity_merge = array();
        $base = 1;
        $val = 0;
        $key = 0;
        foreach($commodity_data as $k=>$v){

            if($base != 1){

                if($v != $val){
                    $commodity_merge[$key.'-'.$k] = $val;
                }elseif($k == $last_key){
                    $commodity_merge[$key.'-'.$k] = $val;
                }else{
                    continue;
                }

            }
            $base = 0;
            $val = $v;
            $key = $k;

        }


var_dump($commodity_merge);

        //$serverFrame = new ServerFrame();


    }


    /**
     * 费率
     * @param $commodityArr
     */
    public function commodity($commodityArr, $type){

        $year = date('Y', time());
        $month = date('m', time());
        $day = date('d', time());

        $after_time = $start_time = mktime(23,30,00,$month,$day,$year);

        $commodity_price = array();
        $befor_time = '';
        $price = $type == 1 ? 'commodity_price' : 'service_price';
        foreach ($commodityArr as $val){
            $start_time = mktime($val['start_hour'],$val['start_minute'],00,$month,$day,$year);
            $end_time = mktime($val['end_hour'],$val['end_minute'],00,$month,$day,$year);
            $start_time_big = $start_time;
            if(!empty($befor_time) && $start_time_big > $befor_time){
                while ($befor_time<=$start_time_big){
                    $time = date('H',$befor_time).date('i',$befor_time);
                    $commodity_price[$time] = 0;
                    //echo date('H:i',$start_time).'--------'.$start_time."<br/>";
                    $befor_time += 1800;
                }
            }
            while ($start_time<=$end_time){
                $time = date('H',$start_time).date('i',$start_time);
                if($start_time > $after_time){   //如果超过当天
                    $commodity_price['2400'] = $val[$price];
                }else{
                    $commodity_price[$time] = $val[$price];
                }
                $start_time += 1800;

            }
            $befor_time = $end_time;
        }

        return $commodity_price;



    }



    private function fill_charge_times_data($charge_times_info, $start_time){
        $time_list = array_column($charge_times_info, 'create_time');
        $start = strtotime($start_time);
        $now = time();
        while($start < $now){
            $time = date('Y-m-d',$start);

            if(in_array($time, $time_list)){
                // do nothing
            }else{
                array_push($charge_times_info, array('charge_times'=>'0', 'create_time'=>$time));
            }

            $start = strtotime("+1 days", $start);
        }

        // 准备要排序的数组
        foreach ($charge_times_info as $k => $v) {
            $sort[] = $v['create_time'];
        }
        array_multisort($sort, SORT_ASC, $charge_times_info);

        return $charge_times_info;
    }



    
    public function test(){

        var_dump($_SERVER);
        //echo $_SERVER['SERVER_ADDR'];die;
//echo GetHostByName($_SERVER['SERVER_NAME']);
//        config(['gateway.gateway.count' => 20]);
//        echo $protocol = Config::get("gateway.gateway.count");
        //$aa = env('CONNECTION_NUMBER');
        //var_dump($aa);
        //$aa = licence('CONNECTION_NUMBER');
        //$evse = Evse::count(1);
        //gitignore('CONNECTION_NUMBER');
        //env();
        //var_dump($aa);
    }












}