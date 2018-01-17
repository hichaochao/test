<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016/10/21
 * Time: 17:02
 */
namespace Wormhole\Protocols\HaiGe\Protocol\Server\Frame;

use Wormhole\Protocols\HaiGe\Protocol\Frame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\PayByCard as DataArea;
class PayByCard extends  Frame
{
    //指令
    CONST OPERATOR=0x76;

    /**
     * @var \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\PayByCard
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(PayByCard::OPERATOR);
    }


    /**
     * @return  \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\PayByCard
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }


    /**
     * @param \Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\PayByCard $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $result =parent::load($frameData);

        //$result = count($loadResult)>0?$loadResult[0]:null;

        if(!empty($result)  && $result->getOperator() ==PayByCard::OPERATOR){

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