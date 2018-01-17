<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/7
 * Time: 15:03
 */
namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class Set24HsCommodityStrategy extends DataArea
{
    /**
     * @var int 确认结果
     */
    private $confirmResult;
    public function __construct()
    {

    }



    /**
     * @return int
     */
    public function getConfirmResult()
    {
        return $this->confirmResult;
    }

    /**
     * @param int $confirmResult
     */
    public function setConfirmResult($confirmResult)
    {
        $this->confirmResult = $confirmResult;
    }



    public function build(){
        $frame =array();
        array_push($frame,$this->confirmResult);
        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->confirmResult = $dataArea[$offset];
        $offset+=1;


    }




}