<?php
/**
 * Created by PhpStorm.
 * User: lingfengchen
 * Date: 2017/1/22
 * Time: 下午11:18
 */

namespace Wormhole\Protocols\NJINT\Controllers;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Wormhole\Http\Controllers\Controller;
use Wormhole\Protocols\MonitorServer;
use Wormhole\Protocols\NJINT\Models\Port;

class EvseController extends Controller
{

    public function healthCheck(){
        $now = Carbon::now();
        $condition =DB::raw(
           "UNIX_TIMESTAMP(last_update_status_time)+  3 * heartbeat_period  < $now->timestamp"
        );

        $ports = Port::where($condition)->get();

        $updateData = [];
        foreach ($ports as $port){
            $updateData[] = ['id',$port->id];
        }

        $updateResult = Port::where($updateData)->update(['online'=>0,'last_update_status_time'=>Carbon::now()]);


        foreach ($ports as $port){
            MonitorServer::updateEvseStatus($port->monitor_code,FALSE,$port->charge_status);
        }

    }

}