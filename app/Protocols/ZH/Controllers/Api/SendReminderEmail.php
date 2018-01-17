<?php

namespace Wormhole\Protocols\HD10\Controllers\Api;

use Wormhole\Protocols\HD10\Controllers\Api\ProcessPodcast;
use Illuminate\Http\Request;
use Wormhole\Http\Controllers\Controller;

///use Illuminate\Http\Request;
use Illuminate\Database\Query;
class SendReminderEmail extends Controller
{
    public function store(Request $request)
    {

        for($i = 0; $i < 50; $i ++) {
            //Queue::push(new ProcessPodcast("ssss".$i));
            dispatch(new ProcessPodcast("ssss".$i));
        }

        echo 123;

    }
}
