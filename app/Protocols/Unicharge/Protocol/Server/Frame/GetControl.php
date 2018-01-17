<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 15:51
 */

namespace Wormhole\Protocols\Unicharge\Protocol\Server\Frame;
use Wormhole\Protocols\Unicharge\protocol\Base\UpgradeFrame;
use Wormhole\Protocols\Unicharge\protocol\Server\DataArea\GetControl as DataArea;

class GetControl extends UpgradeFrame
{
    //指令
    CONST OPERATOR=9902;
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOperator(GetControl::OPERATOR);
    }


    /**
     * @return  DataArea $dataArea
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
        $result= parent::load($frame);

        //$result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)){ //  && $result->getOperator() == GetControl::OPERATOR

            $dataArea = new DataArea();
            $dataArea->load($result->getDataArea());
            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}