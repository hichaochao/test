<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 10:06
 */

namespace Wormhole\Protocols\HD10\Protocol\Server\DataArea;


use Wormhole\Protocols\Tools;

class StopCharge
{
    //桩编号
    private $evseCode;


    /**
     * @var int
     */
    private $userId;

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }



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

        $frame = array_merge($frame,Tools::decToArray($this->userId,8,FALSE));
        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->userId = Tools::arrayToDec(array_slice($dataArea,$offset,8,FALSE));
        $offset+=8;
    }

}