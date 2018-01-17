<?php
namespace Wormhole\Protocols\HD10HotSwap\Controllers\Api;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 15:52
 */
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\HD10\Models\ChargeOrderMapping;
use Wormhole\Protocols\HD10\Models\Evse;
use Wormhole\Protocols\HD10\Protocol\ServerFrame;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\HD10\Events;
use Wormhole\Protocols\HD10\EventsApi;
use Wormhole\Validators\RealtimeChargeInfoValidator;
use Wormhole\Validators\StartChargeValidator;
use Wormhole\Validators\StopChargeValidator;

use Wormhole\Validators\GetStatusValidator;
use Wormhole\Validators\GetMultiStaticticsPowerValidator;
use Wormhole\Validators\GetChargeHistoryValidator;
use Wormhole\Validators\SetCommodityValidator;
use DB;

class EvseController extends \Wormhole\Protocols\HD10\Controllers\Api\EvseController
{

}