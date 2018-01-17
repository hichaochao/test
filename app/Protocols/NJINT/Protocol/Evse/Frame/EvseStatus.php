<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseStatus as DataArea;
class EvseStatus extends Frame
{
    CONST OPERATOR=104;
    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(EvseStatus::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\StartCharge $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }



    public function loadFrame($frame)
    {
        $loadResult= Frame::load($frame);

        $result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==EvseStatus::OPERATOR){

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