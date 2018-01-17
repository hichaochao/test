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

class GetStateFrequency extends Frame
{

    //功能码AFN
    protected $funCode = 0x03;

    //数据单元标识FN
    protected $identificat = 0x03;

    /**
     * 设定时间(分钟)
     * @var int
     */
    protected $setTime = [BIN::class,1,TRUE];

    


}