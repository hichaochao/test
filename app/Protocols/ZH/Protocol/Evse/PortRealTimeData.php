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

class PortRealTimeData extends Frame
{

    //功能码
    protected $funCode = 0x06;

    //数据单元标识
    protected $identificat = 0x02;

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
     * 充电接口标识
     * @var int
     */
    protected $portNumber = [BIN::class,1,TRUE];



    /**
     * 充电接口输出电压
     * @var int
     */
    protected $voltage = [BIN::class,2,TRUE];


    /**
     * 充电接口输出电流
     * @var int
     */
    protected $current = [BIN::class,2,TRUE];


    /**
     * 充电接口总电量
     * @var int
     */
    protected $totalElectricity = [BIN::class,4,TRUE];


    /**
     * 充电接口费率 1 电量
     * @var int
     */
    protected $rateElectricity1 = [BIN::class,4,TRUE];


    /**
     * 充电接口费率 2 电量
     * @var int
     */
    protected $rateElectricity2 = [BIN::class,4,TRUE];


    /**
     * 充电接口费率 3 电量
     * @var int
     */
    protected $rateElectricity3 = [BIN::class,4,TRUE];

    /**
     * 充电接口费率 4 电量
     * @var int
     */
    protected $rateElectricity4 = [BIN::class,4,TRUE];


    /**
     * 电能表读数
     * @var int
     */
    protected $ammeterReading = [BIN::class,4,TRUE];


}