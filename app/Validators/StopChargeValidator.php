<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 18:04
 */

namespace Wormhole\Validators;


use Illuminate\Support\Facades\Validator;

class StopChargeValidator extends Validator
{


    public function rule(){
        return [

            'order_id'   =>'required|string|max:36',         //订单标识

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
