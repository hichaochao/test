<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016/10/21
 * Time: 17:02
 */
namespace Wormhole\Protocols\Haige\protocol\Evse\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\StopCharge as DataArea;
class StopCharge extends  Frame
{
    //指令
    CONST OPERATOR=0x13;

    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\StopCharge
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(StopCharge::OPERATOR);
    }


    /**
     * @return  \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\StopCharge
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }


    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\StopCharge $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $result =parent::load($frameData);

        //$result = count($loadResult)>0?$loadResult[0]:null;

        if(!empty($result)  && $result->getOperator() ==StopCharge::OPERATOR){

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