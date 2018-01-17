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

class GetRealTimeState extends Frame
{

    //功能码
    protected $funCode = 0x07;

    //数据单元标识
    protected $identificat = 0xF3;
    
    /**
     * 充电桩型号
     * @var int
     */
    protected $evseType = [BIN::class,1,TRUE];

    /**
     * 充电桩运行状态
     * @var int
     */
    protected $runStatus = [BIN::class,1,TRUE];

    /**
     * 充电桩最大输出电压
     * @var int
     */
    protected $maxVoltage = [BIN::class,2,TRUE];

    /**
     * 充电桩最大输出电流
     * @var int
     */
    protected $maxCurrent = [BIN::class,2,TRUE];


    /**
     * 充电接口A状态
     * @var int
     */
    protected $astatus = [BIN::class,1,TRUE];


    /**
     * 充电接口B状态
     * @var int
     */
    protected $bstatus = [BIN::class,1,TRUE];


    /**
     * 与后台系统通信接口
     * @var int
     */
    protected $communicationInterface = [BIN::class,1,TRUE];


}