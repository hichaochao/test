<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 15:27
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UpgrateFileSize extends DataArea
{
    /**
     * @var string 文件长度
     */
    private $upgrateFileSize;
    public function __construct()
    {

    }



    /**
     * @return string
     */
    public function getUpgrateFileSize()
    {
        return $this->upgrateFileSize;
    }

    /**
     * @param string $upgrateFileSize
     */
    public function setUpgrateFileSize($upgrateFileSize)
    {
        $this->upgrateFileSize = $upgrateFileSize;
    }

    public function build(){
        $frame =Tools::decToArray($this->upgrateFileSize,4);

        return $frame;


    }

    public function load($dataArea){
        $offset = 0;
        $this->upgrateFileSize = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;


    }
}