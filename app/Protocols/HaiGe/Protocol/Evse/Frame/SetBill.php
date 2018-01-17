<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\Haige\protocol\Evse\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetBill as DataArea;
class SetBill extends Frame
{
    CONST OPERATOR=0x54;
    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetBill
     */
    private $dataArea;


    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(SetBill::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetBill
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea\SetBill $dataArea
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
        if(!empty($result)  && $result->getOperator() ==SetBill::OPERATOR){

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