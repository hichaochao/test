<?php
namespace Wormhole\Protocols\HD10;
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

use \GatewayWorker\Lib\Gateway;
use \Curl\Curl;


use League\Flysystem\Config;
use Workerman\Events\Ev;
use Workerman\Worker;
use Wormhole\Protocols\BaseEvents;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\HD10\Controllers\EvseController;
use Wormhole\Protocols\HD10\Protocol\Frame AS BaseFrame;


use Wormhole\Protocols\HD10\Protocol\Evse\Frame\SignIn as EvseSignInFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartCharge as EvseStartChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StopCharge as EvseStopChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\CardSign as EvseCardSignFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\HeartBeat as EvseHeartBeatFrame;


use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartCharge as EvseStartChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StopCharge as EvseStopChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeLog as EvseChargeLogFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeLog as EvseChargeLogDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeRealtime as EvseChargeRealtimeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeRealtime as EvseChargeRealtimeDataArea;


use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\CardSign as EvseCardSignDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ReservationCharge as EvseReservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ReservationCharge as EvseReservationChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\UnReservationCharge as EvseUnreservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\UnReservationCharge as EvseUnreservationChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartChargeCheck as EvseStartChargeCheckFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartChargeCheck as EvseStartChargeCheckDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\EventUpload as EvseEventUploadFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\EventUpload as EvseEventUploadDataArea;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events extends BaseEvents
{

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message) {
        ///**
        // *@var  BaseFrame[]
        // */
        //$frameList = BaseFrame::load($message);
        //$controller = new EvseController($client_id);
        //foreach ($frameList as $frame) {
        //    switch ($frame->getCommandCode() . $frame->getFunctionCode()) {
        //        case (EvseSignInFrame::commandCode . EvseSignInFrame::functionCode);
        //            \Log::debug( __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩登录");
        //            $result = $controller->signIn($frame);
        //            break;
        //        //case (EvseCardSignFrame::commandCode . EvseCardSignFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩刷卡");
        //        //    $result = $controller->cardSign($frame);
        //        //    break;
        //        ////启动充电相关
        //        ////预约
        //        //case (EvseReservationChargeFrame::commandCode . EvseReservationChargeFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 预约响应");
        //        //    $result = $this->doReservationResponseAsynchronous($frame);
        //        //    break;
        //        ////自检
        //        //case (EvseStartChargeCheckFrame::commandCode . EvseStartChargeCheckFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 启动充电前自检");
        //        //    $result = $this->doStartChargeCheckResponseAsynchronous($frame);
        //        //    break;
        //        ////启动充电
        //        //case (EvseStartChargeFrame::commandCode . EvseStartChargeFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩启动");
        //        //    $result = $this->doStartChargeResponseAsynchronous($frame);
        //        //    break;
        //        ////启动失败，需要解约；
        //        //case (EvseUnreservationChargeFrame::commandCode . EvseUnreservationChargeFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 启动失败，解约");
        //        //    $result = $this->doUnReservationResponseAsynchronous($frame);
        //        //    break;
        //        //
        //        //
        //        //case (EvseHeartBeatFrame::commandCode . EvseHeartBeatFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 心跳");
        //        //    $result = $this->evse_heartbeat($frame);
        //        //    break;
        //        //case (EvseStopChargeFrame::commandCode . EvseStopChargeFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩停止");
        //        //    $result = $this->stopChargeResponse($frame);
        //        //    break;
        //        //case (EvseChargeLogFrame::commandCode . EvseChargeLogFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩记录上报");
        //        //    $result = $this->uploadChargeInfo($frame);
        //        //    break;
        //        //case (EvseChargeRealtimeFrame::commandCode . EvseChargeRealtimeFrame::functionCode);
        //        //    log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 充电桩实时记录上报");
        //        //    $result = $this->evse_upload_charge_realtime($frame);
        //        //    break;
        //        //default:
        //        //    $result = true;
        //    }
        //}

        parent::message($client_id,$message);

        if(!self::continueMessage($client_id)){
            return FALSE;
        }




        $url = \Config::get('gateway.monitor_url');
        $action=\Config::get('gateway.monitor_api_on_message');
        $localServer = \Config::get('gateway.host');
        $localPort = \Config::get('gateway.port');

        $platform_name = \Config::get('gateway.platform_name');
        $protocol_ip = \Config::get('gateway.protocol_ip');
        $protocol_port = \Config::get('gateway.protocol_port');

        $session_id = md5($localServer);

        $data = sprintf(\Config::get('gateway.message'),$localServer,$localPort,$client_id,base64_encode($message),time(), $platform_name, $protocol_ip, $protocol_port);
        self::log(__NAMESPACE__."/".__FUNCTION__."@".__LINE__." Client_id = $client_id; frame:". Tools::asciiStringToHexString($message)." string len:".strlen($message));

        $curl = new Curl();
        $curl->setTimeout(1);
        $curl->setHeader("Content-Type","application/json");
        $curl->setHeader('Cookie','ci_session='.$session_id);

        $url = $url.$action;
        $curl->post($url, $data);
        $curl->close();


    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {

        $clentId = \Cache::get($client_id);
        if(!empty($ClentId)){
            \Cache::forget($client_id);
            \Cache::forget($clentId);
        }

        $url = \Config::get('gateway.monitor_url');
        $action=\Config::get('gateway.monitor_api_on_close');
        $localServer = \Config::get('gateway.host');
        $localPort = \Config::get('gateway.port');
        $session_id = md5($localServer);

        $data = sprintf(\Config::get('gateway.offline'),$localServer,$localPort,$client_id);
        self::log(__NAMESPACE__."/".__FUNCTION__."@".__LINE__." $client_id 断开链接");

        $curl = new Curl();
        $curl->setTimeout(1);
        $curl->setHeader("Content-Type","application/json");
        $curl->setHeader('Cookie','ci_session='.$session_id);

        $url = $url.$action;
        $curl->post($url, $data);
        $curl->close();
    }
}