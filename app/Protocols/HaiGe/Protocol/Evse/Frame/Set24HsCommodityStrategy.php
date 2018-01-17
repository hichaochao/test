<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016/10/21
 * Time: 17:24
 */
namespace Wormhole\Protocols\Haige\Evse\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Set24HsCommodityStrategy as DataArea;
class Set24HsCommodityStrategy extends  Frame
{
    //指令
    CONST OPERATOR=0x39;

    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Set24HsCommodityStrategy
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(Set24HsCommodityStrategy::OPERATOR);
    }


    /**
     * @return  \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Set24HsCommodityStrategy
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }


    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\Set24HsCommodityStrategy $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $result =parent::load($frameData);

        //$result = count($loadResult)>0?$loadResult[0]:null;

        if(!empty($result)  && $result->getOperator() ==Set24HsCommodityStrategy::OPERATOR){

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