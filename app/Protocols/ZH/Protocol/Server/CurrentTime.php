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

class CurrentTime extends Frame
{

    //功能码
    protected $funCode = 0x07;

    //数据单元标识
    protected $identificat = 0xF1;

    

}