<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHd10UpgradeTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hd10_upgrade', function (Blueprint $table) {
            $table->uuid('id',32)->comment('升级表ID');
            //$table->text('file_id')->comment('包序号对应的文件id');
            $table->string('file_id',50)->comment('文件id');
            $table->string('monitor_task_id',50)->comment('monitor任务id');
            $table->string('code',8)->comment('充电桩编号');
            $table->string('evse_id',32)->default('')->comment('充电桩ID');
            $table->string('monitor_code',32)->default('')->comment('monitor server的充电桩编号');
            $table->tinyInteger('upgrade_state')->default(0)->comment('升级状态:0等待 1进行中 2结束');
            $table->integer('task_id',FALSE,TRUE)->default(0)->comment('任务id');
            $table->integer('package_number',FALSE,TRUE)->default(0)->comment('包序号');
            $table->integer('file_size',FALSE,TRUE)->default(0)->comment('文件大小');
            $table->integer('packet_number',FALSE,TRUE)->default(0)->comment('数据包总个数');
            $table->integer('check_sum',FALSE,TRUE)->default(0)->comment('校验和');
            $table->tinyInteger('failure_times')->default(0)->comment('失败次数');
            $table->string('is_success',2048)->default(0)->comment('是否成功 json格式化后数据 [0,0,0,0,0....],元素的位置表示第几包
                                                                0:下发升级文件信息未收到响应 
                                                                1:下发升级数据包未收到响应收到响应
                                                                2:收到下发升级文件信息成功 
                                                                3:收到升级数据包响应成功 
                                                                4:收到下发升级信息响应失败 
                                                                5:收到下发升级数据包响应失败');
            $table->tinyInteger('confirm_status')->default(0)->comment('0未收到响应1收到响应成功2收到响应失败');

            $table->timestamps();
            //$table->softDeletes();

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
        Schema::dropIfExists('hd10_upgrade');
    }
}
