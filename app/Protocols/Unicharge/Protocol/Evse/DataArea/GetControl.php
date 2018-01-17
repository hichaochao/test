<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016/10/24
 * Time: 10:06
 */

namespace Wormhole\Protocols\Unicharge\Protocol\Evse\DataArea;
use Wormhole\Protocols\Tools;
class GetControl
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param $token
     */
    public function setToken($token){
        $this->token = $token;
    }

    public function getToken(){
        return $this->token;
    }


    public function build(){

        $frame =array();
        $frame = array_merge($frame,Tools::asciiStringToDecArray($this->token)); //decToArray
        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->token = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32));
        $offset = $offset+32;

    }

}