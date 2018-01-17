<?php

namespace Wormhole\Protocols\HD10\upgradeQueue;

//use Wormhole\Podcast;
//use Wormhole\AudioProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Wormhole\Protocols\HD10\Models\UpgradeFileInfo;
use Wormhole\Protocols\HD10\Models\Upgrade;
use Wormhole\Protocols\HD10\upgradeQueue\FileInformationMoniotr;
use Wormhole\Protocols\HD10\Protocol\Server\UpgradeDataPack;
use Wormhole\Protocols\HD10\EventsApi;
use Wormhole\Protocols\HD10\upgradeQueue\UpgradePacketMonitor;


use wormhole\Protocols\HD10\Controllers\EvseController;

use Carbon\Carbon;
use Wormhole\Protocols\HD10\Protocol;

class DeviceReset implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $monitorCode;

    protected $taskId;


    public function __construct($monitorCode, $taskId)
    {
        $this->monitorCode = $monitorCode;
        $this->taskId = $taskId;
    }

    public function handle()
    {

        $evse = new EvseController();
        $upgrade = $evse->resetDevice($this->monitorCode, $this->taskId);

    }
}