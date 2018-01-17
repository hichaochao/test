<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHd10EvsesTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hd10_evses', function (Blueprint $table) {
            $table->uuid('id',32)->comment('充电桩ID');
            $table->string('code',8)->unique()->comment('充电桩编号');
            $table->timestamp('production_time')->nullable()-> comment('生产时间');
            $table->string('worker_id',20)->default('')->comment('worker的ID');

            $table->string('protocol_name',20)->default('')->comment('充电枪所用协议名称');
            $table->tinyInteger('version')->unsigned()->default(0)->comment('版本号');
            $table->tinyInteger('port_type')->unsigned()->default(1)->comment('充电枪类型：1：直流 ；2、交流 ');

            $table->string('monitor_code',32)->default('')->comment('monitor server的充电桩编号');

            //启动数据
            $table->uuid('order_id')->default("")->commnet('monitor订单id');
            $table->integer('evse_order_id')->unsigned()->default(0)->commnet('桩一次充电的用户信息标识');
            $table->boolean('is_billing')->default(0)->comment('是否计费:0计费,1不计费');


            $table->tinyInteger('start_type',FALSE,TRUE)->default(0)->comment('启动模式：0、立即；1、定时');
            $table->integer('start_args',FALSE,TRUE)->default(0)->comment('启动模式参数：立即：无效；定时：时间，单位：s');
            $table->tinyInteger('charge_type',FALSE,TRUE)->default(0)->comment('充电策略：0：充满、1：时长充电、2电量、3金额');
            $table->integer('charge_args',FALSE,TRUE)->default(0)->comment('充电参数：
                                                                                //充满模式：无效
                                                                                //时长模式：时间，单位秒；
                                                                                //电量模式：电量，单位：0.01度，精确到：0.01度；
                                                                                //金额模式：钱数，单位：分，精确到：0.01元；');
            $table->integer('user_balance',FALSE,TRUE)->default(0)->comment('用户余额：单位：分');
            //$table->string('charge_user_card',36)->default("")->commnet('充电用户卡号');

            $table->timestamp('last_operator_time')->nullable()->comment('最后操作时间:预约,自检,启动充电、停止充电、停止充电成功');
            $table->tinyInteger('last_operator_status')->default(0)->comment('最后操作状态： 
                                                                            0、预约中
                                                                            1、预约成功
                                                                            2、    失败
                                                                            3、自检中
                                                                            4、自检成功
                                                                            5、    失败
                                                                            6、启动充电中
                                                                            7、启动充电成功
                                                                            8、    失败、
                                                                            9、停止充电中
                                                                            10、停止充电成功
                                                                            11、    失败');



            //状态信息
            $table->tinyInteger('online',FALSE,TRUE)->default(0)->comment('是否联网');

            $table->tinyInteger('is_charging')->default(0)->comment('充电状态:0,空闲；1，充电中');
            $table->integer('car_connect_status')->default(0)->comment('车辆连接状态:0,未连接；1，已连接');
            $table->integer('warning_status')->default(1)->comment('0:急停状态:1：正常；2，过压；3，过流；4，漏电；5，急停；6，拔枪；7，插抢');
            $table->timestamp('last_update_status_time')->nullable()->comment('最后更新充电状态时间');


            //临时信息

            $table->timestamp('start_time')->nullable()->comment('启动时间');
            $table->integer('charged_power')->default(0)->comment('实时充电电量,单位:wh');
            $table->integer('duration')->default(0)->comment('实时充电时长,单位:s');
            $table->integer('fee')->default(0)->comment('实时充电金额,单位:分');
            $table->integer('voltage')->default(0)->comment('实时充电电压,单位:mV');
            $table->integer('current')->default(0)->comment('实时充电电流,单位:mA');
            $table->integer('power')->default(0)->comment('实时功率,单位:w');
            $table->tinyInteger('last_stop_reason')->unsigned()->default(0)->comment('最后停止原因：
                                                                           0、未知
                                                                           1、正常结束
                                                                           2、过压结束
                                                                           3、过流结束
                                                                           4、漏电结束
                                                                           5、急停结束
                                                                           6、拔枪结束
                                                                           7、断线补偿      
            ');

            $table->timestamp('last_update_charge_info_time')->nullable()->comment('最后更新充电信息的时间');


            //桩自身参数
            $table->tinyInteger('heartbeat_period')->unsigned()->default(60)->comment('心跳上报周期，单位：s');

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
        Schema::dropIfExists('hd10_evses');
    }
}
