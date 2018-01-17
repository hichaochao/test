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

class Restart extends Frame
{

    //功能码
    protected $funCode = 0x04;

    //数据单元标识
    protected $identificat = 0x01;
    
    /**
     * 重启类型
     * @var int
     */
    protected $restartType = [BIN::class,1,TRUE];




}