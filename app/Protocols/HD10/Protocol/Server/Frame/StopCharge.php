<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 10:06
 */

namespace Wormhole\Protocols\HD10\Protocol\Server\Frame;


use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\StopCharge as DataArea;

class StopCharge extends Frame
{
    const commandCode = 0x10;
    const functionCode = 0x08;

    /**
     * @var DataArea
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(StopCharge::commandCode);
        parent::setFunctionCode(StopCharge::functionCode);
    }

    /**
     * @param DataArea $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }

    public function loadFrame($frame){
        $loadResult = parent::load($frame);
        $result = count($loadResult[0])>0 ? $loadResult[0]:null;
        if(!empty($result)  && $result->getCommandCode() ==StopCharge::commandCode && $result->getFunctionCode() == StopCharge::functionCode){
            $dataArea = new DataArea();

            $dataArea->load($result->getDataArea());
            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}