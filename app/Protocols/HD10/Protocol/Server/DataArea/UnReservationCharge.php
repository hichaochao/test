<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 18:06
 */

namespace Wormhole\Protocols\HD10\Protocol\Server\DataArea;


use Wormhole\Protocols\Tools;

class UnReservationCharge
{
    //桩编号
    private $evseCode;

    //用户id
    private $userId;

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
     * @param $userId
     */
    public function setUserId($userId){
        $this->userId = $userId;
    }

    public function getUserId(){
        return $this->userId;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        $frame = array_merge($frame,Tools::decToArray($this->userId,8,FALSE));

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->userId = Tools::arrayToDec(array_slice($dataArea,$offset,8),FALSE);
        $offset+=8;
    }
}