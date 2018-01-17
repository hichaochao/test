<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 12:06
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;


use Wormhole\Protocols\Tools;

class EventUpload
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    /**
     * @var int
     * 状态
     */
    private $status;

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        array_push($frame,$this->status);

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->status =array_slice($dataArea,$offset,1)[0];
        $offset++;
    }
}