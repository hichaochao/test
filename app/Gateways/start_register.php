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




// register 服务必须是text协议
$socketName = \Config::get('gateway.register.protocol') ."://".\Config::get('gateway.register.ip').":".\Config::get('gateway.register.port');
$register = new \GatewayWorker\Register($socketName);


