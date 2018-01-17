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

class CalibratTime extends Frame
{

    //功能码
    protected $funCode = 0x04;

    //数据单元标识
    protected $identificat = 0x06;
    
    /**
     * 对时状态
     * @var int
     */
    protected $result = [BIN::class,1,TRUE];




}