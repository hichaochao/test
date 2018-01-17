<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:20
 */

namespace Wormhole\Protocols\HD10\Protocol\Server;



use Wormhole\Protocols\HD10\Protocol\Frame1;
use Wormhole\Protocols\Library\BIN;
use Wormhole\Protocols\Library\CheckSum;

class UpgradeFileInfo extends Frame1
{


    protected $cmd = 0x12;
    protected $func = 0x01;
    /**
     * 文件大小
     * @var int
     */
    protected $size = [BIN::class,4, FALSE];
    /**
     * 数据包总个数
     * @var int
     */
    protected $packetNumber = [BIN::class,2, FALSE];
    /**
     * 单包数据长度
     * @var int
     */
    protected $packetLength = [BIN::class,2, FALSE];
    /**
     * 文件校验和
     * @var int
     */
    protected $checkSum = [BIN::class,2, FALSE];//[CheckSum::class,2, FALSE];


}