<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 14:18
 */
namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class EraseInstruction extends DataArea
{
    /**
     * @var int 擦除/查询指令
     */
    private $erasePercent;

    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getErasePercent()
    {
        return $this->erasePercent;
    }

    /**
     * @param int $erasePercent
     */
    public function setErasePercent($erasePercent)
    {
        $this->erasePercent = $erasePercent;
    }


    public function build(){
        $frame =array();
        array_push($frame,$this->erasePercent);
        return $frame;


    }

    public function load($dataArea){
        $offset = 0;
        $this->erasePercent = $dataArea[$offset];
        $offset+=1;


    }




}