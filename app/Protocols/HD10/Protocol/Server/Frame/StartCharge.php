<?php
namespace Wormhole\Protocols\HD10\Protocol\Server\Frame;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\StartCharge as DataArea;
use Wormhole\Protocols\Tools;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 15:38
 */

class StartCharge extends Frame
{
    const commandCode = 0x10;
    const functionCode = 0x07;

    /**
     * @var DataArea
     */
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

    /**
     * @return  DataArea
     */
    public function getDataArea()
    {
        return $this->dataArea;
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