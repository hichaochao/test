<?php
namespace Wormhole\Protocols\ZH\Controllers\Api;

/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 15:52
 */
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Wormhole\Protocols\ZH\Protocol\Frame;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\Library\Tools;
use Wormhole\Protocols\ZH\EventsApi;
use Wormhole\Protocols\MonitorServer;
use Wormhole\Validators\RealtimeChargeInfoValidator;
use Wormhole\Validators\StartChargeValidator;
use Wormhole\Validators\StopChargeValidator;
use Wormhole\Protocols\ZH\Jobs\CheckStopCharge;

use Wormhole\Protocols\ZH\Protocol;
use Wormhole\Protocols\ZH\Jobs\CheckStartCharge;
use Wormhole\Validators\GetStatusValidator;
use Wormhole\Validators\GetMultiStaticticsPowerValidator;
use Wormhole\Validators\GetChargeHistoryValidator;
use Wormhole\Validators\SetCommodityValidator;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

use Ramsey\Uuid\Uuid;
//use Wormhole\Protocols\ProcessPodcast;


use Wormhole\Protocols\ZH\Models\Evse;
use Wormhole\Protocols\ZH\Models\Port;
use Wormhole\Protocols\ZH\Models\ChargeOrderMapping;

use Wormhole\Protocols\ZH\Models\ChargeRecord;


use Wormhole\Protocols\ZH\Protocol\Evse\Sign;
use Wormhole\Protocols\ZH\Protocol\Evse\Hearbeat;
use Wormhole\Protocols\ZH\Protocol\Evse\StartCharge as StartC;
use Wormhole\Protocols\ZH\Protocol\Evse\StopCharge as StopC;
use Wormhole\Protocols\ZH\Protocol\Evse\PortRealTimeData;
use Wormhole\Protocols\ZH\Protocol\Evse\StopCharge as StopChargeEvse;
use Wormhole\Protocols\ZH\Protocol\Evse\RealTimeState;



use Wormhole\Protocols\ZH\Protocol\controlField;
use Wormhole\Protocols\ZH\Protocol\sequenceDomain;
use Wormhole\Protocols\ZH\Protocol\masterStationAddress;

use Wormhole\Protocols\ZH\Protocol\Server\Restart;
use Wormhole\Protocols\ZH\Protocol\Server\StartCharge;
use Wormhole\Protocols\ZH\Protocol\Server\Unlock;
use Wormhole\Protocols\ZH\Protocol\Server\StopCharge;
use Wormhole\Protocols\ZH\Protocol\Server\ReservationLock;
use Wormhole\Protocols\ZH\Protocol\Server\CancelReservationLock;
use Wormhole\Protocols\ZH\Protocol\Server\SetIpDomain;
use Wormhole\Protocols\ZH\Protocol\Server\SetStateFrequency;
use Wormhole\Protocols\ZH\Protocol\Server\SetChargeDataFrequency;
use Wormhole\Protocols\ZH\Protocol\Server\SetPwmDutyRatio;
use Wormhole\Protocols\ZH\Protocol\Server\SetBasicParameter;
use Wormhole\Protocols\ZH\Protocol\Server\GetIpDomain;
use Wormhole\Protocols\ZH\Protocol\Server\GetRate;
use Wormhole\Protocols\ZH\Protocol\Server\GetStateFrequency;
use Wormhole\Protocols\ZH\Protocol\Server\GetChargeDataFrequency;
use Wormhole\Protocols\ZH\Protocol\Server\GetPwmDutyRatio;
use Wormhole\Protocols\ZH\Protocol\Server\GetBasicParameter;
use Wormhole\Protocols\ZH\Protocol\Server\CurrentTime;
use Wormhole\Protocols\ZH\Protocol\Server\GetBaseData;
use Wormhole\Protocols\ZH\Protocol\Server\GetRunStatus;
use Wormhole\Protocols\ZH\Protocol\Server\GetPortStatus;
use Wormhole\Protocols\ZH\Protocol\Server\GetBatteryData;
use Wormhole\Protocols\ZH\Protocol\Server\GetBatteryChargeStatus;
use Wormhole\Protocols\ZH\Protocol\Server\GetBatteryTemperature;
use Wormhole\Protocols\ZH\Protocol\Server\ControlCommand;
use Wormhole\Protocols\ZH\Protocol\Server\CalibratTime;
use Wormhole\Protocols\ZH\Protocol\Server\SetRate;

use Wormhole\Protocols\ZH\Protocol\Evse\CardStopCharge;

use Kozz\Laravel\Facades\Guzzle;

class EvseController extends BaseController
{


    //启动充电
    public function startCharge(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Start ");

        $params = $this->request->all();

        $orderId = $params['order_id']; //订单号
        $monitorEvseCode = $params['evse_code']; //桩编号
        $startType = $params['start_type']; //充电方式
        $balance = $params['user_balance']; //余额
        //$startArgs = $params['start_args'];
        //$cardNumber = $params['card_number']; //卡号
		$startType = $startType == 0 ? 1 : $startType;
        //$balance = $balance * 100;
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电:orderId:$orderId, monitorEvseCode:$monitorEvseCode, charge_type: $startType, balance:$balance");
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->first();
        if(empty($port)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电通过monitorCode未找到相应桩编号 monitorEvseCode:$monitorEvseCode");
            return false;
        }

        //查找桩信息
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->first();
        if(empty($evse)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电未找到桩信息 division_terminal_address:$port->division_terminal_address");
            return false;
        }
        //取出卡号
        $card_num = $evse->card_num + 1;

        //初始化枪表数据
        $port->order_id = $orderId;
        $port->evse_order_id = ($port->evse_order_id +1)% pow(2,8);;
        $port->start_time = Carbon::now();
        $port->start_type = $startType;
        $port->card_num = $card_num;
        $port->user_balance = $balance;
        $port->start_time = Carbon::now();
        $port->last_operator_time = Carbon::now();
        $port->last_operator_status = 1;

        $port->work_status = 1;
        $port->output_voltage = 0;
        $port->output_current = 0;
        $port->total_power = 0;
        $port->rate_one_power = 0;
        $port->rate_two_power = 0;
        $port->rate_three_power = 0;
        $port->rate_four_power = 0;
        $port->ammeter_degree = 0;
        $port->save();



        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;

        $evse->card_num = $card_num;//更新卡号
        $evse->save();

        $portNumber = $port->port_number; //枪口号
        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);


        $res = ChargeOrderMapping::create([

            'evse_id'=>$evse->id,
            'port_id'=>$port->id,
            'division_terminal_address'=>$divisionTerminalAddress,
            'monitor_code'=>$monitorEvseCode,
            'order_id'=>$orderId,
            'port_number'=>$port->port_number,
            'start_type'=>$startType,
            'user_balances'=>$port->user_balance


        ]);

        if(empty($res)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电,订单映射表创建失败 ");
            return $this->response->array(
                [
                    'status_code' => 500,
                    'message' => "command send fail"
                ]
            );
        }

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $startCharge = new StartCharge();
        $startCharge->version(intval($version));
        $startCharge->control_field(intval($control));
        $startCharge->division_code(intval($res[0]));
        $startCharge->terminal_address(intval($res[1]));
        $startCharge->master_station_address(intval($masterGroupAddress));
        $startCharge->seq(intval($seq));

        $startCharge->port(intval($portNumber));
        $startCharge->cardNumber(intval($card_num));
        $startCharge->balance(intval($balance));
        $startCharge->second(intval($dateTime[5]));
        $startCharge->minute(intval($dateTime[4]));
        $startCharge->hour(intval($dateTime[3]));
        $startCharge->day(intval($dateTime[2]));
        $startCharge->month(intval($dateTime[1]));
        $startCharge->year(intval($dateTime[0]));
        $startCharge->chargeType(intval($startType));

        $frame = strval($startCharge);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电: sendResult:$sendResult");


        //添加检查任务
        $job = (new CheckStartCharge($port->id,$orderId,1))
            ->onQueue(env('APP_KEY'))
            ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
        dispatch($job);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " END，sendResult:$sendResult");
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
    {
        //借用stop charge，只需要订单号
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();

        $validator = $validator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $orderId = $params['order_id'];

        $port = Port::where([['order_id', $orderId], ['work_status', 2]])->first();
        if(empty($port)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 获取实时数据,未找到此订单或者不在充电中 orderId:$orderId ");
            return false;
        }

        $data = [
            'order_id' => $port->order_id,
            'evse_code' => $port->monitor_evse_code,
            'start_time' =>strtotime($port->start_time),
            'duration' => time() - strtotime($port->start_time), //已充时长,当前时间减去启动时间 秒
            'power' => 0,//round($port->output_current/10 - 400,2), //电功率
            'charged_power' => round($port->total_power,2),  //电量
            'fee' => 0,                          //已充费用

            'charge_volt_a' => round($port->output_voltage, 2),       //交流A相充电电压，单位：V
            'charge_curt_a' => round($port->output_current, 2),      //交流A相充电电流，单位：A
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
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 实时数据".json_encode($data));
        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>$data

        ]);

    }



    //停止充电
    public function stopCharge(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电 Start".Carbon::now());

        $params = $this->request->all();
        $orderId = $params['order_id']; //订单id
        $flag = 2;//标志
        //$cardNumber = $params['card_number']; //卡号
        //$cardId = $params['card_id'];   //卡号


        $port = Port::where('order_id',$orderId)->first();
        if(empty($port)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电 未找到枪数据");
            return false;
        }
        $divisionTerminalAddress = $port->division_terminal_address;
        $portNumber = $port->port_number;
        $workStatus = $port->work_status;
        $cardNumber = $port->card_num;


        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);
        //更改桩状态
        $port->work_status = 4; //停止中
        $port->last_operator_status = 4;
        $port->last_operator_time = Carbon::now();
        $res = $port->save();
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电更新状态结果 res:$res");

        //获取版本号和workeId
        $evse = Evse::where('id',$port->evse_id)->first();
        if(empty($evse)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电未找到数据 ");
        }
        $version = $evse->version;
        $workerId = $evse->worker_id;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $stopCharge = new StopCharge();
        $stopCharge->version(intval($version));
        $stopCharge->control_field(intval($control));
        $stopCharge->division_code(intval($res[0]));
        $stopCharge->terminal_address(intval($res[1]));
        $stopCharge->master_station_address(intval($masterGroupAddress));
        $stopCharge->seq(intval($seq));


        $stopCharge->year(intval($dateTime[0]));
        $stopCharge->month(intval($dateTime[1]));
        $stopCharge->day(intval($dateTime[2]));
        $stopCharge->hour(intval($dateTime[3]));
        $stopCharge->minute(intval($dateTime[4]));
        $stopCharge->second(intval($dateTime[5]));
        $stopCharge->port(intval($portNumber));
        $stopCharge->cardNumber(intval($cardNumber));
        $stopCharge->flag(intval($flag));

        $frame = strval($stopCharge);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电: sendResult:$sendResult");


        if (TRUE == $sendResult) {

            //添加检查任务
            $job = (new CheckStopCharge($port->id,$orderId,4))
                ->onQueue(env('APP_KEY'))
                ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
            dispatch($job);

            $message = [
                'status_code' => 201,
                'message' => "command send success"
            ];
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电下发成功 $sendResult");
        } else {
            $message = [
                'status_code' => 500,
                'message' => "command send failed"
            ];
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电下发失败 $sendResult");

        }

        return $this->response->array($message);

    }


    //获取充电桩状态
    public function getStatus(GetStatusValidator $validator, $hash){

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " START");
        $params = $this->request->all();
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 数据: ".json_encode($params));
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
        $port = Port::where([['monitor_evse_code', $monitor_code]])->first();

        $evse = Evse::where([['id', $port->evse_id]])->first();

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 枪数据: ".json_encode($port));
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 桩数据: ".json_encode($evse));

        if(empty($port) || empty($evse)){
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 获取桩或枪数据失败 ");
            return $this->response->array([
                'status_code'=>500,
                'message'=>'',
                'data'=>[]

            ]);
        }

        //去掉桩编号中的-
        $code = str_replace('-','',$evse->division_terminal_address);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " code:$code ");


        //桩自身信息
        $evseStatuInfo['status']['monitor_code'] = $monitor_code;
        $evseStatuInfo['status']['code'] = $code;
        $evseStatuInfo['protocol_name'] = Protocol::NAME;
        $evseStatuInfo['status']['version'] = 0;
        $evseStatuInfo['status']['port_number'] = 0;
        $evseStatuInfo['status']['port_type'] = $evse->port_type;

        //状态数据
        $evseStatuInfo['status']['online'] = $evse->online;
        //$evseStatuInfo['status']['is_car_connected'] = $evse->car_connect_status;  //车辆是否连接
        //$evseStatuInfo['status']['is_charging'] = $evse->is_charging;
        //$evseStatuInfo['status']['warning_status'] = $evse->warning_status;
        $evseStatuInfo['status']['last_update_status_time'] = $evse->last_update_status_time;
        $evseStatuInfo['status']['current_soc'] = 0;
        $evseStatuInfo['status']['initial_soc'] = 0;


        //桩充电启动信息
        //$evseStatuInfo['status']['order_id'] = $port->order_id;
        $evseStatuInfo['status']['evse_order_id'] = $port->order_id;
        $evseStatuInfo['status']['start_type'] = $port->start_type;
        //$evseStatuInfo['status']['start_args'] = $evse->start_args / 60;
        //$evseStatuInfo['status']['charge_type'] = $evse->charge_type;
        //$evseStatuInfo['status']['charge_args'] = $evse->charge_args;
        $evseStatuInfo['status']['user_balance'] = $port->user_balance / 100; //用户余额

        //充电数据
        $evseStatuInfo['status']['start_time'] = $port->start_time;
        $evseStatuInfo['status']['charged_power'] = $port->total_power;
        //$evseStatuInfo['status']['fee'] = $evse->fee /100;
        $start_time = $port->work_status != 2 ? time() : strtotime($port->start_time);
        $evseStatuInfo['status']['duration'] = time() - $start_time;
        $evseStatuInfo['status']['power'] = 0;
        $evseStatuInfo['status']['ac_a_voltage'] = $port->output_voltage;
        $evseStatuInfo['status']['ac_a_current'] = $port->output_current;
        $evseStatuInfo['status']['ac_b_voltage'] = 0;
        $evseStatuInfo['status']['ac_b_current'] = 0;
        $evseStatuInfo['status']['ac_c_voltage'] = 0;
        $evseStatuInfo['status']['ac_c_current'] = 0;
        $evseStatuInfo['status']['dc_voltage'] = 0;
        $evseStatuInfo['status']['dc_current'] = 0;
        $evseStatuInfo['status']['last_update_charge_info_time'] = $evse->last_update_status_time;
        $evseStatuInfo['status']['left_time'] = 0;//$evse->left_time;
        $evseStatuInfo['status']['before_current_ammeter_reading'] = $port->ammeter_degree; //TODO 充电前电表读数
        $evseStatuInfo['status']['current_ammeter_reading'] = $port->ammeter_degree; //TODO 当前前电表读数
        //bsm 信息
        $evseStatuInfo['status']['bms_mode'] = 0;
        $evseStatuInfo['status']['bms_voltage'] = $port->output_voltage;
        $evseStatuInfo['status']['bms_current'] = $port->output_current;




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

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " charge_times_info: ".json_encode($charge_times_info));



        //充电时间，用户个数
        for($i=0;$i<count($charge_times_info);$i++){

            $evseStatuInfo['charge_times_info'][$i]['create_time'] = $charge_times_info[$i]['create_time'];//$evseStatus[$i]->startTime;
            $evseStatuInfo['charge_times_info'][$i]['charge_times'] = $charge_times_info[$i]['charge_times'];//$evseStatus[$i]->amount;

        }

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 获取数据: ".json_encode($evseStatuInfo));



        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " END");
        return $this->response->array([
            'status_code'=>200,
            'message'=>'',
            'data'=>$evseStatuInfo

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
        $monitor_code = $params['monitor_code'];
        $sort_by = empty($params['sort_by']) ? 'end_time' : $params['sort_by'];
        $sort_type = empty($params['sort_type']) ? 'desc' : $params['sort_type'];
        $page_now = empty($params['page_now']) ? '0' : $params['page_now'] - 1;
        $limit = empty($params['limit']) ? '10' : $params['limit'];

        //Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " page_now2 :".$page_now.'--limit:'.$limit);
        //按条件查找需要数据
        $historyInfo = ChargeRecord::where('monitor_code', $monitor_code)
            ->orderBy($sort_by, $sort_type)
            ->skip($page_now)
            ->take($limit)
            ->get();

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 历史数据: :".json_encode($historyInfo));

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

            $code = str_replace('-','', $historyInfo[$i]->division_terminal_address);
            $historyData[$i]['id'] = $historyInfo[$i]->id;
            $historyData[$i]['monitor_code'] = $historyInfo[$i]->monitor_code;
            $historyData[$i]['code'] = $code;
            $historyData[$i]['port_number'] = $historyInfo[$i]->port_number;
            $historyData[$i]['port_type'] = 1; //直流
            $historyData[$i]['order_id'] = $historyInfo[$i]->order_id;
            $historyData[$i]['evse_order_id'] = $historyInfo[$i]->evse_order_id;
            $historyData[$i]['start_type'] = $historyInfo[$i]->start_type;
            $historyData[$i]['start_type_name'] = Config::get('language.start_type_name.'.$historyInfo[$i]->start_type);
            //$historyData[$i]['start_args'] = $historyInfo[$i]->charge_args;
            $historyData[$i]['charge_type'] = $historyInfo[$i]->charge_type;
            $historyData[$i]['charge_type_name'] = Config::get('language.charge_type_name.'.$historyInfo[$i]->charge_type);
            //$historyData[$i]['charge_args'] = $historyInfo[$i]->charge_args;
            $historyData[$i]['start_time'] = $historyInfo[$i]->start_time;
            $historyData[$i]['end_time'] = $historyInfo[$i]->end_time;
            $historyData[$i]['duration'] = $historyInfo[$i]->duration;
            $historyData[$i]['charged_power'] = $historyInfo[$i]->charged_power;
            $historyData[$i]['fee'] = $historyInfo[$i]->charged_fee;
            $historyData[$i]['stop_reason'] = $historyInfo[$i]->stop_reason;
            //$historyData[$i]['push_monitor_result'] = $historyInfo[$i]->;
            $historyData[$i]['start_soc'] = 0;//$historyInfo[$i]->start_soc;
            $historyData[$i]['end_soc'] = 0;//$historyInfo[$i]->end_soc;
            $historyData[$i]['meter_before'] = 0;//$historyInfo[$i]->meter_before;
            $historyData[$i]['meter_after'] = 0;//$historyInfo[$i]->meter_after;

        }


        $data = array('total_num'=>$total_num, 'list'=>$historyData);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " END,返回数: ".json_encode($data));
        if(!empty($data)){
            return $this->response->array([
                'status_code'=>200,
                'message'=>'',
                'data'=>$data

            ]);
        }




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







    //***********************************设置参数**********************************************//

    //服务器IP地址和端口
    public function setIpAndPort(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];
        //重启类型 0应用程序重启 1系统重启
        $mainIp = $params['main_ip'];
        $mainPort = $params['main_port'];
        $secondaryIp = $params['secondary_ip'];
        $secondaryPort = $params['secondary_port'];

        $mainIp = explode('.',$mainIp);
        $secondaryIp = explode('.', $secondaryIp);

        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();

        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;


        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $setIpDomain = new SetIpDomain();
        $setIpDomain->version(intval($version));
        $setIpDomain->control_field(intval($control));
        $setIpDomain->division_code(intval($res[0]));
        $setIpDomain->terminal_address(intval($res[1]));
        $setIpDomain->master_station_address(intval($masterGroupAddress));
        $setIpDomain->seq(intval($seq));

        $setIpDomain->mainIp1(intval($mainIp[0]));
        $setIpDomain->mainIp2(intval($mainIp[1]));
        $setIpDomain->mainIp3(intval($mainIp[2]));
        $setIpDomain->mainIp4(intval($mainIp[3]));
        $setIpDomain->mainPort(intval($mainPort));
        $setIpDomain->secondaryIp1(intval($secondaryIp[0]));
        $setIpDomain->secondaryIp2(intval($secondaryIp[1]));
        $setIpDomain->secondaryIp3(intval($secondaryIp[2]));
        $setIpDomain->secondaryIp4(intval($secondaryIp[3]));
        $setIpDomain->secondaryPort(intval($secondaryPort));


        $frame = strval($setIpDomain);   //组装帧
       // $fra = Tools::asciiStringToHexString($frame);
        $fra = bin2hex($frame);

        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口: sendResult:$sendResult");

    }



    //充电费率设置
    public function setRate(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率设置 Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];
        //充电费率设置
        $ratePattern = $params['ratePattern'];
        $totalElectricity = $params['totalElectricity'];
        $tipPrice = $params['tipPrice'];
        $peakPrice = $params['peakPrice'];
        $flatPrice = $params['flatPrice'];
        $valleyPrice = $params['valleyPrice'];
        $appointmentRate = $params['appointmentRate'];


        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();

        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率设置: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $setRate = new SetRate();
        $setRate->version(intval($version));
        $setRate->control_field(intval($control));
        $setRate->division_code(intval($res[0]));
        $setRate->terminal_address(intval($res[1]));
        $setRate->master_station_address(intval($masterGroupAddress));
        $setRate->seq(intval($seq));

        $setRate->ratePattern(intval($ratePattern));
        $setRate->totalElectricity(intval($totalElectricity));
        $setRate->tipPrice(intval($tipPrice));
        $setRate->peakPrice(intval($peakPrice));
        $setRate->flatPrice(intval($flatPrice));
        $setRate->valleyPrice(intval($valleyPrice));
        $setRate->appointmentRate(intval($appointmentRate));


        $frame = strval($setRate);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率设置: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率设置: sendResult:$sendResult");

    }



    //充电桩状态上传频率设置
    public function setStateFrequency(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率设置  Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];
        //充电桩状态上传频率设置
        $setTime = $params['setTime'];



        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();

        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率设置: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $setStateFrequency = new SetStateFrequency();
        $setStateFrequency->version(intval($version));
        $setStateFrequency->control_field(intval($control));
        $setStateFrequency->division_code(intval($res[0]));
        $setStateFrequency->terminal_address(intval($res[1]));
        $setStateFrequency->master_station_address(intval($masterGroupAddress));
        $setStateFrequency->seq(intval($seq));

        $setStateFrequency->setTime(intval($setTime));



        $frame = strval($setStateFrequency);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率设置: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率设置: sendResult:$sendResult");

    }



    //充电桩充电数据上传频率设置
    public function setChargeDataFrequency(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率设置  Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];
        //充电桩充电数据上传频率设置
        $setTime = $params['setTime'];



        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();

        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率设置: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $setChargeDataFrequency = new SetChargeDataFrequency();
        $setChargeDataFrequency->version(intval($version));
        $setChargeDataFrequency->control_field(intval($control));
        $setChargeDataFrequency->division_code(intval($res[0]));
        $setChargeDataFrequency->terminal_address(intval($res[1]));
        $setChargeDataFrequency->master_station_address(intval($masterGroupAddress));
        $setChargeDataFrequency->seq(intval($seq));

        $setChargeDataFrequency->setTime(intval($setTime));



        $frame = strval($setChargeDataFrequency);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率设置: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率设置: sendResult:$sendResult");

    }


    //PWM占空比设置
    public function setPwmDutyRatio(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " PWM占空比设置  Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];
        //PWM占空比设置
        $dutyData = $params['dutyData'];



        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();

        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " PWM占空比设置: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $setPwmDutyRatio = new SetPwmDutyRatio();
        $setPwmDutyRatio->version($version);
        $setPwmDutyRatio->control_field($control);
        $setPwmDutyRatio->division_code($res[0]);
        $setPwmDutyRatio->terminal_address($res[1]);
        $setPwmDutyRatio->master_station_address($masterGroupAddress);
        $setPwmDutyRatio->seq($seq);

        $setPwmDutyRatio->dutyData($dutyData);



        $frame = strval($setPwmDutyRatio);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " PWM占空比设置: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " PWM占空比设置: sendResult:$sendResult");

    }


    //模块基本参数设置
    public function setBasicParameter(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数设置  Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];
        //PWM占空比设置
        $modularNum1 = $params['modularNum1'];
        $modularNum2 = $params['modularNum2'];
        $voltageLevel = $params['voltageLevel'];
        $currentLevel = $params['currentLevel'];
        $currentLimit = $params['currentLimit'];
        $voltageCap = $params['voltageCap'];
        $voltageLower = $params['voltageLower'];



        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();

        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数设置: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $setBasicParameter = new SetBasicParameter();
        $setBasicParameter->version($version);
        $setBasicParameter->control_field($control);
        $setBasicParameter->division_code($res[0]);
        $setBasicParameter->terminal_address($res[1]);
        $setBasicParameter->master_station_address($masterGroupAddress);
        $setBasicParameter->seq($seq);

        $setBasicParameter->modularNum1($modularNum1);
        $setBasicParameter->modularNum2($modularNum2);
        $setBasicParameter->voltageLevel($voltageLevel);
        $setBasicParameter->currentLevel($currentLevel);
        $setBasicParameter->currentLimit($currentLimit);
        $setBasicParameter->voltageCap($voltageCap);
        $setBasicParameter->voltageLower($voltageLower);



        $frame = strval($setBasicParameter);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数设置: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数设置: sendResult:$sendResult");

    }



    //***********************************查询参数**********************************************//

    //查询服务器IP地址和端口
    public function getIpDomain(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口 Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];


        //找到地址域
        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $getIpDomain = new GetIpDomain();
        $getIpDomain->version($version);
        $getIpDomain->control_field($control);
        $getIpDomain->division_code($res[0]);
        $getIpDomain->terminal_address($res[1]);
        $getIpDomain->master_station_address($masterGroupAddress);
        $getIpDomain->seq($seq);



        $frame = strval($getIpDomain);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口: sendResult:$sendResult");

    }



    //查询充电费率
    public function getRate(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率 Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];

        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        //找到地址域
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $getRate = new GetRate();
        $getRate->version(intval($version));
        $getRate->control_field(intval($control));
        $getRate->division_code(intval($res[0]));
        $getRate->terminal_address(intval($res[1]));
        $getRate->master_station_address(intval($masterGroupAddress));
        $getRate->seq(intval($seq));



        $frame = strval($getRate);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率: sendResult:$sendResult");

    }



    //充电桩状态上传频率
    public function getStateFrequency(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率 Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];


        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $getStateFrequency = new GetStateFrequency();
        $getStateFrequency->version(intval($version));
        $getStateFrequency->control_field(intval($control));
        $getStateFrequency->division_code(intval($res[0]));
        $getStateFrequency->terminal_address(intval($res[1]));
        $getStateFrequency->master_station_address(intval($masterGroupAddress));
        $getStateFrequency->seq(intval($seq));



        $frame = strval($getStateFrequency);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率: sendResult:$sendResult");

    }



    //充电桩充电数据上传频率
    public function getChargeDataFrequency(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率 Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];


        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $getChargeDataFrequency = new GetChargeDataFrequency();
        $getChargeDataFrequency->version(intval($version));
        $getChargeDataFrequency->control_field(intval($control));
        $getChargeDataFrequency->division_code(intval($res[0]));
        $getChargeDataFrequency->terminal_address(intval($res[1]));
        $getChargeDataFrequency->master_station_address(intval($masterGroupAddress));
        $getChargeDataFrequency->seq(intval($seq));



        $frame = strval($getChargeDataFrequency);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率: sendResult:$sendResult");

    }



    //PWM 占空比
    public function getPwmDutyRatio(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " PWM 占空比 Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];


        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " PWM 占空比: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $getPwmDutyRatio = new GetPwmDutyRatio();
        $getPwmDutyRatio->version(intval($version));
        $getPwmDutyRatio->control_field(intval($control));
        $getPwmDutyRatio->division_code(intval($res[0]));
        $getPwmDutyRatio->terminal_address(intval($res[1]));
        $getPwmDutyRatio->master_station_address(intval($masterGroupAddress));
        $getPwmDutyRatio->seq(intval($seq));



        $frame = strval($getPwmDutyRatio);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " PWM 占空比: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " PWM 占空比: sendResult:$sendResult");

    }



    //模块基本参数
    public function getBasicParameter(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数 Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];


        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数 workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $getBasicParameter = new GetBasicParameter();
        $getBasicParameter->version(intval($version));
        $getBasicParameter->control_field(intval($control));
        $getBasicParameter->division_code(intval($res[0]));
        $getBasicParameter->terminal_address(intval($res[1]));
        $getBasicParameter->master_station_address(intval($masterGroupAddress));
        $getBasicParameter->seq(intval($seq));



        $frame = strval($getBasicParameter);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数: sendResult:$sendResult");

    }






    //***********************************控制命令**********************************************//

    //充电桩重启
    public function restart(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩重启Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];
        //重启类型 0应用程序重启 1系统重启
        $restartType = $params['restart_type'];

        //找到地址域
        $port = Port::where('monitor_evse_code',$monitorEvseCode)->firstOrFail();
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩重启: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $restart = new Restart();
        $restart->version(intval($version));
        $restart->control_field(intval($control));
        $restart->division_code(intval($res[0]));
        $restart->terminal_address(intval($res[1]));
        $restart->master_station_address(intval($masterGroupAddress));
        $restart->seq(intval($seq));
        $restart->restartType(intval($restartType));


        $frame = strval($restart);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩重启: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩重启: sendResult:$sendResult");


    }


    //充电卡解锁
    public function unlock(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡解锁Start");
        $params = $this->request->all();
        $monitorEvseCode = $params['monitor_evse_code'];

        //找到地址域
        $evse = Evse::where('monitor_code',$monitorEvseCode)->firstOrFail();
        $divisionTerminalAddress = $evse->division_terminal_address;
        $masterGroupAddress = $evse->master_group_address;
        $version = $evse->version;
        $workerId = $evse->worker_id;
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡解锁: workerId:$workerId");

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$divisionTerminalAddress);
        $unlock = new Unlock();
        $unlock->version($version);
        $unlock->control_field($control);
        $unlock->division_code($res[0]);
        $unlock->terminal_address($res[1]);
        $unlock->master_station_address($masterGroupAddress);
        $unlock->seq($seq);


        $frame = strval($unlock);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡解锁: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡解锁: sendResult:$sendResult");

    }








    //对时命令
    public function calibratTime(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 对时命令Start");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evse = Evse::where('division_terminal_address',$port->division_terminal_address)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;


        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $calibratTime = new CalibratTime();
        $calibratTime->version(intval($version));
        $calibratTime->control_field(intval($control));
        $calibratTime->division_code(intval($res[0]));
        $calibratTime->terminal_address(intval($res[1]));
        $calibratTime->master_station_address(intval($masterGroupAddress));
        $calibratTime->seq(intval($seq));

        $calibratTime->year(intval($dateTime[0]));
        $calibratTime->month(intval($dateTime[1]));
        $calibratTime->day(intval($dateTime[2]));
        $calibratTime->hour(intval($dateTime[3]));
        $calibratTime->minute(intval($dateTime[4]));
        $calibratTime->second(intval($dateTime[5]));

        $frame = strval($calibratTime);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 对时命令: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 对时命令: sendResult:$sendResult");



    }
    
    
    //充电接口预约锁定
    public function reservationLock(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电接口预约锁定");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $reservationLock = new ReservationLock();
        $reservationLock->version($version);
        $reservationLock->control_field($control);
        $reservationLock->division_code($res[0]);
        $reservationLock->terminal_address($res[1]);
        $reservationLock->master_station_address($masterGroupAddress);
        $reservationLock->seq($seq);

        $reservationLock->year($dateTime[0]);
        $reservationLock->month($dateTime[1]);
        $reservationLock->day($dateTime[2]);
        $reservationLock->hour($dateTime[3]);
        $reservationLock->minute($dateTime[4]);
        $reservationLock->second($dateTime[5]);
        $reservationLock->port($portNumber);
        $reservationLock->cardNumber($dateTime[5]);


        $frame = strval($reservationLock);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电接口预约锁定: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电接口预约锁定: sendResult:$sendResult");
        


    }



    //充电桩充电接口取消预约解锁
    public function cancelReservationLock(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口取消预约解锁start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $cancelReservationLock = new CancelReservationLock();
        $cancelReservationLock->version($version);
        $cancelReservationLock->control_field($control);
        $cancelReservationLock->division_code($res[0]);
        $cancelReservationLock->terminal_address($res[1]);
        $cancelReservationLock->master_station_address($masterGroupAddress);
        $cancelReservationLock->seq($seq);

        $cancelReservationLock->year($dateTime[0]);
        $cancelReservationLock->month($dateTime[1]);
        $cancelReservationLock->day($dateTime[2]);
        $cancelReservationLock->hour($dateTime[3]);
        $cancelReservationLock->minute($dateTime[4]);
        $cancelReservationLock->second($dateTime[5]);
        $cancelReservationLock->port($portNumber);
        $cancelReservationLock->cardNumber($card_num);



        $frame = strval($cancelReservationLock);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口取消预约解锁: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口取消预约解锁: sendResult:$sendResult");


    }


    //充电桩通信控制
    public function ControlCommand(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩通信控制 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //桩编号
        $controlCommand = $params['code']; //控制命令

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $controlCommand = new ControlCommand();
        $controlCommand->version($version);
        $controlCommand->control_field($control);
        $controlCommand->division_code($res[0]);
        $controlCommand->terminal_address($res[1]);
        $controlCommand->master_station_address($masterGroupAddress);
        $controlCommand->seq($seq);

        $controlCommand->command($controlCommand);




        $frame = strval($controlCommand);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩通信控制: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩通信控制: sendResult:$sendResult");


    }





    //*******************************查询命令*******************************//

    //充电桩当前时钟
    public function currentTime(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩当前时钟 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $currentTime = new CurrentTime();
        $currentTime->version($version);
        $currentTime->control_field($control);
        $currentTime->division_code($res[0]);
        $currentTime->terminal_address($res[1]);
        $currentTime->master_station_address($masterGroupAddress);
        $currentTime->seq($seq);



        $frame = strval($currentTime);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩当前时钟: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩当前时钟: sendResult:$sendResult");


    }


    //充电桩基本信息查询
    public function getBaseData(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩基本信息查询 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $getBaseData = new GetBaseData();
        $getBaseData->version($version);
        $getBaseData->control_field($control);
        $getBaseData->division_code($res[0]);
        $getBaseData->terminal_address($res[1]);
        $getBaseData->master_station_address($masterGroupAddress);
        $getBaseData->seq($seq);



        $frame = strval($getBaseData);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩当前时钟: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩当前时钟: sendResult:$sendResult");


    }



    //充电桩运行状态查询
    public function getRunStatus(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩运行状态查询 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $getRunStatus = new GetRunStatus();
        $getRunStatus->version($version);
        $getRunStatus->control_field($control);
        $getRunStatus->division_code($res[0]);
        $getRunStatus->terminal_address($res[1]);
        $getRunStatus->master_station_address($masterGroupAddress);
        $getRunStatus->seq($seq);



        $frame = strval($getRunStatus);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩运行状态查询: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩运行状态查询: sendResult:$sendResult");


    }



    //充电桩充电接口状态查询
    public function getPortStatus(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口状态查询 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $getPortStatus = new GetPortStatus();
        $getPortStatus->version($version);
        $getPortStatus->control_field($control);
        $getPortStatus->division_code($res[0]);
        $getPortStatus->terminal_address($res[1]);
        $getPortStatus->master_station_address($masterGroupAddress);
        $getPortStatus->seq($seq);



        $frame = strval($getPortStatus);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口状态查询: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口状态查询: sendResult:$sendResult");


    }




    //电池档案信息查询(BRM)
    public function getBatteryData(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池档案信息查询 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $getBatteryData = new GetBatteryData();
        $getBatteryData->version($version);
        $getBatteryData->control_field($control);
        $getBatteryData->division_code($res[0]);
        $getBatteryData->terminal_address($res[1]);
        $getBatteryData->master_station_address($masterGroupAddress);
        $getBatteryData->seq($seq);



        $frame = strval($getBatteryData);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 电池档案信息查询: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 电池档案信息查询: sendResult:$sendResult");


    }




    //电池充电总状态信息查询
    public function getBatteryChargeStatus(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息查询 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $getBatteryChargeStatus = new GetBatteryChargeStatus();
        $getBatteryChargeStatus->version($version);
        $getBatteryChargeStatus->control_field($control);
        $getBatteryChargeStatus->division_code($res[0]);
        $getBatteryChargeStatus->terminal_address($res[1]);
        $getBatteryChargeStatus->master_station_address($masterGroupAddress);
        $getBatteryChargeStatus->seq($seq);



        $frame = strval($getBatteryChargeStatus);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息查询: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息查询: sendResult:$sendResult");


    }




    //电池温度、单体电压信息上传
    public function getBatteryTemperature(){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池温度、单体电压信息上传 start ");

        $params = $this->request->all();
        $monitorCode = $params['code']; //订单id

        $dateTime = date("Y-m-d-H-i-s", time()); //年月日时分秒
        $dateTime = explode('-',$dateTime);

        $port = Port::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $evseId = $port->evse_id;
        $portNumber = $port->port_number;
        $card_num = $port->card_num;

        $evse = Evse::where('monitor_evse_code',$monitorCode)->firstOrFail();
        $workerId = $evse->worker_id;
        $address = $evse->division_terminal_address;
        $version = $evse->version;
        $masterGroupAddress = $evse->master_group_address;

        //控制域设置
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = $this->controlField($control);
        //sequenceDomain
        $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $seq = $this->sequenceDomain($seq);
        //拆开
        $res = explode('-',$address);
        $getBatteryTemperature = new GetBatteryTemperature();
        $getBatteryTemperature->version($version);
        $getBatteryTemperature->control_field($control);
        $getBatteryTemperature->division_code($res[0]);
        $getBatteryTemperature->terminal_address($res[1]);
        $getBatteryTemperature->master_station_address($masterGroupAddress);
        $getBatteryTemperature->seq($seq);



        $frame = strval($getBatteryTemperature);   //组装帧
        $fra = Tools::asciiStringToHexString($frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 电池温度、单体电压信息上传: frame:$fra");
        $sendResult = EventsApi::sendMsg($workerId, $frame);
        Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 电池温度、单体电压信息上传: sendResult:$sendResult");


    }









    //控制域设置
    public function controlField($control){

        //控制域 保留  启动标志位  传输方向位
        //$control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = new controlField(1, FALSE, $control);
        $controlField = strval($control);
        return $controlField;
    }

    //帧序列域SEQ设置
    public function sequenceDomain($seq){

        //帧序列域SEQ  帧序列号SEQ  CON  FIR  FIN
        //$seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $sequenceDomain = new sequenceDomain(1,FALSE, $seq);
        $sequenceDomain = strval($sequenceDomain);
        return $sequenceDomain;

    }

    //主站地址和组地址标志A3设置
    public function masterStationAddress($address){

        //主站地址和组地址标志A3 group_address  master_address
        //$address = ['group_address'=>0,'master_address'=>13];
        $masterStationAddress = new masterStationAddress(1,FALSE, $address);
        $masterStationAddress = strval($masterStationAddress);
        return $masterStationAddress;

    }



    public function test(){

        //控制域 保留  启动标志位  传输方向位
        $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = new controlField(1, FALSE, $control);
        $controlField = strval($control);

        //帧序列域SEQ  帧序列号SEQ  CON  FIR  FIN
        $seq = ['serial_number'=>1, 'con'=>0, 'fin'=>1, 'fir'=>1];
        $sequenceDomain = new sequenceDomain(1,FALSE, $seq);
        $sequenceDomain = strval($sequenceDomain);

        //主站地址和组地址标志A3 group_address  master_address
        $address = ['group_address'=>0,'master_address'=>127];
        $masterStationAddress = new masterStationAddress(1,FALSE, $address);
        $masterStationAddress = strval($masterStationAddress);


//        $sign = new Sign();
//        $sign->version(intval(1122));
//        $sign->control_field(intval($controlField));
//        $sign->division_code(intval("123321"));
//        $sign->terminal_address(intval(123456));
//        $sign->master_station_address(intval($masterStationAddress));
//        $sign->seq(intval($sequenceDomain));
//        $sign->authPassword(intval(12345));
//        $frame = strval($sign);
//
//        var_dump(bin2hex($frame));die;


        $realTimeState = new RealTimeState();
        $realTimeState->version(intval(1122));
        $realTimeState->control_field(intval($controlField));
        $realTimeState->division_code(intval("123321"));
        $realTimeState->terminal_address(intval(123456));
        $realTimeState->master_station_address(intval($masterStationAddress));
        $realTimeState->seq(intval($sequenceDomain));

        $realTimeState->evseType(1);
        $realTimeState->runStatus(1);
        $realTimeState->maxVoltage(260);
        $realTimeState->maxCurrent(250);
        $realTimeState->astatus(0);
        $realTimeState->bstatus(0);
        $realTimeState->communicationInterface(1);
        $frame = strval($realTimeState);
        var_dump(bin2hex($frame));die;




//        $hearbeat = new Hearbeat();
//        $hearbeat->version(intval(1122));
//        $hearbeat->control_field(intval($controlField));
//        $hearbeat->division_code(intval("110101"));
//        $hearbeat->terminal_address(intval(123456));
//        $hearbeat->master_station_address(intval($masterStationAddress));
//        $hearbeat->seq(intval($sequenceDomain));
//        $frame = strval($hearbeat);

//        $startC = new StartC();
//        $startC->version(intval(1122));
//        $startC->control_field(intval($controlField));
//        $startC->division_code(intval("110"));
//        $startC->terminal_address(intval(123));
//        $startC->master_station_address(intval($masterStationAddress));
//        $startC->seq(intval($sequenceDomain));
//        $startC->portNumber(1);
//        $startC->result(0);
//        $frame = strval($startC);
//        var_dump(bin2hex($frame));die;

//        $stopChargeEvse = new StopChargeEvse();
//        $stopChargeEvse->version(intval(1122));
//        $stopChargeEvse->control_field(intval($controlField));
//        $stopChargeEvse->division_code(intval(110));
//        $stopChargeEvse->terminal_address(intval(123));
//        $stopChargeEvse->master_station_address(intval($masterStationAddress));
//        $stopChargeEvse->seq(intval($sequenceDomain));
//        $stopChargeEvse->portNumber(1);
//        $stopChargeEvse->result(0);
//        $frame = strval($stopChargeEvse);
//        var_dump(bin2hex($frame));die;

        //账单
        $cardStopCharge = new CardStopCharge();
        $cardStopCharge->version(intval(1122));
        $cardStopCharge->control_field(intval($controlField));
        $cardStopCharge->division_code(intval(110));
        $cardStopCharge->terminal_address(intval(123));
        $cardStopCharge->master_station_address(intval($masterStationAddress));
        $cardStopCharge->seq(intval($sequenceDomain));

        $cardStopCharge->portNumber(1);
        $cardStopCharge->cardNumber(1234123);
        $cardStopCharge->consumptionAmount(500);
        $cardStopCharge->balance(1500);
        $cardStopCharge->year(17);
        $cardStopCharge->month(11);
        $cardStopCharge->day(24);
        $cardStopCharge->hour(21);
        $cardStopCharge->minute(4);
        $cardStopCharge->second(26);
        $cardStopCharge->totalChargeDegrees(2500);
        $cardStopCharge->totalMoney(1600);
        $cardStopCharge->totalElectricTip(2800);
        $cardStopCharge->electricTipMoney(2900);
        $cardStopCharge->peakDegree(3000);
        $cardStopCharge->peakDegreeMoney(3100);
        $cardStopCharge->roughnessNumber(3200);
        $cardStopCharge->roughnessNumberMoney(3300);
        $cardStopCharge->grainNumber(3400);
        $cardStopCharge->grainMoney(3500);
        $frame = strval($cardStopCharge);
        var_dump(bin2hex($frame));die;



//        $sign = new PortRealTimeData();
//        $sign->version(1122);
//        $sign->control_field(intval($controlField));
//        $sign->division_code(110);
//        $sign->terminal_address(123);
//        $sign->master_station_address(intval($masterStationAddress));
//        $sign->seq(intval($sequenceDomain));
//        //$sign->authPassword(654321);
//        $sign->year(2017);
//        $sign->month(6);
//        $sign->day(25);
//        $sign->hour(15);
//        $sign->minute(10);
//        $sign->second(32);
//        $sign->portNumber(1);
//        $sign->voltage(2200);
//        $sign->current(43000);
//        $sign->totalElectricity(50000);
//        $sign->rateElectricity1(60000);
//        $sign->rateElectricity2(40000);
//        $sign->rateElectricity3(60000);
//        $sign->rateElectricity4(30000);
//        $sign->ammeterReading(20000);
//
//        $frame = strval($sign);// . strval($upgrade);   //组装帧
//        var_dump(bin2hex($frame));die;


        //$sign = new Sign();
        $sign = new PortRealTimeData();
        $sign->version(1122);
        $sign->control_field(intval($controlField));
        $sign->division_code("110101");
        $sign->terminal_address(123456);
        $sign->master_station_address(intval($masterStationAddress));
        $sign->seq(intval($sequenceDomain));
        //$sign->authPassword(654321);
        $sign->year(2017);
        $sign->month(6);
        $sign->day(25);
        $sign->hour(15);
        $sign->minute(10);
        $sign->second(32);
        $sign->portNumber(1);
        $sign->voltage(22);
        $sign->current(430);
        $sign->totalElectricity(5);
        $sign->rateElectricity1(6);
        $sign->rateElectricity2(4);
        $sign->rateElectricity3(6);
        $sign->rateElectricity4(3);
        $sign->ammeterReading(2);

        $frame = strval($sign);// . strval($upgrade);   //组装帧
        $frame1 = new Frame();
        $frame1 = $frame1($frame);
//var_dump($frame1->control_field->getValue());die;

        var_dump($frame);
        $aa = Tools::asciiStringToHexString($frame);var_dump($aa);

        $sign2 = new PortRealTimeData();
        $frame_load = $sign2($frame);
        var_dump($frame_load->master_station_address->getValue());
        die;


        //$frame = Tools::decArrayToAsciiString(Tools::decToArray(Tools::bcd_compress(123),2,true));
        //echo bin2hex($frame);

//        $upgrade = new ServerUpgradeFileInfo();
//        $upgrade->code(12345678);
//        $upgrade->size(12);             //文件大小
//        $upgrade->packetNumber(111);//数据包总个数
//        $upgrade->packetLength(222);//单包数据长度
//        $upgrade->checkSum(111);                     //文件校验和
//
//        $frame = strval($upgrade);// . strval($upgrade);   //组装帧
//        $le = 0;
//        $aa = Tools::asciiStringToHexString($frame);var_dump($aa);//die;
//        $upgrade2 = new ServerUpgradeFileInfo();
//        $frame_load = $upgrade2($frame);die;


    }









}