<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea;

use Wormhole\Protocols\Tools;

class SetTime
{

    /**
     * @var int 时间
     */
    private $time;



    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }





    public function build(){

        $frame =array();
        $frame = array_merge($frame, Tools::decToDbcArray($this->time, 7));//对时时间

        return $frame;
    }


    public function load($dataArea){
        $offset = 0;
        $this->time = Tools::dbcArrayTodec(array_slice($dataArea,$offset,7));
        $offset = $offset+7;


    }

}