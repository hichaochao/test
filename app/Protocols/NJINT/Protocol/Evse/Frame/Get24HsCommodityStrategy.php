<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/7
 * Time: 11:09
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\Get24HsCommodityStrategy as DataArea;
class Get24HsCommodityStrategy extends  Frame
{
    CONST OPERATOR=1101;

    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\Get24HsCommodityStrategy
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(Get24HsCommodityStrategy::OPERATOR);
    }



    /**
     * @return mixed
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\Get24HsCommodityStrategy $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $loadResult =parent::load($frameData);

        $result = count($loadResult)>0?$loadResult[0]:null;

        if(!empty($result)  && $result->getOperator() ==Get24HsCommodityStrategy::OPERATOR){
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}