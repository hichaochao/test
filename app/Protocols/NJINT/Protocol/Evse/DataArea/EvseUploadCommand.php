<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 10:48
 */


namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
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
     * 充电桩编号32位 ascii
     * @var string
     */
    private $poleId;
    /**
     * @var int 充电枪口
     */
    private $gunNum;
    /**
     * @var string 请求启始地址
     */
    private $reuestStartAddress;
    /**
     * @var int 请求命令个数
     */
    private $reuestCommandAmount;



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
    public function getReuestStartAddress()
    {
        return $this->reuestStartAddress;
    }

    /**
     * @param int $reuestStartAddress
     */
    public function setHeartbeatSequence($reuestStartAddress)
    {
        $this->reuestStartAddress = $reuestStartAddress;
    }
    /**
     * @return int
     */
    public function getReuestCommandAmount()
    {
        return $this->reuestCommandAmount;
    }

    /**
     * @param int $reuestCommandAmount
     */
    public function setReuestCommandAmount($reuestCommandAmount)
    {
        $this->reuestCommandAmount = $reuestCommandAmount;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->poleId,32,0)); //充电桩编号

        array_push($frame,$this->gunNum);//充电枪口

        $frame = array_merge($frame,Tools::decToArray($this->reuestStartAddress,4)); //请求启始地址

        array_push($frame,$this->reuestCommandAmount);//请求命令个数


        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->poleId = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;
        $this->gunNum = $dataArea[$offset];
        $offset++;

        $this->reuestStartAddress = Tools::arrayToDec( array_slice( $dataArea,$offset,4));
        $offset+=4;

        $this->reuestCommandAmount = $dataArea[$offset];
        $offset++;


    }
}