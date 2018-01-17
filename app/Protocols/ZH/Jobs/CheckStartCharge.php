<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-10
 * Time: 14:36
 */

namespace Wormhole\Protocols\ZH\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Wormhole\Protocols\ZH\Controllers\EvseController;
class CheckStartCharge implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string 充电桩id
     */
    protected $id;
    /**
     * @var string 订单id
     */
    protected $orderId;
    /**
     * @var int
     */
    protected $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id,$orderId,$status)
    {

        $this->id = $id;
        $this->orderId = $orderId;
        $this->status = $status;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $evseController = new EvseController();
        $evseController->checkStartCharge($this->id,$this->orderId,$this->status);

    }
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        $this->job->delete();
        // Send user notification of failure, etc...
    }


}