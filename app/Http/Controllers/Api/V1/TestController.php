<?php
namespace Wormhole\Http\Controllers\Api\V1;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Wormhole\Http\Controllers\Api\BaseController;
use Wormhole\Protocols\NJINT\Models\ChargeOrderMapping;
use Wormhole\Protocols\NJINT\Models\ChargeRecord;
use Wormhole\Protocols\NJINT\Models\Port;

use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-10-03
 * Time: 16:37
 */
class TestController extends BaseController
{

    public function test()
    {
        $this->test1(true);



    }


    /**
     * @param $online
     * @param bool $chargeStatus
     */
    public  function  test1($online,$chargeStatus = -1){
        var_dump($chargeStatus);
        $data =[
            "evse_code"=>"code",
            "net_status"=>intval($online),

        ];
        $data = -1 == $chargeStatus ? $data : array_merge($data,["charge_status"=>$chargeStatus]);

        var_dump(-1===$chargeStatus);
        var_dump($data);
    }


    /**
     * @param $startTime
     * @param $stopTime
     * @param $power
     * @return array
     */
    private function formatPower($startTime,$stopTime,$power){


        //var_dump($indexHalfMinute);
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
        if(array_key_exists(count($halfHourPower)-2,$halfHourPower)){

            $halfHourPower[count($halfHourPower)-1]['duration']= $stopTime - $halfHourPower[count($halfHourPower)-1]['time'] ;
        }

        return $halfHourPower;
    }
    /**
     * 格式化电量数据
     * @param $startTime
     * @param $endTime
     * @param array $powerOfTimes
     * @return array
     */
    private function formatPowerOfTime($startTime, $endTime, array $powerOfTimes)
    {


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



    public function startCharge(){


        Log::debug(__NAMESPACE__ . "/" . __FUNCTION__ . "@" . __LINE__ );

    }







}

