<?php
namespace Wormhole\Protocols\HD10\Protocol\Server\DataArea;

use Wormhole\Protocols\Tools;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 19:04
 */

class SignIn
{
    /**
     * @var string
     * 桩编号
     */
    private $evseCode;

    private $resultCode;

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
     * @param $resultCode
     */
    public function setResultCode($resultCode){
        $this->resultCode = $resultCode;
    }

    public function getResultCode(){
        return $this->resultCode;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        array_push($frame,$this->resultCode);

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice(  $dataArea,$offset,8));
        $offset+=8;

        $this->resultCode =array_slice(  $dataArea,$offset,1)[0];
        $offset+=1;
    }
}