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

class ModuleExceptionInformation extends Frame
{

    //功能码
    protected $funCode = 0x05;

    //数据单元标识
    protected $identificat = 0x04;

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
     * 整流模块位号
     * @var int
     */
    protected $moduleNumber = [BIN::class,1,TRUE];

    /**
     * 整流模块异常信息编码
     * @var int
     */
    protected $informationCode = [BIN::class,1,TRUE];

    /**
     * 异常状态类型
     * @var int
     */
    protected $statuType = [BIN::class,1,TRUE];


}