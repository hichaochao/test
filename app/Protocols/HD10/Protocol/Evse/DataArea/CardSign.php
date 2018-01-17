<?php
namespace Wormhole\Protocols\HD10\Protocol\Evse\DataArea;

use Wormhole\Protocols\Tools;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 16:40
 */
class CardSign
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    /**
     * @var int
     * 卡号
     */
    private $cardNumber;

    /**
     * @param $evseCode
     */
    public function setEvseCode($evseCode){
        $this->evseCode = $evseCode;
    }

    public function getEvseCode(){
        return $this->evseCode;
    }

    /**
     * @param $cardCode
     */
    public function setCardNumber($cardNumber){
        $this->cardNumber = $cardNumber;
    }

    public function getCardNumber(){
        return $this->cardNumber;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        $frame = array_merge($frame,Tools::asciiStringToDecArray($this->cardNumber));

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->cardNumber = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;
    }
}