<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 15:40
 */

namespace Wormhole\Protocols\Haige\Protocol\Evse\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Heartbeat as DataArea;
class Heartbeat extends Frame
{
    //指令
    CONST OPERATOR=0x51;
    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Heartbeat
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(Heartbeat::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Heartbeat
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Heartbeat   $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }



    public function loadFrame($frame)
    {
        $result= parent::load($frame);

        //$result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==Heartbeat::OPERATOR){

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