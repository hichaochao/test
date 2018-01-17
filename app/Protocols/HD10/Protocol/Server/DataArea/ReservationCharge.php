<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 17:32
 */

namespace Wormhole\Protocols\HD10\Protocol\Server\DataArea;


use Wormhole\Protocols\Tools;

class ReservationCharge
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    /**
     * @var int
     * 用户id
     */
    private $userId;

    /**
     * @var int
     * 时间戳
     */
    private $time;

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

    /**
     * @param $time
     */
    public function setTime($time){
        $this->time = $time;
    }

    public function getTime(){
        return $this->time;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        $frame = array_merge($frame,Tools::decToArray($this->userId,8,FALSE));

        $frame = array_merge($frame,Tools::decToArray($this->time,4,FALSE));

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;

        $this->userId = Tools::arrayToDec(array_slice($dataArea,$offset,8),FALSE);
        $offset+=8;

        $this->time = Tools::arrayToDec(array_slice($dataArea,$offset,4),FALSE);
        $offset+=4;
    }
}