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

class SetPwmDutyRatio extends Frame
{

    //功能码AFN
    protected $funCode = 0x02;

    //数据单元标识FN
    protected $identificat = 0xF5;

    /**
     * 占空比数据
     * @var int
     */
    protected $dutyData = [BIN::class,1,TRUE];

    


}