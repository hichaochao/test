<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 16:54
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UpgrateFileData extends DataArea
{
    /**
     * @var int 正确接收到 SN
     */
    private $receivedSN;

    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getreceivedSN()
    {
        return $this->receivedSN;
    }

    /**
     * @param int $receivedSN
     */
    public function setreceivedSN($receivedSN)
    {
        $this->receivedSN = $receivedSN;
    }


    public function build(){
        $frame =array();
        array_push($frame,$this->receivedSN);
        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->receivedSN = $dataArea[$offset];
        $offset+=1;


    }




}