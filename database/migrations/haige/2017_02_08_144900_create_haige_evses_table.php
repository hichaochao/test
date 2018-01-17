<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHaiGeEvsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('haige_evses', function (Blueprint $table) {


            //桩基础信息
            $table->uuid('id')->comment('充电桩ID');  //evse_id

            $table->string('name', 255)->default("")->comment('充电桩名称'); //evse_name

            $table->string('code', 32)->default("")->comment('充电桩编号'); //evse_code

            $table->timestamp('production_time')->nullable()->commnet('生产时间');

            $table->tinyInteger('is_register')->unsigned()->default(0)->commnet('是否注册');
            $table->tinyInteger('response_code')->unsigned()->default(0)->commnet('响应码');

            $table->string('carriers', 4)->default("")->comment('运营商');
            $table->string('worker_id', 64)->default("")->comment('worker_id');
            $table->string('protocol_name', 64)->default("")->comment('充电枪所用协议名称');


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('haige_evses');
    }
}
