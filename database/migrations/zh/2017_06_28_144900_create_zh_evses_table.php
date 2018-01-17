<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZhEvsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zh_evses', function (Blueprint $table) {


            //桩基础信息
            $table->uuid('id')->comment('充电桩ID');
            $table->string('version', 6)->default("")->comment('版本号');
            $table->string('protocol_name', 64)->default("")->comment('充电枪所用协议名称');
            $table->tinyInteger('port_num',FALSE,TRUE)->default(0)->comment('充电枪个数');
            $table->string('worker_id', 64)->default("")->comment('worker_id');
            //$table->string('division_code', 10)->default("")->comment('行政区划码');
            //$table->string('terminal_address', 20)->default("")->comment('终端地址');
            $table->string('division_terminal_address', 50)->default("")->comment('行政区划码和终端地址');
            $table->string('master_group_address', 5)->default("")->comment('主站地址和组地址标志');
            $table->tinyInteger('port_type')->unsigned()->default(1)->comment('充电枪类型：1:直流 2:交流 ');
            $table->integer('card_num')->default(1)->commnet('用卡号');

            //状态信息
            $table->tinyInteger('online',FALSE,TRUE)->default(0)->comment('是否联网0未联网 1联网');
            $table->timestamp('last_update_status_time')->nullable()->comment('最后更新充电状态时间');

            //登录信息
            $table->string('auth_password', 50)->default("")->comment('认证密码');

            //充电桩运行状态实时上传
            $table->tinyInteger('evse_type',FALSE,TRUE)->default(0)->comment('充电桩型号 1/直流单枪 2/直流双枪 3/交流单枪 4/交流双枪');
            $table->tinyInteger('run_status',FALSE,TRUE)->default(0)->comment('充电桩运行状态 1/正常 2/故障 3/离线 4/停运');
            $table->integer('max_voltage',FALSE,TRUE)->default(0)->comment('充电桩最大输出电压');
            $table->integer('max_current',FALSE,TRUE)->default(0)->comment('充电桩最大输出电流');
//            $table->tinyInteger('port_a_status',FALSE,TRUE)->default(0)->comment('充电接口 A 状态');
//            $table->tinyInteger('port_b_status',FALSE,TRUE)->default(0)->comment('充电接口 B 状态');
            $table->tinyInteger('communication',FALSE,TRUE)->default(0)->comment('与后台系统通信接口');


            //ip地址和端口
            $table->string('main_ip', 20)->default("")->comment('主ip');
            $table->string('main_port', 5)->default("")->comment('主端口');
            $table->string('spare_ip', 20)->default("")->comment('备用ip');
            $table->string('spare_port', 5)->default("")->comment('备用端口');

            //费率
            $table->string('rate_type', 5)->default("")->comment('费率模式');
            $table->string('total_electricity', 5)->default("")->comment('总费率电价(总电量)');
            $table->string('rate_one', 5)->default("")->comment('费率1电价(尖)');
            $table->string('rate_two', 5)->default("")->comment('费率2电价(峰) ');
            $table->string('rate_three', 5)->default("")->comment('费率3电价(平)');
            $table->string('rate_four', 5)->default("")->comment('费率4电价(谷)');
            $table->string('appointment_rate', 5)->default("")->comment('预约费率');

            //上报周期设置
            $table->tinyInteger('status_frequency',FALSE,TRUE)->default(1)->comment('状态上传频率设置 ');
            $table->tinyInteger('data_frequency',FALSE,TRUE)->default(1)->comment('充电数据上传频率设置');

            $table->tinyInteger('duty_cycle',FALSE,TRUE)->default(1)->comment('占空比数据 10-100');

            //模块基本参数设置
//            $table->tinyInteger('modular_one_num',FALSE,TRUE)->default(0)->comment('模块 1 组个数');
//            $table->tinyInteger('modular_two_num',FALSE,TRUE)->default(0)->comment('模块 2 组个数');
//            $table->integer('voltage_level',FALSE,TRUE)->default(0)->comment('单模块电压等级');
//            $table->integer('current_level',FALSE,TRUE)->default(0)->comment('单模块电流等级');
//            $table->integer('current_limit',FALSE,TRUE)->default(0)->comment('单模块输出电流限制');
//            $table->integer('voltage_cap',FALSE,TRUE)->default(0)->comment('单模块输出电压上限');
//            $table->integer('voltage_lower',FALSE,TRUE)->default(0)->comment('单模块输出电压下限');
//
//            $table->tinyInteger('restart_status',FALSE,TRUE)->default(0)->comment('重启状态');
//            $table->tinyInteger('unlock_status',FALSE,TRUE)->default(0)->comment('解锁状态');

            //告警
//            $table->timestamp('alarm_date')->nullable()->comment('告警时间');
//            $table->tinyInteger('alarm_information_code',FALSE,TRUE)->default(0)->comment('异常信息编码 ');
//            $table->tinyInteger('alarm_information_type',FALSE,TRUE)->default(0)->comment('异常信息类型 ');

            //整流模块异常信息
//            $table->timestamp('modular_alarm_date')->nullable()->comment('告警时间');
//            $table->tinyInteger('modular_no',FALSE,TRUE)->default(0)->comment('整流模块位号 ');
//            $table->tinyInteger('exception_information_type',FALSE,TRUE)->default(0)->comment('整流模块异常信息编码 ');
//            $table->tinyInteger('exception_status_type',FALSE,TRUE)->default(0)->comment('异常状态类型  ');

            //参数变更
            $table->timestamp('parameter_change_tate')->nullable()->comment('参数变更时间');
            $table->tinyInteger('flag',FALSE,TRUE)->default(0)->comment('事件标志 ');
            $table->tinyInteger('element',FALSE,TRUE)->default(0)->comment('变更参数数据单元标识 ');



            //电池档案信息上传
//            $table->tinyInteger('battery',FALSE,TRUE)->default(0)->comment('电池类型 ');
//            $table->integer('battery_capacity',FALSE,TRUE)->default(0)->comment('电池额定容量');
//            $table->integer('battery_total_voltage',FALSE,TRUE)->default(0)->comment('电池额定总电压');
//            $table->string('manufacturer_name',5)->default("")->commnet('电池生产厂商名称');
//            $table->integer('battery_charge_num',FALSE,TRUE)->default(0)->comment('电池充电次数');
//            $table->string('vin',5)->default("")->commnet('车辆识别码(VIN)');






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
        Schema::dropIfExists('zh_evses');
    }
}
