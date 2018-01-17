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

class GetIpDomain extends Frame
{

    //功能码AFN
    protected $funCode = 0x03;

    //数据单元标识FN
    protected $identificat = 0xF1;

    /**
     * 主IP地址1段
     * @var int
     */
    protected $mainIp1 = [BIN::class,1,TRUE];

    /**
     * 主IP地址2段
     * @var int
     */
    protected $mainIp2 = [BIN::class,1,TRUE];

    /**
     * 主IP地址3段
     * @var int
     */
    protected $mainIp3 = [BIN::class,1,TRUE];

    /**
     * 主IP地址4段
     * @var int
     */
    protected $mainIp4 = [BIN::class,1,TRUE];

    /**
     * 端口
     * @var int
     */
    protected $mainPort = [BIN::class,2,TRUE];



    /**
     * 次IP地址1段
     * @var int
     */
    protected $secondaryIp1 = [BIN::class,1,TRUE];

    /**
     * 次IP地址2段
     * @var int
     */
    protected $secondaryIp2 = [BIN::class,1,TRUE];

    /**
     * 次IP地址3段
     * @var int
     */
    protected $secondaryIp3 = [BIN::class,1,TRUE];

    /**
     * 次IP地址4段
     * @var int
     */
    protected $secondaryIp4 = [BIN::class,1,TRUE];

    /**
     * 端口
     * @var int
     */
    protected $secondaryPort = [BIN::class,2,TRUE];
    


}