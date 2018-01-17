<?php
namespace Wormhole\Protocols\HD10\Controllers\Api;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 15:52
 */
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Wormhole\Jobs\CheckNetStatus;
use Wormhole\Protocols\HD10\Jobs\CheckStartCharge;
use Wormhole\Protocols\HD10\Jobs\CheckStopCharge;
use Wormhole\Protocols\HD10\Models\ChargeOrderMapping;
use Wormhole\Protocols\HD10\Models\Evse;
use Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeLog;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\ServerFrame;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\HD10\Events;
use Wormhole\Protocols\HD10\EventsApi;
use Wormhole\Protocols\Library\Tools;
use Wormhole\Protocols\MonitorServer;
use Wormhole\Validators\RealtimeChargeInfoValidator;
use Wormhole\Validators\StartChargeValidator;
use Wormhole\Validators\StopChargeValidator;

use Wormhole\Validators\GetStatusValidator;
use Wormhole\Validators\GetMultiStaticticsPowerValidator;
use Wormhole\Validators\GetChargeHistoryValidator;
use Wormhole\Validators\SetCommodityValidator;
use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

use Ramsey\Uuid\Uuid;
use Wormhole\Protocols\ProcessPodcast;

use Wormhole\Protocols\HD10\Protocol\Server\UpgradeFileInfo;
use Wormhole\Protocols\HD10\Protocol\Server\UpgradeDataPack;

use Wormhole\Protocols\HD10\Models\Upgrade;
use Wormhole\Protocols\HD10\Models\UpgradeFileInfo as UpgradeFile;
use Wormhole\Protocols\HD10\Models\UpgradeTask;

use Wormhole\Protocols\HD10\upgradeQueue\FileInformation;
use Wormhole\Protocols\HD10\upgradeQueue\UpgradeTask AS UpgradeTaskQueue;

use Wormhole\Protocols\HD10\Models\UpgradeFileInfo as UpgradeFileInfoa;

use Wormhole\Protocols\Library;

use Wormhole\Protocols\HD10\Protocol\Evse\UpgradeFileInfo as EvseUpgradeFileInfo;
use Wormhole\Protocols\HD10\Protocol\Frame1;
use Wormhole\Protocols\HD10\Models\ChargeRecord;

use Wormhole\Protocols\HD10\Protocol\Server\UpgradeFileInfo as ServerUpgradeFileInfo;
//use App;
use Wormhole\Protocols\HD10\Protocol\Evse\Heartbeat;
class EvseController extends BaseController
{

    public function job(){
        //write log

            for($i=0;$i<100;$i++){
                $job = (new CheckNetStatus($i))->onQueue('HD10');
                dispatch($job);
            }


    }

    public function startCharge(StartChargeValidator $chargeValidator, $hash){
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start");

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
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电charge_type: $chargeType");
        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();

        //保存充电info
        $evse->order_id = $orderId;
        $evse->evse_order_id = ($evse->evse_order_id +1)% pow(2,8);
        $evse->start_time = Carbon::now();
        $evse->is_billing = 0;
        $evse->start_type = $startType;
        $evse->start_args = $startArgs;
        $evse->charge_type = $chargeType;
        $evse->charge_args = $chargeArgs;
        $evse->user_balance = $userBalance;
        $evse->last_operator_status = 0;
        $evse->last_operator_time = Carbon::now();

        $evse->save();

        ChargeOrderMapping::create([
            'evse_id'=>$evse->id,
            'code'=>$evse->code,
            'monitor_code'=>$evse->monitor_code,
            'order_id'=>$evse->order_id,
            'evse_order_id'=>$evse->evse_order_id,
            'is_billing'=>$evse->is_billing,
            'start_type'=>$evse->start_type,
            'start_args'=>$evse->start_args,
            'charge_type'=>$evse->charge_type,
            'charge_args'=>$evse->charge_args,
            'user_balance'=>$evse->user_balance,

        ]);

        $evseCode = $evse->code;
        $workerId = $evse->worker_id;


        $serverFrame = new ServerFrame();
        $frame = $serverFrame->reservationCharge($evseCode,$evse->evse_order_id,time());

        $sendResult = EventsApi::sendMsg($workerId, $frame);


        //添加检查任务
        $job = (new CheckStartCharge($evse->id,$orderId,0))
                    ->onQueue(env('APP_KEY'))
                    ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
        dispatch($job);

        
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END，sendResult ： $sendResult");
        return $this->response->array(
            [
                'status_code' => 201,
                'message' => "command send sucesss"
            ]
        );
    }






    /**
     * 实时充电数据
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

        $evse = Evse::where([['order_id', $orderId], ['is_charging', 1]])->firstOrFail();

        $data = [
            'order_id' => $evse->order_id,
            'evse_code' => $evse->monitor_code,
            'start_time' =>strtotime( $evse->start_time),
            'duration' => $evse->duration,
            'power' => round($evse->power/1000,2),
            'charged_power' => round($evse->charged_power/1000,2),
            'fee' => round($evse->fee / 100,2),                          //已充费用

            'charge_volt_a' => round($evse->voltage/1000,2),            //交流A相充电电压，单位：V
            'charge_curt_a' => round($evse->current/1000,2),             //交流A相充电电流，单位：A
            'charge_volt_b' => 0,            //交流B相充电电压，单位：V
            'charge_curt_b' => 0,            //交流B相充电电流，单位：A
            'charge_volt_c' => 0,             //交流C相充电电压，单位：V
            'charge_curt_c' => 0,          //交流C相充电电流，单位：A

            'charge_mode' => 0,                 //设置BMS充电模式
            'require_volt' => 0,               //BMS需求电压，单位：V
            'require_curt' => 0,               //BMS需求电流，单位：A

            'drt_charge_volt' => 0,             //直流充电电压 ，单位：V
            'drt_charge_curt' => 0,             //直流充电电流，单位：A
        ];
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>$data

        ]);

    }

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
        $evse = Evse::where([['order_id','=', $orderId]])->firstOrFail();
        //var_dump($port);
        $evse->last_operator_status = 9;
        $evse->last_operator_time = Carbon::now();
        $evse->save();

        $serverFrame = new ServerFrame();
        $frame = $serverFrame->stopCharge($evse->code, $evse->evse_order_id);

        $sendResult = EventsApi::sendMsg($evse->worker_id, $frame);



        $message = [];
        if (TRUE == $sendResult) {

            //添加检查任务
            $job = (new CheckStopCharge($evse->id,$orderId,9))
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

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END，sendResult ： $sendResult");
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

        }

        //DATE_FORMAT
        //当前用户充电数据 [['monitor_code', $monitor_code],['is_charging', 1]]
        $evse = Evse::where([['monitor_code', $monitor_code]])->firstorFail();

        if(empty($evse)){
            return $this->response->array([
                'status_code'=>500,
                'message'=>'',
                'data'=>[]

            ]);
        }

        //桩自身信息
        $evseStatuInfo['status']['monitor_code'] = $monitor_code;
        $evseStatuInfo['status']['code'] = $evse->code;
        $evseStatuInfo['protocol_name'] = Protocol::NAME;
        $evseStatuInfo['status']['version'] = 0;
        $evseStatuInfo['status']['port_number'] = 0;
        $evseStatuInfo['status']['port_type'] = $evse->port_type;

        //状态数据
        $evseStatuInfo['status']['online'] = $evse->online;
        $evseStatuInfo['status']['is_car_connected'] = $evse->car_connect_status;  //车辆是否连接
        $evseStatuInfo['status']['is_charging'] = $evse->is_charging;
        $evseStatuInfo['status']['warning_status'] = $evse->warning_status;
        $evseStatuInfo['status']['last_update_status_time'] = $evse->last_update_status_time;
        $evseStatuInfo['status']['current_soc'] = 0;
        $evseStatuInfo['status']['initial_soc'] = 0;


        //桩充电启动信息
        $evseStatuInfo['status']['order_id'] = $evse->order_id;
        $evseStatuInfo['status']['evse_order_id'] = $evse->evse_order_id;
        $evseStatuInfo['status']['start_type'] = $evse->start_type;
        $evseStatuInfo['status']['start_args'] = $evse->start_args / 60;
        $evseStatuInfo['status']['charge_type'] = $evse->charge_type;
        $evseStatuInfo['status']['charge_args'] = $evse->charge_args;
        $evseStatuInfo['status']['user_balance'] = $evse->user_balance / 100; //用户余额

        //充电数据
        $evseStatuInfo['status']['start_time'] = $evse->start_time;
        $evseStatuInfo['status']['charged_power'] = $evse->charged_power / 1000;
        $evseStatuInfo['status']['fee'] = $evse->fee /100;
        $evseStatuInfo['status']['duration'] = $evse->duration;
        $evseStatuInfo['status']['power'] = $evse->power / 1000;
        $evseStatuInfo['status']['ac_a_voltage'] = $evse->voltage / 1000;
        $evseStatuInfo['status']['ac_a_current'] = $evse->current / 1000;
        $evseStatuInfo['status']['ac_b_voltage'] = 0;
        $evseStatuInfo['status']['ac_b_current'] = 0;
        $evseStatuInfo['status']['ac_c_voltage'] = 0;
        $evseStatuInfo['status']['ac_c_current'] = 0;
        $evseStatuInfo['status']['dc_voltage'] = 0;
        $evseStatuInfo['status']['dc_current'] = 0;
        $evseStatuInfo['status']['last_update_charge_info_time'] = $evse->last_update_status_time;
        $evseStatuInfo['status']['left_time'] = 0;//$evse->left_time;
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
                'message'=>'',
                'data'=>[]

            ]);
        }

        $duration = json_encode($history_statistics);

        $evseStatuInfo['total_charge_info']['total_duration'] = $history_statistics[0]->duration;
        $evseStatuInfo['total_charge_info']['total_power'] = $history_statistics[0]->charged_power / 1000;
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

            $evseStatuInfo['charge_times_info'][$i]['create_time'] = $charge_times_info[$i]['create_time'];//$evseStatus[$i]->startTime;
            $evseStatuInfo['charge_times_info'][$i]['charge_times'] = $charge_times_info[$i]['charge_times'];//$evseStatus[$i]->amount;


        }
//        $statuinfo = json_encode($evseStatuInfo);
//        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 用户时间个数1: $statuinfo");
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

       //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " page_now1 :".$params['page_now'].'--limit:'.$params['limit']);

        $sort_by = empty($params['sort_by']) ? 'end_time' : $params['sort_by'];
        $sort_type = empty($params['sort_type']) ? 'desc' : $params['sort_type'];
        $page_now = empty($params['page_now']) ? '0' : $params['page_now'] - 1;
        $limit = empty($params['limit']) ? '10' : $params['limit'];

        //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " page_now2 :".$page_now.'--limit:'.$limit);
        //按条件查找需要数据
        $historyInfo = DB::table('hd10_charge_records')
            ->where('monitor_code', $params['monitor_code'])
            ->orderBy($sort_by, $sort_type)
            ->skip($page_now)
            ->take($limit)
            ->get();

//        $history = json_encode($historyInfo);
//        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " history : $history");

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
            $historyData[$i]['port_number'] = 0;
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
            $historyData[$i]['charged_power'] = $historyInfo[$i]->charged_power / 1000;
            $historyData[$i]['fee'] = $historyInfo[$i]->fee / 100;
            $historyData[$i]['stop_reason'] = $historyInfo[$i]->stop_reason;
            //$historyData[$i]['push_monitor_result'] = $historyInfo[$i]->;
            $historyData[$i]['start_soc'] = 0;//$historyInfo[$i]->start_soc;
            $historyData[$i]['end_soc'] = 0;//$historyInfo[$i]->end_soc;
            $historyData[$i]['meter_before'] = 0;//$historyInfo[$i]->meter_before;
            $historyData[$i]['meter_after'] = 0;//$historyInfo[$i]->meter_after;

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
        //$monitor_code = '';
        $evse_info = Evse::whereIn('monitor_code',$monitor_codes)->get();

        //00点-24点 每半小时分割 48个时间段
        $commodity_info = $this->commodity($commodity, 1);
        $service_info = $this->commodity($service, 2);

        //$commodities = $this->commodity_compute($commodity_info, $service_info);

        //组织发送数据
        $serverFrame = new ServerFrame();
        foreach($evse_info as $k=>$v){
            //组装电费帧，发送
            $frame = $serverFrame->commoditySet($v->code,0,$commodity_info);
            $sendResult = EventsApi::sendMsg($v->worker_id, $frame);
            //如果电费下发成功，接着下发服务费
           if(!empty($sendResult)){
               $frame = $serverFrame->commoditySet($v->code,1,$service_info);
               $sendResult = EventsApi::sendMsg($v->worker_id, $frame);
           }
            sleep(2);

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

                    $commodity_price[$time] = $val[$price];

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


    /**
     * 下发升级
     */
    public function upgrade(){



//此任务升级结束,删除相应数据
        //$res = UpgradeTask::where('task_id', '123')->delete();
        //$res = Upgrade::where('monitor_task_id', '123')->delete();
        //$res = UpgradeFile::where('monitor_task_id', '0')->delete();
//echo 123;
       // die;

        //$url = 'http://192.168.1.66/pub/api/pub/api/get_file/hash/monitor_updateManager/b40154d085657176a56e21860a94064c';
        //$result = MonitorServer::get($url);

        //下发之前清一下数据
//        $res = UpgradeTask::where('id', '>', 1)->delete();
//        $res = Upgrade::where('id', '>', 1)->delete();
//        $res = UpgradeFile::where('packet_size', '>', 1)->delete();

//        DB::table('hd10_upgrade')->delete();
//        DB::table('hd10_upgrade_task')->delete();
//        DB::table('hd10_upgrade_file_info')->delete();
        



        //桩编号,文件路径,任务id,开始时间
        //$monitorCodes = array('UN000074','UN000075');

        $params = $this->request->all();
        $monitorCodes = $params['code_list'];
        $packct_size = $params['packct_size'];
        $taskId = $params['upgrade_task_id'];
        $upgradeUrl = $params['upgrade_url'];
        $upgradeDate = $params['start_upgrade_time'];

        if(!is_numeric($packct_size) || $packct_size > 512){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级文件每包数据不能大于512, packct_size:$packct_size ");
            return $this->response->array([
                'status_code'=>500,
                'message'=>'',
                'data'=>false

            ]);

        }
        //测试
        //$monitorCodes = ['IW000107'];

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 升级任务收到参数 monitorCodes: ".json_encode($monitorCodes)." taskId:$taskId, upgradeUrl:$upgradeUrl, upgradeDate:$upgradeDate");

//        $upgradeUrl = 123123;
//        $taskId = 1111;
//        $upgradeDate = date('Y-m-d H:i:s', time());

        //通过monitorcode获取code
//        $codes = Evse::whereIn("monitor_code", $monitorCodes)
//            ->select("code")
//            ->get(); //找出所有code

        //如果没有时间则立即执行
        $time = 0;
        if(empty($upgradeDate)){
            $time = 1;
            $upgradeDate = date('Y-m-d H:i:s', time());
        }else{
            $time = strtotime($upgradeDate) - time();
            if($time <= 1){
                $time = 1;
            }
        }
        //将任务添加到任务表
        $res = UpgradeTask::create([
            'code' => json_encode($monitorCodes),
            'file_id' => $upgradeUrl,
            'task_id' => $taskId,
            'start_date'=>$upgradeDate,
            'packet_size'=>$packct_size

        ]);


        //如果有正在执行的任务,则直接返回
        $upgrade = UpgradeTask::where('status', 1)->first();
        if(empty($upgrade)){

            //如果任务添加成功,生成监控任务
            //$upgradeTime = strtotime($upgradeDate);
            $job = (new UpgradeTaskQueue($taskId))
                ->onQueue(env('APP_KEY'))
                ->delay(Carbon::now()->addSeconds(3)); //$upgradeTime+20 - time()
            dispatch($job);

        }


        //如果任务添加成功,生成监控任务
        //$upgradeTime = strtotime($upgradeDate);
//        $job = (new UpgradeTaskQueue($taskId))
//            ->onQueue(env('APP_KEY'))
//            ->delay(Carbon::now()->addSeconds(3)); //$upgradeTime+20 - time()
//        dispatch($job);


        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>true

        ]);

        
       





//        var_dump($sendResult);
//$frame = strval($upgrade);   //组装帧
//        $up = new UpgradeFileInfo();
//
//        $frame_load = $up($frame);   //解析帧
//        var_dump($frame_load);

    }

    //取消未开始的任务
    public function cancelUpgrade(){

        $params = $this->request->all();
        $taskId = $params['upgrade_task_id'];

        $upgradeInfo = UpgradeTask::where('task_id', $taskId)->first();
        //判断任务是否存在
        if(empty($upgradeInfo)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 取消未开始的任务,未找到此任务 taskId:$taskId ");
            return $message = [
                'status_code' => 500,
                'message' => "command send failed"
            ];
        }
        //如果任务在升级中不能取消
        if($upgradeInfo->status == 1){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 取消未开始的任务,此任务在进行中,不能取消 status: ".$upgradeInfo->status);
            return $message = [
                'status_code' => 500,
                'message' => "command send failed"
            ];
        }

        $res = UpgradeTask::where('task_id', $taskId)->delete();
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 取消未开始的任务,结果 res: $res");


        if (TRUE == $res) {
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

        return $message;


    }







}