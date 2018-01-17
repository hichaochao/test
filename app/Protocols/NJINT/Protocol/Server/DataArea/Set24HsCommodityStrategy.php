<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/7
 * Time: 15:03
 */
namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\NJINT\Protocol\Server\Command\Commodity;

//TODO 协议处理
class Set24HsCommodityStrategy extends DataArea
{

    /**
     * @var Commodity[] 费率组列表
     */
    private $commodityList ;
    /**
     * @var int 最大费率数量
     */
    private $max_commodity_number = 6;


    public function __construct()
    {
        $commodityList = [];
    }


    /**
     * @return array
     */
    public function getCommodityList()
    {
        return $this->commodityList;
    }

    /**
     * @param Commodity[] $commodityList
     */
    public function setCommodityList($commodityList)
    {
        if(count($commodityList) <= $this->max_commodity_number) {
            $this->commodityList = $commodityList;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @param $commodity Commodity
     * @return bool
     */
    public function pushNewCommodity($commodity){
        if(count($this->commodityList) < $this->max_commodity_number){
            array_push($this->commodityList,$commodity);
            return TRUE;
        }
        return FALSE;
    }




    public function build(){
        //数据准备
        while (count($this->commodityList)<$this->max_commodity_number){
            $tmpCommodity = new Commodity();
            array_push($this->commodityList,$tmpCommodity);
        }

        $frame =array();
        foreach ($this->commodityList as $commodity){
            $frame =array_merge($frame, $commodity->build());
        }
        return $frame;
    }

    public function load($dataArea){
        $this->commodityList = Commodity::load($dataArea);
    }
}