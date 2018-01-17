<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:20
 */

namespace Wormhole\Protocols\ZH\Protocol\Server;



use Wormhole\Protocols\ZH\Protocol\Frame;
use Wormhole\Protocols\Library\BIN;
use Wormhole\Protocols\Library\BCD;

class SetRate extends Frame
{

    //功能码AFN
    protected $funCode = 0x02;

    //数据单元标识FN
    protected $identificat = 0x02;

    /**
     * 费率模式
     * @var int
     */
    protected $ratePattern = [BIN::class,1,TRUE];

    /**
     * 总费率电价(总电量)
     * @var int
     */
    protected $totalElectricity = [BIN::class,2,TRUE];

    /**
     * 费率1电价(尖)
     * @var int
     */
    protected $tipPrice = [BIN::class,2,TRUE];

    /**
     * 费率2电价(峰)
     * @var int
     */
    protected $peakPrice = [BIN::class,2,TRUE];

    /**
     * 费率3电价(平)
     * @var int
     */
    protected $flatPrice = [BIN::class,2,TRUE];


    /**
     * 费率4电价(谷)
     * @var int
     */
    protected $valleyPrice = [BIN::class,2,TRUE];

    /**
     * 预约费率
     * @var int
     */
    protected $appointmentRate = [BIN::class,2,TRUE];




}