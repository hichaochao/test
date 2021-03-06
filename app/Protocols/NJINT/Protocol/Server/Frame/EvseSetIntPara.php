<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/6/17
 * Time: 16:11
 */
namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;

use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseSetIntPara as DataArea;
class EvseSetIntPara extends Frame
{
    CONST OPERATOR=1;
    /**
     * @var EvseSetIntPara
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(EvseSetIntPara::OPERATOR);
    }


    /**
     * @return mixed
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseSetCharPara $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $loadResult =parent::load($frameData);

        $result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==EvseSetIntPara::OPERATOR){
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }


}