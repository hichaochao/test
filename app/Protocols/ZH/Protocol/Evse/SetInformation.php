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

class SetInformation extends Frame
{

    //功能码
    protected $funCode = 0x02;

    //数据单元标识
    protected $identificat = 0x02;
    
    /**
     * 变更参数状态
     * @var int
     */
    protected $changeStatus = [BIN::class,1,TRUE];



}