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

class GetBatteryTemperatureData extends Frame
{

    //功能码
    protected $funCode = 0x07;

    //数据单元标识
    protected $identificat = 0xF7;
    
    /**
     * 充电接口标识
     * @var int
     */
    protected $portNumber = [BIN::class,1,TRUE];

    /**
     * 蓄电池最高温度
     * @var int
     */
    protected $maxTemperature = [BIN::class,1,TRUE];

    /**
     * 蓄电池最低温度
     * @var int
     */
    protected $minTemperature = [BIN::class,1,TRUE];


    /**
     * 电池单体总数
     * @var int
     */
    protected $monomerNum = [BIN::class,1,TRUE];

    /**
     * 单体电压
     * @var int
     */
    protected $monomerVoltage = [BIN::class,2,TRUE];




}