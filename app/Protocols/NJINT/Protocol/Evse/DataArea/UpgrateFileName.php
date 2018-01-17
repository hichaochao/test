<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 16:03
 */
namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UpgrateFileName extends DataArea
{
    /**
     * @var int 允许服务发送的升级数据报文数据长度
     */
    private $allowedUpgrateDataLength;
    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getAllowedUpgrateDataLength()
    {
        return $this->allowedUpgrateDataLength;
    }

    /**
     * @param int $allowedUpgrateDataLength
     */
    public function setAllowedUpgrateDataLength($allowedUpgrateDataLength)
    {
        $this->allowedUpgrateDataLength = $allowedUpgrateDataLength;
    }



    public function build(){
        $frame =Tools::decToArray($this->allowedUpgrateDataLength,4);
        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->allowedUpgrateDataLength = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;


    }




}