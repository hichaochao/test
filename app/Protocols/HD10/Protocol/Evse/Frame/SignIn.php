<?php
namespace Wormhole\Protocols\HD10\Protocol\Evse\Frame;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as DataArea;
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 19:32
 */
class SignIn extends Frame
{
    const commandCode = 0x10;
    const functionCode = 0x01;

    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setCommandCode(SignIn::commandCode);
        parent::setFunctionCode(SignIn::functionCode);
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
        if(!empty($result)  && $result->getCommandCode() ==SignIn::commandCode && $result->getFunctionCode() == SignIn::functionCode){
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