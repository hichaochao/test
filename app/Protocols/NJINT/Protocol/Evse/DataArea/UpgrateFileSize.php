<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 15:26
 */
namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UpgrateFileSize extends DataArea
{
    /**
     * @var int 响应标志
     */
    private $responseSign;

    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getResponseSign()
    {
        return $this->responseSign;
    }

    /**
     * @param int $responseSign
     */
    public function setResponseSign($responseSign)
    {
        $this->responseSign = $responseSign;
    }


    public function build(){
        $frame =Tools::decToArray($this->responseSign,4);
        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->responseSign = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;


    }




}