<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016/10/21
 * Time: 17:02
 */
namespace Wormhole\Protocols\HaiGe\Protocol\Server\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;

class StartChargeResponse extends  Frame
{
    //指令
    CONST OPERATOR=0x56;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(StartChargeResponse::OPERATOR);
    }


    public function loadFrame($frameData){
        $result =parent::load($frameData);

        //$result = count($loadResult)>0?$loadResult[0]:null;

        if(!empty($result)  && $result->getOperator() ==StartChargeResponse::OPERATOR){

            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->setFrameString($result->getFrameString());

            return true;
        }
        return false;
    }
}