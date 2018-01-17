<?php
namespace Wormhole\Protocols\NJINT;
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
use Illuminate\Support\Facades\Log;

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
    public static function message($client_id, $message) {
        parent::message($client_id,$message);

        if(!self::continueMessage($client_id)){
            return FALSE;
        }


        $action=\Config::get('gateway.monitor_api_on_message');
        $localServer = \Config::get('gateway.host');
        $localPort = \Config::get('gateway.port');
        $session_id = md5($localServer);
        $url = \Config::get('gateway.monitor_url');

        $platform_name = \Config::get('gateway.platform_name');
        $protocol_ip = \Config::get('gateway.protocol_ip');
        $protocol_port = \Config::get('gateway.protocol_port');

        $data = sprintf(\Config::get('gateway.message'),$localServer,$localPort,$client_id,base64_encode($message),time(), $platform_name, $protocol_ip, $protocol_port);
        //self::log(__NAMESPACE__."/".__FUNCTION__."  frame:". Tools::asciiStringToHexString($message)." string len:".strlen($message));
        Log::debug(__NAMESPACE__."/".__FUNCTION__."  frame:". Tools::asciiStringToHexString($message)." string len:".strlen($message));

        $curl = new Curl();
        $curl->setTimeout(1);
        $curl->setHeader("Content-Type","application/json");
        $curl->setHeader('Cookie','ci_session='.$session_id);

        $url = $url.$action;
        $curl->post($url, $data);
        //self::log(__NAMESPACE__."/".__FUNCTION__." send to $url");
        Log::debug(__NAMESPACE__."/".__FUNCTION__." send to $url");


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

        $action=\Config::get('gateway.monitor_api_on_close');
        $localServer = \Config::get('gateway.host');
        $localPort = \Config::get('gateway.port');
        $url = \Config::get('gateway.monitor_url');
        $session_id = md5($localServer);

        $data = sprintf(\Config::get('gateway.offline'),$localServer,$localPort,$client_id);
        self::log(__NAMESPACE__."/".__FUNCTION__."@".__LINE__." $client_id 断开链接");

        $curl = new Curl();
        $curl->setTimeout(1);
        $curl->setHeader("Content-Type","application/json");
        $curl->setHeader('Cookie','ci_session='.$session_id);

        $url = $url.$action;
        $curl->post($url, $data);
        self::log(__NAMESPACE__."/".__FUNCTION__." send to $url");

        $curl->close();

    }
}