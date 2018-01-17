<?php
namespace Wormhole\Protocols\HD10\Protocol\Evse\Frame;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as DataArea;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/21
 * Time: 15:31
 */
class HeartBeat extends Frame
{
    const commandCode = 0x10;
    const functionCode = 0x02;

    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(HeartBeat::commandCode);
        parent::setFunctionCode(HeartBeat::functionCode);
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
        if(!empty($result)  && $result->getCommandCode() ==HeartBeat::commandCode && $result->getFunctionCode() == HeartBeat::functionCode){
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