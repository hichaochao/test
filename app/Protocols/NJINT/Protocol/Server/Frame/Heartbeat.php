<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 15:51
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\Heartbeat as DataArea;
class Heartbeat extends Frame
{
    CONST OPERATOR=101;
    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge
     */
    private $dataArea;



    public function __construct()
    {
        //log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 服务器协议 heartbeat Frame");
        parent::__construct();
        parent::setOpeartor(Heartbeat::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\Heartbeat|\NJ_INT\Base\DataArea $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }



    public function loadFrame($frame)
    {
        $loadResult= parent::load($frame);

        $result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==Heartbeat::OPERATOR){

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