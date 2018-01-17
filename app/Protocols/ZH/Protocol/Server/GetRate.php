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

class GetRate extends Frame
{

    //功能码AFN
    protected $funCode = 0x03;

    //数据单元标识FN
    protected $identificat = 0x02;




}