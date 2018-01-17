<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 18:04
 */

namespace Wormhole\Validators;


use Illuminate\Support\Facades\Validator;

class GetStatusValidator extends Validator
{


    public function rule(){
        return [

            'monitor_code'   =>'required|string|max:36',         //设备编号
            'start_time'     => 'required|numeric',   //开始时间
            'date_type'      => 'required|string|required_without:year,month,day,hour,week'
            //'history_statistics'   =>'required|array|max:36',   //充电用户统计参数
            //'history_statistics.start_time' => 'required|numeric', //开始时间  after:tomorrow
            //'history_statistics.start_time' => 'required|date|after:2017-05-21 16:13:17',   //开始时间
            //'history_statistics.date_type' => 'required|string|required_without:year,month,day,hour,week'    //时间类型
        ];


    }
    public function message(){
        return [


        ];
    }

    public function make($data){
        return Validator::make($data,$this->rule(),$this->message());
    }
}
