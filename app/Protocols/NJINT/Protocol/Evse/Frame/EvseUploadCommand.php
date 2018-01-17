<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 11:16
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseUploadCommand as DataArea;
class EvseUploadCommand extends Frame
{
    CONST OPERATOR=10;
    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseUploadCommand
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(EvseUploadCommand::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseUploadCommand
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseUploadCommand|\NJ_INT\Base\DataArea $dataArea
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
