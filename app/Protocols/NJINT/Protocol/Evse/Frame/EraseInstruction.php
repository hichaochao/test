<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 14:19
 */
namespace Wormhole\Protocols\NJINT\Protocol\Evse\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EraseInstruction as DataArea;
class EraseInstruction extends  Frame
{
    CONST OPERATOR=1002;

    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EraseInstruction
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(EraseInstruction::OPERATOR);
    }



    /**
     * @return mixed
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EraseInstruction $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $loadResult =parent::load($frameData);

        $result = count($loadResult)>0?$loadResult[0]:null;

        if(!empty($result)  && $result->getOperator() ==EraseInstruction::OPERATOR){
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}