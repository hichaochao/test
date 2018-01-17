<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZhPortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zh_ports', function (Blueprint $table) {

            //枪基础数据
            $table->uuid('id')->commnet('枪ID');
            $table->string('evse_id',40)->default("")->commnet('充电桩ID');
            //$table->string('division_code', 10)->default("")->comment('行政区划码');
            //$table->string('terminal_address', 20)->default("")->comment('终端地址');
            $table->string('division_terminal_address', 50)->default("")->comment('行政区划码和终端地址');
            $table->integer('port_number')->unsigned()->comment('充电接口');

            //启动数据
            $table->string('order_id',36)->nullable()->commnet('订单编号');
            $table->integer('evse_order_id')->unsigned()->default(0)->commnet('桩一次充电的用户信息标识');
            $table->string('monitor_evse_code',36)->default("")->commnet('monitor充电桩编号');
            //$table->string('card_num',32)->default("")->commnet('用卡号');
            $table->integer('card_num')->default(1)->commnet('用卡号');
            $table->integer('user_balance',FALSE,TRUE)->default(0)->comment('用户余额：单位：分');
            $table->timestamp('start_time')->nullable()->comment('启动充电时间');
            $table->tinyInteger('start_type',FALSE,TRUE)->default(0)->comment('启动模式：1、按自动；2、按时间 3按电量 4按金额');
            $table->timestamp('last_operator_time')->nullable()->comment('最后操作时间:启动充电, 停止充电, 启动充电成功, 启动充电失败, 停止充电成功, 停止充电成功');
            $table->tinyInteger('last_operator_status')->default(0)->comment('最后操作状态:
                                                                            1、启动充电中
                                                                            2、启动充电成功
                                                                            3、启动充电失败
                                                                            4、停止充电中
                                                                            5、停止充电成功
                                                                            6、停止充电失败');



            //状态数据
            //$table->integer('port_status')->unsigned()->nullable()->default(0)->comment('充电桩枪连接状态：0未连接；1正常；2正常但BMS不正常');
            $table->tinyInteger('work_status')->unsigned()->default(0)->comment('工作状态 0/空闲 1/启动中 2/充电中 3/启动失败 4/停止中 5/停止失败');
            $table->tinyInteger('port_status')->unsigned()->default(0)->comment('枪口状态 0/正常 1/故障 ');


            //充电桩充电接口实时上传数据
            //$table->integer('left_time')->unsigned()->nullable()->default(0)->comment('充电剩余时长');
            $table->timestamp('real_time_data')->nullable()->comment('实时数据上传时间');
            $table->integer('output_voltage')->unsigned()->nullable()->default(0)->comment('充电接口输出电压');
            $table->integer('output_current')->unsigned()->nullable()->default(0)->comment('充电接口输出电流');
            $table->integer('total_power')->unsigned()->default(0)->comment('充电接口总电量');
            $table->integer('rate_one_power')->unsigned()->default(0)->comment('充电接口费率1电量');
            $table->integer('rate_two_power')->unsigned()->default(0)->comment('充电接口费率2电量');
            $table->integer('rate_three_power')->unsigned()->default(0)->comment('充电接口费率3电量');
            $table->integer('rate_four_power')->unsigned()->default(0)->comment('充电接口费率4电量');
            $table->integer('ammeter_degree')->unsigned()->default(0)->comment('电能表读数');


            //预约信息
            $table->timestamp('appointment_time')->nullable()->comment('预约时间');
            $table->timestamp('cancel_appointment_time')->nullable()->comment('取消预约时间');
            $table->tinyInteger('appointment_status')->unsigned()->default(0)->comment('预约状态 0/未预约 1/预约中 2/取消预约 3/取消预约中');

            //二维码
//            $table->string('code_type',5)->default("")->commnet('二维码类别');
//            $table->string('code_length',5)->default("")->commnet('二维码长度');
//            $table->string('code_content',5)->default("")->commnet('二维码内容');


            //BMS异常信息
//            $table->timestamp('alarm_date')->nullable()->comment('告警时间');
//            $table->tinyInteger('exception_information_code',FALSE,TRUE)->default(0)->comment('BMS 异常信息编码 ');
//            $table->tinyInteger('exception_information_type',FALSE,TRUE)->default(0)->comment('异常状态类型  ');

            //停止充电原因
            $table->timestamp('stop_date')->nullable()->comment('停机时间');
            $table->tinyInteger('stop_reason',FALSE,TRUE)->default(0)->comment('停止充电原因');


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
        Schema::dropIfExists('zh_ports');
    }
}
