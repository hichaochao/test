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

class BatteryData extends Frame
{

    //功能码
    protected $funCode = 0x06;

    //数据单元标识
    protected $identificat = 0x03;
    
    /**
     * 充电接口标识
     * @var int
     */
    protected $portNumber = [BIN::class,1,TRUE];

    /**
     * 电池类型
     * @var int
     */
    protected $batteryType = [BIN::class,1,TRUE];

    /**
     * 电池额定容量
     * @var int
     */
    protected $capacity = [BIN::class,2,TRUE];


    /**
     * 电池额定总电压
     * @var int
     */
    protected $totalVoltage = [BIN::class,2,TRUE];

    /**
     * 电池生产厂商名称
     * @var int
     */
    protected $manufacturerName = [ASCII::class,4,TRUE];

    /**
     * 电池组充电次数
     * @var int
     */
    protected $chargeNumber = [BIN::class,3,TRUE];

    /**
     * 车辆识别码(VIN)
     * @var int
     */
    protected $vin = [ASCII::class,17,TRUE];


}