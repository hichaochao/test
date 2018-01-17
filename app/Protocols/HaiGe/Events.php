<?php
namespace Wormhole\Protocols\HaiGe;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-08
 * Time: 11:16
 */

use \GatewayWorker\Lib\Gateway;
use \Curl\Curl;
use League\Flysystem\Config;
use Workerman\Events\Ev;
use Workerman\Worker;
use Wormhole\Protocols\Tools;

use Wormhole\Protocols\HaiGe\Protocol\Frame AS BaseFrame;
use Wormhole\Protocols\HaiGe\Controllers\EvseController;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events extends Worker
{


    /**
     * @param $client_id
     * @param $msg
     * @param $serverAddress
     * @return bool
     */
    public static function sendMsg($client_id, $msg){


        self::log(__CLASS__."/".__FUNCTION__."@".__LINE__." Client_id = $client_id; msg = ".Tools::asciiStringToHexString(base64_decode($msg)));
        $result = Gateway::sendToClient($client_id, base64_decode($msg));
        return $result;
    }
    /**
     * 当客户端连接时触
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        self::log( " new client  Client_id : $client_id");
        //// 向当前client_id发送数据
        //Gateway::sendToClient($client_id, "Hello $client_id");
        //// 向所有人发送
        //Gateway::sendToAll("$client_id login");
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {

        $debug = \Config::get('gateway.debug');
        self::log(__CLASS__."/".__FUNCTION__."  frame:". bin2hex($message)." string len:".strlen($message));
        if(TRUE === $debug){ //测试模式，直接返回发送帧
            Gateway::sendToAll( Tools::asciiStringToHexString(  $message));
        }

        $url =  \Config::get('gateway.monitor_url');
        $action=\Config::get('gateway.monitor_api_on_message');
        $localServer = \Config::get('gateway.host');
        $localPort = \Config::get('gateway.port');
        $session_id = md5($localServer);

        $platform_name = \Config::get('gateway.platform_name');
        $protocol_ip = \Config::get('gateway.protocol_ip');
        $protocol_port = \Config::get('gateway.protocol_port');

        $session_id = md5($localServer);

        $data = sprintf(\Config::get('gateway.message'),$localServer,$localPort,$client_id,base64_encode($message),time(), $platform_name, $protocol_ip, $protocol_port);


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
        $url = \Config::get('gateway.monitor_url');
        $action=\Config::get('gateway.monitor_api_on_close');
        $localServer = \Config::get('gateway.host');
        $localPort = \Config::get('gateway.port');
        $session_id = md5($localServer);

        $data = sprintf(\Config::get('gateway.offline'),$localServer,$localPort,$client_id);
        self::log(__CLASS__."/".__FUNCTION__."@".__LINE__." $client_id 断开链接");

        $curl = new Curl();
        $curl->setTimeout(1);
        $curl->setHeader("Content-Type","application/json");
        $curl->setHeader('Cookie','ci_session='.$session_id);

        $url = $url.$action;
        $curl->post($url, $data);
        $curl->close();
    }
}