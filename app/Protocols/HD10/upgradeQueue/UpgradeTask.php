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
class UpgradeTask implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;


    public function __construct($taskId)
    {
        $this->taskId = $taskId;
    }

    public function handle()
    {

        $evse = new EvseController();
        $upgradeInfo = $evse->upgradeTask($this->taskId);


    }
}