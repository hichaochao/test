<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-10-21
 * Time: 17:20
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Server\DataArea;
use Wormhole\Protocols\Tools;

class Set24HsCommodityStrategy
{

    /**
     * @var int 1号费率
     */
    private $commodityOne;

    /**
     * @var int 2号费率
     */
    private $commodityTwo;

    /**
     * @var int 3号费率
     */
    private $commodityThree;

    /**
     * @var int 4号费率
     */
    private $commodityFour;

    /**
     * @var int 服务费
     */
    private $serviceCharge;

    /**
     * @var int 时间点执行费率号
     */
    private $implement;





    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getCommodityOne()
    {
        return $this->commodityOne;
    }

    /**
     * @param int $commodityOne
     */
    public function setCommodityOne($commodityOne)
    {
        $this->commodityOne = $commodityOne;
    }


    /**
     * @return int
     */
    public function getCommodityTwo()
    {
        return $this->commodityTwo;
    }

    /**
     * @param int $commodityTwo
     */
    public function setCommodityTwo($commodityTwo)
    {
        $this->commodityTwo = $commodityTwo;
    }



    /**
     * @return int
     */
    public function getCommodityThree()
    {
        return $this->commodityThree;
    }

    /**
     * @param int $commodityThree
     */
    public function setCommodityThree($commodityThree)
    {
        $this->commodityThree = $commodityThree;
    }


    /**
     * @return int
     */
    public function getCommodityFour()
    {
        return $this->commodityFour;
    }

    /**
     * @param int $commodityFour
     */
    public function setCommodityFour($commodityFour)
    {
        $this->commodityFour = $commodityFour;
    }



    /**
     * @return int
     */
    public function getServiceCharge()
    {
        return $this->serviceCharge;
    }

    /**
     * @param int $serviceCharge
     */
    public function setServiceCharge($serviceCharge)
    {
        $this->serviceCharge = $serviceCharge;
    }

    /**
     * @return int
     */
    public function getImplement()
    {
        return $this->implement;
    }

    /**
     * @param int $implement
     */
    public function setImplement($implement)
    {
        $this->implement = $implement;
    }





    public function build(){

        $frame =array();
        $frame = array_merge($frame,Tools::decToArray($this->commodityOne,2));
        $frame = array_merge($frame,Tools::decToArray($this->commodityTwo,2));
        $frame = array_merge($frame,Tools::decToArray($this->commodityThree,2));
        $frame = array_merge($frame,Tools::decToArray($this->commodityFour,2));
        $frame = array_merge($frame,Tools::decToArray($this->serviceCharge,2));
        $frame = array_merge($frame,Tools::decToArray($this->implement,48,false));
        return $frame;
    }


    public function load($dataArea){
        $offset = 0;
        $this->commodityOne = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->commodityTwo = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->commodityThree = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->commodityFour = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->serviceCharge = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->implement = Tools::arrayToDec(array_slice($dataArea,$offset,48));
        $offset = $offset+48;

    }


}