<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 17:00
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class ChargingHistory extends DataArea
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
     * 查询记录起始索引
     * @var int
     */
    private $logIndex;
    /**
     * 查询充电记录个数
     * @var
     */
    private $logAmount;


    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
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
     * @return int
     */
    public function getLogIndex()
    {
        return $this->logIndex;
    }

    /**
     * @param int $logIndex
     */
    public function setLogIndex($logIndex)
    {
        $this->logIndex = $logIndex;
    }

    /**
     * @return mixed
     */
    public function getLogAmount()
    {
        return $this->logAmount;
    }

    /**
     * @param mixed $logAmount
     */
    public function setLogAmount($logAmount)
    {
        $this->logAmount = $logAmount;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2


        $frame = array_merge($frame,Tools::decToArray($this->logIndex,4));

        $frame = array_merge($frame,Tools::decToArray($this->logAmount,4));

        return $frame;

    }
    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->logIndex = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->logAmount = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

    }
}