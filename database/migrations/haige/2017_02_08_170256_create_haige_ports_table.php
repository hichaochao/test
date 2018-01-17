<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHaiGePortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('haige_ports', function (Blueprint $table) {

            $table->string('id',40)->default("")->commnet('枪ID');  //gun_id
            $table->string('evse_id',40)->default("")->commnet('充电桩ID');
            $table->string('evse_code',32)->default("")->commnet('充电桩编号');
            $table->integer('port_number')->unsigned()->comment('枪口号');


            //启动数据
            $table->string('order_id',36)->nullable()->commnet('充电id');
            $table->string('evse_order_id',36)->nullable()->default("")->commnet('用卡号／用户标识，本次充电的唯一标识，由协议生成');
            $table->string('monitor_evse_code',36)->commnet('monitor充电桩编号');
            //$table->string('user_id',32)->default("")->commnet('充电桩临时用户id');
            $table->tinyInteger('start_type',FALSE,TRUE)->default(0)->comment('启动模式：0、立即；1、定时');
            $table->integer('start_args',FALSE,TRUE)->default(0)->comment('充电参数：对应于策略的，充满：无效；时长：时间长度');
            $table->tinyInteger('charge_type',FALSE,TRUE)->default(0)->comment('充电策略：0：充满、1：时长充电、2电量、3金额');
            $table->tinyInteger('charge_args',FALSE,TRUE)->default(0)->comment('充电参数：
                                                                                //充满模式：无效
                                                                                //时长模式：时间，单位秒；
                                                                                //电量模式：电量，单位：度，精确到：0.01度；
                                                                                //金额模式：钱数，单位：元，精确到：0.01元；');



            //状态数据
            $table->tinyInteger('task_status')->unsigned()->default(0)->comment('任务状态：0，无；1、启动充电中；2、停止充电中');
            $table->integer('charge_status')->unsigned()->nullable()->default(0)->comment('充电桩充电状态：0 空闲、1、充电、2预约');
            $table->integer('port_status')->unsigned()->nullable()->default(0)->comment('充电桩枪连接状态：0未连接；1正常；2正常但BMS不正常');


            //实时充电数据
            $table->timestamp('start_chrge_time')->nullable()->comment('启动时间');
            $table->timestamp('end_chrge_time')->nullable()->comment('停止时间');
            $table->timestamp('reservation_start_charge_time')->nullable()->comment('预约启动时间');
            $table->integer('power')->unsigned()->nullable()->default(0)->comment('充电桩实时功率');
            $table->integer('charged_power')->unsigned()->default(0)->comment('充电桩实时电量');
            $table->integer('charge_money')->unsigned()->default(0)->comment('充电桩实时金额');
            $table->integer('duration')->unsigned()->nullable()->default(0)->comment('充电桩实时时长');
            $table->integer('voltage')->unsigned()->nullable()->default(0)->comment('充电桩实时电压');
            $table->integer('electric_current')->unsigned()->nullable()->default(0)->comment('充电桩实时电流');
            $table->integer('left_time')->unsigned()->nullable()->default(0)->comment('充电剩余时长');
            $table->integer('emergency_status')->unsigned()->nullable()->default(0)->comment('充电桩急停状态');
            $table->integer('warning_status')->unsigned()->nullable()->default(0)->comment('充电桩告警状态');
            $table->integer('net_status')->unsigned()->nullable()->default(0)->comment('充电桩联网状态');
            $table->timestamp('last_update_status_time')->nullable()->comment('最后更新充电状态时间');
            $table->integer('start_soc')->unsigned()->default(0)->nullable()->comment('开始soc 1~100');
            $table->integer('current_soc')->unsigned()->default(0)->nullable()->comment('当前soc 1~100');
            $table->timestamp('last_operator_time')->nullable()->comment('最后操作时间:预约');
            //$table->dateTime('deleted_at')->comment('最后操作时间:预约');




            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('haige_ports');
    }
}
