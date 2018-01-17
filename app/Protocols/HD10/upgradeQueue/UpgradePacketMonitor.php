<?php

namespace Wormhole\Protocols\HD10\upgradeQueue;

//use Wormhole\Podcast;
//use Wormhole\AudioProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Wormhole\Protocols\HD10\Models\Upgrade;
use Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\MonitorServer;

use Wormhole\Protocols\HD10\Controllers\EvseController;

class UpgradePacketMonitor implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $monitorCode;
    protected $packageNumber;


    public function __construct($monitorEvseCode, $packageNumber)
    {
        $this->monitorCode = $monitorEvseCode;
        $this->packageNumber = $packageNumber;
    }

    public function handle()
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级数据包,进入监控  packageNumber:$this->packageNumber");
        //通过monitor和包序号判断是否收到响应
        $upgradeInfo = Upgrade::where('monitor_code',$this->monitorCode)->firstOrFail();
        $task_id = $upgradeInfo->task_id;
        $monitorCode = $upgradeInfo->monitor_code;
        //$packageNumber = $upgradeInfo->package_number; //包序号
        $packageNumber = $this->packageNumber; //包序号
        //$isSuccess = json_decode($upgradeInfo['is_success']); //下发状态
        $isSuccess = json_decode($upgradeInfo->is_success); //下发状态
        //0未收到响应,继续执行第一步,并且记录已经失败了几次，如果3次，则告诉monitor
        if($isSuccess[$packageNumber] == 0 || $isSuccess[$packageNumber] == 5){
            if($upgradeInfo['failure_times'] == 3){
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级数据包,失败超过3次, 包序号:$packageNumber ");
                //3次失败,把升级状态更改为结束
                $upgradeInfo->upgrade_state = 2; //失败超过3次,此桩升级结束
                $upgradeInfo->failure_times = 0;  //失败次数清0
                $upgradeInfo->package_number = 0;
                $upgradeInfo->save();

                //升级失败调monitor接口
                $res = MonitorServer::update_evse_upgrade_type($task_id,$monitorCode,4);
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "升级数据包,失败超过3次, 调用monitor结果res:$res ");
                //判断时候还有没升级的桩,有的话进行升级 EvseController
                $evse = new EvseController;
                $evse->upgradeInfo();

            }else{
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "监控,升级数据包,未收到响应, 包序号:$packageNumber ");
                //失败加一
                $upgradeInfo->failure_times++;
                $upgradeInfo->package_number = 0;
                $upgradeInfo->save();
                //dispatch(new FileInformation());
                $job = (new FileInformation())
                    ->onQueue(env('APP_KEY'));
                dispatch($job);
            }


        }

    }
}