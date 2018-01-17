<?php
namespace Wormhole\Protocols\ZH;
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\BaseEvents;
use Wormhole\Protocols\ZH\Controllers\ProtocolController;
use Wormhole\Protocols\ZH\Protocol\Frame;
use Wormhole\Protocols\Tools;
use Carbon\Carbon;

use Wormhole\Protocols\ZH\Protocol\controlField;
use Wormhole\Protocols\ZH\Protocol\sequenceDomain;
use Wormhole\Protocols\ZH\Protocol\masterStationAddress;
use Wormhole\Protocols\ZH\Models\Evse;
use Wormhole\Protocols\ZH\Models\Port;

use Wormhole\Protocols\ZH\Protocol\Evse\Sign;
use Wormhole\Protocols\ZH\Protocol\Evse\SignOut;
use Wormhole\Protocols\ZH\Protocol\Evse\Hearbeat;
use Wormhole\Protocols\ZH\Protocol\Evse\Restart;
use Wormhole\Protocols\ZH\Protocol\Evse\StartCharge;
use Wormhole\Protocols\ZH\Protocol\Evse\StopCharge;
use Wormhole\Protocols\ZH\Protocol\Evse\PortRealTimeData;
use Wormhole\Protocols\ZH\Protocol\Evse\Unlock;
use Wormhole\Protocols\ZH\Protocol\Evse\CalibratTime;
use Wormhole\Protocols\ZH\Protocol\Evse\ReservationLock;
use Wormhole\Protocols\ZH\Protocol\Evse\CancelReservationLock;
use Wormhole\Protocols\ZH\Protocol\Evse\PortStatuChange;
use Wormhole\Protocols\ZH\Protocol\Evse\ExceptionInformation;
use Wormhole\Protocols\ZH\Protocol\Evse\BMSExceptionInformation;
use Wormhole\Protocols\ZH\Protocol\Evse\ModuleExceptionInformation;
use Wormhole\Protocols\ZH\Protocol\Evse\ParameterChange;
use Wormhole\Protocols\ZH\Protocol\Evse\StopChargeReason;
use Wormhole\Protocols\ZH\Protocol\Evse\RealTimeState;
use Wormhole\Protocols\ZH\Protocol\Evse\BatteryData;
use Wormhole\Protocols\ZH\Protocol\Evse\BatteryStatuData;
use Wormhole\Protocols\ZH\Protocol\Evse\BatteryTemperatureData;
use Wormhole\Protocols\ZH\Protocol\Evse\SetInformation;
use Wormhole\Protocols\ZH\Protocol\Evse\GetBasicParameter;
use Wormhole\Protocols\ZH\Protocol\Evse\GetPwmDutyRatio;
use Wormhole\Protocols\ZH\Protocol\Evse\GetChargeDataFrequency;
use Wormhole\Protocols\ZH\Protocol\Evse\GetStateFrequency;
use Wormhole\Protocols\ZH\Protocol\Evse\GetRate;
use Wormhole\Protocols\ZH\Protocol\Evse\GetIpDomain;

use Wormhole\Protocols\ZH\Protocol\Evse\GetCalibratTime;
use Wormhole\Protocols\ZH\Protocol\Evse\GetBaseData;
use Wormhole\Protocols\ZH\Protocol\Evse\GetRealTimeState;
use Wormhole\Protocols\ZH\Protocol\Evse\GetPortRealTimeData;
use Wormhole\Protocols\ZH\Protocol\Evse\GetBatteryData;
use Wormhole\Protocols\ZH\Protocol\Evse\GetBatteryStatuData;
use Wormhole\Protocols\ZH\Protocol\Evse\GetBatteryTemperatureData;

use Wormhole\Protocols\ZH\Protocol\Evse\PayCard;
use Wormhole\Protocols\ZH\Protocol\Evse\CardStartCharge;
use Wormhole\Protocols\ZH\Protocol\Evse\CardStopCharge;
use Wormhole\Protocols\ZH\Protocol\Evse\RetransmissionData;
use Wormhole\Protocols\ZH\Protocol\Evse\Recharge;

use Wormhole\Protocols\ZH\Protocol\Server\PayCard as ServerPayCard;
use Wormhole\Protocols\ZH\Protocol\Server\CardStartCharge as ServerCardStartCharge;
use Wormhole\Protocols\ZH\Protocol\Server\CardStopCharge as ServerCardStopCharge;
use Wormhole\Protocols\ZH\Protocol\Server\RetransmissionData as ServerRetransmissionData;
use Wormhole\Protocols\ZH\Protocol\Server\Recharge as ServerRecharge;



use Wormhole\Protocols\ZH\Protocol\Server\Confirm;









/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class EventsApi extends BaseEvents
{
    public static $client_id = '';
    public static $controller;
    /**
     * 当客户端发来消息时触发
     * @param string $client_id 连接id
     * @param mixed $message 具体消息
     * @return bool
     */
    public static function message($client_id, $message)
    {

        self::$client_id = $client_id;
        self::$controller = new ProtocolController($client_id);
        Log::debug(__NAMESPACE__ . "\\".__CLASS__ ."\\" . __FUNCTION__ . "@" . __LINE__ . "  client_id:$client_id, message:" . bin2hex($message));

        //升级帧解析
        $frame = new Frame();
        $frame = $frame($message);
        if(empty($frame)){
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 帧无效 ");
            return false;
        }
        $afn = $frame->afn->getValue(); //功能吗
        $fn = $frame->fn->getValue();  //单元标识

        if(!empty($frame)){ //|| $frame->isValid == 1

            switch ($afn.' '.$fn ){
                //*******************************链路检测*******************************//
                case (0x01 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 登录 ");
                    self::Sign($message);
                    break;
                case (0x01 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 退出 ");
                    self::SignOut($message);
                    break;
                case (0x01 . ' ' . 0x03):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 心跳 ");
                    self::Hearbeat($message);
                    break;
                case (0x01 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 注册 ");
                    self::Register($message);
                    break;

                //*******************************设置参数*******************************//
                case (0x02 . ' ' . $fn):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 设置参数 ");
                    self::SetParameters($message);
                    break;

                //*******************************查询参数*******************************//
                case (0x03 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 服务器IP地址和端口 ");
                    self::getIpDomain($message);
                    break;
                case (0x03 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电费率 ");
                    self::getRate($message);
                    break;
                case (0x03 . ' ' . 0x03):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩状态上传频率 ");
                    self::getStateFrequency($message);
                    break;
                case (0x03 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩充电数据上传频率 ");
                    self::getChargeDataFrequency($message);
                    break;
                case (0x03 . ' ' . 0x05):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " PWM 占空比 ");
                    self::getPwmDutyRatio($message);
                    break;
                case (0x03 . ' ' . 0x06):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 模块基本参数 ");
                    self::getBasicParameter($message);
                    break;

                //*******************************事件主动上报*******************************//
                case (0x05 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩充电端口状态变更 ");
                    self::PortStatuChange($message);
                    break;
                case (0x05 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩异常信息 ");
                    self::ExceptionInformation($message);
                    break;
                case (0x05 . ' ' . 0x03):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " BMS 异常信息 ");
                    self::BMSExceptionInformation($message);
                    break;
                case (0x05 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 整流模块异常信息 ");
                    self::ModuleExceptionInformation($message);
                    break;
                case (0x05 . ' ' . 0x05):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 参数变更 ");
                    self::ParameterChange($message);
                    break;
                case (0x05 . ' ' . 0x06):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 停止充电原因 ");
                    self::StopChargeReason($message);
                    break;


                //*******************************数据周期上传*******************************//
                case (0x06 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩运行状态实时上传  ");
                    self::RealTimeState($message);
                    break;
                case (0x06 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩充电接口实时上传数据 ");
                    self::RealTimeData($message);
                    break;
                case (0x06 . ' ' . 0x03):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 电池档案信息上传(BMS 辨识报文 BRM) ");
                    self::BatteryData($message);
                    break;
                case (0x06 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 电池充电总状态信息上传 ");
                    self::BatteryStatuData($message);
                    break;
                case (0x06 . ' ' . 0x05):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 电池温度、单体电压信息上传 ");
                    self::BatteryTemperatureData($message);
                    break;


                //*******************************查询命令*******************************//
                case (0x07 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩当前时钟 ");
                    self::GetCalibratTime($message);
                    break;
                case (0x07 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩基本信息查询 ");
                    self::GetBaseData($message);
                    break;
                case (0x07 . ' ' . 0x03):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩运行状态查询  ");
                    self::GetRealTimeState($message);
                    break;
                case (0x07 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩充电接口状态查询 ");
                    self::GetPortRealTimeData($message);
                    break;
                case (0x07 . ' ' . 0x05):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 电池档案信息查询(BRM) ");
                    self::GetBatteryData($message);
                    break;
                case (0x07 . ' ' . 0x06):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 电池充电总状态信息查询 ");
                    self::GetBatteryStatuData($message);
                    break;
                case (0x07 . ' ' . 0x07):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 电池温度、单体电压信息上传 ");
                    self::GetBatteryTemperatureData($message);
                    break;



                //*******************************刷卡充电流程控制*******************************//
                case (0x08 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 终端刷卡 ");
                    self::PayCard($message);
                    break;
                case (0x08 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 启动充电 ");
                    self::CardStartCharge($message);
                    break;
                case (0x08 . ' ' . 0x03):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 结束充电,账单 ");
                    //Log::debug(__NAMESPACE__ . "\\".__CLASS__ ."\\" . __FUNCTION__ . "@" . __LINE__ . "  client_id:$client_id, message:" . bin2hex($message));
                    self::CardStopCharge($message);
                    break;
                case (0x08 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电设备断网时充电数据重传 ");
                    self::RetransmissionData($message);
                    break;
                case (0x08 . ' ' . 0x05):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电卡充值 ");
                    self::Recharge($message);
                    break;




                //*******************************控制命令*******************************//
                case (0x04 . ' ' . 0x01):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩重启 ");
                    self::Restart($message);
                    break;
                case (0x04 . ' ' . 0x02):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电卡解锁 ");
                    self::unlock($message);
                    break;
                case (0x04 . ' ' . 0x03):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 启动充电 ");
                    self::StartCharge($message);
                    break;
                case (0x04 . ' ' . 0x04):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 停止充电 ");
                    self::StopCharge($message);
                    break;
                case (0x04 . ' ' . 0x05):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 对时命令 ");
                    self::CalibratTime($message);
                    break;
                case (0x04 . ' ' . 0x06):
                    Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩充电接口预约锁定 ");
                    self::ReservationLock($message);
                    break;
                default:
                    $result = true;

            }
        }

    }



        //*******************************主动上报*******************************//
        //登录
        private static function Sign($message){


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 登录 start ".Carbon::now());
            //解析帧
            $sign = new Sign();
            $frame_load = $sign($message);
            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ

            $authPassword = $frame_load->authPassword->getValue(); //认证密码


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 认证密码, authPassword:$authPassword, version:$version, 
            ontrol_field:$control_field, division_code:$division_code, terminal_address:$terminal_address, 
            master_station_address:$master_station_address, seq:$seq ");


            //处理数据
            $result = self::$controller->signIn($division_code, $terminal_address, $master_station_address, $version, $authPassword);
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 登录处理数据结果 result:$result ");

            //如果数据处理失败则不给应答
            if(empty($result)){
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 登录处理数据失败 ");
                return false;
            }


            $confirm = new Confirm();
            $confirm->version(intval($version));
            $confirm->control_field(intval($control_field));
            $confirm->division_code(intval($division_code));
            $confirm->terminal_address(intval($terminal_address));
            $confirm->master_station_address(intval($master_station_address));
            $confirm->seq(intval($seq));


            $frame = strval($confirm);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 登录: frame:$fra");
            $sendResult = EventsApi::sendMsg(self::$client_id, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 登录: sendResult:$sendResult");


        }


        //充电桩运行状态实时上传(相当于心跳)
        public static function RealTimeState($message){

            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩运行状态实时上传 start ".Carbon::now());

            //解析帧
            $realTimeState = new RealTimeState();
            $frame_load = $realTimeState($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ


            $evseType = $frame_load->evseType->getValue(); //充电桩型号
            $runStatus = $frame_load->runStatus->getValue(); //充电桩运行状态
            $maxVoltage = $frame_load->maxVoltage->getValue() / 10; //充电桩最大输出电压
            $maxCurrent = $frame_load->maxCurrent->getValue() / 10; //充电桩最大输出电流
            $astatus = $frame_load->astatus->getValue(); //充电接口A状态
            $bstatus = $frame_load->bstatus->getValue(); //充电接口B状态
            $communicationInterface = $frame_load->communicationInterface->getValue(); //与后台系统通信接口


            $realTimeState = self::$controller->realTimeState($division_code, $terminal_address, $evseType, $runStatus, $maxVoltage, $maxCurrent, $astatus, $bstatus, $communicationInterface);



            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩运行状态实时上传: 
                evseType:$evseType, runStatus:$runStatus "."maxVoltage:".$maxVoltage.
                ' maxCurrent:'.$maxCurrent.' astatus:'.$astatus.' bstatus:'.$bstatus.
                ' communicationInterface:'.$communicationInterface);


        }



    //启动充电
    public static function StartCharge($message){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电收到响应 ".Carbon::now());

        //解析帧
        $start_charge = new StartCharge();
        $frame_load = $start_charge($message);

        //接收数据
        $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
        $division_code = $frame_load->division_code->getValue(); //行政区划码
        $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
        $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
        $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ

        $port_number = $frame_load->portNumber->getValue(); //充电接口标识
        $result = $frame_load->result->getValue(); //成功标识 0成功 1失败
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩启动充电结果: port_number:$port_number, 
            result:$result, control_field:$control_field, division_code:$division_code, result:$result ");
        //处理数据
        $result = self::$controller->startCharge($division_code, $terminal_address, $port_number, $result);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩启动充电结果: port_number:$port_number, result:$result, control_field:$control_field, division_code:$division_code ");

    }


    //充电桩充电接口实时上传数据(实时数据)
    public static function RealTimeData($message){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口实时上传数据 start ".Carbon::now());
        $data = [];
        //解析帧
        $portRealTimeData = new PortRealTimeData();
        $frame_load = $portRealTimeData($message);

        //接收数据
        $version = $frame_load->version->getValue(); //版本号
        $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
        $division_code = $frame_load->division_code->getValue(); //行政区划码
        $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
        $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
        $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ

        $year = $frame_load->year->getValue(); //年
        $month = $frame_load->month->getValue(); //月
        $day = $frame_load->day->getValue(); //日
        $hour = $frame_load->hour->getValue(); //时
        $minute = $frame_load->minute->getValue(); //分
        $second = $frame_load->second->getValue(); //秒
        $current_time = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;


        $data['portNumber'] = $frame_load->portNumber->getValue(); //枪口号
        $data['voltage'] = round($frame_load->voltage->getValue() / 10); //电压
        $data['current'] = round($frame_load->current->getValue() / 10 - 400); //电流
        $data['totalElectricity'] = round($frame_load->totalElectricity->getValue() / 100); //总电量
        $data['rateElectricity1'] = round($frame_load->rateElectricity1->getValue() / 100); //充电接口费率1电量
        $data['rateElectricity2'] = round($frame_load->rateElectricity2->getValue() / 100); //充电接口费率2电量
        $data['rateElectricity3'] = round($frame_load->rateElectricity3->getValue() / 100); //充电接口费率3电量
        $data['rateElectricity4'] = round( $frame_load->rateElectricity4->getValue() / 100); //充电接口费率4电量
        $data['ammeterRead'] = round($frame_load->ammeterReading->getValue() / 100); //电能表读数



        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口实时上传数据 ,control_field:$control_field, division_code:$division_code 
            
            year:".$year.' month:'.$month.' day:'.$day.' hour:'.$hour.' minute:'.$minute.' second:'.$second.' portNumber:'.
            $data['portNumber'].' voltage:'.$data['voltage'].' current:'.$data['current'].' totalElectricity:'.$data['totalElectricity'].' rateElectricity1:'.
            $data['rateElectricity1'].' rateElectricity2:'.$data['rateElectricity2'].' rateElectricity3:'.$data['rateElectricity3'].' rateElectricity4:'.
            $data['rateElectricity4'].' ammeterRead:'.$data['ammeterRead']);


        //处理数据
        $result = self::$controller->realTimeData($division_code, $terminal_address, $data, $current_time);



    }






    //停止充电
    public static function StopCharge($message){

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电start ".Carbon::now());

        //解析帧
        $stop_charge = new StopCharge();
        $frame_load = $stop_charge($message);

        //接收数据
        $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
        $division_code = $frame_load->division_code->getValue(); //行政区划码
        $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
        $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
        $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ

        $port_number = $frame_load->portNumber->getValue(); //充电接口标识
        $result = $frame_load->result->getValue(); //成功标识 0成功 1失败

        //处理数据
        $result = self::$controller->stopCharge($division_code, $terminal_address, $port_number, $result);

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩停止充电结果: port_number:$port_number, result:$result ");

    }








        //退出
        private static function SignOut($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 退出start ".$date);

            //解析帧
            $signOut = new SignOut();
            $frame_load = $signOut($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ



            //应答充电桩
            $divisionTerminalAddress = $division_code.'-'.$terminal_address;
            $evse = Evse::where('division_terminal_address',$divisionTerminalAddress)->firstOrFail();
            $workerId = $evse->worker_id;

            //控制域设置
            $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
            $control = self::controlField($control);
            //sequenceDomain
            $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
            $seq = self::sequenceDomain($seq);
            //拆开
            $res = explode('-',$divisionTerminalAddress);
            $confirm = new Confirm();
            $confirm->version($version);
            $confirm->control_field($control);
            $confirm->division_code($res[0]);
            $confirm->terminal_address($res[1]);
            $confirm->master_station_address($master_station_address);
            $confirm->seq($seq);


            $frame = strval($confirm);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 退出: frame:$fra");
            $sendResult = EventsApi::sendMsg($workerId, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 退出: sendResult:$sendResult");


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 退出,control_field:$control_field, division_code:$division_code ");

        }

        //心跳,暂不做处理,没有上报任何数据,下面会处理(充电桩运行状态实时上传),这个和心跳一样,隔一定的时间自动上报
        private static function Hearbeat($message){


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 心跳start ".Carbon::now());

            //解析帧
            $hearbeat = new Hearbeat();
            $frame_load = $hearbeat($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ



            //更新心跳时间
            $result = self::$controller->hearbeat($division_code, $terminal_address);

            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 行政区划码: division_code:$division_code");

            //应答充电桩
            $divisionTerminalAddress = $division_code.'-'.$terminal_address;
            $evse = Evse::where('division_terminal_address',$divisionTerminalAddress)->first();
            if(empty($evse)){
                Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 心跳未找到此桩 $divisionTerminalAddress");
                return false;
            }

            $workerId = $evse->worker_id;

            $confirm = new Confirm();
            $confirm->version(intval($version));
            $confirm->control_field(intval($control_field));
            $confirm->division_code(intval($division_code));
            $confirm->terminal_address(intval($terminal_address));
            $confirm->master_station_address(intval($master_station_address));
            $confirm->seq(intval($seq));


            $frame = strval($confirm);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 心跳: frame:$fra");
            $sendResult = EventsApi::sendMsg($workerId, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 心跳: sendResult:$sendResult");









            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 心跳,control_field:$control_field, division_code:$division_code ");

        }

        //注册
        private static function Register($message){



        }


        //*******************************设置参数*******************************//
        public static function SetParameters($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 设置参数 start ".$date);

            //解析帧
            $setInformation = new setInformation();
            $frame_load = $setInformation($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $changeStatus = $frame_load->changeStatus->getValue(); //变更参数状态



            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 设置参数结果: 
            changeStatus:$changeStatus");

        }


        //*******************************查询参数*******************************//

        //服务器IP地址和端口
        public static function getIpDomain($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口 start ".$date);

            //解析帧
            $getIpDomain = new GetIpDomain();
            $frame_load = $getIpDomain($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $mainIp1 = $frame_load->mainIp1->getValue();
            $mainIp2 = $frame_load->mainIp2->getValue();
            $mainIp3 = $frame_load->mainIp3->getValue();
            $mainIp4 = $frame_load->mainIp4->getValue();
            $mainPort = $frame_load->mainPort->getValue();
            $secondaryIp1 = $frame_load->secondaryIp1->getValue();
            $secondaryIp2 = $frame_load->secondaryIp2->getValue();
            $secondaryIp3 = $frame_load->secondaryIp3->getValue();
            $secondaryIp4 = $frame_load->secondaryIp4->getValue();
            $secondaryPort = $frame_load->secondaryPort->getValue();


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 服务器IP地址和端口: 
            mainIp1:$mainIp1, mainIp2:$mainIp2 ".'mainIp3:'.$mainIp3.'mainIp4:'.$mainIp4."mainPort:".$mainPort.
                ' secondaryIp1:'.$secondaryIp1.' secondaryIp2:'.$secondaryIp2.' secondaryIp3:'.$secondaryIp3.
                ' secondaryIp4:'.$secondaryIp4.' secondaryPort:'.$secondaryPort);



        }

        //充电费率
        public static function getRate($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率 start ".$date);

            //解析帧
            $getRate = new GetRate();
            $frame_load = $getRate($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $ratePattern = $frame_load->ratePattern->getValue();
            $totalElectricity = $frame_load->totalElectricity->getValue();
            $tipPrice = $frame_load->tipPrice->getValue();
            $peakPrice = $frame_load->peakPrice->getValue();
            $flatPrice = $frame_load->flatPrice->getValue();
            $valleyPrice = $frame_load->valleyPrice->getValue();
            $appointmentRate = $frame_load->appointmentRate->getValue();



            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电费率: 
            ratePattern:$ratePattern, totalElectricity:$totalElectricity, tipPrice:$tipPrice ".'peakPrice:'.$peakPrice.'flatPrice:'.$flatPrice."valleyPrice:".$valleyPrice.
                ' appointmentRate:'.$appointmentRate );


        }

        //充电桩状态上传频率
        public static function getStateFrequency($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率 start ".$date);

            //解析帧
            $getStateFrequency = new GetStateFrequency();
            $frame_load = $getStateFrequency($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $setTime = $frame_load->setTime->getValue();




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩状态上传频率: 
            setTime:$setTime" );



        }


        //充电桩充电数据上传频率
        public static function getChargeDataFrequency($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率 start ".$date);

            //解析帧
            $getChargeDataFrequency = new GetChargeDataFrequency();
            $frame_load = $getChargeDataFrequency($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $setTime = $frame_load->setTime->getValue();




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电数据上传频率: 
            setTime:$setTime" );


        }

        //PWM占空比
        public static function getPwmDutyRatio($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " PWM占空比 start ".$date);

            //解析帧
            $getPwmDutyRatio = new GetPwmDutyRatio();
            $frame_load = $getPwmDutyRatio($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $dutyData = $frame_load->dutyData->getValue();




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " PWM占空比: 
            dutyData:$dutyData" );

        }

        //模块基本参数
        public static function getBasicParameter($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数 start ".$date);

            //解析帧
            $getBasicParameter = new GetBasicParameter();
            $frame_load = $getBasicParameter($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $modularNum1 = $frame_load->modularNum1->getValue();
            $modularNum2 = $frame_load->modularNum2->getValue();
            $voltageLevel = $frame_load->voltageLevel->getValue();
            $currentLevel = $frame_load->currentLevel->getValue();
            $currentLimit = $frame_load->currentLimit->getValue();
            $voltageCap = $frame_load->voltageCap->getValue();
            $voltageLower = $frame_load->voltageLower->getValue();




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 模块基本参数: 
            ".' modularNum1:'.$modularNum1.' modularNum2:'.$modularNum2.' voltageLevel:'.$voltageLevel.
            ' currentLevel:'.$currentLevel.' currentLimit:'.$currentLimit.' voltageCap:'.$voltageCap.' voltageLower:'.$voltageLower);

        }


        //*******************************事件主动上报*******************************//

        //充电桩充电端口状态变更
        public static function PortStatuChange($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电端口状态变更start ".$date);

            //解析帧
            $portStatuChange = new PortStatuChange();
            $frame_load = $portStatuChange($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $status = $frame_load->portNumber->getValue(); //充电接口
            $data['year'] = $frame_load->year->getValue(); //年
            $data['month'] = $frame_load->month->getValue(); //月
            $data['day'] = $frame_load->day->getValue(); //日
            $data['hour'] = $frame_load->hour->getValue(); //时
            $data['minute'] = $frame_load->minute->getValue(); //分
            $data['second'] = $frame_load->second->getValue(); //秒
            $beforeStatus = $frame_load->beforeStatus->getValue(); //变更前状态
            $afterStatus = $frame_load->afterStatus->getValue(); //变更后状态


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电端口状态变更: 
            status:$status, beforeStatus:$beforeStatus, afterStatus:$afterStatus "."year:".$data['year'].
            ' month:'.$data['month'].' day:'.$data['day'].' hour:'.$data['hour'].' minute:'.$data['minute'].' second:'.$data['second']);








        }


        //充电桩异常信息
        public static function ExceptionInformation($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩异常信息start ".$date);

            //解析帧
            $exceptionInformation = new ExceptionInformation();
            $frame_load = $exceptionInformation($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $data['year'] = $frame_load->year->getValue(); //年
            $data['month'] = $frame_load->month->getValue(); //月
            $data['day'] = $frame_load->day->getValue(); //日
            $data['hour'] = $frame_load->hour->getValue(); //时
            $data['minute'] = $frame_load->minute->getValue(); //分
            $data['second'] = $frame_load->second->getValue(); //秒
            $alarmDataCode = $frame_load->alarmDataCode->getValue(); //异常信息编码
            $alarmDataType = $frame_load->alarmDataType->getValue(); //异常信息类型


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电端口状态变更: 
            alarmDataCode:$alarmDataCode, alarmDataType:$alarmDataType "."year:".$data['year'].
                ' month:'.$data['month'].' day:'.$data['day'].' hour:'.$data['hour'].' minute:'.$data['minute'].' second:'.$data['second']);


        }
    

        //BMS异常信息
        public static function BMSExceptionInformation($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " BMS异常信息 start ".$date);

            //解析帧
            $BMSExceptionInformation = new BMSExceptionInformation();
            $frame_load = $BMSExceptionInformation($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $portNumber = $frame_load->portNumber->getValue(); //充电接口
            $data['year'] = $frame_load->year->getValue(); //年
            $data['month'] = $frame_load->month->getValue(); //月
            $data['day'] = $frame_load->day->getValue(); //日
            $data['hour'] = $frame_load->hour->getValue(); //时
            $data['minute'] = $frame_load->minute->getValue(); //分
            $data['second'] = $frame_load->second->getValue(); //秒

            $alarmDataCode = $frame_load->alarmDataCode->getValue(); //异常信息编码
            $alarmDataType = $frame_load->alarmDataType->getValue(); //异常信息类型


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " BMS异常信息:  portNumber:$portNumber
            alarmDataCode:$alarmDataCode, alarmDataType:$alarmDataType "."year:".$data['year'].
                ' month:'.$data['month'].' day:'.$data['day'].' hour:'.$data['hour'].' minute:'.$data['minute'].' second:'.$data['second']);

        }


        //整流模块异常信息
        public static function ModuleExceptionInformation($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 整流模块异常信息 start ".$date);

            //解析帧
            $ModuleExceptionInformation = new ModuleExceptionInformation();
            $frame_load = $ModuleExceptionInformation($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $portNumber = $frame_load->portNumber->getValue(); //充电接口
            $data['year'] = $frame_load->year->getValue(); //年
            $data['month'] = $frame_load->month->getValue(); //月
            $data['day'] = $frame_load->day->getValue(); //日
            $data['hour'] = $frame_load->hour->getValue(); //时
            $data['minute'] = $frame_load->minute->getValue(); //分
            $data['second'] = $frame_load->second->getValue(); //秒

            $alarmDataCode = $frame_load->alarmDataCode->getValue(); //异常信息编码
            $alarmDataType = $frame_load->alarmDataType->getValue(); //异常信息类型


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " BMS异常信息:  portNumber:$portNumber
            alarmDataCode:$alarmDataCode, alarmDataType:$alarmDataType "."year:".$data['year'].
                ' month:'.$data['month'].' day:'.$data['day'].' hour:'.$data['hour'].' minute:'.$data['minute'].' second:'.$data['second']);

        }



        //参数变更
        public static function ParameterChange($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 参数变更 start ".$date);

            //解析帧
            $parameterChange = new ParameterChange();
            $frame_load = $parameterChange($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $data['year'] = $frame_load->year->getValue(); //年
            $data['month'] = $frame_load->month->getValue(); //月
            $data['day'] = $frame_load->day->getValue(); //日
            $data['hour'] = $frame_load->hour->getValue(); //时
            $data['minute'] = $frame_load->minute->getValue(); //分
            $data['second'] = $frame_load->second->getValue(); //秒

            $flag = $frame_load->flag->getValue(); //事件标志
            $identification = $frame_load->identification->getValue(); //变更参数数据单元标识


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " BMS异常信息: 
            flag:$flag, identification:$identification "."year:".$data['year'].
                ' month:'.$data['month'].' day:'.$data['day'].' hour:'.$data['hour'].' minute:'.$data['minute'].' second:'.$data['second']);



        }



        //停止充电原因
        public static function StopChargeReason($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电原因 start ".$date);

            //解析帧
            $stopChargeReason = new StopChargeReason();
            $frame_load = $stopChargeReason($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $data['year'] = $frame_load->year->getValue(); //年
            $data['month'] = $frame_load->month->getValue(); //月
            $data['day'] = $frame_load->day->getValue(); //日
            $data['hour'] = $frame_load->hour->getValue(); //时
            $data['minute'] = $frame_load->minute->getValue(); //分
            $data['second'] = $frame_load->second->getValue(); //秒

            $portNumber = $frame_load->portNumber->getValue(); //充电口
            $reason = $frame_load->reason->getValue(); //停止充电原因


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 停止充电原因: 
            portNumber:$portNumber, reason:$reason "."year:".$data['year'].
                ' month:'.$data['month'].' day:'.$data['day'].' hour:'.$data['hour'].' minute:'.$data['minute'].' second:'.$data['second']);


        }







        //*******************************数据周期上传*******************************//






        //电池档案信息上传(BMS 辨识报文 BRM)
        public static function BatteryData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池档案信息上传 start ".$date);
            $data = [];
            //解析帧
            $batteryData = new BatteryData();
            $frame_load = $batteryData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $portNumber = $frame_load->portNumber->getValue(); //充电接口标识
            $batteryType = $frame_load->batteryType->getValue(); //电池类型
            $capacity = $frame_load->capacity->getValue(); //电池额定容量
            $totalVoltage = $frame_load->totalVoltage->getValue(); //电池额定总电压
            $manufacturerName = $frame_load->manufacturerName->getValue(); //电池生产厂商名称
            $chargeNumber = $frame_load->chargeNumber->getValue(); //电池组充电次数
            $vin = $frame_load->vin->getValue(); //车辆识别码(VIN)


//            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池档案信息上传 ,control_field:$control_field,
//            division_code:$division_code
//
//            portNumber:".$portNumber.' batteryType:'.$batteryType.' capacity:'.$capacity.' totalVoltage:'.$totalVoltage.' manufacturerName:'.$manufacturerName.
//                ' chargeNumber:'.$chargeNumber.' vin'.$vin);

        }


        //电池充电总状态信息上传
        public static function BatteryStatuData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息上传 start ".$date);
            $data = [];
            //解析帧
            $batteryStatuData = new BatteryStatuData();
            $frame_load = $batteryStatuData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $portNumber = $frame_load->portNumber->getValue(); //充电接口标识
            $voltageValue = $frame_load->voltageValue->getValue(); //充电电压测量值
            $currentValue = $frame_load->currentValue->getValue() - 400; //充电电流测量值
            $voltage = $frame_load->voltage->getValue(); //最高单体动力蓄电池电压
            $soc = $frame_load->soc->getValue(); //SOC
            $leftTime = $frame_load->leftTime->getValue(); //估算剩余充电时间



            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息上传 ,control_field:".json_encode($control_field).", division_code:$division_code 
            
            portNumber:".$portNumber.' voltageValue:'.$voltageValue.' currentValue:'.$currentValue.' voltage:'.$voltage.' soc:'.$soc.
                ' leftTime:'.$leftTime);

        }


        //电池温度、单体电压信息上传
        public static function BatteryTemperatureData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池温度、单体电压信息上传 start ".$date);
            $data = [];
            //解析帧
            $batteryTemperatureData = new BatteryTemperatureData();
            $frame_load = $batteryTemperatureData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $portNumber = $frame_load->portNumber->getValue(); //充电接口标识
            $maxTemperature = $frame_load->maxTemperature->getValue(); //蓄电池最高温度
            $minTemperature = $frame_load->minTemperature->getValue(); //蓄电池最低温度
            $monomerNum = $frame_load->monomerNum->getValue(); //电池单体总数
            $monomerVoltage = $frame_load->monomerVoltage->getValue(); //单体电压


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池温度、单体电压信息上传 ,control_field:".json_encode($control_field).", division_code:".json_encode($division_code)." 
            
            portNumber:".$portNumber.' maxTemperature:'.$maxTemperature.' minTemperature:'.$minTemperature.
                ' monomerNum:'.$monomerNum.' monomerVoltage:'.$monomerVoltage);


        }



        //*******************************查询命令*******************************//

        //充电桩当前时钟
        public function GetCalibratTime($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩当前时钟 start ".$date);
            $data = [];
            //解析帧
            $getCalibratTime = new GetCalibratTime();
            $frame_load = $getCalibratTime($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $year = $frame_load->year->getValue();
            $month = $frame_load->month->getValue();
            $day = $frame_load->day->getValue();
            $hour = $frame_load->hour->getValue();
            $minute = $frame_load->minute->getValue();
            $second = $frame_load->second->getValue();


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩当前时钟 ,control_field:$control_field, division_code:$division_code 
            
            year:".$year.' month:'.$month.' day:'.$day.
                ' hour:'.$hour.' minute:'.$minute.' second:'.$second);


        }



        //充电桩基本信息查询
        public function GetBaseData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩基本信息查询 start ".$date);
            $data = [];
            //解析帧
            $getBaseData = new GetBaseData();
            $frame_load = $getBaseData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $manufacturerCode = $frame_load->manufacturerCode->getValue();
            $evseType = $frame_load->evseType->getValue();
            $softwareVersion = $frame_load->softwareVersion->getValue();
            $hardwareVersion = $frame_load->hardwareVersion->getValue();
            $year = $frame_load->year->getValue();
            $month = $frame_load->month->getValue();
            $day = $frame_load->day->getValue();
            $power = $frame_load->power->getValue();

            $num = $frame_load->num->getValue();
            $voltageCap = $frame_load->voltageCap->getValue();
            $volageLower = $frame_load->volageLower->getValue();

            $current = $frame_load->current->getValue();
            $volage = $frame_load->volage->getValue();
            $portCurrent = $frame_load->portCurrent->getValue();
            $portVolage = $frame_load->portVolage->getValue();
            $directCurrent = $frame_load->directCurrent->getValue();
            $communicationMode = $frame_load->communicationMode->getValue();

            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩基本信息查询 ,control_field:$control_field, 
            division_code:$division_code.'manufacturerCode:'.$manufacturerCode.' evseType:$evseType'.' softwareVersion:'.$softwareVersion.
            ' hardwareVersion:'.$hardwareVersion.' power:'.$power.' $num:'.$num.' voltageCap:'.$voltageCap.' volageLower:'.$volageLower.
            ' current:'.$current.' volage:'.$volage.' portCurrent:'.$portCurrent.' portVolage:'.$portVolage.' directCurrent:'.$directCurrent.
            ' communicationMode:'.$communicationMode.'year:'.$year.' month:'.$month.' day:'.$day");

        }

        //充电桩运行状态查询
        public function GetRealTimeState($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩运行状态查询 start ".$date);
            $data = [];
            //解析帧
            $getRealTimeState = new GetRealTimeState();
            $frame_load = $getRealTimeState($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $evseType = $frame_load->evseType->getValue();
            $runStatus = $frame_load->runStatus->getValue();
            $maxVoltage = $frame_load->maxVoltage->getValue();
            $maxCurrent = $frame_load->maxCurrent->getValue();
            $astatus = $frame_load->astatus->getValue();
            $bstatus = $frame_load->bstatus->getValue();
            $communicationInterface = $frame_load->communicationInterface->getValue();


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩运行状态查询 ,control_field:$control_field, division_code:$division_code 
            
            evseType:".$evseType.' runStatus:'.$runStatus.' maxVoltage:'.$maxVoltage.
                ' maxCurrent:'.$maxCurrent.' astatus:'.$astatus.' bstatus:'.$bstatus.' communicationInterface:'.$communicationInterface);



        }


        //充电桩充电接口状态查询
        public function GetPortRealTimeData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口状态查询 start ".$date);
            $data = [];
            //解析帧
            $getPortRealTimeData = new GetPortRealTimeData();
            $frame_load = $getPortRealTimeData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $year = $frame_load->year->getValue();
            $month = $frame_load->month->getValue();
            $day = $frame_load->day->getValue();
            $hour = $frame_load->hour->getValue();
            $minute = $frame_load->minute->getValue();
            $second = $frame_load->second->getValue();

            $portNumber = $frame_load->portNumber->getValue();
            $voltage = $frame_load->voltage->getValue();
            $current = $frame_load->current->getValue();
            $totalElectricity = $frame_load->totalElectricity->getValue();
            $rateElectricity1 = $frame_load->rateElectricity1->getValue();
            $rateElectricity2 = $frame_load->rateElectricity2->getValue();
            $rateElectricity3 = $frame_load->rateElectricity3->getValue();
            $rateElectricity4 = $frame_load->rateElectricity4->getValue();
            $ammeterReading = $frame_load->ammeterReading->getValue();



            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口状态查询 ,control_field:$control_field, division_code:$division_code 
            
            portNumber:".$portNumber.' voltage:'.$voltage.' current:'.$current.
                ' totalElectricity:'.$totalElectricity.' rateElectricity1:'.$rateElectricity1.' rateElectricity2:'.$rateElectricity2.
                ' rateElectricity3:'.$rateElectricity3.' rateElectricity4:'.$rateElectricity4.' ammeterReading:'.$ammeterReading.
            ' year'.$year.' month:'.$month.' day:'.$day.' hour:'.$hour.' minute:'.$minute.' second:'.$second);

        }


        //电池档案信息查询(BRM)
        public function GetBatteryData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池档案信息查询 start ".$date);
            $data = [];
            //解析帧
            $getBatteryData = new GetBatteryData();
            $frame_load = $getBatteryData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $portNumber = $frame_load->portNumber->getValue();
            $batteryType = $frame_load->batteryType->getValue();
            $capacity = $frame_load->capacity->getValue();
            $totalVoltage = $frame_load->totalVoltage->getValue();
            $manufacturerName = $frame_load->manufacturerName->getValue();
            $chargeNumber = $frame_load->chargeNumber->getValue();
            $vin = $frame_load->vin->getValue();




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池档案信息查询 ,control_field:$control_field, division_code:$division_code 
            
            portNumber:".$portNumber.' batteryType:'.$batteryType.' capacity:'.$capacity.
                ' totalVoltage:'.$totalVoltage.' manufacturerName:'.$manufacturerName.' chargeNumber:'.$chargeNumber.
                ' vin:'.$vin);


        }


        //电池充电总状态信息查询
        public function GetBatteryStatuData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息查询 start ".$date);
            $data = [];
            //解析帧
            $getBatteryStatuData = new GetBatteryStatuData();
            $frame_load = $getBatteryStatuData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $portNumber = $frame_load->portNumber->getValue();
            $voltageValue = $frame_load->voltageValue->getValue();
            $currentValue = $frame_load->currentValue->getValue();
            $voltage = $frame_load->voltage->getValue();
            $soc = $frame_load->soc->getValue();
            $leftTime = $frame_load->leftTime->getValue();





            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息查询 ,control_field:$control_field, division_code:$division_code 
            
            portNumber:".$portNumber.' voltageValue:'.$voltageValue.' currentValue:'.$currentValue.
                ' voltage:'.$voltage.' soc:'.$soc.' leftTime:'.$leftTime);

        }


        // 电池温度、单体电压信息上传
        public function GetBatteryTemperatureData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息查询 start ".$date);
            $data = [];
            //解析帧
            $getBatteryTemperatureData = new GetBatteryTemperatureData();
            $frame_load = $getBatteryTemperatureData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $portNumber = $frame_load->portNumber->getValue();
            $maxTemperature = $frame_load->maxTemperature->getValue();
            $minTemperature = $frame_load->minTemperature->getValue();
            $monomerNum = $frame_load->monomerNum->getValue();
            $monomerVoltage = $frame_load->monomerVoltage->getValue();



            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 电池充电总状态信息查询 ,control_field:$control_field, division_code:$division_code 
            
            portNumber:".$portNumber.' maxTemperature:'.$maxTemperature.' minTemperature:'.$minTemperature.
                ' monomerNum:'.$monomerNum.' monomerVoltage:'.$monomerVoltage );

        }



        //*******************************刷卡充电流程控制*******************************//

        //终端刷卡
        public function PayCard($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 终端刷卡 start ".$date);
            $data = [];
            //解析帧
            $payCard = new PayCard();
            $frame_load = $payCard($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $cardNumber = $frame_load->cardNumber->getValue();
            $password = $frame_load->password->getValue();
            $balance = $frame_load->balance->getValue();

            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 终端刷卡 ,control_field:$control_field, division_code:$division_code 
            
            cardNumber:".$cardNumber.' password:'.$password.' balance:'.$balance );


            //应答充电桩

            //控制域设置
            $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
            $control = $this->controlField($control);
            //sequenceDomain
            $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
            $seq = $this->sequenceDomain($seq);

            $serverPayCard = new ServerPayCard();
            $serverPayCard->version(intval($version));
            $serverPayCard->control_field(intval($control));
            $serverPayCard->division_code(intval($division_code));
            $serverPayCard->terminal_address(intval($terminal_address));
            $serverPayCard->master_station_address(intval($master_station_address));
            $serverPayCard->seq(intval($seq));

            $serverPayCard->cardStatus(1);


            $frame = strval($serverPayCard);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 终端刷卡: frame:$fra");
            $sendResult = EventsApi::sendMsg(self::$client_id, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 终端刷卡: sendResult:$sendResult");







        }

        //启动充电
        public static function CardStartCharge($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电 start ".$date);
            $data = [];
            //解析帧
            $cardStartCharge = new CardStartCharge();
            $frame_load = $cardStartCharge($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $portNumber = $frame_load->portNumber->getValue();
            $cardNumber = $frame_load->cardNumber->getValue();
            $balance = $frame_load->balance->getValue();
            $year = $frame_load->year->getValue();
            $month = $frame_load->month->getValue();
            $day = $frame_load->day->getValue();
            $hour = $frame_load->hour->getValue();
            $minute = $frame_load->minute->getValue();
            $second = $frame_load->second->getValue();
            $startType = $frame_load->startType->getValue();




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电 ,control_field:,".json_encode($control_field)." division_code:".json_encode($division_code)." 
            
            $portNumber:".$portNumber.' $cardNumber:'.$cardNumber.' $balance:'.$balance.' year:'.$year.' month:'.$month.' day:'.$day.
            ' hour:'.$hour.' minute：'.$minute.' second:'.$second.' startType:'.$startType);







            //应答充电桩

            //控制域设置
            $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
            $control = self::controlField($control);
            //sequenceDomain
            $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
            $seq = self::sequenceDomain($seq);

            $serverCardStartCharge = new ServerCardStartCharge();
            $serverCardStartCharge->version(intval($version));
            $serverCardStartCharge->control_field(intval($control));
            $serverCardStartCharge->division_code(intval($division_code));
            $serverCardStartCharge->terminal_address(intval($terminal_address));
            $serverCardStartCharge->master_station_address(intval($master_station_address));
            $serverCardStartCharge->seq(intval($seq));



            $frame = strval($serverCardStartCharge);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电: frame:$fra");
            $sendResult = EventsApi::sendMsg(self::$client_id, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 启动充电: sendResult:$sendResult");





        }

        //结束充电
        public static function CardStopCharge($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 结束充电 start ".$date);
            $data = [];
            //解析帧
            $cardStopCharge = new CardStopCharge();
            $frame_load = $cardStopCharge($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ


            $portNumber = $frame_load->portNumber->getValue();
            $cardNumber = $frame_load->cardNumber->getValue();
            $consumptionAmount = round($frame_load->consumptionAmount->getValue() / 100, 2); //消费金额
            $balance = round($frame_load->balance->getValue() / 100, 2); //余额
            $year = $frame_load->year->getValue();
            $month = $frame_load->month->getValue();
            $day = $frame_load->day->getValue();
            $hour = $frame_load->hour->getValue();
            $minute = $frame_load->minute->getValue();
            $second = $frame_load->second->getValue();


            $totalChargeDegrees = round( $frame_load->totalChargeDegrees->getValue() /100, 2 );
            $totalMoney = round($frame_load->totalMoney->getValue() / 100, 2 );
            $totalElectricTip = round($frame_load->totalElectricTip->getValue() / 100, 2 );
            $electricTipMoney = round($frame_load->electricTipMoney->getValue() / 100, 2 );
            $peakDegree = round($frame_load->peakDegree->getValue() / 100, 2 );

            $peakDegreeMoney = round($frame_load->peakDegreeMoney->getValue() / 100, 2 );
            $roughnessNumber = round($frame_load->roughnessNumber->getValue() / 100, 2 );
            $roughnessNumberMoney = round($frame_load->roughnessNumberMoney->getValue()/ 100, 2 );
            $grainNumber = round($frame_load->grainNumber->getValue() / 100, 2 );
            $grainMoney = round($frame_load->grainMoney->getValue() / 100, 2 );




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 结束充电 ,control_field:$control_field, division_code:$division_code 
            
            ,portNumber:".$portNumber.' $cardNumber:'.$cardNumber.' consumptionAmount:'.$consumptionAmount.' totalChargeDegrees:'.$totalChargeDegrees.
                ' totalMoney:'.$totalMoney.' totalElectricTip:'.$totalElectricTip.' electricTipMoney:'.$electricTipMoney.' peakDegree:'.$peakDegree.
                ' peakDegreeMoney:'.$peakDegreeMoney.' roughnessNumber:'.$roughnessNumber.' roughnessNumberMoney:'.$roughnessNumberMoney.
                ' grainNumber:'.$grainNumber.' grainMoney:'.$grainMoney.
                ' balance:'.$balance.' year:'.$year.' month:'.$month.' day:'.$day.
                ' hour:'.$hour.' minute：'.$minute.' second:'.$second );



            //处理数据
            $result = self::$controller->uploadChargeInfo($division_code, $terminal_address, $master_station_address, $consumptionAmount, $balance, $portNumber, $year,$month,$day,$hour,$minute,$second, $totalChargeDegrees,$totalMoney, $message);


            //应答充电桩

            //控制域设置
            //$control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
           // $control = $this->controlField($control);
            //sequenceDomain
            //$seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
            //$seq = $this->sequenceDomain($seq);

            //控制域设置
            $control = ['retain'=>0, 'flag_bit'=>1, 'direction_bit'=>0];
            $control = self::controlField($control);
            //sequenceDomain
            $seq = ['serial_number'=>0, 'con'=>0, 'fir'=>1, 'fin'=>1];
            $seq = self::sequenceDomain($seq);


            $serverCardStopCharge = new ServerCardStopCharge();
            $serverCardStopCharge->version(intval($version));
            $serverCardStopCharge->control_field(intval($control)); //$control_field
            $serverCardStopCharge->division_code(intval($division_code));
            $serverCardStopCharge->terminal_address(intval($terminal_address));
            $serverCardStopCharge->master_station_address(intval($master_station_address));
            $serverCardStopCharge->seq(intval($seq));

            $serverCardStopCharge->settlementStatus(1); //计算状态

            $frame = strval($serverCardStopCharge);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 结束充电: frame:$fra");
            $sendResult = EventsApi::sendMsg(self::$client_id, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 结束充电: sendResult:$sendResult");







        }

        //充电设备断网时充电数据重传
        public static function RetransmissionData($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电设备断网时充电数据重传 start ".$date);
            $data = [];
            //解析帧
            $retransmissionData = new RetransmissionData();
            $frame_load = $retransmissionData($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue()['value']; //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue()['value']; //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue()['value']; //帧序列域SEQ


            $portNum = $frame_load->portNum->getValue();
            $portNumber = $frame_load->portNumber->getValue();
            $cardNumber = $frame_load->cardNumber->getValue();
            $startYear = $frame_load->startYear->getValue();
            $startMonth = $frame_load->startMonth->getValue();
            $startDay = $frame_load->startDay->getValue();
            $startHour = $frame_load->startHour->getValue();
            $startMinute = $frame_load->startMinute->getValue();
            $startSecond = $frame_load->startSecond->getValue();

            $endYear = $frame_load->endYear->getValue();
            $endMonth = $frame_load->endMonth->getValue();
            $endDay = $frame_load->endDay->getValue();
            $endHour = $frame_load->endHour->getValue();
            $endMinute = $frame_load->endMinute->getValue();
            $endSecond = $frame_load->endSecond->getValue();
            $totalDegree = $frame_load->totalDegree->getValue();

            $totalAmount = $frame_load->totalAmount->getValue();
            $cuspNumber = $frame_load->cuspNumber->getValue();
            $cuspNumberMoney = $frame_load->cuspNumberMoney->getValue();
            $peakDegree = $frame_load->peakDegree->getValue();
            $peakDegreeMoney = $frame_load->peakDegreeMoney->getValue();

            $roughnessNumber = $frame_load->roughnessNumber->getValue();
            $roughnessNumberMoney = $frame_load->roughnessNumberMoney->getValue();
            $grainNumber = $frame_load->grainNumber->getValue();
            $grainNumberMoney = $frame_load->grainNumberMoney->getValue();



            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电设备断网时充电数据重传 ,control_field:$control_field, division_code:$division_code
            ' portNum:'.$portNum.' cardNumber:'.$cardNumber.' startYear:'.$startYear.' startMonth:'.$startMonth.' startDay:'.$startDay.' startHour:'.$startHour.
            ' startMinute:'.$startMinute.' startSecond:'.$startSecond.' endYear:'.$endYear.' endMonth:'.$endMonth.' endDay:'.$endDay.' endHour:'.$endHour.
            ' endMinute:'.$endMinute.' $endSecond:'.$endSecond.' totalDegree:'.$totalDegree.' totalAmount:'.$totalAmount.' cuspNumber:'.$cuspNumber.
            ' cuspNumberMoney:'.$cuspNumberMoney.' peakDegree:'.$peakDegree.' peakDegreeMoney:'.$peakDegreeMoney.' roughnessNumber:'.$roughnessNumber.
            ' roughnessNumberMoney:'.$roughnessNumberMoney.' grainNumber:'.$grainNumber.' grainNumberMoney:'.$grainNumberMoney ");



            //应答充电桩

            //控制域设置
//            $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
//            $control = $this->controlField($control);
            //sequenceDomain
//            $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
//            $seq = $this->sequenceDomain($seq);

            $serverRetransmissionData = new ServerRetransmissionData();
            $serverRetransmissionData->version(intval($version));
            $serverRetransmissionData->control_field(intval($control_field));
            $serverRetransmissionData->division_code(intval($division_code));
            $serverRetransmissionData->terminal_address(intval($terminal_address));
            $serverRetransmissionData->master_station_address(intval($master_station_address));
            $serverRetransmissionData->seq(intval($seq));

            $serverRetransmissionData->status(1);



            $frame = strval($serverRetransmissionData);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电设备断网时充电数据重传: frame:$fra");
            $sendResult = EventsApi::sendMsg(self::$client_id, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电设备断网时充电数据重传: sendResult:$sendResult");







        }

        //充电卡充值
        public function Recharge($message){


            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡充值 start ".$date);
            $data = [];
            //解析帧
            $recharge = new Recharge();
            $frame_load = $recharge($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $cardNumber = $frame_load->cardNumber->getValue();

            $year = $frame_load->year->getValue();
            $month = $frame_load->month->getValue();
            $day = $frame_load->day->getValue();
            $hour = $frame_load->hour->getValue();
            $minute = $frame_load->minute->getValue();
            $second = $frame_load->second->getValue();
            $balance = $frame_load->balance->getValue();
            $rechargeMony = $frame_load->rechargeMony->getValue();
            $totalMony = $frame_load->totalMony->getValue();




            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡充值 ,control_field:$control_field, division_code:$division_code 
            
            ' $cardNumber:'.$cardNumber.' $balance:'.$balance.' year:'.$year.' month:'.$month.' day:'.$day.
                ' hour:'.$hour.' minute：'.$minute.' second:'.$second.' rechargeMony:'.$rechargeMony.' totalMony:'.$totalMony ");




            //应答充电桩

            //控制域设置
            $control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
            $control = $this->controlField($control);
            //sequenceDomain
            $seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
            $seq = $this->sequenceDomain($seq);

            $serverRecharge = new ServerRecharge();
            $serverRecharge->version($version);
            $serverRecharge->control_field($control);
            $serverRecharge->division_code($division_code);
            $serverRecharge->terminal_address($terminal_address);
            $serverRecharge->master_station_address($master_station_address);
            $serverRecharge->seq($seq);

            $serverRecharge->status($seq);



            $frame = strval($serverRecharge);   //组装帧
            $fra = Tools::asciiStringToHexString($frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡充值: frame:$fra");
            $sendResult = EventsApi::sendMsg(self::$client_id, $frame);
            Log::debug(__CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡充值: sendResult:$sendResult");








        }























        //*******************************控制命令*******************************//

        //充电桩重启
        private static function Restart($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩重启start ".$date);

            //解析帧
            $restart = new Restart();
            $frame_load = $restart($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $status = $frame_load->status->getValue(); //重启状态 0成功 1失败

            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩重启结果: status:$status, control_field:".json_encode($control_field).", division_code:$division_code ");

        }


        //充电卡解锁
        public static function unlock($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡解锁start ".$date);

            //解析帧
            $unlock = new Unlock();
            $frame_load = $unlock($message);

            //接收数据
            $version = $frame_load->version->getValue(); //版本号
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ

            $status = $frame_load->status->getValue();

            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电卡解锁结果: status:$status, control_field:$control_field, division_code:$division_code ");



        }







    //对时结果
    public static function CalibratTime($message){

        $date = date('Y-m-d H:i:s', time());
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 对时结果start ".$date);

        //解析帧
        $calibratTime = new CalibratTime();
        $frame_load = $calibratTime($message);

        //接收数据
        $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
        $division_code = $frame_load->division_code->getValue(); //行政区划码
        $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
        $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
        $seq = $frame_load->seq->getValue(); //帧序列域SEQ

        $result = $frame_load->result->getValue(); //成功标识 0成功 1失败


        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 对时结果: result:$result, control_field:,".json_encode($control_field)." division_code:$division_code ");



    }



        //充电桩充电接口预约锁定
        public static function ReservationLock($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口预约锁定start ".$date);

            //解析帧
            $reservationLock = new ReservationLock();
            $frame_load = $reservationLock($message);

            //接收数据
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $portNumber = $frame_load->portNumber->getValue();
            $status = $frame_load->status->getValue(); //成功标识 0成功 1失败


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口预约锁定: portNumber:$portNumber,status:$status control_field:$control_field, division_code:$division_code ");


        }


        //充电桩充电接口取消预约解锁
        public static function CancelReservationLock($message){

            $date = date('Y-m-d H:i:s', time());
            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口取消预约解锁start ".$date);

            //解析帧
            $cancelReservationLock = new CancelReservationLock();
            $frame_load = $cancelReservationLock($message);

            //接收数据
            $control_field = $frame_load->control_field->getValue(); //控制域C(上行下行方向)
            $division_code = $frame_load->division_code->getValue(); //行政区划码
            $terminal_address = $frame_load->terminal_address->getValue(); //终端地址
            $master_station_address = $frame_load->master_station_address->getValue(); //主站地址和组地址标志A3
            $seq = $frame_load->seq->getValue(); //帧序列域SEQ


            $portNumber = $frame_load->portNumber->getValue();
            $status = $frame_load->status->getValue(); //成功标识 0成功 1失败


            Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 充电桩充电接口取消预约解锁: portNumber:$portNumber,status:$status control_field:$control_field, division_code:$division_code ");



        }


















    //控制域设置
    public static function controlField($control){

        //控制域 保留  启动标志位  传输方向位
        //$control = ['retain'=>0, 'flag_bit'=>0, 'direction_bit'=>1];
        $control = new controlField(1, FALSE, $control);
        $controlField = strval($control);
        return $controlField;
    }

    //帧序列域SEQ设置
    public static function sequenceDomain($seq){

        //帧序列域SEQ  帧序列号SEQ  CON  FIR  FIN
        //$seq = ['serial_number'=>2, 'con'=>1, 'fir'=>1, 'fin'=>0];
        $sequenceDomain = new sequenceDomain(1,FALSE, $seq);
        $sequenceDomain = strval($sequenceDomain);
        return $sequenceDomain;

    }

    //主站地址和组地址标志A3设置
    public static function masterStationAddress($address){

        //主站地址和组地址标志A3 group_address  master_address
        //$address = ['group_address'=>0,'master_address'=>13];
        $masterStationAddress = new masterStationAddress(1,FALSE, $address);
        $masterStationAddress = strval($masterStationAddress);
        return $masterStationAddress;

    }






    

}
