<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-08
 * Time: 11:21
 */

namespace Wormhole\Protocols\HD10HotSwap;

use Illuminate\Support\Facades\Log;
use Workerman\Connection\TcpConnection;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\HD10\Protocol\Frame;

class Protocol extends \Wormhole\Protocols\HD10\Protocol
{
    const NAME="HD10HotSwap";

}