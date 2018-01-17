<?php
namespace Wormhole\Protocols\HD10HotSwap;
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
class Events extends \Wormhole\Protocols\HD10\Events
{

}