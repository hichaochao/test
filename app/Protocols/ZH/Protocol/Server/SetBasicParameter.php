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

class SetBasicParameter extends Frame
{

    //功能码AFN
    protected $funCode = 0x02;

    //数据单元标识FN
    protected $identificat = 0xF6;

    /**
     * 模块1组个数
     * @var int
     */
    protected $modularNum1 = [BIN::class,1,TRUE];

    /**
     * 模块2组个数
     * @var int
     */
    protected $modularNum2 = [BIN::class,1,TRUE];


    /**
     * 单模块电压等级
     * @var int
     */
    protected $voltageLevel = [BIN::class,2,TRUE];


    /**
     * 单模块电流等级
     * @var int
     */
    protected $currentLevel = [BIN::class,2,TRUE];


    /**
     * 单模块输出电流限制
     * @var int
     */
    protected $currentLimit = [BIN::class,2,TRUE];


    /**
     * 单模块输出电压上限
     * @var int
     */
    protected $voltageCap = [BIN::class,2,TRUE];


    /**
     * 单模块输出电压下限
     * @var int
     */
    protected $voltageLower = [BIN::class,2,TRUE];

    


}