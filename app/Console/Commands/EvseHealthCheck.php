<?php

namespace Wormhole\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class EvseHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evse:healthCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用于检测充电桩状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $protocol = Config::get('gateway.gateway.protocol');
        $index = strrpos($protocol,"\\");
        $namespace = substr($protocol,0,$index);
        $controller = "$namespace\\Controllers\\EvseController";
        $controller = new $controller;

        $controller->healthCheck();


    }
}
