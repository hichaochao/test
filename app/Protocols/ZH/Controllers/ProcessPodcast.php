<?php

namespace Wormhole\Http\Controllers;

//use Wormhole\Podcast;
//use Wormhole\AudioProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessPodcast implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $podcast;


    public function __construct($podcast)
    {
        $this->podcast = $podcast;
    }

    public function handle()
    {
        sleep(4);
        echo $this->podcast."\t".date("Y-m-d H:i:s")."\n";
        $this->delete();
    }
}