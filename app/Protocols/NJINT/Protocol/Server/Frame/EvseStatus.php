<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseStatus as DataArea;
class EvseStatus extends Frame
{
    CONST OPERATOR=103;
    /**
     * @var DataArea
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(EvseStatus::OPERATOR);
    }

    /**
     * @return DataArea
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param DataArea $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }



    public function loadFrame($frame)
    {
        $loadResult= parent::load($frame);

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