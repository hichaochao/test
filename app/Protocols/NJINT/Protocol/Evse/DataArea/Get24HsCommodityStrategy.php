<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/7
 * Time: 11:09
 */

namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class Get24HsCommodityStrategy extends DataArea
{
    /**
     * @var int 开始小时
     */
    private $startHour1;
    private $startMinute1;
    private $endHour1;
    private $endMinute1;
    private $rate1;
    private $startHour2;
    private $startMinute2;
    private $endHour2;
    private $endMinute2;
    private $rate2;
    private $startHour3;
    private $startMinute3;
    private $endHour3;
    private $endMinute3;
    private $rate3;
    private $startHour4;
    private $startMinute4;
    private $endHour4;
    private $endMinute4;
    private $rate4;
    private $startHour5;
    private $startMinute5;
    private $endHour5;
    private $endMinute5;
    private $rate5;
    private $startHour6;
    private $startMinute6;
    private $endHour6;
    private $endMinute6;
    private $rate6;

    public function __construct()
    {

    }



    /**
     * @return int
     */
    public function getStartHour1()
    {
        return $this->startHour1;
    }

    /**
     * @param int $startHour1
     */
    public function setStartHour1($startHour1)
    {
        $this->startHour1 = $startHour1;
    }

    /**
     * @return mixed
     */
    public function getStartMinute1()
    {
        return $this->startMinute1;
    }

    /**
     * @param mixed $startMinute1
     */
    public function setStartMinute1($startMinute1)
    {
        $this->startMinute1 = $startMinute1;
    }

    /**
     * @return mixed
     */
    public function getEndHour1()
    {
        return $this->endHour1;
    }

    /**
     * @param mixed $endHour1
     */
    public function setEndHour1($endHour1)
    {
        $this->endHour1 = $endHour1;
    }

    /**
     * @return mixed
     */
    public function getEndMinute1()
    {
        return $this->endMinute1;
    }

    /**
     * @param mixed $endMinute1
     */
    public function setEndMinute1($endMinute1)
    {
        $this->endMinute1 = $endMinute1;
    }

    /**
     * @return mixed
     */
    public function getRate1()
    {
        return $this->rate1;
    }

    /**
     * @param mixed $rate1
     */
    public function setRate1($rate1)
    {
        $this->rate1 = $rate1;
    }

    /**
     * @return mixed
     */
    public function getStartHour2()
    {
        return $this->startHour2;
    }

    /**
     * @param mixed $startHour2
     */
    public function setStartHour2($startHour2)
    {
        $this->startHour2 = $startHour2;
    }

    /**
     * @return mixed
     */
    public function getStartMinute2()
    {
        return $this->startMinute2;
    }

    /**
     * @param mixed $startMinute2
     */
    public function setStartMinute2($startMinute2)
    {
        $this->startMinute2 = $startMinute2;
    }

    /**
     * @return mixed
     */
    public function getEndHour2()
    {
        return $this->endHour2;
    }

    /**
     * @param mixed $endHour2
     */
    public function setEndHour2($endHour2)
    {
        $this->endHour2 = $endHour2;
    }

    /**
     * @return mixed
     */
    public function getEndMinute2()
    {
        return $this->endMinute2;
    }

    /**
     * @param mixed $endMinute2
     */
    public function setEndMinute2($endMinute2)
    {
        $this->endMinute2 = $endMinute2;
    }

    /**
     * @return mixed
     */
    public function getRate2()
    {
        return $this->rate2;
    }

    /**
     * @param mixed $rate2
     */
    public function setRate2($rate2)
    {
        $this->rate2 = $rate2;
    }

    /**
     * @return mixed
     */
    public function getStartHour3()
    {
        return $this->startHour3;
    }

    /**
     * @param mixed $startHour3
     */
    public function setStartHour3($startHour3)
    {
        $this->startHour3 = $startHour3;
    }

    /**
     * @return mixed
     */
    public function getStartMinute3()
    {
        return $this->startMinute3;
    }

    /**
     * @param mixed $startMinute3
     */
    public function setStartMinute3($startMinute3)
    {
        $this->startMinute3 = $startMinute3;
    }

    /**
     * @return mixed
     */
    public function getEndHour3()
    {
        return $this->endHour3;
    }

    /**
     * @param mixed $endHour3
     */
    public function setEndHour3($endHour3)
    {
        $this->endHour3 = $endHour3;
    }

    /**
     * @return mixed
     */
    public function getEndMinute3()
    {
        return $this->endMinute3;
    }

    /**
     * @param mixed $endMinute3
     */
    public function setEndMinute3($endMinute3)
    {
        $this->endMinute3 = $endMinute3;
    }

    /**
     * @return mixed
     */
    public function getRate3()
    {
        return $this->rate3;
    }

    /**
     * @param mixed $rate3
     */
    public function setRate3($rate3)
    {
        $this->rate3 = $rate3;
    }

    /**
     * @return mixed
     */
    public function getStartHour4()
    {
        return $this->startHour4;
    }

    /**
     * @param mixed $startHour4
     */
    public function setStartHour4($startHour4)
    {
        $this->startHour4 = $startHour4;
    }

    /**
     * @return mixed
     */
    public function getStartMinute4()
    {
        return $this->startMinute4;
    }

    /**
     * @param mixed $startMinute4
     */
    public function setStartMinute4($startMinute4)
    {
        $this->startMinute4 = $startMinute4;
    }

    /**
     * @return mixed
     */
    public function getEndHour4()
    {
        return $this->endHour4;
    }

    /**
     * @param mixed $endHour4
     */
    public function setEndHour4($endHour4)
    {
        $this->endHour4 = $endHour4;
    }

    /**
     * @return mixed
     */
    public function getEndMinute4()
    {
        return $this->endMinute4;
    }

    /**
     * @param mixed $endMinute4
     */
    public function setEndMinute4($endMinute4)
    {
        $this->endMinute4 = $endMinute4;
    }

    /**
     * @return mixed
     */
    public function getRate4()
    {
        return $this->rate4;
    }

    /**
     * @param mixed $rate4
     */
    public function setRate4($rate4)
    {
        $this->rate4 = $rate4;
    }

    /**
     * @return mixed
     */
    public function getStartHour5()
    {
        return $this->startHour5;
    }

    /**
     * @param mixed $startHour5
     */
    public function setStartHour5($startHour5)
    {
        $this->startHour5 = $startHour5;
    }

    /**
     * @return mixed
     */
    public function getStartMinute5()
    {
        return $this->startMinute5;
    }

    /**
     * @param mixed $startMinute5
     */
    public function setStartMinute5($startMinute5)
    {
        $this->startMinute5 = $startMinute5;
    }

    /**
     * @return mixed
     */
    public function getEndHour5()
    {
        return $this->endHour5;
    }

    /**
     * @param mixed $endHour5
     */
    public function setEndHour5($endHour5)
    {
        $this->endHour5 = $endHour5;
    }

    /**
     * @return mixed
     */
    public function getEndMinute5()
    {
        return $this->endMinute5;
    }

    /**
     * @param mixed $endMinute5
     */
    public function setEndMinute5($endMinute5)
    {
        $this->endMinute5 = $endMinute5;
    }

    /**
     * @return mixed
     */
    public function getRate5()
    {
        return $this->rate5;
    }

    /**
     * @param mixed $rate5
     */
    public function setRate5($rate5)
    {
        $this->rate5 = $rate5;
    }
    public function getStartHour6()
    {
        return $this->startHour6;
    }

    /**
     * @param int $startHour6
     */
    public function setStartHour6($startHour6)
    {
        $this->startHour6 = $startHour6;
    }

    /**
     * @return mixed
     */
    public function getStartMinute6()
    {
        return $this->startMinute6;
    }

    /**
     * @param mixed $startMinute6
     */
    public function setStartMinute6($startMinute6)
    {
        $this->startMinute6 = $startMinute6;
    }

    /**
     * @return mixed
     */
    public function getEndHour6()
    {
        return $this->endHour6;
    }

    /**
     * @param mixed $endHour6
     */
    public function setEndHour6($endHour6)
    {
        $this->endHour6 = $endHour6;
    }

    /**
     * @return mixed
     */
    public function getEndMinute6()
    {
        return $this->endMinute6;
    }

    /**
     * @param mixed $endMinute6
     */
    public function setEndMinute6($endMinute6)
    {
        $this->endMinute6 = $endMinute6;
    }

    /**
     * @return mixed
     */
    public function getRate6()
    {
        return $this->rate6;
    }

    /**
     * @param mixed $rate6
     */
    public function setRate6($rate6)
    {
        $this->rate6 = $rate6;
    }

    public function build(){
        $frame =array();
        array_push($frame,$this->startHour1);
        array_push($frame,$this->startMinute1);
        array_push($frame,$this->endHour1);
        array_push($frame,$this->endMinute1);
        $frame=array_merge($frame,Tools::decToArray($this->rate1,4));
        array_push($frame,$this->startHour2);
        array_push($frame,$this->startMinute2);
        array_push($frame,$this->endHour2);
        array_push($frame,$this->endMinute2);
        $frame=array_merge($frame,Tools::decToArray($this->rate2,4));
        array_push($frame,$this->startHour3);
        array_push($frame,$this->startMinute3);
        array_push($frame,$this->endHour3);
        array_push($frame,$this->endMinute3);
        $frame=array_merge($frame,Tools::decToArray($this->rate3,4));
        array_push($frame,$this->startHour4);
        array_push($frame,$this->startMinute4);
        array_push($frame,$this->endHour4);
        array_push($frame,$this->endMinute4);
        $frame=array_merge($frame,Tools::decToArray($this->rate4,4));
        array_push($frame,$this->startHour5);
        array_push($frame,$this->startMinute5);
        array_push($frame,$this->endHour5);
        array_push($frame,$this->endMinute5);
        $frame=array_merge($frame,Tools::decToArray($this->rate5,4));
        array_push($frame,$this->startHour6);
        array_push($frame,$this->startMinute6);
        array_push($frame,$this->endHour6);
        array_push($frame,$this->endMinute6);
        $frame=array_merge($frame,Tools::decToArray($this->rate6,4));
        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->startHour1 = $dataArea[$offset];
        $offset+=1;
        $this->startHour1 = $dataArea[$offset];
        $offset+=1;
        $this->startHour1 = $dataArea[$offset];
        $offset+=1;
        $this->startHour1 = $dataArea[$offset];
        $offset+=1;
        $this->rate1 = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->startHour2 = $dataArea[$offset];
        $offset+=1;
        $this->startHour2 = $dataArea[$offset];
        $offset+=1;
        $this->startHour2 = $dataArea[$offset];
        $offset+=1;
        $this->startHour2 = $dataArea[$offset];
        $offset+=1;
        $this->rate2 = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->startHour3 = $dataArea[$offset];
        $offset+=1;
        $this->startHour3 = $dataArea[$offset];
        $offset+=1;
        $this->startHour3 = $dataArea[$offset];
        $offset+=1;
        $this->startHour3 = $dataArea[$offset];
        $offset+=1;
        $this->rate3 = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->startHour4 = $dataArea[$offset];
        $offset+=1;
        $this->startHour4 = $dataArea[$offset];
        $offset+=1;
        $this->startHour4 = $dataArea[$offset];
        $offset+=1;
        $this->startHour4 = $dataArea[$offset];
        $offset+=1;
        $this->rate4 = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->startHour5 = $dataArea[$offset];
        $offset+=1;
        $this->startHour5 = $dataArea[$offset];
        $offset+=1;
        $this->startHour5 = $dataArea[$offset];
        $offset+=1;
        $this->startHour5 = $dataArea[$offset];
        $offset+=1;
        $this->rate5 = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->startHour6 = $dataArea[$offset];
        $offset+=6;
        $this->startHour6 = $dataArea[$offset];
        $offset+=6;
        $this->startHour6 = $dataArea[$offset];
        $offset+=6;
        $this->startHour6 = $dataArea[$offset];
        $offset+=6;
        $this->rate6 = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;


    }




}