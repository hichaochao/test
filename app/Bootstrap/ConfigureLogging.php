<?php
namespace Wormhole\Bootstrap;

/**
 * Created by PhpStorm.
 * User: lingfeng.chen
 * Date: 2017/2/14
 * Time: 下午4:25
 */
use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseConfigureLogging;
use Illuminate\Support\Facades\Config;
use Monolog\Handler\LogEntriesHandler;
use Monolog\Processor\TagProcessor;
use Ramsey\Uuid\Uuid;

class ConfigureLogging extends BaseConfigureLogging {

    /**
     * Custom Monolog handler that for Logentries.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureCustomHandler(Application $app, Writer $log)
    {

//        $log->getMonolog()->pushProcessor(
//            new TagProcessor(
//                array(
//                    'request_id' => Uuid::uuid4()->toString()
//                )
//            )
//        );
        // Also Log to Dayily files too.
        $protocol = $app->config['gateway.gateway.protocol'];
        $protocolName  = $protocol::NAME;
        $log->useDailyFiles($app->basePath()."/storage/logs/$protocolName.log", env('APP_LOG_FILES'),env('APP_LOG_LEVEL'));
    }

}