<?php
namespace Wormhole\Protocols\HD10HotSwap\Controllers;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-23
 * Time: 17:40
 */

use Wormhole\Http\Controllers\Controller;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\Protocol\Frame;

use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;
use Wormhole\Protocols\HD10\Models\Evse;

use Wormhole\Protocols\MonitorServer;
use Wormhole\Protocols\NJINT\Events;
class EvseController extends \Wormhole\Protocols\HD10\Controllers\EvseController
{
    //处理自身桩数据

}