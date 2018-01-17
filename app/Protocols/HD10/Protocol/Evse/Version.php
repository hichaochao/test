<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:20
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse;


use Wormhole\Protocols\HD10\Protocol\Frame1;
use Wormhole\Protocols\Library\BIN;

class Version extends Frame1
{

    protected $cmd = 0x12;
    protected $func = 0x03;

    /**
     * 版本号
     * @var int
     */
    protected $version = [BIN::class,4,FALSE];
    /**
     * 发布日期
     * @var
     */
    protected $publishDate = [BIN::class,4,FALSE];

}