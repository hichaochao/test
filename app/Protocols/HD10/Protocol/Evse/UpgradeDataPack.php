<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:20
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse;


use Wormhole\Protocols\HD10\Protocol\Frame1;
use Wormhole\Protocols\Library\BIN;

class UpgradeDataPack extends Frame1
{


    protected $cmd = 0x12;
    protected $func = 0x02;
    /**
     * 操作结果：0xaa 接受，0x55拒绝
     * @var int
     */
    protected $result = [BIN::class,1,FALSE];


}