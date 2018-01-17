<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 14:11
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

class EvseControl  extends DataArea
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
     * 充电桩编码 ascii 字符串
     * @var string
     */
    private $poleId;

    /**
     * @var int 充电枪口
     */
    private $gunNum;
    /**
     * 命令起始标志，同设置命令
     * @var int
     */
    private $cmdStartPosition;
    /**
     * 命令个数，同设置命令
     * @var int
     */
    private $cmdNum;
    /**
     * 命令执行结果
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
     * @return string
     */
    public function getPoleId()
    {
        return $this->poleId;
    }


    /**
     * @param string $poleId
     */
    public function setPoleId($poleId)
    {
        $this->poleId = $poleId;
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
    public function getCmdStartPosition()
    {
        return $this->cmdStartPosition;
    }

    /**
     * @param int $cmdStartPosition
     */
    public function setCmdStartPosition($cmdStartPosition)
    {
        $this->cmdStartPosition = $cmdStartPosition;
    }

    /**
     * @return int
     */
    public function getCmdNum()
    {
        return $this->cmdNum;
    }

    /**
     * @param int $cmdNum
     */
    public function setCmdNum($cmdNum)
    {
        $this->cmdNum = $cmdNum;
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


    /**
     *
     */
    public function build(){

        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->poleId,32,0));//充电桩编码

        array_push($frame,$this->gunNum);//充电枪口
        //var_dump($frame);

        $frame = array_merge($frame,Tools::decToArray($this->cmdStartPosition,4)); //命令起始标志

        array_push($frame,$this->cmdNum);//命令个数

        array_push($frame,$this->excuteResult);//执行结果

        return $frame;

    }

    /**
     * @param $dataArea
     */
    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->setPoleId( trim(  Tools::decArrayToAsciiString(   array_slice($dataArea,$offset,32))));
        $offset+=32;

        $this->gunNum = $dataArea[$offset];
        $offset++;

        $this->cmdStartPosition = Tools::arrayToDec( array_slice( $dataArea,$offset,4));
        $offset+=4;

        $this->cmdNum = $dataArea[$offset];
        $offset++;

        $this->excuteResult = $dataArea[$offset];
        $offset++;

    }
}