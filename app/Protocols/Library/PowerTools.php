<?php
/**
 * Created by PhpStorm.
 * User: lingfeng.chen.cn
 * Date: 2017/2/28
 * Time: 下午6:29
 */

namespace Wormhole\Protocols\Library;



use Illuminate\Support\Facades\Log;

trait PowerTools
{

    /**
     * 格式化电量数据，
     * 电量总和，分割成有半小时格式；
     * @param $startTime
     * @param $stopTime
     * @param $power integer 电量，单位 wh
     * @return array
     */
    private function formatPower($startTime,$stopTime,$power){

        //计算开始时间
        $startMinute = intval(date("i", $startTime)) < 30 ?0:30;
        $calcStartTime = strtotime( date("Y-m-d H:$startMinute:0",$startTime));

        $duration = $stopTime-$startTime;
        $perSecondPower = $duration > 0 ? $power/$duration:$power;//每秒钟电量
        //var_dump("perSecondPower :".$perSecondPower);
        $perSecondPowerArray = array_fill(0,$duration,$perSecondPower);
        //var_dump("duration :".$duration);

        $fillNumber = $startTime - $calcStartTime ;
        $fillArray = array_fill(0,$fillNumber,0);
        $filledPerSeccondPowerArray = array_merge($fillArray,$perSecondPowerArray);



        //half hour power and time;
        $halfHourPower=[];
        $tmpCalcStartTime = $calcStartTime;
        $tmpStartTime = $startTime;

        while (count($filledPerSeccondPowerArray) > 0){
            //var_dump(count($filledPerSeccondPowerArray));
            $halfPower["time"] = $tmpCalcStartTime;


            $tmpCalcStartTime += 30*60;
            $halfPower["duration"]= $tmpCalcStartTime-$tmpStartTime;
            $halfPower['power'] = ceil( array_sum(array_splice($filledPerSeccondPowerArray,0,30*60)));

            $tmpStartTime = $tmpCalcStartTime;
            $halfHourPower[] = $halfPower;
        }

        //如果只有一条数据，时间需要修复（第一次的tmpCalcStarttime 肯定大于 stoptime了）；
        $startSecond = date("i",$startTime)*60 + date("s",$startTime);
        $subStartSecond = intval( date("i",$calcStartTime)) == 0 ? 30*60 :60*60;
        $subStartSecond = 1 == count($halfHourPower) ? date("i",$stopTime)*60 + date("s",$stopTime) :$subStartSecond; //如果只有一条，使用结束时间来减开始时间
        $startSecond = $subStartSecond - $startSecond;
        $halfHourPower[0]['duration'] = $startSecond;

        if(array_key_exists(count($halfHourPower)-2,$halfHourPower)){

            $halfHourPower[count($halfHourPower)-1]['duration']= $stopTime - $halfHourPower[count($halfHourPower)-1]['time'] ;
        }

        return $halfHourPower;
    }

    /**
     * 格式化电量数据，
     * 半小时格式，格式化成我们所需求的数据顺序。
     * @param $startTime
     * @param $endTime
     * @param array $powerOfTimes
     * @return array
     */
    private function formatPowerOfTime($startTime, $endTime, array $powerOfTimes)
    {

        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ ." startTime:$startTime ,endTime:$endTime ".json_encode($powerOfTimes));
        //组织数据顺序

        //获取开始时间的启动位置；
        $indexHalfMinute = intval(date("H", $startTime))*2 + floor( intval(date("i", $startTime))/30);
        //var_dump($indexHalfMinute);
        $spliteArray = array_splice($powerOfTimes,0,$indexHalfMinute); //开始时间之后，视作第二天的充电数据




        //计算开始时间
        $startSecond = intval(date("i", $startTime)) < 30 ?0:30;
        $calcStartTime = strtotime( date("Y-m-d H:$startSecond:0",$startTime));
        //var_dump($calcStartTime);

        $halfNumbers =  ceil(($endTime-$calcStartTime)/60/30); //启停之间拥有的半小时数
        //var_dump($halfNumbers);
        $fillArray = array_fill(0,$halfNumbers,0); //填充数据，准备有合并使用，保证最终数据数量足够
        $orderedPowerOfTimes = array_merge($powerOfTimes,$spliteArray,$fillArray); //数据顺序为，从充电开始往后的电量。但是如果超过24小时，第二天的电量和第一天的电量合并了；
        //var_dump($orderedPowerOfTimes);
        $powerOfTimes = array_splice($orderedPowerOfTimes,0,$halfNumbers); //从头开始计算应该拥有的半小时数

        //var_dump($powerOfTimes);
        //组织格式化数据
        $tmpCalcStartTime = $calcStartTime;
        $formattedPower = [];

        //var_dump($powerOfTimes);
        foreach ($powerOfTimes as $power){
            $powerFormatted[]=[
                "time" => $tmpCalcStartTime ,
                "power" => $power,
                "duration" => 30*60
            ];
            $tmpCalcStartTime += 30*60;
        }
        $startSecond = date("i",$startTime)*60 + date("s",$startTime);

        //var_dump( intval( date("i",$calcStartTime)));
        $subStartSecond = intval( date("i",$calcStartTime)) == 0 ? 30*60 :60*60;
        $subStartSecond = 1 == $halfNumbers ? date("i",$endTime)*60 + date("s",$endTime) :$subStartSecond;
        $startSecond = $subStartSecond - $startSecond;
        //var_dump($startSecond);
        $powerFormatted[0]['duration'] = $startSecond;
        //var_dump($endTime-$startTime);
        //var_dump($tmpCalcStartTime);
        //var_dump($endTime);
        //var_dump($powerFormatted);

        if(array_key_exists(count($powerFormatted)-2,$powerFormatted)){

            $powerFormatted[count($powerFormatted)-1]['duration']= $endTime - $powerFormatted[count($powerFormatted)-1]['time'] ;
        }


        return $powerFormatted;


    }

}