<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHd10ChargeRecordFramesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hd10_charge_record_frames', function (Blueprint $table) {
            $table->uuid('id',32)->commit("记录id");
            $table->uuid('evse_id')->comment('充电桩ID');
            $table->string('code',36)->comment('充电桩编号');
            $table->string('monitor_code',36)->commnet('monitor充电桩编号');

            $table->text('frame')->commit("充电记录的帧数据");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hd10_charge_record_frames');
    }
}
