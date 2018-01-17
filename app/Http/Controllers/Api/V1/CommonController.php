<?php
namespace Wormhole\Http\Controllers\Api\V1;
use Wormhole\Http\Controllers\Api\BaseController;
use Workerman\Worker;
use Wormhole\Validators\SendCmdValidator;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-10-03
 * Time: 16:37
 */
class CommonController extends BaseController
{
    public function sendCmd( SendCmdValidator $sendCmdValidator, $hash){
        
        $params = $this->request->all();

        $validator = $sendCmdValidator->make($params);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $clientId = $params['params']['client_id'];
        $frame = $params['params']['frame'];



        if(empty($clientId)){
            return FALSE;
        }

        //Worker::$logFile = '/storage/logs/workerman.log';
        Worker::$logFile =  app_path().'/../storage/logs/workerman.log';
        Worker::$daemonize = true;

        $event = \Config::get('gateway.worker.event');
            //连接worekman发送指令
        $result = $event::sendMsg($clientId, $frame);

        return $this->response->array([
            'status'=>$result,
            'error_msg'=>$result?"发送成功":"发送失败",
            "error_code"=>$result?200:400
            ]);
    }


    public function binDing(){

        $params = $this->request->all();
        $cliendId = $params['params']['frame'];
        $cliendId = base64_decode($cliendId);
        $cliendIdArr = explode(',',$cliendId);
        $evseCliendId = $cliendIdArr[0];
        $factoryClientId = $cliendIdArr[1];

        $event = \Config::get('gateway.worker.event');
        //连接worekman发送指令
        $result = $event::binDing($evseCliendId, $factoryClientId);

        return $this->response->array([
            'status'=>$result,
            'error_msg'=>$result?"发送成功":"发送失败",
            "error_code"=>$result?200:400
        ]);


    }




}