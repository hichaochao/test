<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:20
 */

namespace Wormhole\Protocols\ZH\Protocol\Evse;



use Wormhole\Protocols\ZH\Protocol\Frame;
use Wormhole\Protocols\Library\BIN;
use Wormhole\Protocols\Library\BCD;

class CancelReservationLock extends Frame
{

    //功能码
    protected $funCode = 0x04;

    //数据单元标识
    protected $identificat = 0xF7;

    /**
     * 充电接口
     * @var int
     */
    protected $portNumber = [BIN::class,1,TRUE];

    /**
     * 返回取消预约状态信息
     * @var int
     */
    protected $status = [BIN::class,1,TRUE];




}