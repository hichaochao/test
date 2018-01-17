<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 11:13
 */

namespace Wormhole\Protocols\HD10\Protocol\Server\DataArea;


use Wormhole\Protocols\Tools;

class ChargeLog
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    /**
     * @var string
     * 返回值
     */
    private $resultCode;

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
     * @return string
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * @param string $resultCode
     */
    public function setResultCode($resultCode)
    {
        $this->resultCode = $resultCode;
    }

    public function build() {
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        array_push($frame,$this->resultCode);

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->resultCode = array_slice($dataArea,$offset,1)[0];
        $offset+=1;
    }
}