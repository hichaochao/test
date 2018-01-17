<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 10:17
 */
namespace Wormhole\Protocols\NJINT\Protocol\Server\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadBMSInfo as DataArea;

class UploadBMSInfo extends Frame
{
    CONST OPERATOR=301;
    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadBMSInfo
     */
    private $dataArea;



    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(UploadBMSInfo::OPERATOR);
    }

    /**
     * @return \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadBMSInfo
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadBMSInfo|\NJ_INT\Base\DataArea $dataArea
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
        if(!empty($result)  && $result->getOperator() ==UploadBMSInfo::OPERATOR){
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }

}