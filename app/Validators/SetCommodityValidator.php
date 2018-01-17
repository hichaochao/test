<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 18:04
 */

namespace Wormhole\Validators;


use Illuminate\Support\Facades\Validator;

class SetCommodityValidator extends Validator
{


    public function rule(){
        return [

            'token'   =>'required|string|max:36',       //费率编号
            'monitor_codes' => 'required|array',
            'commodity' => 'required|array',
            'service'  => 'required|array',

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
