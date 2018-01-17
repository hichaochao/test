<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 17:01
 */

namespace Wormhole\Protocols\HD10\Protocol\Evse\Frame;


use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ReadChargeLogHistory as DataArea;

class ReadChargeLogHistory extends Frame
{
    const commandCode = 0x11;
    const functionCode = 0x04;

    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(self::commandCode);
        parent::setFunctionCode(self::functionCode);
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
        if(!empty($result)  && $result->getCommandCode() ==self::commandCode && $result->getFunctionCode() == self::functionCode){
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