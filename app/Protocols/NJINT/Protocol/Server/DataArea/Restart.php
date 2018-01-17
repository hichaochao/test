<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 18:21
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class Restart extends DataArea
{
    /**
     * @var string 预留
     */
    private $reserved;
    public function __construct()
    {
        $this->reserved=[0x00,0x00];

    }



    /**
     * @return string
     */
    public function getReserved()
    {
        return $this->reserved;
    }

    /**
     * @param string $reserved
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