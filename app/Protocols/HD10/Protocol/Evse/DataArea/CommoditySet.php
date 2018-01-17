<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 11:03
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;


use Wormhole\Protocols\Tools;

class CommoditySet
{
    /**
     * @var string
     */
    private $evseCode;

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

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
    }
}