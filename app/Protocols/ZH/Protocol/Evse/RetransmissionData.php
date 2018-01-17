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

class RetransmissionData extends Frame
{

    //功能码
    protected $funCode = 0x08;

    //数据单元标识
    protected $identificat = 0x04;

    /**
     * 重传的充电数据个数
     * @var int
     */
    protected $portNum = [BIN::class,2,TRUE];

    /**
     * 充电接口
     * @var int
     */
    protected $portNumber = [BIN::class,1,TRUE];

    /**
     * 卡号
     * @var int
     */
    protected $cardNumber = [ASCII::class,16,TRUE];

    /**
     * 启动时间：年月日时分秒
     * @var int
     */
    /**
     * 年
     * @var int
     */
    protected $startYear = [BIN::class,2,TRUE];

    /**
     * 月
     * @var int
     */
    protected $startMonth = [BIN::class,1,TRUE];

    /**
     * 日
     * @var int
     */
    protected $startDay = [BIN::class,1,TRUE];

    /**
     * 时
     * @var int
     */
    protected $startHour = [BIN::class,1,TRUE];

    /**
     * 分
     * @var int
     */
    protected $startMinute = [BIN::class,1,TRUE];

    /**
     * 秒
     * @var int
     */
    protected $startSecond = [BIN::class,1,TRUE];


    /**
     * 结束时间：年月日时分秒
     * @var int
     */
    /**
     * 年
     * @var int
     */
    protected $endYear = [BIN::class,2,TRUE];

    /**
     * 月
     * @var int
     */
    protected $endMonth = [BIN::class,1,TRUE];

    /**
     * 日
     * @var int
     */
    protected $endDay = [BIN::class,1,TRUE];

    /**
     * 时
     * @var int
     */
    protected $endHour = [BIN::class,1,TRUE];

    /**
     * 分
     * @var int
     */
    protected $endMinute = [BIN::class,1,TRUE];

    /**
     * 秒
     * @var int
     */
    protected $endSecond = [BIN::class,1,TRUE];


    /**
     * 充电总度数
     * @var int
     */
    protected $totalDegree = [BIN::class,4,TRUE];

    /**
     * 总度数金额
     * @var int
     */
    protected $totalAmount = [BIN::class,4,TRUE];


    /**
     * 充电尖度数
     * @var int
     */
    protected $cuspNumber = [BIN::class,4,TRUE];


    /**
     * 尖度数金额
     * @var int
     */
    protected $cuspNumberMoney = [BIN::class,4,TRUE];


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
     * 谷度数金额
     * @var int
     */
    protected $grainNumberMoney = [BIN::class,4,TRUE];


}