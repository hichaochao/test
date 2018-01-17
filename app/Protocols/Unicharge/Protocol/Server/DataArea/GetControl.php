<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016/10/24
 * Time: 10:06
 */

namespace Wormhole\Protocols\Unicharge\Protocol\Server\DataArea;
use Wormhole\Protocols\Tools;
class GetControl
{
    //桩编号
    private $result;

    /**
     * @param $result
     */
    public function setResult($result){
        $this->result = $result;
    }

    public function getResult(){
        return $this->result;
    }


    public function build(){

        return [$this->result];
    }

    public function load($dataArea){
        $offset = 0;
        $this->result =$dataArea[$offset];
        $offset++;

    }

}