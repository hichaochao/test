<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-09
 * Time: 15:52
 */

namespace Wormhole\Console\Commands;

use Illuminate\Console\Command;

use Wormhole\Protocols\Licence;

class GatewayAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gatewayAll {opt} {--d}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'gatewayAll 
                             {opt : start|stop|restart|reload|status|kill}
                             {--d : run as daemon}  ';

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

        // 加载所有Applications/*/start.php，以便启动所有服务
        foreach(glob('app/Gateways/start*.php') as $start_file)
        {
            require_once $start_file;
        }

        $opt = $this->argument('opt');
        $isDaemon = $this->option('d');
        global $argv;
        $argv[1] =  $opt;
        if( TRUE == $isDaemon){
            $argv[2] = '-d';
        }


        \Workerman\Worker::$logFile = app_path().'/../storage/logs/workerman.log';
        \Workerman\Worker::runAll();
    }

}