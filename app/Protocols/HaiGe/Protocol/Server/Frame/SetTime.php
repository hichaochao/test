<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-10-21
 * Time: 17:38
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Server\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use  Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\SetTime as DataArea;



class SetTime extends Frame
{
    //指令
    CONST OPERATOR=0x34;

    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\SetTime
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(SetTime::OPERATOR);
    }


    /**
     * @return  \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\SetTime
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }


    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\SetTime $dataArea
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