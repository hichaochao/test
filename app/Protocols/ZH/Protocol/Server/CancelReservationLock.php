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

class CancelReservationLock extends Frame
{

    //功能码
    protected $funCode = 0x04;

    //数据单元标识
    protected $identificat = 0xF7;

    /**
     * 秒
     * @var int
     */
    protected $second = [BIN::class,1,TRUE];

    /**
     * 分
     * @var int
     */
    protected $minute = [BIN::class,1,TRUE];

    /**
     * 时
     * @var int
     */
    protected $hour = [BIN::class,1,TRUE];


    /**
     * 日
     * @var int
     */
    protected $day = [BIN::class,1,TRUE];


    /**
     * 月
     * @var int
     */
    protected $month = [BIN::class,1,TRUE];


    /**
     * 年
     * @var int
     */
    protected $year = [BIN::class,2,TRUE];

    /**
     * 充电接口
     * @var int
     */
    protected $port = [BIN::class,1,TRUE];

    /**
     * 卡号
     * @var int
     */
    protected $cardNumber = [ASCII::class,16,TRUE];

    


}