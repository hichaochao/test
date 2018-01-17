<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/7
 * Time: 11:09
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class Get24HsCommodityStrategy extends DataArea
{
//    /**
//     * @var string 文件数据
//     */
    //private $upgrateFileData;
    public function __construct()
    {

    }

//
//    /**
//     * @return string
//     */
//    public function getUpgrateFileData()
//    {
//        return $this->upgrateFileData;
//    }
// //
//    /**
//     * @param string $upgrateFileData
//     */
   // public function setUpgrateFileData($upgrateFileData)
   // {
   //     $this->upgrateFileData = $upgrateFileData;
   // }
//
//     public function build(){
// //        $frame =Tools::decToArray($this->upgrateFileData,4096 );
//         $frame =array();
        
//         $frame =array_merge($frame, $commodity->build());
        
//         return $frame;


//     }

    public function load($dataArea){
        $offset = 0;
//        $this->upgrateFileData = Tools::arrayToDec(array_slice($dataArea,$offset,4096));
//        $offset+=4096;


    }
}