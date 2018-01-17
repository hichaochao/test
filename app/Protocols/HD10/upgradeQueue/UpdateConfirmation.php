<?php

namespace Wormhole\Protocols\HD10\upgradeQueue;

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
class UpdateConfirmation implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $monitorcode;


    public function __construct($monitorCode)
    {
        $this->monitorcode = $monitorCode;
    }

    public function handle()
    {

        $evse = new EvseController();
        $update = $evse->updateConfirmation($this->monitorcode);
        //生成更新确认监控队列,30秒后执行,判断是否收到响应
        $job = (new UpdateConfirmationMonitor($this->monitorcode))
            ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));
            //->onQueue(env('APP_KEY'));
        dispatch($job);

    }
}