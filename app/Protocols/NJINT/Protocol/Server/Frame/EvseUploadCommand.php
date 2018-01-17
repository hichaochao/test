<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 11:28
 */


namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseUploadCommand as DataArea;
class EvseUploadCommand extends Frame
{
    CONST OPERATOR=9;
    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseUploadCommand
     */
    private $dataArea;



    public function __construct()
    {
        //log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " . " 服务器协议 EvseUploadCommand Frame");
        parent::__construct();
        parent::setOpeartor(EvseUploadCommand::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseUploadCommand
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseUploadCommand|\NJ_INT\Base\DataArea $dataArea
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
        if(!empty($result)  && $result->getOperator() ==EvseUploadCommand::OPERATOR){
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}