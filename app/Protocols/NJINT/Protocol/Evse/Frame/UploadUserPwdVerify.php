<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 17:34
 */



namespace Wormhole\Protocols\NJINT\Protocol\Evse\Frame;


use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadUserPwdVerify as DataArea;
class UploadUserPwdVerify extends Frame
{
    CONST OPERATOR=206;

    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadUserPwdVerify
     */
    private $dataArea;

    public function __construct()
    {
        parent::__construct();
        parent::setOpeartor(UploadUserPwdVerify::OPERATOR);
    }



    /**
     * @return DataArea |\NJ_INT\Base\DataArea
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadUserPwdVerify $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
        parent::setDataArea($dataArea->build());
    }


    public function loadFrame($frameData){
        $loadResult =parent::load($frameData);

        $result = count($loadResult)>0?$loadResult[0]:null;
        if(!empty($result)  && $result->getOperator() ==UploadUserPwdVerify::OPERATOR){
            $dataArea = new DataArea();
            $dataArea->load( $result->getDataArea());

            $this->dataArea = $dataArea;

            return true;
        }
        return false;
    }
}