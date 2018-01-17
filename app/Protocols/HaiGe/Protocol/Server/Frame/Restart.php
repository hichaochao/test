<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:50
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Server\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;


class Restart extends Frame
{
    //指令
    CONST OPERATOR=0x18;


    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(Restart::OPERATOR);
    }




    public function loadFrame($frame)
    {
        $result= parent::load($frame);

        //$result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==Restart::OPERATOR){

            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->setFrameString($result->getFrameString());

            return true;
        }
        return false;
    }


}