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

class RunData extends Frame
{

    //功能码
    protected $funCode = 0x06;

    //数据单元标识
    protected $identificat = 0xF6;
    
    /**
     * 模块总数 n
     * @var int
     */
    protected $totalModular = [BIN::class,1,TRUE];


    /**
     * 机号
     * @var int
     */
    protected $machineNum = [ASCII::class,6,TRUE];


    /**
     * 输出电压
     * @var int
     */
    protected $voltage = [BIN::class,2,TRUE];


    /**
     * 输出电流
     * @var int
     */
    protected $current = [BIN::class,2,TRUE];

    /**
     * 运行温度
     * @var int
     */
    protected $runTemperature = [BIN::class,1,TRUE];


}