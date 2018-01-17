<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 18:34
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\Frame;


use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartChargeCheck as DataArea;

class StartChargeCheck extends Frame
{
    /**
     * 操作码
     */
    const commandCode = 0x10;
    const functionCode = 0x06;

    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(StartChargeCheck::commandCode);
        parent::setFunctionCode(StartChargeCheck::functionCode);
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
        if(!empty($result)  && $result->getCommandCode() ==StartChargeCheck::commandCode && $result->getFunctionCode() == StartChargeCheck::functionCode){
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