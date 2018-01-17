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



// gateway 进程，这里使用Text协议，可以用telnet测试
$socketName = \Config::get('gateway.gateway.protocol') ."://".\Config::get('gateway.gateway.ip').":".\Config::get('gateway.gateway.port');
$gateway = new \GatewayWorker\Gateway($socketName);
// gateway名称，status方便查看
$gateway->name = \Config::get('gateway.gateway.name');
// gateway进程数
$gateway->count = \Config::get('gateway.gateway.count');
// 本机ip，分布式部署时使用内网ip
$gateway->lanIp = \Config::get('gateway.gateway.lanIp');
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口 
$gateway->startPort = \Config::get('gateway.gateway.startPort');
// 服务注册地址
$socketName = \Config::get('gateway.register.ip').":".\Config::get('gateway.register.port');
$gateway->registerAddress = $socketName;

// 心跳间隔
//$gateway->pingInterval = 10;
// 心跳数据
//$gateway->pingData = '{"type":"ping"}';

/* 
// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
$gateway->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection , $http_header)
    {
        // 可以在这里判断连接来源是否合法，不合法就关掉连接
        // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
        if($_SERVER['HTTP_ORIGIN'] != 'http://kedou.workerman.net')
        {
            $connection->close();
        }
        // onWebSocketConnect 里面$_GET $_SERVER是可用的
        // var_dump($_GET, $_SERVER);
    };
}; 
*/