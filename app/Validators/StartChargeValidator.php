<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 18:04
 */

namespace Wormhole\Validators;


use Illuminate\Support\Facades\Validator;

class StartChargeValidator extends Validator
{


    public function rule(){
        return [

            'order_id'   =>'required|string|max:36',         //订单标识
            //'evse_code' => 'required|string|max:64',             //monitor的充电桩编号
            //'user_id'   => 'required|string|max:36',
            'start_type' => 'required|integer|max:2',          //充电启动模式：0、立即充电；1、定时充电；
            'start_args' => 'required|integer|max:65535',          //充电启动参数：
                                                                                //立即充电：无效
                                                                                //定时充电：时间，单位s
            'charge_type' => 'required|integer|max:4',         //充电模式：0 充满，1 按时长 ，2：电量，3：金额
            'charge_args' => 'required|numeric',                //充电参数：
                                                                                //充满模式：无效
                                                                                //时长模式：时间，单位秒；
                                                                                //电量模式：电量，单位：度，精确到：0.01度；
                                                                                //金额模式：钱数，单位：元，精确到：0.01元；
            'user_balance' => 'required|numeric',               //用户余额,单位：元，精确到： 0.01元

        ];
    }
    public function message(){
        return [
            //'params.evse_code' => '需要monitor的充电桩编号',
            //'params.start_type' => '',
            //'params.charge_type' => '',
            //'params.charge_args' => '',
            //'params.user_id' => '',
            //'params.user_balance' => '',

        ];
    }

    public function make($data){
        return Validator::make($data,$this->rule(),$this->message());
    }
}
