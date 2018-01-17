<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 16:50
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea as BaseDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\SignIn as DataArea;


//TODO 字段定义和代码实现
class SignIn extends Frame
{
    CONST OPERATOR=106;
    /**
     * @var DataArea
     */
    private $dataArea;



    public function __construct()
    {

        parent::__construct();
        parent::setOpeartor(SignIn::OPERATOR);
    }

    /**
     * @return DataArea
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param DataArea|BaseDataArea $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }



    public function loadFrame($frame)
    {

        $loadResult= Frame::load($frame);

        $result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==SignIn::OPERATOR){

            $this->setCorrectFormat($result->isCorrectFormat());
            $this->setFormatMsg($result->getFormatMsg());
            $this->setFrameString($result->getFrameString());
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }

}