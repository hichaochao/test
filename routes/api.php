<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//
//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:api');


$api = app('Dingo\Api\Routing\Router');


$api->version('v1', ['namespace' => 'Wormhole\Http\Controllers\Api\V1'], function ($api) {
    $api->post('send_cmd/{hash}', [
        'as'   => 'api.gtw.sendCmd',
        'uses' => 'CommonController@sendCmd'
    ]);

    //$api->post('send_cmd',function (){
    //    return ['a'=>'b'];
    //});

    $api->post('binding/{hash}', [
        'as'   => 'api.gtw.binding',
        'uses' => 'CommonController@binding'
    ]);

    $api->any('test/{hash}', [
        'as'   => 'api.test',
        'uses' => 'TestController@test'
    ]);

    $api->post('start_charge/{hash}', [
        'as'   => 'api.startCharge',
        'uses' => 'TestController@startCharge'
    ]);

});

// Header =>  Accept:application/vnd.wormhole.njint+json
$api->version('NJINT', ['namespace' => 'Wormhole\Protocols\NJINT\Controllers\Api',
    'middleware'=>[
        'Wormhole\Http\Middleware\MonitorRequest',
        'Wormhole\Http\Middleware\ReponseFormat']
    ], function ($api) {

        $api->post('start_charge/{hash}', [
            'as'   => 'api.startCharge',
            'uses' => 'EvseController@startCharge'
        ]);
        $api->post('realtime_charge_info/{hash}', [
            'as'   => 'api.realtimeChargeInfo',
            'uses' => 'EvseController@realtimeChargeInfo'
        ]);
        $api->post('stop_charge/{hash}', [
            'as'   => 'api.stopCharge',
            'uses' => 'EvseController@stopCharge'
        ]);

    $api->post('get_status/{hash}', [
        'as'   => 'api.getStatus',
        'uses' => 'EvseController@getStatus'
    ]);

    $api->post('get_multi_statictics_power/{hash}', [
        'as'   => 'api.getMultiStaticticsPower',
        'uses' => 'EvseController@getMultiStaticticsPower'
    ]);

    $api->post('get_charge_history/{hash}', [
        'as'   => 'api.getChargeHistory',
        'uses' => 'EvseController@getChargeHistory'
    ]);


    $api->post('set_commodity/{hash}', [
        'as'   => 'api.setCommodity',
        'uses' => 'EvseController@setCommodity'
    ]);

});

// Header =>  Accept:application/vnd.wormhole.HD10+json
$api->version('HD10', [
        'namespace' => 'Wormhole\Protocols\HD10\Controllers\Api',
        'middleware'=>[
                'Wormhole\Http\Middleware\MonitorRequest',
                'Wormhole\Http\Middleware\ReponseFormat'
        ]
    ], function ($api) {

    $api->post('start_charge/{hash}', [
        'as'   => 'api.gtw.startCharge',
        'uses' => 'EvseController@startCharge'
    ]);

    $api->post('realtime_charge_info/{hash}', [
        'as'   => 'api.realtimeChargeInfo',
        'uses' => 'EvseController@realtimeChargeInfo'
    ]);
    $api->post('stop_charge/{hash}', [
        'as'   => 'api.stopCharge',
        'uses' => 'EvseController@stopCharge'
    ]);


    $api->post('get_status/{hash}', [
        'as'   => 'api.getStatus',
        'uses' => 'EvseController@getStatus'
    ]);

    $api->post('get_multi_statictics_power/{hash}', [
        'as'   => 'api.getMultiStaticticsPower',
        'uses' => 'EvseController@getMultiStaticticsPower'
    ]);

    $api->post('get_charge_history/{hash}', [
        'as'   => 'api.getChargeHistory',
        'uses' => 'EvseController@getChargeHistory'
    ]);


    $api->post('set_commodity/{hash}', [
        'as'   => 'api.setCommodity',
        'uses' => 'EvseController@setCommodity'
    ]);


    $api->any('upgrade/{hash}', [
        'as'   => 'api.upgrade',
        'uses' => 'EvseController@upgrade'
    ]);

    $api->any('cancelUpgrade/{hash}', [
        'as'   => 'api.upgrade',
        'uses' => 'EvseController@cancelUpgrade'
    ]);


    $api->post('test/{hash}', [
        'as'   => 'api.test',
        'uses' => 'EvseController@test'
    ]);



    $api->post('job/{hash}', [
        'as'   => 'api.job',
        'uses' => 'EvseController@job'
    ]);





});


$api->version('HAIGE', ['namespace' => 'Wormhole\Protocols\HaiGe\Controllers\Api',
    'middleware'=>[
        'Wormhole\Http\Middleware\MonitorRequest',
        'Wormhole\Http\Middleware\ReponseFormat']
], function ($api) {
    $api->post('start_charge/{hash}', [
        'as'   => 'api.startCharge',
        'uses' => 'EvseController@startCharge'
    ]);
    $api->post('realtime_charge_info/{hash}', [
        'as'   => 'api.realtimeChargeInfo',
        'uses' => 'EvseController@realtimeChargeInfo'
    ]);
    $api->post('stop_charge/{hash}', [
        'as'   => 'api.stopCharge',
        'uses' => 'EvseController@stopCharge'
    ]);


    $api->post('get_status/{hash}', [
        'as'   => 'api.getStatus',
        'uses' => 'EvseController@getStatus'
    ]);
    $api->post('get_multi_statictics_power/{hash}', [
        'as'   => 'api.getMultiStaticticsPower',
        'uses' => 'EvseController@getMultiStaticticsPower'
    ]);

    $api->post('set_commodity/{hash}', [
        'as'   => 'api.setCommodity',
        'uses' => 'EvseController@setCommodity'
    ]);

    $api->post('get_charge_history/{hash}', [
        'as'   => 'api.getChargeHistory',
        'uses' => 'EvseController@getChargeHistory'
    ]);


    $api->any('test/{hash}', [
        'as'   => 'api.test',
        'uses' => 'EvseController@test'
    ]);



});









$api->version('ZH', ['namespace' => 'Wormhole\Protocols\ZH\Controllers\Api',
    'middleware'=>[
        'Wormhole\Http\Middleware\MonitorRequest',
        'Wormhole\Http\Middleware\ReponseFormat']
], function ($api) {

    $api->post('test/{hash}', [
        'as'   => 'api.test',
        'uses' => 'EvseController@test'
    ]);

    $api->post('start_charge/{hash}', [
        'as'   => 'api.startCharge',
        'uses' => 'EvseController@startCharge'
    ]);

    $api->post('stop_charge/{hash}', [
        'as'   => 'api.stopCharge',
        'uses' => 'EvseController@stopCharge'
    ]);

    $api->post('realtime_charge_info/{hash}', [
        'as'   => 'api.realtimeChargeInfo',
        'uses' => 'EvseController@realtimeChargeInfo'
    ]);

    $api->post('get_status/{hash}', [
        'as'   => 'api.getStatus',
        'uses' => 'EvseController@getStatus'
    ]);

    $api->post('get_charge_history/{hash}', [
        'as'   => 'api.getChargeHistory',
        'uses' => 'EvseController@getChargeHistory'
    ]);

    $api->post('setIpAndPort/{hash}', [
        'as'   => 'api.setIpAndPort',
        'uses' => 'EvseController@setIpAndPort'
    ]);

    $api->post('setRate/{hash}', [
        'as'   => 'api.setRate',
        'uses' => 'EvseController@setRate'
    ]);


    $api->post('setStateFrequency/{hash}', [
        'as'   => 'api.setStateFrequency',
        'uses' => 'EvseController@setStateFrequency'
    ]);


    $api->post('setChargeDataFrequency/{hash}', [
        'as'   => 'api.setChargeDataFrequency',
        'uses' => 'EvseController@setChargeDataFrequency'
    ]);


    $api->post('setPwmDutyRatio/{hash}', [
        'as'   => 'api.setPwmDutyRatio',
        'uses' => 'EvseController@setPwmDutyRatio'
    ]);


    $api->post('setBasicParameter/{hash}', [
        'as'   => 'api.setBasicParameter',
        'uses' => 'EvseController@setBasicParameter'
    ]);


    $api->post('getIpDomain/{hash}', [
        'as'   => 'api.getIpDomain',
        'uses' => 'EvseController@getIpDomain'
    ]);


    $api->post('getRate/{hash}', [
        'as'   => 'api.getRate',
        'uses' => 'EvseController@getRate'
    ]);


    $api->post('getStateFrequency/{hash}', [
        'as'   => 'api.getStateFrequency',
        'uses' => 'EvseController@getStateFrequency'
    ]);


    $api->post('getChargeDataFrequency/{hash}', [
        'as'   => 'api.getChargeDataFrequency',
        'uses' => 'EvseController@getChargeDataFrequency'
    ]);


    $api->post('getPwmDutyRatio/{hash}', [
        'as'   => 'api.getPwmDutyRatio',
        'uses' => 'EvseController@getPwmDutyRatio'
    ]);


    $api->post('getBasicParameter/{hash}', [
        'as'   => 'api.getBasicParameter',
        'uses' => 'EvseController@getBasicParameter'
    ]);


    $api->post('restart/{hash}', [
        'as'   => 'api.restart',
        'uses' => 'EvseController@restart'
    ]);


    $api->post('unlock/{hash}', [
        'as'   => 'api.unlock',
        'uses' => 'EvseController@unlock'
    ]);



    $api->post('calibratTime/{hash}', [
        'as'   => 'api.calibratTime',
        'uses' => 'EvseController@calibratTime'
    ]);


    $api->post('reservationLock/{hash}', [
        'as'   => 'api.reservationLock',
        'uses' => 'EvseController@reservationLock'
    ]);


    $api->post('cancelReservationLock/{hash}', [
        'as'   => 'api.cancelReservationLock',
        'uses' => 'EvseController@cancelReservationLock'
    ]);



    $api->post('ControlCommand/{hash}', [
        'as'   => 'api.ControlCommand',
        'uses' => 'EvseController@ControlCommand'
    ]);


    $api->post('currentTime/{hash}', [
        'as'   => 'api.currentTime',
        'uses' => 'EvseController@currentTime'
    ]);


    $api->post('getBaseData/{hash}', [
        'as'   => 'api.getBaseData',
        'uses' => 'EvseController@getBaseData'
    ]);


    $api->post('getRunStatus/{hash}', [
        'as'   => 'api.getRunStatus',
        'uses' => 'EvseController@getRunStatus'
    ]);


    $api->post('getPortStatus/{hash}', [
        'as'   => 'api.getPortStatus',
        'uses' => 'EvseController@getPortStatus'
    ]);


    $api->post('getBatteryData/{hash}', [
        'as'   => 'api.getBatteryData',
        'uses' => 'EvseController@getBatteryData'
    ]);


    $api->post('getBatteryChargeStatus/{hash}', [
        'as'   => 'api.getBatteryChargeStatus',
        'uses' => 'EvseController@getBatteryChargeStatus'
    ]);


    $api->post('getBatteryTemperature/{hash}', [
        'as'   => 'api.getBatteryTemperature',
        'uses' => 'EvseController@getBatteryTemperature'
    ]);


    $api->post('getBatteryTemperature/{hash}', [
        'as'   => 'api.getBatteryTemperature',
        'uses' => 'EvseController@getBatteryTemperature'
    ]);























});





















