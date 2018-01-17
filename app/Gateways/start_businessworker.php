<?php
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




// bussinessWorker 进程
$worker = new \GatewayWorker\BusinessWorker();
// worker名称
$worker->name = \Config::get('gateway.worker.name');
// bussinessWorker进程数量
$worker->count = \Config::get('gateway.worker.count');
// 服务注册地址
$socketName = \Config::get('gateway.register.ip').":".\Config::get('gateway.register.port');
$worker->registerAddress = $socketName;

$worker->eventHandler = \Config::get('gateway.worker.event');

