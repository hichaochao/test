<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-03
 * Time: 16:31
 */

namespace Wormhole\Protocols\HD10\Protocol\Server;


use Wormhole\Protocols\HD10\Protocol\Frame1;

class DeviceReset extends Frame1
{
    protected $cmd = 0x12;
    protected $func = 0x05;

}