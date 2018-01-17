<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\Haige\Evse\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\PayByCard as DataArea;
class PayByCard extends Frame
{
    CONST OPERATOR=0x75;
    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\PayByCard
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(PayByCard::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\PayByCard
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\PayByCard $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }



    public function loadFrame($frame)
    {
        $result= Frame::load($frame);

        //$result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==PayByCard::OPERATOR){

            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->setFrameString($result->getFrameString());
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}