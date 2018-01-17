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
use Wormhole\Protocols\Library\ASCII;


class Recharge extends Frame
{

    //功能码
    protected $funCode = 0x08;

    //数据单元标识
    protected $identificat = 0xF5;
    
    /**
     * 卡号
     * @var int
     */
    protected $cardNumber = [ASCII::class,16,TRUE];

    /**
     * 充值时间：年月日时分秒
     * @var int
     */
    /**
     * 年
     * @var int
     */
    protected $year = [BIN::class,2,TRUE];

    /**
     * 月
     * @var int
     */
    protected $month = [BIN::class,1,TRUE];

    /**
     * 日
     * @var int
     */
    protected $day = [BIN::class,1,TRUE];

    /**
     * 时
     * @var int
     */
    protected $hour = [BIN::class,1,TRUE];

    /**
     * 分
     * @var int
     */
    protected $minute = [BIN::class,1,TRUE];

    /**
     * 秒
     * @var int
     */
    protected $second = [BIN::class,1,TRUE];

    /**
     * 余额
     * @var int
     */
    protected $balance = [BIN::class,4,TRUE];

    /**
     * 充值金额
     * @var int
     */
    protected $rechargeMony = [BIN::class,4,TRUE];

    /**
     * 总金额
     * @var int
     */
    protected $totalMony = [BIN::class,4,TRUE];


}