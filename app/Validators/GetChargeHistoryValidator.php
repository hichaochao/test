<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-29
 * Time: 18:04
 */

namespace Wormhole\Validators;


use Illuminate\Support\Facades\Validator;

class GetChargeHistoryValidator extends Validator
{


    public function rule(){
        return [

            'monitor_code'   =>'required|string|max:11', //设备编号
            'page_now'  => 'integer',            //第几页
            'limit'     => 'integer',             //显示几条数据
            'sort_by'  => 'string',              //排序字段
            'sort_type' => 'string'              //排序规则

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
