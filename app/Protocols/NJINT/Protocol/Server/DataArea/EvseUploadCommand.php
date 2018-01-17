<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 11:20
 */


namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;

use Wormhole\Protocols\Tools;

class EvseUploadCommand extends DataArea
{
    /**
     * @var array 协议预留1
     */
    private $reserved1;
    /**
     * @var array 协议预留2
     */
    private $reserved2;

    /**
     * @var int 充电枪号
     */
    private $gunNum;

    /**
     * 执行结果
     * @var int
     */
    private $excuteResult;


    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
    }


    /**
     * @return array
     */
    public function getReserved1()
    {
        return $this->reserved1;
    }

    /**
     * @param array $reserved1
     */
    public function setReserved1($reserved1)
    {
        $this->reserved1 = $reserved1;
    }

    /**
     * @return array
     */
    public function getReserved2()
    {
        return $this->reserved2;
    }

    /**
     * @param array $reserved2
     */
    public function setReserved2($reserved2)
    {
        $this->reserved2 = $reserved2;
    }
    /**
     * @return int
     */
    public function getGunNum()
    {
        return $this->gunNum;
    }

    /**
     * @param int $gunNum
     */
    public function setGunNum($gunNum)
    {
        $this->gunNum = $gunNum;
    }

    /**
     * @return int
     */
    public function getExcuteResult()
    {
        return $this->excuteResult;
    }

    /**
     * @param int $excuteResult
     */
    public function setExcuteResult($excuteResult)
    {
        $this->excuteResult = $excuteResult;
    }


    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2


        array_push($frame,$this->gunNum);//充电枪口

        $frame = array_merge($frame,Tools::decToArray($this->excuteResult,4));

        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->gunNum = $dataArea[$offset];
        $offset++;

        $this->excuteResult =  Tools::arrayToDec( array_slice($dataArea,$offset,4));


    }
}