<?php
namespace  Wormhole\Validators;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-10-12
 * Time: 18:17
 */

use Illuminate\Support\Facades\Validator;
class SendCmdValidator extends Validator
{
    public function rule(){
        return [
                    'params.client_id' => 'required|string|max:64',
                    'params.frame' => 'required|string',
                    //'params.server_address' => 'sometimes|required|url',

        ];
    }

    public function make($data){
        return Validator::make($data,$this->rule());
    }
}