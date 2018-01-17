<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHd10UpgradeTaskTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hd10_upgrade_task', function (Blueprint $table) {
            $table->uuid('id',36)->comment('升级任务表ID');
            $table->string('code',500)->comment('monitor充电桩编号');
            $table->string('file_id',200)->comment('文件id');
            $table->string('task_id',32)->default('')->comment('任务id');
            $table->timestamp('start_date')->nullable()->comment('开始升级时间');
            $table->tinyInteger('status')->default(0)->comment('任务状态 0未执行/1执行中/2已取消');
            $table->integer('packet_size',FALSE,TRUE)->default(0)->comment('单数据包长度');

            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hd10_upgrade_task');
    }
}
