<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-09-19
 * Time: 16:18
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\Command;
use Wormhole\Protocols\Tools;


class Commodity
{
    private $startHour;
    private $startMinute;
    private $endHour;
    private $endMinute;
    private $rate;

    public function __construct()
    {
        $this->startHour = 0;
        $this->startMinute = 0;
        $this->endHour = 0;
        $this->endMinute = 0;
        $this->rate=0;
    }


    /**
     * @return mixed
     */
    public function getStartHour()
    {
        return $this->startHour;
    }

    /**
     * @param mixed $startHour
     */
    public function setStartHour($startHour)
    {
        $this->startHour = $startHour;
    }

    /**
     * @return mixed
     */
    public function getStartMinute()
    {
        return $this->startMinute;
    }

    /**
     * @param mixed $startMinute
     */
    public function setStartMinute($startMinute)
    {
        $this->startMinute = $startMinute;
    }

    /**
     * @return mixed
     */
    public function getEndHour()
    {
        return $this->endHour;
    }

    /**
     * @param mixed $endHour
     */
    public function setEndHour($endHour)
    {
        $this->endHour = $endHour;
    }

    /**
     * @return mixed
     */
    public function getEndMinute()
    {
        return $this->endMinute;
    }

    /**
     * @param mixed $endMinute
     */
    public function setEndMinute($endMinute)
    {
        $this->endMinute = $endMinute;
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    public function build(){
        $frame =array();
        array_push($frame,$this->startHour);
        array_push($frame,$this->startMinute);
        array_push($frame,$this->endHour);
        array_push($frame,$this->endMinute);
        $frame=array_merge($frame,Tools::decToArray($this->rate,4));

        return $frame;
    }

    public function loadOne($data){
        $offset = 0;

        $this->startHour = $data[$offset];
        $offset+=1;
        $this->startHour = $data[$offset];
        $offset+=1;
        $this->startHour = $data[$offset];
        $offset+=1;
        $this->startHour = $data[$offset];
        $offset+=1;
        $this->rate = Tools::arrayToDec(array_slice($data,$offset,4));
        $offset+=4;

    }

    /**
     * @param $data array
     * @return bool|Commodity[]
     */
    public static function load($data){

        $commodityList = [];
        $offset = 0;

        if(count($data) % 8 != 0){return FALSE;}

        while ($offset<count($data)){
            $tmpCommodity = new Commodity();

            $tmpCommodity->startHour = $data[$offset];
            $offset+=1;
            $tmpCommodity->startHour = $data[$offset];
            $offset+=1;
            $tmpCommodity->startHour = $data[$offset];
            $offset+=1;
            $tmpCommodity->startHour = $data[$offset];
            $offset+=1;
            $tmpCommodity->rate = Tools::arrayToDec(array_slice($data,$offset,4));
            $offset+=4;

            $commodityList[] = $tmpCommodity;
        }

        return $commodityList;
    }
}