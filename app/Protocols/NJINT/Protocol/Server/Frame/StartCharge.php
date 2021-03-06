<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 11:59
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;

use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\StartCharge as DataArea;
class StartCharge extends Frame
{
    CONST OPERATOR=7;
    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(StartCharge::OPERATOR);
    }


    /**
     * @return \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge $dataArea
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

        if(!empty($result)  && $result->getOperator() ==StartCharge::OPERATOR){

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