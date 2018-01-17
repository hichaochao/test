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
use Wormhole\Protocols\Library\ASCII;

class CodeBalance extends Frame
{

    //功能码
    protected $funCode = 0x04;

    //数据单元标识
    protected $identificat = 0xF9;


    /**
     * 充电接口
     * @var int
     */
    protected $port = [BIN::class,1,TRUE];

    /**
     * 卡号
     * @var int
     */
    protected $cardNumber = [ASCII::class,16,TRUE];


    /**
     * 二维码类别
     * @var int
     */
    protected $type = [BIN::class,1,TRUE];



    /**
     * 二维码长度
     * @var int
     */
    protected $length = [BIN::class,1,TRUE];



    /**
     * 二维码内容
     * @var int
     */
    protected $content = [ASCII::class,16,TRUE];


    


}