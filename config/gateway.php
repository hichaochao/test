<?php

/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-10-05
 * Time: 22:16
 */

//$protocol = "HD10";
//$protocol = "NJINT";
//$protocol = "HaiGe";
//$protocol = "tcp";

$protocol = "ZH";

//协议名称，注意：协议为tcp时，必须手动指定协议名称；（某协议Protocol::NAME
$namespace = "\\Wormhole\\Protocols\\$protocol";

$protocolInstance = $namespace."\\Protocol";
//var_dump($protocolInstance);die;
$protocolName =$protocolInstance::NAME;

$event = "$namespace\\EventsApi";
//$event = "$namespace\\Events";

return [

    //需配置内容：monitor 服务器地址，本机地址和端口， gateway协议和端口，消息对应的协议名称；

    "debug"=>true,



    #region 旧版本，wormhole只做gateway使用的参数


    //监控平台信息
    "monitor_url"=>"http://m.uni.cn:80",  // http://domain:port
    "monitor_api_on_message"=>"/api/mni/api/evse_message/hash/",
    "monitor_api_on_close"=>"/api/mni/api/evse_offline/hash/",

    //协议服务器ip，供monitor调用
    "protocol_ip"=>'10.44.64.18',//10.10.33.238
    //协议服务器http端口，供monitor调用
//    "protocol_port"=>'8889',  // njint
//    "protocol_port"=>'8899',  // 海格
    //"protocol_port"=>'8890',  // 航电
    "protocol_port"=>'8891', //中恒
    #endregion

    //本机信息
    "host"=>"10.10.33.238", // 本机ip地址 10.10.33.238
//    "port"=>'8889',  // njint
//    "port"=>'8899',  // 海格
    //"port"=>'8890',  // 航电
    "port"=>'8891',  // 中恒

    //平台名称，当一个协议拥有不同的运营商是，可以使用改名字进行区分。
    "platform_name"=>'ZH',


    //register
    "register"=>[
        "protocol"=> "text",
        "ip"=>"0.0.0.0",
        "port"=>1238
    ],
    //gateway服务信息
    "gateway"=>[
        "name"=>"gateway",
        "protocol"=>$protocolInstance,

        "ip"=>"0.0.0.0",
        "port"=>8282,    //对外暴露的socket端口，用于和桩进行tcp通信  hd：8202 , haige :8283 int:自定义
        "count"=>4, //线程数
        "lanIp" =>'172.18.0.5',// gateway所在的ip，worker可以通过该ip远程调用gateway
        "startPort"=> 2900,// 通信起始端口；

    ],
    //worker
    "worker"=>[
        "event"=> $event,
        "name"=>"businessWorker",
        "count"=>4, //线程数
    ],

    //以下由开发配置


    "message"=> json_encode([
                        "params"=>[
                            "server_ip"=>"%s",
                            "gateway_port"=>"%u",
                            "client_id"=>"%s",
                            "frame"=>"%s",
                            "sequence"=>"%u",

                            "platform_name"=>'%s',
                            "protocol_ip"=>'%s',
                            "protocol_port"=>'%s',

                            "protocol"=>$protocolName

                        ]
                    ]),
    "offline"=>json_encode([
        "params" => array(
            "server_ip" => "%s",
            "gateway_port" => "%u",
            "client_id" => "%s",
            "protocol"=>$protocolName
        )
    ]),











];
