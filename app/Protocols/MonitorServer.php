<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-01-09
 * Time: 18:18
 */

namespace Wormhole\Protocols;

use Illuminate\Support\Facades\Config;
use Kozz\Laravel\Facades\Guzzle;
use Illuminate\Support\Facades\Log;

class MonitorServer
{
    /**
     * 工作状态-空闲中
     */
    const WORK_STATUS_FREE=0;
    /**
     * 工作状态-充电中
     */
    const WORK_STATUS_CHARGING=1;
    /**
     * 工作状态-故障中
     */
    const WORK_STATUS_FAILURE=3;
    /**
     * 工作状态-已预约
     */
    const WORK_STATUS_RESERVED=4;
    /**
     * 交流电
     */
    const CURRENT_AC = 2;
    /**
     * 直流电
     */
    const CURRENT_DC=1;

    /**
     * 告警：其他
     */
    const ALARM_OTHER = 1;
    /**
     * 告警：急停
     */
    const ALARM_EMERGENCY_STOP = 2;
    /**
     * 告警：过压
     */
    const ALARM_OVERPRESSURE = 3;
    /**
     * 告警：欠压
     */
    const ALARM_UNDERVOLTAGE = 4;
    /**
     * 告警：过流
     */
    const ALARM_OVERCURRENT = 5;




    public static function validateControlToken($token){
        $api = Config::get('monitor.validateTokenAPI');
        $server =Config::get('monitor.host');

        $url = $server.$api;
        $data =[
                    "access_token"=>$token
        ];

        $data = self::request($url,$data);

        if(FALSE === $data){
            return FALSE;
        }
        return $data['evse_code'];
    }

    /**
     * 添加充电桩
     * @param array $codes
     * [
     *  0=>[
     *  "code"=>"ni",
     *  "port" =>1
     * ]
     * ]
     * @param $currentType int 1、交流；2、直流
     * @return array|bool [port=>monitor_code]
     */
    public static function addEvse(array $codes, $currentType){
        $api = Config::get('monitor.createEvseBatchAPI');
        $server =Config::get('monitor.host');

        $protocol = Config::get("gateway.gateway.protocol");


        $platformName = Config::get("gateway.platform_name");
        $platformIP = Config::get("gateway.host");   //host
        $platformPort = Config::get("gateway.port");  //port

        $url = $server.$api;
        $params =[
            "evse_type"=>$currentType,
            "protocol_name"=> $protocol::NAME,
            "platform_short_name"=>$platformName,
            "protocol_server_ip"=>$platformIP,
            "protocol_server_port"=>$platformPort,
            "self_evse_codes"=>$codes,
        ];

        $data = self::request($url,$params);

        if(FALSE == $data || count($data['evse_codes']) != count($codes)){
            return FALSE;
        }


        return $data['evse_codes'];

        ////todo 测试用
        //return [[0=>'NJINT001']];


    }



    /**
     * 更新充电桩状态
     * @param string $monitorCode monitor充电桩编号
     * @param bool $isOnline 是否联网 true：联网；false：未联网
     * @param int $workStatus MonitorServer const
     * @param bool $isConnected 充电口是否已经链接车，0 未链接；1 已链接
     * @return bool
     */
    public static function updateEvseStatus($monitorCode, $isOnline, $workStatus =-1, $isConnected=-1){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " start, monitorCode:$monitorCode,isOnline:$isOnline,chargeStatus:$workStatus,isConnected:$isConnected");
        $api = Config::get('monitor.updateEvseStatusAPI');
        $server =Config::get('monitor.host');

        $url = $server.$api;
        $data =[
            "evse_code"=>$monitorCode,
            "net_status"=>intval($isOnline),
        ];
        $data = -1 === $workStatus ? $data : array_merge($data,["charge_status" =>intval($workStatus)]);
        $data = -1 === $isConnected ? $data: array_merge($data,["car_conn_status"=>intval($isConnected)]);

        $data = self::request($url,$data);

        if(FALSE === $data){
            return FALSE;
        }
        return TRUE;
    }


    /**
     * @param $orderId
     * @param bool $result
     * @return bool
     */
    public static  function startCharge($orderId,$result){
        $server = Config::get('monitor.host');
        $api =  TRUE == $result ? Config::get('monitor.startChargeSuccess'): Config::get('monitor.startChargeFailed');
        $url = $server.$api;

        $data=["order_id"=>$orderId];

        $data = self::request($url,$data);

        if(FALSE === $data){
            return FALSE;
        }



        return TRUE;
    }

    public static function stopCharge($orderId, $stopped){

        $server = Config::get('monitor.host');

        $api =  TRUE == $stopped ? Config::get('monitor.stopChargeSuccess'): Config::get('monitor.stopChargeFailed');

        $url = $server.$api;
        $data=["order_id"=>$orderId];

        $data = self::request($url,$data);

        if(FALSE === $data){
            return FALSE;
        }



        return TRUE;

    }

    /**
     * 充电记录上报
     * @param $orderId
     * @param $startTime
     * @param $stopTime
     * @param float $chargedPower 充电总电量，单位：度
     * @param array $formattedPowerList
     * [[ 'time'=>time(),'power'=>0,'duration'=>30 ]]
     * @param bool $isCard 是否刷卡充电
     * @param int $portStartType 充电口启动来源：（1、刷卡；2、即插即用（热插拔）；3、本地管理员；4、后台（通过monitor系统启动））
     * @param int $duration   充电时长，单位：s
     * @param float $chargedFee 充电金额，单位：元
     * @return bool
     */
    public static function uploadChargeRecord($orderId, $startTime, $stopTime, $chargedPower, $formattedPowerList, $isCard =  FALSE, $portStartType=4, $duration=0, $chargedFee=0){
        $server = Config::get('monitor.host');
        $api = FALSE == $isCard ?  Config::get('monitor.newChargeRecord') : Config::get('monitor.newChargeRecordHotSwap') ;
        $url = $server.$api;

        $data = [
                'order_id'=>$orderId,
                'start_time'=>$startTime,
                'end_time'=>$stopTime,
                'total_power'=>$chargedPower,
                'start_type'=>$portStartType,
                'duration'=>$duration,
                'amount'=>$chargedFee,
                'power_list'=>$formattedPowerList

        ];

        $data = self::request($url,$data);
        if(FALSE === $data){
            return FALSE;
        }

        return TRUE;
    }

    /**
     * 刷卡启动事件
     * @param $cardNo
     * @param $monitorCode
     * @return array|bool
     */
    public static function swipeCardEvent($cardNo,$monitorCode){
        $server = Config::get('monitor.host');
        $api =   Config::get('monitor.swipeCardEvent');
        $url = $server.$api;

        $data=[
            'evse_code'=>$monitorCode,
            'card_no'=>$cardNo,
        ];

        $data = self::request($url,$data);
        if(FALSE === $data){
            return FALSE;
        }

        return $data['data']['order_id'];


    }

    /**
     * 刷卡鉴权后，后台启动充电
     * @param $cardNo
     * @param $monitorCode
     * @return array|bool
     */
    public static function swipeCardToStartCharge($cardNo,$monitorCode){
        $server = Config::get('monitor.host');
        $api =   Config::get('monitor.swipeCardToStartCharge');
        $url = $server.$api;

        $data=[
            'evse_code'=>$monitorCode,
            'card_no'=>$cardNo,
        ];

        $data = self::request($url,$data);
        if(FALSE === $data){
            return FALSE;
        }

        return $data['data'];
    }

    /**
     * 告警信息上报
     * @param $monitorCode
     * @param int $alarmType 告警类别： 1：未知；2：急停；3：过压；4：欠压；5：过流；
     * @param $comment
     * @return bool
     */

    public static function newAlarm($monitorCode,$alarmType,$comment){
        $server = Config::get('monitor.host');
        $api =   Config::get('monitor.newAlarm');
        $url = $server.$api;

        $data=[
            'evse_code'=>$monitorCode,
            'alarm_type'=>$alarmType,
            'alarm_comment'=>$comment
        ];

        $data = self::request($url,$data);
        if(FALSE === $data){
            return FALSE;
        }

        return TRUE;

    }



    //升级状态接口
    public static function update_evse_upgrade_type($task_id,$code,$status){

        $server = Config::get('monitor.host');
        $api =   Config::get('monitor.upgradeStatus');
        $url = $server.$api;

        $data=[
            'upgrade_task_id'=>$task_id,
            'evse_code'=>$code,
            'upgrade_type'=>$status
        ];

        $data = self::request($url,$data);
        if(FALSE === $data){
            return FALSE;
        }

        return TRUE;

    }


    public static function get($url){


        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " request monitor url:$url");


        $reponse = Guzzle::get($url);

        if($reponse->getStatusCode() == 200){
            $data = json_decode($reponse->getBody(),TRUE);
            //Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " ". $reponse->getBody());
            
            if(TRUE == $data['status']){
                if(empty($data['data'])){
                    return TRUE;
                }
                return $data['data'];
            }
        }

        return FALSE;

    }





    private static function request($url,$data,$method="POST"){
        $data = ["params"=>$data];

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " request monitor url:$url data:".json_encode($data));

        $request = [
            "headers"=>[
                'Content-Type'=>'application/json'
            ],
            "body"=> json_encode($data)

        ];
        $reponse = Guzzle::post($url,$request);

        if($reponse->getStatusCode() == 200){
            $data = json_decode($reponse->getBody(),TRUE);
            Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " ". $reponse->getBody());
            if(TRUE == $data['status']){
                if(empty($data['data'])){
                    return TRUE;
                }
                return $data['data'];
            }
        }

        return FALSE;

    }


}