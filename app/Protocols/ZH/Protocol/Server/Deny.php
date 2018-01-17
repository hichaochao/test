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

class Deny extends Frame
{

    //功能码AFN
    protected $funCode = 0x00;

    //数据单元标识FN
    protected $identificat = 0xF2;

    /**
     * 信息代码
     * @var int
     */
    protected $authPassword = [BIN::class,1,TRUE];

    


}