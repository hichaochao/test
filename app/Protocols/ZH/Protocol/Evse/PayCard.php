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
use Wormhole\Protocols\Library\ASCII;

class PayCard extends Frame
{

    //功能码
    protected $funCode = 0x08;

    //数据单元标识
    protected $identificat = 0x01;
    
    /**
     * 卡号
     * @var int
     */
    protected $cardNumber = [ASCII::class,16,TRUE];

    /**
     * 密码
     * @var int
     */
    protected $password = [ASCII::class,6,TRUE];

    /**
     * 余额
     * @var int
     */
    protected $balance = [BIN::class,4,TRUE];


}