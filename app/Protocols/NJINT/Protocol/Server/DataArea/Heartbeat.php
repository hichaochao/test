<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 15:50
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;

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
     * 心跳序号
     * @var int
     */
    private $heartbeatReply;

    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
        $this->heartbeatReply=0;
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
    public function getHeartbeatReply()
    {
        return $this->heartbeatReply;
    }

    /**
     * @param int $heartbeatReply
     */
    public function setHeartbeatReply($heartbeatReply)
    {
        $this->heartbeatReply = $heartbeatReply;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2


        $frame = array_merge($frame,Tools::decToArray($this->heartbeatReply,2)); //心跳序号

        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->heartbeatReply = Tools::arrayToDec( array_slice(  $dataArea,$offset,2));
        $offset+=2;


    }
}