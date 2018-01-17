<?php
namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;

use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseControl as DataArea;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 10:29
 */
class EvseControl extends Frame
{
    CONST OPERATOR=5;
    /**
     * @var EvseControl
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(EvseControl::OPERATOR);
    }


    /**
     * @return mixed
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseControl $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $loadResult =parent::load($frameData);

        $result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==EvseControl::OPERATOR){

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