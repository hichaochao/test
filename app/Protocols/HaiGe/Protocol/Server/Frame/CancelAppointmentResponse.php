<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:50
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Server\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;

class CancelAppointmentResponse extends Frame
{
    //指令
    CONST OPERATOR = 0x58;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(CancelAppointmentResponse::OPERATOR);

    }




    public function loadFrame($frame)
    {
        $result = parent::load($frame);

        //$result = count($loadResult)>0?$loadResult[0]:null;
        if (!empty($result) && $result->getOperator() == CancelAppointmentResponse::OPERATOR) {

            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->setFrameString($result->getFrameString());

            return true;
        }
        return false;
    }


}