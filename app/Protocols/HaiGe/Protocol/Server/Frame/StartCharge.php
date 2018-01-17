<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:50
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Server\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\StartCharge as DataArea;

class StartCharge extends Frame
{
    //指令
    CONST OPERATOR = 0x10;
    /**
     * @var DataArea
     */
    private $dataArea;


    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(StartCharge::OPERATOR);
    }


    /**
     * @return  \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\StartCharge
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }


    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\StartCharge $dataArea
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
        if (!empty($result) && $result->getOperator() == StartCharge::OPERATOR) {

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