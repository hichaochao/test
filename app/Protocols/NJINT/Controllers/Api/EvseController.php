<?php
namespace Wormhole\Protocols\NJINT\Controllers\Api;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\ApiInterface;
use Wormhole\Protocols\MonitorServer;
use Wormhole\Protocols\NJINT\EventsApi;
use Wormhole\Protocols\NJINT\Models\ChargeOrderMapping;
use Wormhole\Protocols\NJINT\Models\ChargeRecord;
use Wormhole\Protocols\NJINT\Models\Evse;
use Wormhole\Protocols\NJINT\Models\Port;
use Wormhole\Protocols\NJINT\Protocol;
use Wormhole\Protocols\NJINT\Protocol\ServerFrame;
use Wormhole\Protocols\Tools;
use Wormhole\Validators\RealtimeChargeInfoValidator;
use Wormhole\Validators\StartChargeValidator;
use Wormhole\Validators\StopChargeValidator;
use Wormhole\Validators\SetCommodityValidator;

use Wormhole\Validators\GetStatusValidator;
use Wormhole\Validators\GetMultiStaticticsPowerValidator;
use Wormhole\Validators\GetChargeHistoryValidator;

use Wormhole\Protocols\NJINT\Protocol\Server\Command\Commodity as SeverCommodity;

use Illuminate\Support\Facades\Config;

/**
 * Created by PhpStorm.
 * User: lingfengchen
 * Date: 2017/1/19
 * Time: 下午4:13
 */
class EvseController extends BaseController implements ApiInterface
{


    /**
     * 启动充电
     * @see ApiInterface::startCharge()
     * @api
     */
    public function startCharge(StartChargeValidator $chargeValidator, $hash)
    {

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $chargeValidator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $orderId = $params['order_id'];
        $monitorEvseCode = $params['evse_code'];
        $startType = $params['start_type'];
        $startArgs = $params['start_args'];
        $chargeType = $params['charge_type'];
        $chargeArgs = $params['charge_args'];

        $userBalance = $params['user_balance'];


        $port = Port::where('monitor_code', $monitorEvseCode)->firstOrFail();

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 查询到port信息");

        $evseCode = $port->code;
        $workerId = $port->worker_id;

        //save start charge data
        $port->order_id = $orderId;
        $port->start_type = $startType;
        $port->start_args = $startArgs;
        $port->charge_type = $chargeType;
        $port->charge_args = $chargeArgs;
        $port->user_balance = $userBalance;
        $port->start_time = Carbon::now();
        $port->evse_order_id = substr( str_replace("-","",  Uuid::uuid4()->toString()),0,-1);
        $port->sequence = ($port->sequence + 1) % 255;
        $port->task_status = 1;
        $port->save();

        //添加 order map 数据
        ChargeOrderMapping::create([
            'evse_id'=>$port->evse->id,
            'port_id'=>$port->id,
            'code'=>$port->code,
            'monitor_code'=>$port->monitor_code,
            'order_id'=>$port->order_id,
            'evse_order_id'=>$port->evse_order_id,
            'start_type'=>$port->start_type,
            'start_args'=>$port->start_args,
            'charge_type'=>$port->charge_type,
            'charge_args'=>$port->charge_args,
            'user_balance'=>$port->user_balance,

        ]);


        $serverFrame = new ServerFrame();
        $frame = $serverFrame->startCharge($port->sequence, $port->port_number, $startType, $chargeType, $chargeArgs, $port->evse_order_id);

        $sendResult = EventsApi::sendMsg($workerId, $frame);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array(
            [
                'status_code' => 201,
                'message' => "command send sucesss"
            ]
        );
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
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $orderId = $params['order_id'];

        $port = Port::where([['order_id', $orderId], ['is_charging', 1]])->firstOrFail();

        //todo 组织返回数据即可
        $data = [
            'order_id'      => $port->order_id,
            'evse_code'     => $port->monitor_code,
            'start_time'    => strtotime($port->start_time),
            'duration'      => $port->duration,
            'power'         => round( $port->power/1000,2),                           //充电功率：kw
            'charged_power' => round($port->charged_power/1000,2),           //已充电量：hwh
            'fee'           => round($port->fee/100,2),                      //已充费用,单位：元

            'port_type' => 1 == $port->port_type ? MonitorServer::CURRENT_DC:MonitorServer::CURRENT_AC,

            'charge_volt_a' => round($port->ac_a_voltage/1000,2),            //交流A相充电电压，单位：V
            'charge_curt_a' => round($port->ac_a_current/1000,2),             //交流A相充电电流，单位：A
            'charge_volt_b' => round($port->ac_b_voltage/1000,2),            //交流B相充电电压，单位：V
            'charge_curt_b' => round($port->ac_b_current/1000,2),            //交流B相充电电流，单位：A
            'charge_volt_c' => round($port->ac_c_voltage/1000,2),             //交流C相充电电压，单位：V
            'charge_curt_c' => round($port->ac_c_current/1000,2),          //交流C相充电电流，单位：A

            'charge_mode' => $port->bms_mode,                 //设置BMS充电模式
            'require_volt' => round($port->bms_voltage/1000,2),               //BMS需求电压，单位：V
            'require_curt' => round($port->bms_current/1000,2),               //BMS需求电流，单位：A

            'drt_charge_volt' => round($port->dc_voltage/1000,2),             //直流充电电压 ，单位：V
            'drt_charge_curt' => round($port->dc_current/1000,2),             //直流充电电流，单位：A
        ];
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array([
                'status_code'=>200,
                'message'=>'',
                'data'=>$data

            ]);

    }

    /**
     * 停止充电
     * @api
     * @see ApiInterface::stopCharge()
     * @param StopChargeValidator $validator
     * @param $hash
     * @return mixed
     */
    public function stopCharge(StopChargeValidator $validator, $hash)
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }


        $orderId = $params['order_id'];
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " order:$orderId");
        $port = Port::where([['order_id','=', $orderId],['is_charging', 1]])->firstOrFail();
        //var_dump($port);
        $port->sequence = ($port->sequence + 1) % 255;
        $port->task_status = 2;
        $port->save();

        $serverFrame = new ServerFrame();
        $frame = $serverFrame->stopCharge($port->sequence, $port->port_number);

        $sendResult = EventsApi::sendMsg($port->worker_id, $frame);

        $message = [];
        if (TRUE == $sendResult) {
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
            default :
                $evseStatus = [];
        }

        if(empty($evseStatus)){
            return $this->response->array([
                'status_code'=>500,
                'message'=>'未找到充电时间和充电次数',
                'data'=>[]

            ]);
        }



        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 查询桩状态信息get_status: monitor_code:$monitor_code");
        //DATE_FORMAT
        //当前用户充电数据
        $port = Port::where([['monitor_code', $monitor_code]])->first(); //,['is_charging', 1]


        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 查询桩状态信息");


        if(empty($port)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 查询桩状态信息为空");
            return $this->response->array([
                'status_code'=>500,
                'message'=>'未找到当前用户充电数据',
                'data'=>[]

            ]);
        }
        //桩自身信息
        $evseStatuInfo['status']['monitor_code'] = $port->monitor_code;
        $evseStatuInfo['status']['code'] = $port->code;
        $evseStatuInfo['status']['protocol_name'] =Protocol::NAME;
        $evseStatuInfo['status']['version'] = 0;
        $evseStatuInfo['status']['port_number'] = $port->port_number;
        $evseStatuInfo['status']['port_type'] = $port->port_type;

        //状态数据
        $evseStatuInfo['status']['online'] = $port->online;
        $evseStatuInfo['status']['is_car_connected'] = $port->is_car_connected;  //车辆是否连接
        $evseStatuInfo['status']['is_charging'] = $port->is_charging;
        $evseStatuInfo['status']['warning_status'] = $port->warning_status;
        $evseStatuInfo['status']['last_update_status_time'] = $port->last_update_status_time;
        $evseStatuInfo['status']['current_soc'] = $port->current_soc;
        $evseStatuInfo['status']['initial_soc'] = $port->initial_soc;
        $evseStatuInfo['status']['task_status'] = $port->task_status;

        //桩充电启动信息
        $evseStatuInfo['status']['order_id'] = $port->order_id;
        $evseStatuInfo['status']['evse_order_id'] = $port->evse_order_id;
        $evseStatuInfo['status']['start_type'] = $port->start_type;
        $evseStatuInfo['status']['start_args'] = $port->start_args;
        $evseStatuInfo['status']['charge_type'] = $port->charge_type;
        $evseStatuInfo['status']['charge_args'] = $port->charge_args;
        $evseStatuInfo['status']['user_balance'] = $port->user_balance / 100; //用户余额

        //充电数据
        $evseStatuInfo['status']['start_time'] = $port->start_time;
        $evseStatuInfo['status']['charged_power'] = $port->charged_power / 1000;
        $evseStatuInfo['status']['fee'] = $port->fee / 100;
        $evseStatuInfo['status']['duration'] = $port->duration;
        $evseStatuInfo['status']['power'] = $port->power;
        $evseStatuInfo['status']['ac_a_voltage'] = $port->ac_a_voltage / 1000;
        $evseStatuInfo['status']['ac_a_current'] = $port->ac_a_current;
        $evseStatuInfo['status']['ac_b_voltage'] = $port->ac_b_voltage / 1000;
        $evseStatuInfo['status']['ac_b_current'] = $port->ac_b_current;
        $evseStatuInfo['status']['ac_c_voltage'] = $port->ac_c_voltage / 1000;
        $evseStatuInfo['status']['ac_c_current'] = $port->ac_c_current;
        $evseStatuInfo['status']['dc_voltage'] = $port->dc_voltage / 1000;
        $evseStatuInfo['status']['dc_current'] = $port->dc_current;
        $evseStatuInfo['status']['last_update_charge_info_time'] = $port->last_update_status_time;
        $evseStatuInfo['status']['left_time'] = $port->left_time;
        $evseStatuInfo['status']['before_current_ammeter_reading'] = $port->before_current_ammeter_reading /1000; //TODO 充电前电表读数
        $evseStatuInfo['status']['current_ammeter_reading'] = $port->current_ammeter_reading / 1000; //TODO 当前前电表读数

        //bsm 信息
        $evseStatuInfo['status']['bms_mode'] = $port->bms_mode;
        $evseStatuInfo['status']['bms_voltage'] = $port->bms_voltage / 100;
        $evseStatuInfo['status']['bms_current'] = $port->bms_current;

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电压 ".$port->dc_voltage / 100);
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 查询历史充电充电用户start");
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

        $info = json_encode($evseStatus);

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

            $evseStatuInfo['charge_times_info'][$i]['create_time'] = $charge_times_info[$i]['create_time'];//$evseStatus[$i]->startTime;
            $evseStatuInfo['charge_times_info'][$i]['charge_times'] = $charge_times_info[$i]['charge_times'];//$evseStatus[$i]->amount;

        }
        $data = json_encode($evseStatuInfo);

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

        $info = Evse::whereIn('monitor_code', $params['monitor_codes'])
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

        $sort_by = empty($params['sort_by']) ? 'end_time' : $params['sort_by'];
        $sort_type = empty($params['sort_type']) ? 'desc' : $params['sort_type'];
        $page_now = empty($params['page_now']) ? '1' : $params['page_now'];
        $limit = empty($params['limit']) ? '10' : $params['limit'];

        //按条件查找需要数据
        $historyInfo = DB::table('njint_charge_records')
            ->where('monitor_code', $params['monitor_code'])
            ->orderBy($sort_by, $sort_type)
            ->skip($page_now)
            ->take($limit)
            ->get();

        //如果没找到数据,返回空
        if(empty($historyInfo)){
            return $this->response->array([
                'status_code'=>500,
                'message'=>'',
                'data'=>[]

            ]);
        }

        $historyData = array();
        $total_num = count($historyInfo);
        for($i=0;$i<$total_num;$i++){

            $historyData[$i]['id'] = $historyInfo[$i]->id;
            $historyData[$i]['monitor_code'] = $historyInfo[$i]->monitor_code;
            $historyData[$i]['code'] = $historyInfo[$i]->code;
            $historyData[$i]['port_number'] = $historyInfo[$i]->port_number;
            $historyData[$i]['port_type'] = $historyInfo[$i]->port_type;
            $historyData[$i]['order_id'] = $historyInfo[$i]->order_id;
            $historyData[$i]['evse_order_id'] = $historyInfo[$i]->evse_order_id;
            $historyData[$i]['start_type'] = $historyInfo[$i]->start_type;
            $historyData[$i]['start_type_name'] = Config::get('language.start_type_name.'.$historyInfo[$i]->start_type);
            $historyData[$i]['start_args'] = $historyInfo[$i]->charge_args;
            $historyData[$i]['charge_type'] = $historyInfo[$i]->charge_type;
            $historyData[$i]['charge_type_name'] = Config::get('language.charge_type_name.'.$historyInfo[$i]->charge_type);
            $historyData[$i]['charge_args'] = $historyInfo[$i]->charge_args;
            $historyData[$i]['start_time'] = $historyInfo[$i]->start_time;
            $historyData[$i]['end_time'] = $historyInfo[$i]->end_time;
            $historyData[$i]['duration'] = $historyInfo[$i]->duration;
            $historyData[$i]['charged_power'] = $historyInfo[$i]->charged_power;
            $historyData[$i]['fee'] = $historyInfo[$i]->fee; // 100;
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
        if(!empty($data)){
            return $this->response->array([
                'status_code'=>200,
                'message'=>'',
                'data'=>$data

            ]);
        }




    }








    //设置费率
    public function setCommodity(SetCommodityValidator $validator, $hash){

        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }



        $token = $params['token'];
        $monitor_codes = $params['monitor_codes'];
        $commodity = $params['commodity'];
        $service = $params['service'];

        //获取monitor获取数据
        $port = Port::whereIn('monitor_code', $monitor_codes)->get();

        //00点-24点 每半小时分割 48个时间段
        $commodity_info = $this->commodity($commodity, 1);
        $service_info = $this->commodity($service, 2);
        $commodities = $this->commodity_compute($commodity_info, $service_info);


        //组织发送数据
        $serverFrame = new ServerFrame();
        $serverCommodities = [];
        foreach ($commodities as $key=>$commodity){
            $tmpCommodity = new SeverCommodity();
            $res = explode('-',$key);

            $tmpCommodity->setStartHour(substr($res[0],0,2));
            $tmpCommodity->setStartMinute(substr($res[0],2,2));
            $tmpCommodity->setEndHour(substr($res[1],0,2));
            $tmpCommodity->setEndMinute(substr($res[1],2,2));
            $tmpCommodity->setRate($commodity);

            $serverCommodities[] = $tmpCommodity;
        }

        //循环下发多个code
        foreach($port as $k=>$v){
            $frame = $serverFrame->set24HsCommodityStrategy(1,$serverCommodities);
            $sendResult = EventsApi::sendMsg($v->worker_id, $frame);
        }





        $message = [];
        if (TRUE == $sendResult) {
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


    /*
     * 计算费率
     */
public function commodity_compute($commodity_info, $service_info){


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

return $commodity_merge;

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






}