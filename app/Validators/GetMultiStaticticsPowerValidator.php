<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 18:04
 */

namespace Wormhole\Validators;


use Illuminate\Support\Facades\Validator;

class GetMultiStaticticsPowerValidator extends Validator
{


    public function rule(){
        return [

            'monitor_codes'   =>'required|array',         //设备编号


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
