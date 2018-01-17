<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-03
 * Time: 16:30
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse;


use Wormhole\Protocols\HD10\Protocol\Frame1;
use Wormhole\Protocols\Library\BIN;

class UpgradeComfirm extends Frame1
{
    protected $cmd = 0x12;
    protected $func = 0x04;

    /**
     * 结果：0 成功 1失败
     * @var
     */
    protected $result = [BIN::class,1,FALSE];

}