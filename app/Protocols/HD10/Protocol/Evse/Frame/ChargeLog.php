<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 11:12
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\Frame;


use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeLog as DataArea;

class ChargeLog extends Frame
{
    const commandCode = 0x11;
    const functionCode = 0x01;

    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(ChargeLog::commandCode);
        parent::setFunctionCode(ChargeLog::functionCode);
    }

    /**
     * @param DataAreao $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }

    public function loadFrame($frame){
        $loadResult = parent::load($frame);
        $result = count($loadResult[0])>0 ? $loadResult[0]:null;
        if(!empty($result)  && $result->getCommandCode() ==ChargeLog::commandCode && $result->getFunctionCode() == ChargeLog::functionCode){
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