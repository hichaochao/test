<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 16:56
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;

use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadChargingLog as DataArea;
class UploadChargingLog extends Frame
{
    CONST OPERATOR=201;
    /**
     * @var EvseControl
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(UploadChargingLog::OPERATOR);
    }


    /**
     * @return  \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseControl|\NJ_INT\Base\DataArea
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseControl|\NJ_INT\Base\DataArea $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $loadResult =parent::load($frameData);

        $result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==UploadChargingLog::OPERATOR){

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