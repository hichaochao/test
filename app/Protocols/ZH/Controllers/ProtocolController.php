<?php
namespace Wormhole\Protocols\ZH\Controllers;
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
use Wormhole\Protocols\ZH\Protocol;
use Wormhole\Protocols\CommonTools;
use Wormhole\Protocols\EvseConstant;
use Wormhole\Protocols\ZH\EventsApi;

use Wormhole\Protocols\ZH\Jobs\CheckHeartbeat;
use Wormhole\Protocols\ZH\Models\Evse;
use Wormhole\Protocols\ZH\Models\Port;
use Wormhole\Protocols\ZH\Models\ChargeOrderMapping;
use Wormhole\Protocols\ZH\Models\ChargeRecord;


use Wormhole\Protocols\MonitorServer;
class ProtocolController extends Controller
{
    use CommonTools;
    protected  $workerId ;
    public function __construct($workerId='')
    {
        $this->workerId = $workerId;
    }

    //登陆
    public function signIn($division_code, $terminal_address, $master_station_address, $version, $authPassword){

        Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " division_code:$division_code, 
        terminal_address:$terminal_address, master_station_address:$master_station_address, version:$version, authPassword:$authPassword");

        //桩地址,唯一
        $code = $division_code.'-'.$terminal_address;
        $address = $division_code.$terminal_address;
        $evse = Evse::where('division_terminal_address',$code)->first();

        //未找到桩,创建
        if(empty($evse)){
            Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 未找到充电桩：".$code ." 创建桩,获取monitor编号 ");

            //默认单枪
            $codes = [
                0=>[
                    'code'=>$address,
                    'port'=>0
                ]
            ];
            //获取monitor桩编号
            $portMonitorCodes = MonitorServer::addEvse($codes,MonitorServer::CURRENT_DC);//TODO 默认是直流

            //判断monitor是否返回来桩编号
            if(empty($portMonitorCodes)){
                Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 创建桩,调用monitor,返回数据为空 " );
                return false;
            }
            Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 创建充电桩, 调用monitor， portMonitorCodes:".json_encode($portMonitorCodes));
            //创建桩信息
            $evse = Evse::create([
                'division_terminal_address'=>$code,
                'master_group_address'=>$master_station_address,
                'worker_id'=>$this->workerId,
                'protocol_name'=> \Wormhole\Protocols\ZH\Protocol::NAME,
                'version'=>$version,
                //'port_quantity'=>2,
                'port_type' => MonitorServer::CURRENT_DC, //直流 TODO 默认是直流
                'online'=>1,
                'last_update_status_time'=>date('Y-m-d H:i:s', time()),
                'auth_password'=>$authPassword
            ]);
            Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 创建桩， evse:$evse" );
            if(empty($evse)){
                Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 登录,桩数据创建失败 ");
                return false;
            }
            Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 创建桩成功， id:".
                $evse->id.', division_terminal_address:'.$evse->division_terminal_address );

            //创建枪,单枪
            $port = Port::create([

                'evse_id'=>$evse->id,
                'division_terminal_address'=>$code,
                'port_number'=>1,
                'monitor_evse_code'=>$portMonitorCodes[0][0]//$portMonitorCodes[$i][$evse_port[$i]],
            ]);

            Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 创建枪， port:".json_encode($port) );
            if(empty($port)){
                Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 登录,枪数据创建失败 ");
                return false;
                }

            Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 登录,枪数据创建成功 ");


        }else{

            $evse->worker_id = $this->workerId;
            $evse->last_update_status_time = date('Y-m-d H:i:s', time());
            $evse->auth_password = $authPassword;
            $evse->online = 1;
            $evse = $evse->save();
            if(empty($evse)){
                Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 登录,更新数据失败 ");
                return false;
            }

        }

        //调用monitor
        $port = Port::where('division_terminal_address', $code)->first();
        if(empty($port)){
            Log::debug( __NAMESPACE__ .  "/".__CLASS__ ."/" . __FUNCTION__ . "@" . __LINE__ . " 登陆,未找到枪 ".$code);
            return false;
        }
        //登陆调用monitor
        $signInResult = MonitorServer::updateEvseStatus($port->monitor_evse_code,TRUE,FALSE,TRUE);
        Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 登录,调用monitor结果 $signInResult ");


        return TRUE;
    }





    //桩运行状态实时上传(相当于心跳)
    public function realTimeState($division_code, $terminal_address, $evseType, $runStatus, $maxVoltage, $maxCurrent, $astatus, $bstatus, $communicationInterface){


            $code = $division_code.'-'.$terminal_address;
            //找到桩的id
            $evse = Evse::where('division_terminal_address', $code)->first();
            if(empty($evse)){
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 桩运行状态实时上传,未找到桩id,桩编号为 $code");
                return false;
            }
            $port = Port::where('evse_id', $evse->id)->first();
            if(empty($port)){
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 桩运行状态实时上传, 未找到枪口,或者找到枪口数量不是2, evse_id:".$evse->id);
                return false;
            }
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 桩运行数据 maxVoltage:$maxVoltage, maxCurrent:$maxCurrent");



            //枪口状态在启动中,停止中,再最大等待时间内,不进行操作
            $lastOperatorTime = empty($port->last_operator_time) ? 0 : $port->last_operator_time;
            $portInfoChargeStatus = $port->work_status;
            $passedTime = time()-strtotime($lastOperatorTime);

            if( (1 == $portInfoChargeStatus || 2 == $portInfoChargeStatus  || 4 == $portInfoChargeStatus || 3 == $portInfoChargeStatus || 5 == $portInfoChargeStatus || 6 == $portInfoChargeStatus)  &&  $passedTime < Protocol::MAX_TIMEOUT + 20){
                Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 启动，停止期间，不进行操作");
                return true;
            }


            $monitor_code = $port->monitor_evse_code;
            //充电枪口状态
            $status = array(MonitorServer::WORK_STATUS_FREE,  MonitorServer::WORK_STATUS_FAILURE, MonitorServer::WORK_STATUS_CHARGING, MonitorServer::WORK_STATUS_FREE, MonitorServer::WORK_STATUS_RESERVED);
            $res = array_key_exists($astatus, $status);
            $workStatus = !empty($res) ? $status[$astatus] : 0;


            Log::debug( __NAMESPACE__ ."/".__CLASS__. "/" . __FUNCTION__ . "@" . __LINE__ . " 枪口状态:astatus:$astatus, workStatus:$workStatus ");

            //$workStatus = 3 == $port ? MonitorServer::WORK_STATUS_FREE : 4 == $port ? MonitorServer::WORK_STATUS_RESERVED : $workStatus;
            $updateResponse = MonitorServer::updateEvseStatus($monitor_code,TRUE,$workStatus,TRUE);

            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 桩运行状态实时上传,调用monitor结果 $updateResponse");

            //更新枪口状态
            $port->work_status = $astatus == 2 ? $astatus : 0; //充电状态
            $port->port_status = $astatus == 1 ? $astatus : 0; //枪口状态 0/正常 1/故障
            $port->save();




            $evse->evse_type = $evseType; //桩型号 1/直流单枪 2/直流双枪 3/交流单枪 4/交流双枪
            $evse->run_status = $runStatus; //桩运行状态 1/正常 2/故障 3/离线 4/停运
            $evse->max_voltage = $maxVoltage;  //最大输出电压
            $evse->max_current = $maxCurrent; //最大输出电流
            $evse->communication = $communicationInterface; //与后台系统通信接口
            $evse->last_update_status_time = Carbon::now();
            $evse->save();

            $time = Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT*10);
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 队列执行时间: $time ");

            //增加queue检查心跳
            $job = (new CheckHeartbeat($evse->id))
                ->onQueue(env('APP_KEY'))
                ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT*10));
            dispatch($job);



    }




    //启动充电
    public function startCharge($division_code, $terminal_address, $port_number, $result){

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电处理数据Start ");

        $address = $division_code.'-'.$terminal_address;
        $condition = [
            ['division_terminal_address', '=', $address],
            ['port_number', '>=', $port_number]
        ];

        $port = Port::where($condition)->first();
        if(empty($port)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电响应,未找到数据 address:$address, port_number:$port_number ");
            return false;
        }

        // 启动成功
        if(0 == $result){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动成功 ");
            $port->last_operator_status = 2; //启动成功
            $port->last_operator_time =Carbon::now();
            $port->work_status = 2; //充电中
            $port->start_time = date('Y-m-d H:i:s', time());
            //$port->left_time = 0;
            $port->save();


            //启动成功后,更新关联状态未成功
            $condition = [
                ['division_terminal_address', '=', $address],
                ['order_id', '>=', $port->order_id]
            ];
            $orderMap =  ChargeOrderMapping::where($condition)->first();
			Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动成功,更新映射表, address:$address, orderId: ".$port->order_id);
            if(!is_null($orderMap)){
                $orderMap->is_start_success = TRUE;
                $orderMap->save();
            }


            //启动成功调用monitor
            $result = MonitorServer::startCharge($port->order_id,0 == $result);
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动成功！调用monitor result:$result ");
            return true;


        }else{ //启动失败
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动失败！");
            $port->work_status = 3;
            $port->last_operator_status = 3; //启动失败
            $port->last_operator_time =Carbon::now();
            $port->order_id="";
            $port->start_type = 0;
            $port->user_balance = 0;
            $port->save();

            //启动失败调用monitor
            $result = MonitorServer::startCharge($port->order_id,0 == $result);
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动失败！调用monitor result:$result");
            return true;
        }



    }

    //停止充电
    public function stopCharge($division_code, $terminal_address, $port_number, $result){

        $address = $division_code.'-'.$terminal_address;
        $condition = [
            ['division_terminal_address', '=', $address],
            ['port_number', '>=', $port_number]
        ];

        $port = Port::where($condition)->first();
        if(empty($port)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电响应,未找到数据 address:$address, port_number:$port_number ");
            return false;
        }

        //订单号
        $order_id = $port->order_id;

        if(0 == $result){ // 停止成功
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电成功 ");
            //$port->order_id="";
            $port->start_type = 0;
            $port->user_balance = 0;
            $port->output_voltage = 0;
            $port->output_current = 0;
            $port->total_power = 0;
            $port->rate_one_power = 0;
            $port->rate_two_power = 0;
            $port->rate_three_power = 0;
            $port->rate_four_power = 0;
            $port->last_operator_time = Carbon::now();
            $port->last_operator_status = 5;

            $port->work_status = 6; //停止成功
            $port->stop_date = Carbon::now();
            //$port->left_time = 0;
            $port->save();

        }else{
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电失败 ");
            $port->last_operator_time = Carbon::now();
            $port->last_operator_status = 6;
            //$port->work_status = 2;
            $port->save();

        }

        //调用停止成功
        $result = MonitorServer::stopCharge($order_id,0==$result);
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电调用monitor结果,result: $result ");

        return $result;

    }


    //账单上报处理
    public  function uploadChargeInfo($division_code, $terminal_address, $master_station_address, $consumptionAmount, $balance, $portNumber, $year,$month,$day,$hour,$minute,$second, $totalChargeDegrees,$totalMoney, $message, $offline=FALSE){

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报Start, ".Carbon::now() );


        $order = ChargeOrderMapping::where([
            [
                "division_terminal_address",$division_code.'-'.$terminal_address
            ],
            [
                "port_number",$portNumber
            ],
            [
                "is_start_success",TRUE
            ]
        ])
            ->orderBy('created_at','desc')
            ->first();

        if(empty($order)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 未找到订单表数据 division_terminal_address:$division_code.$terminal_address, 
             port_number:$portNumber");
            return false;
        }


        $evse = Evse::where("division_terminal_address",$division_code.'-'.$terminal_address)->first();
        if(empty($evse)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报未找到相应桩数据,桩编号 $division_code.$terminal_address ");
            return false;
        }
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报找到桩数据,桩编号 $division_code.$terminal_address, evse_id: ".$evse->id." portNumber:$portNumber");

        $condition = [
            ['evse_id', '=', $evse->id],
            ['port_number', '>=', $portNumber]
        ];
        $port = Port::where($condition)->first();
        if(empty($port)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报未找到相应枪数据,桩编号 evseId: ".$evse->id." portNumber:$portNumber");
            return false;
        }
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报找到枪数据,桩编号code: $division_code.$terminal_address, portNumber:$portNumber ");

        //开始充电时间和结束充电时间
        $startTime = strtotime($port->start_time); //开始时间
        $stopTime = strtotime($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second); //停止时间
        if($startTime > $stopTime){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报, 开始时间大于停止时间: $startTime, 结束充电时间: $stopTime");
            return false;
        }
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报,开始充电时间: $startTime, 结束充电时间: $stopTime");



        //查询是否已经存储过
        $chargeRecord = ChargeRecord::where('order_id',$order->order_id)->first();

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报,查看是否已存储充电记录 ");

        $chargeRecordRes = 0;
        $formattedPower = [];
        if(is_null($chargeRecord)){ //没有存储过
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报,没有存储充电记录 ");

            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报, startTime:$startTime, stopTime:$stopTime, totalChargeDegrees:$totalChargeDegrees");

            $formattedPower = $this->formatPower($startTime,$stopTime,$totalChargeDegrees);

            $data =[
                'evse_id'=>$port->evse_id,
                'division_terminal_address'=>$port->division_terminal_address,
                'monitor_code'=>$port->monitor_evse_code,
                'port_id'=>$port->id,
                'port_number'=>$port->port_number,
                //'port_type' => $port->port_type,
                'order_id'=>$port->order_id,
                'evse_order_id'=>$port->card_num,
                'charge_type'=>$port->start_type,
                'start_time'=>Carbon::createFromTimestamp( $startTime),
                'end_time'=>Carbon::createFromTimestamp($stopTime),
                'charged_power'=>$totalChargeDegrees,
                'duration'=>$stopTime-$startTime,
                'fee' =>$totalMoney,
                'formatted_power'=>json_encode($formattedPower),
                'card_id'=>$port->card_num,
                //'is_billing'=>$port->is_billing,
                'start_type'=>$port->start_type,
                'charged_fee'=>$consumptionAmount, //消费金额
                'card_balance_before'=>$balance, //余额

                //'start_args'=>$port->start_args,
                //'charge_type'=>$port->charge_type,
                //'charge_args'=>$port->charge_args,
            ];



            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Save charge record".json_encode($data));
            $chargeRecordRes = ChargeRecord::create($data);
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报,存储充电记录成功 chargeRecord:$chargeRecordRes");
            if(empty($chargeRecordRes)){
                Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报,存储充电记录失败 chargeRecord:$chargeRecordRes");
                return false;
            }
        }


        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报保存充电记录 formattedPower:".json_encode($formattedPower));
        if(!empty($chargeRecordRes)){

            $pushResult = MonitorServer::uploadChargeRecord($order->order_id, $startTime, $stopTime,
                $totalChargeDegrees,$formattedPower,FALSE,4,
                $stopTime-$startTime,$consumptionAmount);

            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上调用monitor pushResult:$pushResult,
            orderId:".$order->order_id."startTime:$startTime, stopTime:$stopTime, totalChargeDegrees:$totalChargeDegrees,
             formattedPower:".json_encode($formattedPower)." time: $stopTime-$startTime, consumptionAmount:$consumptionAmount");

            $res = $order->delete();
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 账单上报,清除映射表结果 $res ");

            if(FALSE == $pushResult){
                return FALSE;
            }

            return TRUE;
        }

        return FALSE;
    }




    //实时充电数据
    public function realTimeData($division_code, $terminal_address, $data, $current_time){

        $address = $division_code.'-'.$terminal_address;
        $condition = [
            ['division_terminal_address', '=', $address],
            ['port_number', '>=', $data['portNumber']]
        ];

        $port = Port::where($condition)->first();
        if(empty($port)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 实时充电数据响应,未找到数据 address:$address, port_number: ".$data['portNumber']);
            return false;
        }

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 实时充电数据响应 address:$address, port_number: ".$data['portNumber']);

        //$port->real_time_data = $data['year'].'-'.$data['month'].'-'.$data['day'].' '.$data['hour'].':'.$data['minute'].':'.$data['second'];
        $port->total_power = $data['totalElectricity']; //枪口总电量
        $port->real_time_data = $current_time; //实时数上传时间

        $port->output_voltage = $data['voltage']; //电压
        $port->output_current = $data['current']; //电流

        $port->rate_one_power = $data['rateElectricity1'];
        $port->rate_two_power = $data['rateElectricity2'];
        $port->rate_three_power = $data['rateElectricity3'];
        $port->rate_four_power = $data['rateElectricity4'];
        $port->ammeter_degree = $data['ammeterRead'];

        $res = $port->save();
        if(empty($res)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 实时充电数据存储失败 ");
            return false;
        }
        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 实时充电数据存储结果 $res");

        return true;

    }



    //心跳
    public function hearbeat($division_code, $terminal_address){

        Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 心跳处理数据Start");

        $address = $division_code.'-'.$terminal_address;
        $condition = [
            ['division_terminal_address', '=', $address]
        ];


        $evse = Evse::where($condition)->first();
        if(empty($evse)){
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 心跳响应,未找到数据 address:$address ");
            return false;
        }



        //更新联网时间
        //$evse->last_update_status_time = date('Y-m-d H:i:s', time());
        //$evse->worker_id = $this->workerId;
        //$evse->save();





    }








}