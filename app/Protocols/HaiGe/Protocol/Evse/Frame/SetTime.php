<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\Haige\Evse\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetTime as DataArea;
class SetTime extends Frame
{
    CONST OPERATOR=0x35;

    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetTime
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(SetTime::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetTime
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetTime $dataArea
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
        if(!empty($result)  && $result->getOperator() ==SetTime::OPERATOR){

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