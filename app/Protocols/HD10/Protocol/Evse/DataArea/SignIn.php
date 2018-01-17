<?php
namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;

use Wormhole\Protocols\Tools;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 19:32
 */
class SignIn
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    /**
     * @param $evseCode
     */
    public function setEvseCode($evseCode){
        $this->evseCode = $evseCode;
    }

    public function getEvseCode(){
        return $this->evseCode;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice(  $dataArea,$offset,8));
    }
}