<?php

return [
    "host"=>"localhost:8886", //monitor 地址

    "pubHost"=>"http://112.74.22.51:8887",

    //token有效性验证
    "validateTokenAPI"=>"/api/mni/api/validate_control_token/hash",

    //创建充电桩
    "createEvseBatchAPI"=>"/api/mni/api/add_evses/hash",
    //更新桩状态
    "updateEvseStatusAPI"=>"/api/mni/api/update_evse_status/hash",
    //启动充电响应
    "startChargeSuccess"=>'/api/mni/api/start_charge_success/hash', //成功
    "startChargeFailed"=>'/api/mni/api/start_charge_failed/hash', //失败

    //停止充电响应
    "stopChargeSuccess"=>'/api/mni/api/stop_charge_success/hash', //成功
    "stopChargeFailed"=>'/api/mni/api/stop_charge_failed/hash', //失败

    //告警上报
    "newAlarm" =>'/api/mni/api/stop_cupload_alarm_info/hash', //

    //充电记录上报
    "newChargeRecord" =>'/api/mni/api/upload_charge_log/hash', //正常
    "newChargeRecordHotSwap" =>'/api/mni/api/upload_charge_log_only_create_order/hash', //即插即用

    //刷卡
    "swipeCardEvent" => '/api/mni/api/card_start_charge_event/hash',  //刷卡，桩自身启动充电
    "swipeCardToStartCharge" => '/api/mni/api/verify_card/hash',              //刷卡鉴权，启动充电

    
    
    //升级状态接口
    "upgradeStatus"=>'/api/mni/api/update_evse_upgrade_type/hash',

    //获取文件接口
    "getFile"=>'/api/pub/api/get_file/hash',









];