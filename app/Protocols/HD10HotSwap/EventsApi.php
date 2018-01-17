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

use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\BaseEvents;
use Wormhole\Protocols\HD10\Controllers\ProtocolController;




use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\ServerFrame;
use Wormhole\Protocols\Tools;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\SignIn AS EvseSignInFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as EvseSignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\SignIn AS ServerSignInFrame;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\SignIn as ServerSignInDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\HeartBeat AS EvseHeartbeatFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as EvseHeartBeatDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\CardSign AS EvseCardSignFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\CardSign AS EvseCardSignDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ReservationCharge AS EvseReservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ReservationCharge AS EvseReservationChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\UnReservationCharge AS EvseUnreservationChargeFrame;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartChargeCheck AS EvseStartChargeCheckFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartChargeCheck AS EvseStartChargeCheckDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartCharge AS EvseStartChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartCharge AS EvseStartChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StopCharge AS EvseStopChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StopCharge AS EvseStopChargeDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeLog AS EvseChargeLogFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeLog AS EvseChargeLogDataArea;

use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeRealtime AS EvseChargeRealtimeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeRealtime AS EvseChargeRealtimeDataArea;


/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class EventsApi extends \Wormhole\Protocols\HD10\EventsApi
{

}
