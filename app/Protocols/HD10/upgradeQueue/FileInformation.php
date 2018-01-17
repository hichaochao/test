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

use Wormhole\Protocols\HD10\Controllers\EvseController;

class FileInformation implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $podcast;


    public function __construct()
    {

    }

    public function handle()
    {

        $evse = new EvseController();
        $upgradeInfo = $evse->upgradeInfo();
        //生成监控队列,30秒后执行,判断是否收到响应
        $job = (new FileInformationMoniotr($upgradeInfo['monitorEvseCode'], $upgradeInfo['packageNumber']))
                    ->onQueue(env("APP_KEY"))
                    ->delay(Carbon::now()->addSeconds(Protocol::MAX_TIMEOUT));

        dispatch($job);

    }
}