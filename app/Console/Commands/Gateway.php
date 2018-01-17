<?php
namespace Wormhole\Console\Commands;
use Illuminate\Console\Command;
use Workerman\Worker;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-10-01
 * Time: 23:10
 */
class Gateway extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway {opt} {--d}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'gateway 
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

        require 'app/Gateways/start_gateway.php';


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