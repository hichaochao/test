<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:20
 */

namespace Wormhole\Protocols\HD10\Protocol\Server;


use Wormhole\Protocols\HD10\Protocol\Frame1;

class UpgradeDataPack extends Frame1
{


    protected $cmd = 0x12;
    protected $func = 0x02;
    /**
     * 数据内容
     */
    protected $data = "";


}