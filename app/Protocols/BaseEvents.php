<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-01-10
 * Time: 11:19
 */

namespace Wormhole\Protocols;


use Wormhole\Console\Commands\Worker;
use Illuminate\Support\Facades\Log;
use \GatewayWorker\Lib\Gateway;
use Wormhole\Protocols\Unicharge\Protocol\Base\UpgradeFrame;
use Wormhole\Protocols\Unicharge\Protocol\Evse\DataArea\GetControl AS EvseGetControlDataArea;
use Wormhole\Protocols\Unicharge\Protocol\Evse\Frame\GetControl AS EvseGetControlFrame;
use Wormhole\Protocols\Unicharge\Protocol\Server\DataArea\GetControl AS ServerGetControlDataArea;
use Wormhole\Protocols\Unicharge\Protocol\Server\Frame\GetControl AS ServerGetControlFrame;

class BaseEvents extends Worker
{

    protected static $hasUpgradeFrame = TRUE;

    //绑定clientid
    public static function binding($evseCliendId, $factoryClientId){

        \Cache::forever($evseCliendId, $factoryClientId);
        \Cache::forever($factoryClientId, $evseCliendId);
        Log::debug( __NAMESPACE__ . "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " evseCliendId:$evseCliendId,  factoryClientId:$factoryClientId");
        return true;
    }
    /**
     * @param $client_id
     * @param $msg
     * @param $serverAddress
     * @return bool
     */
    public static function sendMsg($client_id, $msg){
        Log::info( __NAMESPACE__ .  "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " Client_id = $client_id; msg = ".bin2hex($msg));
        $result = Gateway::sendToClient($client_id,  $msg);
        return $result;
    }

    /**
     * 当客户端连接时触
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        //// 向当前client_id发送数据
        //Gateway::sendToClient($client_id, "Hello $client_id");
        //// 向所有人发送
        //Gateway::sendToAll("$client_id login");

        //清掉之前的缓存
        $clentId = \Cache::get($client_id);

        if(!empty($clentId)){

            \Cache::forget($client_id);
            \Cache::forget($clentId);
        }
        $address = $_SERVER['REMOTE_ADDR'];
        $port = $_SERVER['REMOTE_PORT'];

        $message = "New connect id : $client_id , address:$address , port : $port ";
        if(isset($_SERVER['HTTP-X-REAL-IP'])){
            $message .= " Real Ip(ngix):". $_SERVER['HTTP-X-REAL-IP'];
        }
        Log::debug(__NAMESPACE__. "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . $message .PHP_EOL);

    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function beforeMessage($client_id, $message){
        Log::debug( __NAMESPACE__.  "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "  START");

        $frame = UpgradeFrame::load($message);
        $result = TRUE;
        Log::debug( __NAMESPACE__.  "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 是否授权帧： ". (!empty($frame)?"TRUE":"FALSE"));
        if(!empty($frame)){
            switch ($frame->getOperator()){
                case EvseGetControlFrame::OPERATOR:
                    Log::debug( __NAMESPACE__ .  "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " $client_id 请求获取控制权");

                    $dataArea = new EvseGetControlDataArea();
                    $dataArea->load($dataArea);
                    $token = $dataArea->getToken();


                    $monitorEvseCode = MonitorServer::validateControlToken($token);//验证结果，成功继续，返回monitorEvseCode，失败，返回false
                    if(FALSE === $monitorEvseCode){
                        Log::debug( __NAMESPACE__ .  "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 无效TOKEN，验证失败");
                    }else{
                        if(is_callable(static::getControl)){
                            call_user_func(static::getControl,$client_id,$monitorEvseCode);
                        }
                    }
                    $result=FALSE;
                    break;
                case ServerGetControlFrame::OPERATOR:
                    Log::debug( __NAMESPACE__ .  "/".__CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 请求解除控制权");

                    if(is_callable(static::removeControl)){
                        call_user_func(static::removeControl,$client_id);
                    }

                    $result=FALSE;
                    break;
                default:
            }
        }
        return $result;
    }


    /**
     * 当客户端发来消息时触发
     * @param string $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function message($client_id, $message){

        $sendClentId = \Cache::get($client_id);
        $result = FALSE;
        if(!empty($sendClentId)){
            Log::debug(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." sendClentId:".$sendClentId);
            $result = Gateway::sendToClient($sendClentId,  $message);
        }
        return $result;
    }

    /**
     * @param string $client_id 链接ID
     * @return bool true:continue ; false :end;
     */
    public static function continueMessage($client_id){
        $sendToClientId = \Cache::get($client_id);
        return empty($sendToClientId)?TRUE:FALSE;
    }


    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function afterMessage($client_id, $message){

    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message) {
        Log::info(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." START".PHP_EOL." client:$client_id , new message:". bin2hex($message));
        if(static::$hasUpgradeFrame){
            $result = static::beforeMessage($client_id,$message);
            Log::debug(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." beforeMessage Result :".($result?"TRUE":"FALSE"));
            if(FALSE === $result){
              return FALSE;
            }
        }
        $result = static::message($client_id,$message);
        Log::debug(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." message result : $result");
        if(FALSE === $result) {
            return FALSE;
        }
        static::afterMessage($client_id, $message);

        Log::debug(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." END".PHP_EOL.PHP_EOL);

    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {
        Log::debug(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." Start, client: $client_id ");
        $client2 = \Cache::get($client_id);
        if(!empty($ClentId)){
            Log::debug(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." 清空链接关系, client: $client_id ，client2 ： $client2 ");
            \Cache::forget($client_id);
            \Cache::forget($client2);
        }

        Log::debug(__NAMESPACE__. "/".__CLASS__."/".__FUNCTION__."@".__LINE__." END".PHP_EOL.PHP_EOL);

    }

}