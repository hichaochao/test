<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 11:52
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\Frame;


use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeRealtime as DataArea;

class ChargeRealtime extends Frame
{
    const commandCode = 0x11;
    const functionCode = 0x02;

    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(ChargeRealtime::commandCode);
        parent::setFunctionCode(ChargeRealtime::functionCode);
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
        if(!empty($result)  && $result->getCommandCode() ==ChargeRealtime::commandCode && $result->getFunctionCode() == ChargeRealtime::functionCode){
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