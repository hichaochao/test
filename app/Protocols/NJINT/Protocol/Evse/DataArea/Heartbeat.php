<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 15:42
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

class Heartbeat extends DataArea
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
    private $evseCode;


    /**
     * 心跳序号
     * @var int
     */
    private $heartbeatSequence;

    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
        $this->heartbeatSequence=0;
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
    public function getEvseCode()
    {
        return $this->evseCode;
    }

    /**
     * @param string $evseCode
     */
    public function setEvseCode($evseCode)
    {
        $this->evseCode = $evseCode;
    }
    /**
     * @return int
     */
    public function getHeartbeatSequence()
    {
        return $this->heartbeatSequence;
    }

    /**
     * @param int $heartbeatSequence
     */
    public function setHeartbeatSequence($heartbeatSequence)
    {
        $this->heartbeatSequence = $heartbeatSequence;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->evseCode,32,0)); //充电桩编号

        $frame = array_merge($frame,Tools::decToArray($this->heartbeatSequence,2)); //心跳序号

        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->evseCode = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;

        $this->heartbeatSequence = Tools::arrayToDec( array_slice(  $dataArea,$offset,2));
        $offset+=2;


    }
}