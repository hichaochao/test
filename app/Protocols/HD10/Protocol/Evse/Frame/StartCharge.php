<?php
namespace Wormhole\Protocols\HD10\Protocol\Evse\Frame;

use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartCharge as dataArea;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 10:13
 */

class StartCharge extends Frame
{
    /**
     * 操作码
     */
    const commandCode = 0x10;
    const functionCode = 0x07;

    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(StartCharge::commandCode);
        parent::setFunctionCode(StartCharge::functionCode);
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
        if(!empty($result)  && $result->getCommandCode() ==StartCharge::commandCode && $result->getFunctionCode() == StartCharge::functionCode){
            $dataArea = new dataArea();
            $dataArea->load($result->getDataArea());
            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}
