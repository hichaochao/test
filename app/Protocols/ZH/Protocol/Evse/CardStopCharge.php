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

class CardStopCharge extends Frame
{

    //功能码
    protected $funCode = 0x08;

    //数据单元标识
    protected $identificat = 0x03;
    
    /**
     * 充电接口标识
     * @var int
     */
    protected $portNumber = [BIN::class,1,TRUE];

    /**
     * 卡号
     * @var int
     */
    protected $cardNumber = [ASCII::class,16,TRUE];

    /**
     * 消费金额
     * @var int
     */
    protected $consumptionAmount = [BIN::class,4,TRUE];

    /**
     * 余额
     * @var int
     */
    protected $balance = [BIN::class,4,TRUE];


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
     * 充电总度数
     * @var int
     */
    protected $totalChargeDegrees = [BIN::class,4,TRUE];


    /**
     * 总度数金额
     * @var int
     */
    protected $totalMoney = [BIN::class,4,TRUE];

    /**
     * 充电尖度数
     * @var int
     */
    protected $totalElectricTip = [BIN::class,4,TRUE];

    /**
     * 尖度数金额
     * @var int
     */
    protected $electricTipMoney = [BIN::class,4,TRUE];

    /**
     * 充电峰度数
     * @var int
     */
    protected $peakDegree = [BIN::class,4,TRUE];

    /**
     * 峰度数金额
     * @var int
     */
    protected $peakDegreeMoney = [BIN::class,4,TRUE];

    /**
     * 充电平度数
     * @var int
     */
    protected $roughnessNumber = [BIN::class,4,TRUE];

    /**
     * 平度数金额
     * @var int
     */
    protected $roughnessNumberMoney = [BIN::class,4,TRUE];


    /**
     * 充电谷度数
     * @var int
     */
    protected $grainNumber = [BIN::class,4,TRUE];


    /**
     * 充电谷度数金额
     * @var int
     */
    protected $grainMoney = [BIN::class,4,TRUE];






}