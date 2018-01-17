<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 17:37
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UpgrateFileDataFinish extends DataArea
{
    /**
     * @var array 协议预留
     */
    private $reserved;

    public function __construct()
    {
        $this->reserved=[0x00,0x00];

    }


    /**
     * @return array
     */
    public function getReserved()
    {
        return $this->reserved;
    }

    /**
     * @param array $reserved
     */
    public function setReserved($reserved)
    {
        $this->reserved = $reserved;
    }



    public function build(){
        $frame =$this->reserved;
        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved = array_slice(  $dataArea,$offset,4);
        $offset+=4 ;

    }




}