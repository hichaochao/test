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
use Wormhole\Protocols\Library\ASCII;

class BatteryStatuData extends Frame
{

    //功能码
    protected $funCode = 0x06;

    //数据单元标识
    protected $identificat = 0x04;
    
    /**
     * 充电接口标识
     * @var int
     */
    protected $portNumber = [BIN::class,1,TRUE];

    /**
     * 充电电压测量值
     * @var int
     */
    protected $voltageValue = [BIN::class,2,TRUE];

    /**
     * 充电电流测量值
     * @var int
     */
    protected $currentValue = [BIN::class,2,TRUE];


    /**
     * 最高单体动力蓄电池电压
     * @var int
     */
    protected $voltage = [BIN::class,2,TRUE];

    /**
     * SOC
     * @var int
     */
    protected $soc = [BIN::class,2,TRUE];

    /**
     * 估算剩余充电时间
     * @var int
     */
    protected $leftTime = [BIN::class,2,TRUE];



}