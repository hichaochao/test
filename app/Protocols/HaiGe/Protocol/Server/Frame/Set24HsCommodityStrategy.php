<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:50
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Server\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\Set24HsCommodityStrategy as DataArea;

class Set24HsCommodityStrategy extends Frame
{
    CONST OPERATOR=0x38;

    /**
     * @var DataArea
     */
    private $dataArea;


    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(Set24HsCommodityStrategy::OPERATOR);

    }


    /**
     * @return  \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\Set24HsCommodityStrategy
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }


    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\Set24HsCommodityStrategy $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frame)
    {

        $result = parent::load($frame);

        //$result = count($loadResult)>0?$loadResult[0]:null;
        if (!empty($result) && $result->getOperator() == Set24HsCommodityStrategy::OPERATOR) {

            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->setFrameString($result->getFrameString());

            $dataArea = new DataArea();
            $dataArea->load($result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }



}