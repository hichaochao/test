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

class GetBaseData extends Frame
{

    //功能码
    protected $funCode = 0x07;

    //数据单元标识
    protected $identificat = 0xF2;
    
    /**
     * 厂商编号
     * @var int
     */
    protected $manufacturerCode = [ASCII::class,8,TRUE];

    /**
     * 充电桩型号
     * @var int
     */
    protected $evseType = [BIN::class,1,TRUE];

    /**
     * 软件版本
     * @var int
     */
    protected $softwareVersion = [ASCII::class,4,TRUE];

    /**
     * 硬件版本
     * @var int
     */
    protected $hardwareVersion = [ASCII::class,4,TRUE];

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
     * 额定功率
     * @var int
     */
    protected $power = [BIN::class,2,TRUE];


    /**
     * 模块数量
     * @var int
     */
    protected $num = [BIN::class,1,TRUE];


    /**
     * 模块输出电压上限
     * @var int
     */
    protected $voltageCap = [BIN::class,2,TRUE];


    /**
     * 模块输出电压下限
     * @var int
     */
    protected $volageLower = [BIN::class,1,TRUE];


    /**
     * 模块额定电流
     * @var int
     */
    protected $current = [BIN::class,1,TRUE];


    /**
     * 充电枪额定电压
     * @var int
     */
    protected $volage = [BIN::class,1,TRUE];

    /**
     * 充电枪额定电流
     * @var int
     */
    protected $portCurrent = [BIN::class,1,TRUE];


    /**
     * 直流接触器额定电压
     * @var int
     */
    protected $portVolage = [BIN::class,1,TRUE];


    /**
     * 直流接触器额定电流
     * @var int
     */
    protected $directCurrent = [BIN::class,1,TRUE];


    /**
     * 与后台通信方式
     * @var int
     */
    protected $communicationMode = [BIN::class,1,TRUE];


}