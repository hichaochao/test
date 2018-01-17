<?php

namespace Wormhole\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Wormhole\Console\Commands\Gateway::class,
        \Wormhole\Console\Commands\Worker::class,
        \Wormhole\Console\Commands\Register::class,
        \Wormhole\Console\Commands\GatewayAll::class,
        \Wormhole\Console\Commands\EvseHealthCheck::class,
        \Wormhole\Console\Commands\WormholeQueue::class

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
