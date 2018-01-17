<?php

namespace Wormhole\Protocols\HD10\upgradeQueue;

use Illuminate\Support\Facades\Log;
//use Wormhole\Podcast;
//use Wormhole\AudioProcessor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Wormhole\Protocols\HD10\Models\UpgradeFileInfo;
use Wormhole\Protocols\HD10\Models\Upgrade;
use Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\upgradeQueue\FileInformationMoniotr;

use Wormhole\Protocols\HD10\EventsApi;

use wormhole\Protocols\HD10\Controllers\EvseController;
use Wormhole\Protocols\HD10\upgradeQueue\UpdateConfirmation;
use Wormhole\Protocols\MonitorServer;
class UpdateConfirmationMonitor implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $monitorCode;


    public function __construct($monitorCode)
    {
        $this->monitorCode = $monitorCode;
    }

    public function handle()
    {
        Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " 更新确认监控start ");
        //通过monitor和包序号判断是否收到响应
        $upgradeInfo = Upgrade::where('monitor_code',$this->monitorCode)->firstOrFail();
        $task_id = $upgradeInfo->monitor_task_id;
        $monitorCode = $upgradeInfo->monitor_code;
        $confirmStatus = $upgradeInfo->confirm_status; //更新确认下发状态
        //0未收到响应,继续执行第一步,并且记录已经失败了几次，如果3次，则告诉monitor
        if($confirmStatus == 0 || $confirmStatus == 2){
            if($upgradeInfo->failure_times == 3){
                $upgradeInfo->failure_times = 0; //失败次数清0
                $upgradeInfo->save();

                //升级失败调monitor接口
                $res = MonitorServer::update_evse_upgrade_type($task_id,$monitorCode,4);
                Log::debug(__NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . "更新确认,失败超过3次, 调用monitor结果res:$res ");
                //判断时候还有没升级的桩,有的话进行升级 EvseController
                $evse = new EvseController;
                $evse->upgradeInfo();

                //TODO调monitor接口
                //升级失败调monitor接口
                //MonitorServer::update_evse_upgrade_type($task_id,$monitorCode,4);
                //return ;
            }else{
                //失败加一
                //$upgradeInfo->upgrade_state = 0;
                $upgradeInfo->package_number = 0;
                $upgradeInfo->failure_times++;
                $upgradeInfo->save();
                //dispatch(new UpdateConfirmation($this->monitorCode));
                $job = (new UpdateConfirmation($this->monitorCode))
                    ->onQueue(env('APP_KEY'));
                dispatch($job);
            }


        }


    }
}