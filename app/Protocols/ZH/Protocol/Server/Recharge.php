<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:20
 */

namespace Wormhole\Protocols\ZH\Protocol\Server;



use Wormhole\Protocols\ZH\Protocol\Frame;
use Wormhole\Protocols\Library\BIN;
use Wormhole\Protocols\Library\BCD;
use Wormhole\Protocols\Library\ASCII;


class Recharge extends Frame
{

    //功能码
    protected $funCode = 0x08;

    //数据单元标识
    protected $identificat = 0xF5;
    

    /**
     * 充值上报状态
     * @var int
     */
    protected $status = [BIN::class,1,TRUE];




}