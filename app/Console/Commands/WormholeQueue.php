<?php

namespace Wormhole\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WormholeQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wormhole:queue {connection? : The name of connection} 
                            {--daemon : Run the worker in daemon mode (Deprecated)}
                            {--once : Only process the next job on the queue}
                            {--delay= : Amount of time to delay failed jobs,default =0}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory= : The memory limit in megabytes,default =128}
                            {--sleep= : Number of seconds to sleep when no job is available,default = 3}
                            {--timeout= : The number of seconds a child process can run,default : 60}
                            {--tries= : Number of times to attempt a job before logging it failed,default : 0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '在基础queue上增加一层，实现自动获取当前app_key，并监听/处理;
                               除了 --queue 不需要设置外，其他同 queue
                                ';

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


        $daemon= $this->option('daemon');
        $once= $this->option('once');
        $delay= $this->option('delay');
        $force= $this->option('force');
        $memory= $this->option('memory');
        $sleep = $this->option('sleep');
        $timeout= $this->option('timeout');
        $tries= $this->option('tries');
        $key = env('APP_KEY');
        $cmd = "php artisan queue:work --queue=".$key;

        $cmd .= TRUE === $daemon ? " --daemon":"";
        $cmd .= TRUE === $once ? " --once":"";
        $cmd .= !is_null( $delay) ? " --delay=$delay":"";
        $cmd .= TRUE === $force ? " --force":"";
        $cmd .= !is_null($memory) ? " --memory=$memory":"";
        $cmd .= !is_null($sleep) ? " --sleep=$sleep":"";
        $cmd .= !is_null($timeout) ? " --timeout=$timeout":"";
        $cmd .= !is_null($tries) ? " --tries=$tries":"";

        echo "Running command : ".$cmd . PHP_EOL;
        Log::info(__CLASS__."/".__FUNCTION__."@".__LINE__. PHP_EOL ."Running command : ".$cmd . PHP_EOL);
        exec($cmd);

    }
}
